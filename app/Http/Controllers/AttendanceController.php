<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceHoliday;
use App\Models\Employee;
use App\Models\User;
use App\Services\PhilippineHolidayService;
use App\Notifications\AttendanceAbsenceFollowUpNotification;
use App\Notifications\AttendanceLateThresholdNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function __construct(protected PhilippineHolidayService $philippineHolidayService)
    {
    }

    public function index(Request $request)
    {
        $selectedDate = Carbon::parse($request->query('date', now()->toDateString()))->toDateString();
        $month = (int) Carbon::parse($selectedDate)->month;
        $year = (int) Carbon::parse($selectedDate)->year;
        $schedules = $this->attendanceSchedules();
        $customHolidaysForMonth = AttendanceHoliday::query()
            ->whereBetween('holiday_date', [
                Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString(),
                Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString(),
            ])
            ->orderBy('holiday_date')
            ->get();
        $officialHolidaysForMonth = $this->philippineHolidayService->officialHolidaysForMonth($year, $month);
        $holidaysForMonth = $this->philippineHolidayService->mergeHolidays($officialHolidaysForMonth, $customHolidaysForMonth);
        $selectedHoliday = $this->philippineHolidayService->mergeHolidayForDate(
            $selectedDate,
            AttendanceHoliday::query()->whereDate('holiday_date', $selectedDate)->get()
        );
        $selectedScheduleType = $this->scheduleTypeForDate($selectedDate);
        $selectedSchedule = $schedules[$selectedScheduleType] ?? $this->attendanceSchedule($selectedScheduleType);
        $selectedScheduleLabel = $selectedHoliday
            ? ($selectedHoliday->title . ' (Holiday)')
            : ($selectedSchedule['label'] ?? ucfirst($selectedScheduleType));

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

        // A holiday can be added after attendance was recorded, so derive the
        // displayed schedule from the selected date instead of stale row data.
        $recordsForDate->each(fn (AttendanceRecord $record) => $record->setAttribute('schedule_type', $selectedScheduleType));

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
            'selectedScheduleType',
            'selectedSchedule',
            'selectedScheduleLabel',
            'schedules',
            'holidaysForMonth',
            'officialHolidaysForMonth',
            'customHolidaysForMonth',
            'selectedHoliday',
            'monthStart',
            'monthEnd'
        ));
    }

    public function holidays(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $customHolidays = AttendanceHoliday::query()
            ->whereBetween('holiday_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('holiday_date')
            ->get();

        $officialHolidays = $this->philippineHolidayService->officialHolidaysForMonth($year, $month);
        $holidays = $this->philippineHolidayService->mergeHolidays($officialHolidays, $customHolidays);

        return view('attendance.holidays', compact(
            'holidays',
            'officialHolidays',
            'customHolidays',
            'month',
            'year',
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
            'check_out_at' => ['nullable', 'date_format:H:i'],
            'minutes_late' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (in_array($validated['status'], ['present', 'late'], true) && blank($validated['check_in_at'] ?? null)) {
            return back()
                ->withErrors(['check_in_at' => 'Check-in time is required when the status is Present or Late.'])
                ->withInput();
        }

        $record = DB::transaction(function () use ($validated, $request) {
            $scheduleType = $this->scheduleTypeForDate($validated['attendance_date']);
            $schedule = $this->attendanceSchedule($scheduleType);
            $checkInAt = $this->dateTimeForAttendance($validated['attendance_date'], $validated['check_in_at'] ?? null);
            $checkOutAt = $this->dateTimeForAttendance($validated['attendance_date'], $validated['check_out_at'] ?? null);
            $derivedStatus = $validated['status'];
            $minutesLate = $validated['minutes_late'] ?? null;

            if (filled($checkInAt) && in_array($derivedStatus, ['present', 'late'], true)) {
                $lateCutoff = Carbon::parse($validated['attendance_date'] . ' ' . $this->scheduleLateCutoffTime($scheduleType));
                $checkInTime = Carbon::parse($checkInAt);

                $derivedStatus = $checkInTime->gt($lateCutoff) ? 'late' : 'present';

                if ($derivedStatus === 'late' && $minutesLate === null) {
                    $minutesLate = max(1, abs($checkInTime->diffInMinutes($lateCutoff, false)));
                }
            }

            $record = AttendanceRecord::updateOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'attendance_date' => $validated['attendance_date'],
                ],
                [
                    'schedule_type' => $scheduleType,
                    'status' => $derivedStatus,
                    'check_in_at' => $checkInAt,
                    'check_out_at' => $checkOutAt,
                    'minutes_late' => $minutesLate,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            $record->loadMissing('employee.user');

            $this->dispatchAttendanceNotifications($record);

            return $record;
        });

        return back()->with('status', 'Attendance record saved for ' . ($record->employee?->full_name ?? $record->employee_id) . '.');
    }

    public function updateRecord(Request $request, AttendanceRecord $attendanceRecord)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'check_in_at' => ['nullable', 'date_format:H:i'],
            'check_out_at' => ['nullable', 'date_format:H:i'],
        ]);

        $record = DB::transaction(function () use ($attendanceRecord, $validated) {
            $scheduleType = $this->scheduleTypeForDate($attendanceRecord->attendance_date->toDateString());
            $checkInAt = $this->dateTimeForAttendance($attendanceRecord->attendance_date->toDateString(), $validated['check_in_at'] ?? null);
            $checkOutAt = $this->dateTimeForAttendance($attendanceRecord->attendance_date->toDateString(), $validated['check_out_at'] ?? null);
            $minutesLate = null;
            $status = $attendanceRecord->status;

            if (filled($checkInAt)) {
                $lateCutoff = Carbon::parse($attendanceRecord->attendance_date->toDateString() . ' ' . $this->scheduleLateCutoffTime($scheduleType));
                $checkInTime = Carbon::parse($checkInAt);

                $status = $checkInTime->gt($lateCutoff) ? 'late' : 'present';
                $minutesLate = $status === 'late'
                    ? max(1, abs($checkInTime->diffInMinutes($lateCutoff, false)))
                    : 0;
            } elseif (in_array($status, ['present', 'late'], true)) {
                $status = 'absent';
            }

            $attendanceRecord->forceFill([
                'schedule_type' => $scheduleType,
                'status' => $status,
                'check_in_at' => $checkInAt,
                'check_out_at' => $checkOutAt,
                'minutes_late' => $minutesLate,
            ])->save();

            return $attendanceRecord->refresh()->loadMissing('employee');
        });

        return back()->with('status', 'Attendance record updated for ' . ($record->employee?->full_name ?? $record->employee_id) . '.');
    }

    public function storeHoliday(Request $request)
    {
        $validated = $request->validate([
            'holiday_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $holiday = AttendanceHoliday::updateOrCreate(
            ['holiday_date' => $validated['holiday_date']],
            [
                'title' => $validated['title'],
                'notes' => $validated['notes'] ?? null,
                'created_by_user_id' => $request->user()?->id,
            ]
        );

        $this->refreshScheduleForDate($holiday->holiday_date->toDateString());

        return back()->with('status', 'Holiday saved for ' . $holiday->holiday_date->toFormattedDateString() . '.');
    }

    public function destroyHoliday(AttendanceHoliday $holiday)
    {
        $date = $holiday->holiday_date->toDateString();
        $holiday->delete();

        $this->refreshScheduleForDate($date);

        return back()->with('status', 'Holiday removed for ' . Carbon::parse($date)->toFormattedDateString() . '.');
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

    protected function attendanceSchedules(): array
    {
        return config('attendance.schedules', []);
    }

    protected function attendanceSchedule(string $scheduleType): array
    {
        $schedules = $this->attendanceSchedules();
        $defaultKey = config('attendance.default_schedule', 'regular');
        $lateCutoffTime = config('attendance.late_cutoff_time', '08:00');

        return $schedules[$scheduleType] ?? $schedules[$defaultKey] ?? [
            'label' => ucfirst($scheduleType),
            'start_time' => '08:00',
            'end_time' => '17:00',
            'late_cutoff_time' => $lateCutoffTime,
            'checkout_offset_minutes' => 0,
        ];
    }

    protected function normalizeScheduleType(?string $scheduleType): string
    {
        $scheduleType = (string) ($scheduleType ?: 'regular');

        return array_key_exists($scheduleType, $this->attendanceSchedules())
            ? $scheduleType
            : 'regular';
    }

    protected function scheduleLateCutoffTime(string $scheduleType): string
    {
        return data_get($this->attendanceSchedules(), $scheduleType . '.late_cutoff_time', config('attendance.late_cutoff_time', '08:00'));
    }

    protected function scheduleTypeForDate(string $date): string
    {
        $carbonDate = Carbon::parse($date);
        $customHoliday = AttendanceHoliday::query()->whereDate('holiday_date', $date)->exists();
        $officialHoliday = $this->philippineHolidayService->holidayForDate($date) !== null;
        $friday = $carbonDate->copy()->startOfWeek(Carbon::MONDAY)->addDays(4);
        $customFridayHoliday = AttendanceHoliday::query()->whereDate('holiday_date', $friday->toDateString())->exists();
        $officialFridayHoliday = $this->philippineHolidayService->holidayForDate($friday) !== null;

        return $this->resolveScheduleTypeForDate(
            $carbonDate,
            $customHoliday || $officialHoliday,
            $customFridayHoliday || $officialFridayHoliday
        );
    }

    protected function resolveScheduleTypeForDate(Carbon $date, bool $isHoliday, bool $isFridayHoliday = false): string
    {
        if ($date->isMonday() || $date->isTuesday() || $date->isWednesday() || $date->isThursday()) {
            return $isFridayHoliday
                ? 'holiday'
                : config('attendance.default_schedule', 'regular');
        }

        if (! $isHoliday || ! $date->isFriday()) {
            return config('attendance.default_schedule', 'regular');
        }

        return 'holiday';
    }

    protected function dateTimeForAttendance(string $date, ?string $time): ?string
    {
        if (blank($time)) {
            return null;
        }

        return Carbon::parse($date . ' ' . $time)->toDateTimeString();
    }

    protected function refreshScheduleForDate(string $date): void
    {
        $weekStart = Carbon::parse($date)->startOfWeek(Carbon::MONDAY);

        foreach (range(0, 4) as $dayOffset) {
            $officeDate = $weekStart->copy()->addDays($dayOffset);

            AttendanceRecord::query()
                ->whereDate('attendance_date', $officeDate->toDateString())
                ->update([
                    'schedule_type' => $this->scheduleTypeForDate($officeDate->toDateString()),
                ]);
        }
    }
}
