<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeaveBenefit;
use App\Models\EmployeeLeaveHistory;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CreditsController extends Controller
{
    public function index(): View
    {
        $allBenefits = EmployeeLeaveBenefit::with('employee')
            ->where(function ($query) {
                $query->whereNull('credit_type')
                    ->orWhere(function ($query) {
                        $query->whereRaw('LOWER(TRIM(credit_type)) NOT IN (?, ?)', [
                            'credited time-off',
                            'credited time off',
                        ])->whereRaw('LOWER(credit_type) NOT LIKE ?', ['%cto%']);
                    });
            })
            ->orderBy('start_date', 'desc')
            ->get();

        $allCreditTypesFromDb = EmployeeLeaveBenefit::query()
            ->whereNotNull('credit_type')
            ->distinct()
            ->pluck('credit_type')
            ->sort()
            ->values()
            ->all();

        $leaveTypesPermanent = [
            'Vacation Leave',
            'Sick Leave',
            'Wellness Leave',
            'Special Privilege Leave',
            'Maternity Leave',
            'Paternity Leave',
            'Solo Parent Leave',
            'Rehabilitation Leave',
            'Special Emergency Leave',
            'Credited Time-Off',
        ];

        $leaveTypesCos = [
            'Wellness Leave',
            'Credited Time-Off',
        ];

        $leaveTypesFromDb = $leaveTypesPermanent;

        return view('credits.leave-credits', compact(
            'allBenefits',
            'leaveTypesFromDb',
            'leaveTypesPermanent',
            'leaveTypesCos'
        ));
    }

    public function cto(): View
    {
        $ctoBenefits = EmployeeLeaveBenefit::with('employee')
            ->where(function ($query) {
                $query->whereRaw('LOWER(TRIM(credit_type)) IN (?, ?)', [
                    'credited time-off',
                    'credited time off',
                ])->orWhereRaw('LOWER(credit_type) LIKE ?', ['%cto%']);
            })
            ->where('credit_hours', '>', 0)
            ->orderBy('start_date', 'desc')
            ->get();

        $leaveTypesPermanent = [
            'Special Emergency Leave',
            'Rehabilitation Leave',
            'Solo Parent Leave',
            'Paternity Leave',
            'Maternity Leave',
            'Special Privilege Leave',
            'Wellness Leave',
            'Vacation Leave',
        ];

        $leaveTypesCos = [
            'Wellness Leave',
            'Credited Time-Off',
        ];

        return view('credits.cto', [
            'ctoBenefits' => $ctoBenefits,
            'leaveTypesPermanent' => $leaveTypesPermanent,
            'leaveTypesCos' => $leaveTypesCos,
        ]);
    }

    public function edit(EmployeeLeaveBenefit $credit): View
    {
        $benefit = $credit->load('employee.division');

        $leaveTypesPermanent = [
            'Special Emergency Leave',
            'Rehabilitation Leave',
            'Solo Parent Leave',
            'Paternity Leave',
            'Maternity Leave',
            'Special Privilege Leave',
            'Wellness Leave',
            'Vacation Leave',
        ];

        $leaveTypesCos = [
            'Wellness Leave',
            'Credited Time-Off',
        ];

        return view('credits.edit', [
            'benefit' => $benefit,
            'leaveTypesPermanent' => $leaveTypesPermanent,
            'leaveTypesCos' => $leaveTypesCos,
        ]);
    }

    public function update(Request $request, EmployeeLeaveBenefit $credit)
    {
        $validated = $request->validate([
            'start_date'   => 'required|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'credit_type'  => 'required|string',
            'date_applied' => 'required|date',
            'credit_hours' => 'nullable|integer|min:0',
            'status'       => 'required|in:ACTIVE,INACTIVE',
            'remarks'      => 'nullable|string',
            'location'     => 'nullable|string',
            'employee_ids' => 'nullable',
        ]);

        $typeLower = strtolower(trim((string) $validated['credit_type']));

        $isCtoInput = $typeLower === 'credited time-off'
            || $typeLower === 'credited time off'
            || str_contains($typeLower, 'cto');

        $canonicalCreditType = $isCtoInput ? 'Credited Time-Off' : $validated['credit_type'];
        $isCto = $canonicalCreditType === 'Credited Time-Off';

        if ($isCto) {
            $creditHours = isset($validated['credit_hours']) ? (int) $validated['credit_hours'] : 0;
        } else {
            $start    = Carbon::parse($validated['start_date']);
            $end      = $validated['end_date'] ? Carbon::parse($validated['end_date']) : $start;
            $dayCount = (int) $start->diffInDays($end) + 1;
            $creditHours = $dayCount * 10;
        }

        $employeeIds = $this->extractEmployeeIds($validated);

        if ($isCto && !empty($employeeIds)) {
            $credit->load('employee.division');

            $currentRemarks = $credit->remarks ?? '';
            $originalEmpNo = $credit->emp_no;

            $groupBenefits = EmployeeLeaveBenefit::where('remarks', $currentRemarks)
                ->where(function ($query) {
                    $query->whereRaw('LOWER(TRIM(credit_type)) IN (?, ?)', [
                        'credited time-off',
                        'credited time off',
                    ])->orWhereRaw('LOWER(credit_type) LIKE ?', ['%cto%']);
                })
                ->get()
                ->keyBy('emp_no');

            $existingEmpNos = $groupBenefits->keys()->all();
            $employees = Employee::with('division')
                ->whereIn('emp_no', $employeeIds)
                ->get()
                ->keyBy('emp_no');

            $toRemove = array_diff($existingEmpNos, $employeeIds);
            if (!empty($toRemove)) {
                EmployeeLeaveBenefit::whereIn('emp_no', $toRemove)
                    ->where('id', $groupBenefits->whereIn('emp_no', $toRemove)->pluck('id'))
                    ->delete();
            }

            $toAdd = array_diff($employeeIds, $existingEmpNos);
            foreach ($toAdd as $newEmpNo) {
                $employee = $employees->get($newEmpNo);
                if (!$employee) continue;

                $resolvedRole = $employee->position
                    ?? $employee->pos_title
                    ?? $employee->designation
                    ?? $employee->job_title
                    ?? $employee->role
                    ?? 'N/A';

                $resolvedEmploymentType = $employee->employment_type
                    ?? $employee->appointment_status
                    ?? $employee->emp_type
                    ?? $employee->type
                    ?? 'COS';

                EmployeeLeaveBenefit::create([
                    'emp_no'          => $employee->emp_no,
                    'name'            => $employee->full_name,
                    'departments'     => optional($employee->division)->code ?? 'N/A',
                    'role'            => $resolvedRole,
                    'employment_type' => $resolvedEmploymentType,
                    'credit_type'     => $canonicalCreditType,
                    'start_date'      => $validated['start_date'],
                    'end_date'        => $validated['end_date'] ?? null,
                    'date_applied'    => Carbon::parse($validated['date_applied'])->toDateString(),
                    'credit_hours'    => $creditHours,
                    'status'          => $validated['status'],
                    'remarks'         => $validated['remarks'] ?? $currentRemarks,
                    'location'        => $validated['location'] ?? null,
                ]);
            }

            foreach ($groupBenefits as $existingBenefit) {
                if (in_array((int) $existingBenefit->emp_no, $toRemove)) continue;

                $existingBenefit->credit_type  = $canonicalCreditType;
                $existingBenefit->start_date   = Carbon::parse($validated['start_date'])->toDateString();
                $existingBenefit->end_date     = $validated['end_date'] ? Carbon::parse($validated['end_date'])->toDateString() : null;
                $existingBenefit->date_applied = Carbon::parse($validated['date_applied'])->toDateString();
                $existingBenefit->credit_hours = $creditHours;
                $existingBenefit->status       = $validated['status'];
                $existingBenefit->remarks      = $validated['remarks'] ?? $currentRemarks;
                $existingBenefit->location     = $validated['location'] ?? $existingBenefit->location;

                $existingBenefit->load('employee.division');
                if ($existingBenefit->employee) {
                    $existingBenefit->name            = $existingBenefit->employee->full_name;
                    $existingBenefit->departments     = optional($existingBenefit->employee->division)->code ?? 'N/A';

                    $resolvedRole = $existingBenefit->employee->position
                        ?? $existingBenefit->employee->pos_title
                        ?? $existingBenefit->employee->designation
                        ?? $existingBenefit->employee->job_title
                        ?? $existingBenefit->employee->role
                        ?? 'N/A';

                    $existingBenefit->role            = $resolvedRole;
                    $existingBenefit->employment_type = $existingBenefit->employee->employment_type ?? 'COS';
                } else {
                    $existingBenefit->role = $existingBenefit->role ?? 'N/A';
                }

                $existingBenefit->save();
            }

            return redirect()->route('credits.cto')->with('success', 'CTO entry updated successfully');
        }

        $credit->credit_type  = $canonicalCreditType;
        $credit->start_date   = Carbon::parse($validated['start_date'])->toDateString();
        $credit->end_date     = $validated['end_date'] ? Carbon::parse($validated['end_date'])->toDateString() : null;
        $credit->date_applied = Carbon::parse($validated['date_applied'])->toDateString();
        $credit->status       = $validated['status'];
        $credit->remarks      = $validated['remarks'] ?? $credit->remarks;
        $credit->location     = $validated['location'] ?? $credit->location;
        $credit->credit_hours = $creditHours;

        $credit->load('employee.division');
        if ($credit->employee) {
            $credit->name            = $credit->employee->full_name;
            $credit->departments     = optional($credit->employee->division)->code ?? 'N/A';
            $credit->role            = $credit->employee->position ?? $credit->employee->role ?? 'N/A';
            $credit->employment_type = $credit->employee->employment_type ?? 'COS';
        }

        $credit->save();

        return redirect()->route('credits.index')->with('success', 'Leave credit updated successfully');
    }

    public function destroy(EmployeeLeaveBenefit $credit)
    {
        $credit->delete();
        return redirect()->route('credits.index')->with('success', 'Leave credit deleted successfully');
    }
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q') ?? $request->input('query', '');

        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $employees = Employee::with('division')
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('employee_id', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($emp) {
                return [
                    'id'              => $emp->emp_no,   
                    'emp_no'          => $emp->emp_no,
                    'full_name'       => $emp->full_name,
                    'fullname'        => $emp->full_name,
                    'employee_id'     => $emp->employee_id,
                    'division_code'   => optional($emp->division)->code ?? 'N/A',
                    'department_name' => optional($emp->division)->code ?? 'N/A', 
                    'position'        => $emp->position,
                    'employment_type' => $emp->employment_type,
                ];
            });

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'  => 'nullable|exists:inventory.employees,emp_no',
            'employee_ids' => 'nullable',
            'start_date'   => 'required|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'credit_type'  => 'required|string',
            'date_applied' => 'nullable|date',
            'credit_hours' => 'nullable|integer|min:0',
            'remarks'      => 'nullable|string',
            'location'     => 'nullable|string',
            'cto_action'   => 'nullable|in:add,deduct',
        ]);

        $employeeIds = $this->extractEmployeeIds($validated);

        if (empty($employeeIds)) {
            throw ValidationException::withMessages([
                'employee_id' => 'Please select at least one employee.',
            ]);
        }

        $employees = Employee::with('division')
            ->whereIn('emp_no', $employeeIds)
            ->get()
            ->keyBy('emp_no');

        if ($employees->count() !== count($employeeIds)) {
            throw ValidationException::withMessages([
                'employee_id' => 'One or more selected employees could not be found.',
            ]);
        }

        $creditType = (string) $validated['credit_type'];
        $typeLower  = strtolower(trim($creditType));

        $isCtoInput = $typeLower === 'credited time-off'
            || $typeLower === 'credited time off'
            || str_contains($typeLower, 'cto');

        if ($isCtoInput) {
            $creditType = 'Credited Time-Off';
        }

        $isDayBased = str_contains($typeLower, 'vacation')
            || str_contains($typeLower, 'sick')
            || str_contains($typeLower, 'wellness')
            || str_contains($typeLower, 'maternity')
            || str_contains($typeLower, 'paternity')
            || str_contains($typeLower, 'solo parent')
            || str_contains($typeLower, 'special privilege')
            || str_contains($typeLower, 'special emergency')
            || str_contains($typeLower, 'rehabilitation');

        $start    = Carbon::parse($validated['start_date']);
        $end      = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : $start;
        $dayCount = (int) $start->diffInDays($end) + 1;

        $creditHoursInput = isset($validated['credit_hours']) ? (int) $validated['credit_hours'] : null;
        $isCto      = $creditType === 'Credited Time-Off';
        $ctoAction  = $isCto ? ($validated['cto_action'] ?? 'add') : null;

        if ($isCto) {
            $creditHours = $creditHoursInput ?? 0;
            if ($ctoAction === 'deduct') {
                $creditHours = -abs($creditHours);
            }
        } else {
            $creditHours = $isDayBased ? ($dayCount * 10) : 0;
        }

        $dateApplied = !empty($validated['date_applied'])
            ? Carbon::parse($validated['date_applied'])->toDateString()
            : now()->toDateString();

        DB::transaction(function () use (
            $employeeIds, $employees, $validated,
            $creditType, $creditHours, $isCto, $ctoAction, $dateApplied
        ) {
            foreach ($employeeIds as $employeeId) {
                $employee        = $employees->get($employeeId);
                $currentCtoHours = 0;

                if ($isCto) {
                    $currentCtoHours = (int) EmployeeLeaveBenefit::where('emp_no', $employee->emp_no)
                        ->where(function ($query) {
                            $query->whereRaw('LOWER(TRIM(credit_type)) IN (?, ?)', [
                                'credited time-off',
                                'credited time off',
                            ])->orWhereRaw('LOWER(credit_type) LIKE ?', ['%cto%']);
                        })
                        ->sum('credit_hours');
                }

                $resolvedRole = $employee->position
                    ?? $employee->pos_title
                    ?? $employee->designation
                    ?? $employee->job_title
                    ?? $employee->role
                    ?? 'N/A';

                $resolvedEmploymentType = $employee->employment_type
                    ?? $employee->appointment_status
                    ?? $employee->emp_type
                    ?? $employee->type
                    ?? 'COS';

                $creditData = [
                    'emp_no'          => $employee->emp_no,
                    'name'            => $employee->full_name,
                    'departments'     => optional($employee->division)->code ?? 'N/A',
                    'role'            => $resolvedRole,
                    'employment_type' => $resolvedEmploymentType,
                    'credit_type'     => $creditType,
                    'start_date'      => $validated['start_date'],
                    'end_date'        => $validated['end_date'] ?? null,
                    'date_applied'    => $dateApplied,
                    'credit_hours'    => $creditHours,
                    'hours_used'      => ($isCto && $ctoAction === 'deduct') ? abs($creditHours) : 0,
                    'hours_remaining' => $isCto ? ($currentCtoHours + $creditHours) : null,
                    'status'          => 'ACTIVE',
                    'remarks'         => $validated['remarks'] ?? null,
                    'location'        => $validated['location'] ?? null,
                ];

                $benefit = EmployeeLeaveBenefit::create($creditData);

                if ($isCto) {
                    try {
                        EmployeeLeaveHistory::create([
                            'emp_no'          => $employee->emp_no,
                            'leave_benefit_id' => $benefit->id,
                            'credit_type'     => $creditType,
                            'credits_added'   => $ctoAction === 'add' ? $creditHours : 0,
                            'hours_used'      => $ctoAction === 'deduct' ? abs($creditHours) : 0,
                            'hours_remaining' => $currentCtoHours + $creditHours,
                            'remarks'         => $validated['remarks'] ?? null,
                            'location'        => $validated['location'] ?? null,
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('EmployeeLeaveHistory creation failed: ' . $e->getMessage());
                    }
                }
            }
        });

        $route = ($isCto && $ctoAction === 'add') ? 'credits.cto' : 'credits.index';
        $message = match (true) {
            $isCto && $ctoAction === 'add' => 'CTO entry created successfully',
            $isCto                         => 'CTO hours deducted successfully',
            default                        => 'Leave credit created successfully',
        };

        return redirect()->route($route)->with('success', $message);
    }

    private function extractEmployeeIds(array $validated): array
    {
        $employeeIds = [];

        if (!empty($validated['employee_ids'])) {
            $rawEmployeeIds = $validated['employee_ids'];

            if (is_string($rawEmployeeIds)) {
                $decoded        = json_decode($rawEmployeeIds, true);
                $rawEmployeeIds = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
            }

            if (is_array($rawEmployeeIds)) {
                $employeeIds = $rawEmployeeIds;
            }
        }

        if (empty($employeeIds) && !empty($validated['employee_id'])) {
            $employeeIds = [$validated['employee_id']];
        }

        return array_values(
            array_unique(
                array_map('intval', array_filter($employeeIds, fn($v) => is_numeric($v)))
            )
        );
    }
}
