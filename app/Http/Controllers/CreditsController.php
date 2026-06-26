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

        // Business rule: COS employees are only entitled to Wellness Leave and CTO
        $leaveTypesCos = [
            'Wellness Leave',
            'Credited Time-Off',
        ];

        return view('credits.leave-credits', compact('allBenefits', 'leaveTypesPermanent', 'leaveTypesCos'));
    }

    public function cto(): View
    {
        $allBenefits = EmployeeLeaveBenefit::with('employee')
            ->orderBy('start_date', 'desc')
            ->get();

        $ctoBenefits = $allBenefits->filter(function ($benefit) {
            $type = strtolower(trim((string) $benefit->credit_type));
            $isCto = $type === 'credited time-off' || str_contains($type, 'cto') || $type === 'credited time off';

            return $isCto && (int) $benefit->credit_hours > 0;
        })->values();

        // Keep same leave-type arrays so the CTO page can reuse future UI if needed
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
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'credit_type' => 'required|string',
            'date_applied' => 'required|date',
            'date_effective' => 'required|date',
            'credit_hours' => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        // Update credit row
        $credit->credit_type = $validated['credit_type'];
        $credit->start_date = Carbon::parse($validated['start_date'])->toDateString();
        $credit->end_date = $validated['end_date'] ? Carbon::parse($validated['end_date'])->toDateString() : null;
        $credit->date_applied = Carbon::parse($validated['date_applied'])->toDateString();
        $credit->date_effective = Carbon::parse($validated['date_effective'])->toDateString();
        $credit->status = $validated['status'];

        $typeLower = strtolower(trim((string) $validated['credit_type']));

        // Canonicalize CTO input so employee profile filters work reliably.
        $isCtoInput = $typeLower === 'credited time-off' || $typeLower === 'credited time off' || str_contains($typeLower, 'cto');
        if ($isCtoInput) {
            $credit->credit_type = 'Credited Time-Off';
        } else {
            $credit->credit_type = $validated['credit_type'];
        }

        $isCto = $credit->credit_type === 'Credited Time-Off';


        if ($isCto) {
            $credit->credit_hours = isset($validated['credit_hours']) ? (int) $validated['credit_hours'] : 0;
        } else {
            // Day-based credits: 1 day = 10 hours (inclusive)
            $start = Carbon::parse($validated['start_date']);
            $end = $validated['end_date'] ? Carbon::parse($validated['end_date']) : $start;
            $dayCount = (int) $start->diffInDays($end) + 1;
            $credit->credit_hours = $dayCount * 10;
        }

        // Refresh stored employee fields from relationship (source of truth)
        $credit->load('employee.division');
        if ($credit->employee) {
            $credit->name = $credit->employee->full_name;
            $credit->division = $credit->employee->division?->code ?? 'N/A';
            $credit->position = $credit->employee->position;
            $credit->employment_type = $credit->employee->employment_type;
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
        $query = $request->input('q', '');

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
                    'id' => $emp->id,
                    'full_name' => $emp->full_name,
                    'employee_id' => $emp->employee_id,
                    'division_code' => optional($emp->division)->code ?? 'N/A',
                    'position' => $emp->position,
                    'employment_type' => $emp->employment_type,
                ];
            });

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'employee_ids' => 'nullable',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'credit_type' => 'required|string',
            'date_applied' => 'required|date',
            'date_effective' => 'required|date',
            'credit_hours' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
            'cto_action' => 'nullable|in:add,deduct',
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
        $typeLower = strtolower(trim($creditType));

        // Canonicalize CTO input so employee profile filters work reliably.
        $isCtoInput = $typeLower === 'credited time-off' || $typeLower === 'credited time off' || str_contains($typeLower, 'cto');
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

        $start = Carbon::parse($validated['start_date']);
        $end = $validated['end_date'] ? Carbon::parse($validated['end_date']) : $start;
        $dayCount = (int) $start->diffInDays($end) + 1;

        $creditHoursInput = isset($validated['credit_hours']) ? (int) $validated['credit_hours'] : null;
        $isCto = $creditType === 'Credited Time-Off';
        $ctoAction = $isCto ? ($validated['cto_action'] ?? 'deduct') : null;


        if ($isCto) {
            $creditHours = $creditHoursInput ?? 0;
            if ($ctoAction === 'deduct') {
                $creditHours = -abs($creditHours);
            }
        } else {
            $creditHours = $isDayBased ? ($dayCount * 10) : 0;
        }

        DB::transaction(function () use ($employeeIds, $employees, $validated, $creditType, $creditHours, $isCto, $ctoAction) {
            foreach ($employeeIds as $employeeId) {
                $employee = $employees->get($employeeId);
                $currentCtoHours = 0;

                if ($isCto) {
                    $currentCtoHours = (int) EmployeeLeaveBenefit::where('employee_id', $employee->id)
                        ->where(function ($query) {
                            $query->whereRaw('LOWER(TRIM(credit_type)) IN (?, ?)', [
                                'credited time-off',
                                'credited time off',
                            ])->orWhereRaw('LOWER(credit_type) LIKE ?', ['%cto%']);
                        })
                        ->sum('credit_hours');
                }

                $creditData = [
                    'employee_id' => $employee->id,
                    'name' => $employee->full_name,
                    'division' => optional($employee->division)->code ?? 'N/A',
                    'position' => $employee->position,
                    'employment_type' => $employee->employment_type,
                    'credit_type' => $creditType,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'] ?? null,
                    'credit_hours' => $creditHours,
                    'hours_used' => $isCto && $ctoAction === 'deduct' ? abs($creditHours) : 0,
                    'hours_remaining' => $isCto ? ($currentCtoHours + $creditHours) : null,
                    'status' => 'ACTIVE',
                    'remarks' => $validated['remarks'] ?? null,
                ];

                $benefit = EmployeeLeaveBenefit::create($creditData);

                if ($isCto) {
                    EmployeeLeaveHistory::create([
                        'employee_id' => $employee->id,
                        'leave_benefit_id' => $benefit->id,
                        'credit_type' => $creditType,
                        'credits_added' => $ctoAction === 'add' ? $creditHours : 0,
                        'hours_used' => $ctoAction === 'deduct' ? abs($creditHours) : 0,
                        'hours_remaining' => $currentCtoHours + $creditHours,
                        'remarks' => $validated['remarks'] ?? null,
                    ]);
                }
            }
        });

        $route = $isCto && $ctoAction === 'add' ? 'credits.cto' : 'credits.index';
        $message = match (true) {
            $isCto && $ctoAction === 'add' => 'CTO entry created successfully',
            $isCto => 'CTO hours deducted successfully',
            default => 'Leave credit created successfully',
        };

        return redirect()->route($route)->with('success', $message);
    }

    private function extractEmployeeIds(array $validated): array
    {
        $employeeIds = [];

        if (!empty($validated['employee_ids'])) {
            $rawEmployeeIds = $validated['employee_ids'];

            if (is_string($rawEmployeeIds)) {
                $decoded = json_decode($rawEmployeeIds, true);
                $rawEmployeeIds = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
            }

            if (is_array($rawEmployeeIds)) {
                $employeeIds = $rawEmployeeIds;
            }
        }

        if (empty($employeeIds) && !empty($validated['employee_id'])) {
            $employeeIds = [$validated['employee_id']];
        }

        return array_values(array_unique(array_map('intval', $employeeIds)));
    }
}
