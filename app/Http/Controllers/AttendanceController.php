<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\AttendanceAbsenceFollowUpNotification;
use App\Notifications\AttendanceLateThresholdNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $selectedDate = Carbon::parse($request->query('date', now()->toDateString()))->toDateString();

        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $employees = Employee::query()
            ->with(['division', 'departmentRecord', 'user'])
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', 'inactive');
            })
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get();

        $recordsForMonth = AttendanceRecord::query()
            ->with(['employee.division'])
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderByDesc('attendance_date')
            ->orderBy('employee_id')
            ->get();

        $recordsForDate = AttendanceRecord::query()
            ->with(['employee.division'])
            ->whereDate('attendance_date', $selectedDate)
            ->orderBy('employee_id')
            ->get();

        $lateCounts = $recordsForMonth
            ->where('status', 'late')
            ->groupBy('employee_id')
            ->map(fn (Collection $rows) => $rows->count());

        $absenceCounts = $recordsForMonth
            ->where('status', 'absent')
            ->groupBy('employee_id')
            ->map(fn (Collection $rows) => $rows->count());

        $warnings = $recordsForMonth
            ->groupBy('employee_id')
            ->map(function (Collection $rows) {
                $latestWarning = $rows->firstWhere('late_warning_sent_at');
                $latestMemo = $rows->firstWhere('memo_flagged_at');
                $latestAbsence = $rows->firstWhere('absence_follow_up_sent_at');

                return [
                    'warning_sent' => (bool) $latestWarning,
                    'memo_flagged' => (bool) $latestMemo,
                    'absence_follow_up_sent' => (bool) $latestAbsence,
                ];
            });

        $employeeRows = $employees->map(function (Employee $employee) use ($lateCounts, $absenceCounts, $warnings, $recordsForDate) {
            $todayRecord = $recordsForDate->firstWhere('employee_id', (string) $employee->emp_no);

            return [
                'employee' => $employee,
                'today_record' => $todayRecord,
                'late_count' => (int) ($lateCounts->get((string) $employee->emp_no) ?? 0),
                'absence_count' => (int) ($absenceCounts->get((string) $employee->emp_no) ?? 0),
                'warning_sent' => (bool) data_get($warnings->get((string) $employee->emp_no), 'warning_sent', false),
                'memo_flagged' => (bool) data_get($warnings->get((string) $employee->emp_no), 'memo_flagged', false),
                'absence_follow_up_sent' => (bool) data_get($warnings->get((string) $employee->emp_no), 'absence_follow_up_sent', false),
            ];
        })->values();

        $summary = [
            'late_today' => $recordsForDate->where('status', 'late')->count(),
            'absent_today' => $recordsForDate->where('status', 'absent')->count(),
            'employees_with_7_or_more_lates' => $employeeRows->where('late_count', '>=', 7)->count(),
            'employees_with_10_or_more_lates' => $employeeRows->where('late_count', '>=', 10)->count(),
        ];

        return view('attendance.index', compact(
            'employees',
            'employeeRows',
            'recordsForMonth',
            'recordsForDate',
            'summary',
            'month',
            'year',
            'selectedDate',
            'monthStart',
            'monthEnd'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'string', 'exists:inventory.employees,emp_no'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', 'in:present,late,absent,leave'],
            'check_in_at' => ['nullable', 'date_format:H:i'],
            'minutes_late' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $record = DB::transaction(function () use ($validated, $request) {
            $record = AttendanceRecord::updateOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'attendance_date' => $validated['attendance_date'],
                ],
                [
                    'status' => $validated['status'],
                    'check_in_at' => $validated['check_in_at'] ?? null,
                    'minutes_late' => $validated['minutes_late'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            $record->loadMissing('employee.user');

            $this->dispatchAttendanceNotifications($record);

            return $record;
        });

        return back()->with('status', 'Attendance record saved for ' . ($record->employee?->full_name ?? $record->employee_id) . '.');
    }

    protected function dispatchAttendanceNotifications(AttendanceRecord $record): void
    {
        $record->loadMissing('employee.user');

        if ($record->status === 'late') {
            $lateCount = AttendanceRecord::query()
                ->where('employee_id', $record->employee_id)
                ->where('status', 'late')
                ->whereBetween('attendance_date', [
                    Carbon::parse($record->attendance_date)->copy()->startOfMonth()->toDateString(),
                    Carbon::parse($record->attendance_date)->copy()->endOfMonth()->toDateString(),
                ])
                ->count();

            if ($lateCount === 7 && ! $record->late_warning_sent_at) {
                $headline = 'Attendance warning: 7 lates reached this month';
                $message = ($record->employee?->full_name ?? $record->employee_id) . ' has reached 7 lates for the month. Please address the attendance habit before it escalates.';

                $this->notifyEmployeeAndHr(
                    $record,
                    new AttendanceLateThresholdNotification(
                        record: $record,
                        lateCount: $lateCount,
                        threshold: 7,
                        headline: $headline,
                        message: $message,
                        kind: 'warning'
                    )
                );

                $record->forceFill(['late_warning_sent_at' => now()])->saveQuietly();
            }

            if ($lateCount === 10 && ! $record->memo_flagged_at) {
                $headline = 'Attendance memo flag: 10 lates reached this month';
                $message = ($record->employee?->full_name ?? $record->employee_id) . ' has reached 10 lates for the month and is now ready for memo action or further HR review.';

                $this->notifyEmployeeAndHr(
                    $record,
                    new AttendanceLateThresholdNotification(
                        record: $record,
                        lateCount: $lateCount,
                        threshold: 10,
                        headline: $headline,
                        message: $message,
                        kind: 'memo'
                    )
                );

                $record->forceFill(['memo_flagged_at' => now()])->saveQuietly();
            }
        }

        if ($record->status === 'absent' && ! $record->absence_follow_up_sent_at) {
            $headline = 'Attendance follow-up: absence recorded';
            $message = ($record->employee?->full_name ?? $record->employee_id) . ' was marked absent on ' . optional($record->attendance_date)->toFormattedDateString() . '. Please confirm whether this should be a filed leave or an unfiled absence.';

            $this->notifyEmployeeAndHr(
                $record,
                new AttendanceAbsenceFollowUpNotification(
                    record: $record,
                    headline: $headline,
                    message: $message
                )
            );

            $record->forceFill(['absence_follow_up_sent_at' => now()])->saveQuietly();
        }
    }

    protected function notifyEmployeeAndHr(AttendanceRecord $record, object $notification): void
    {
        $recipients = $this->attendanceNotificationRecipients($record);

        $recipients->each->notify($notification);
    }

    protected function attendanceNotificationRecipients(AttendanceRecord $record): Collection
    {
        $users = collect();

        if ($record->employee?->relationLoaded('user') && $record->employee->user) {
            $users->push($record->employee->user);
        } elseif ($record->employee) {
            $employeeUser = User::query()->where('emp_no', $record->employee->emp_no)->first();

            if ($employeeUser) {
                $users->push($employeeUser);
            }
        }

        $hrUsers = User::query()
            ->whereHas('role', function ($query) {
                $query->where('slug', 'hr')->orWhere('slug', 'superadmin');
            })
            ->get();

        return $users
            ->merge($hrUsers)
            ->filter()
            ->unique('id')
            ->values();
    }
}
