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
            // FIX: was 'date_effective' which does not exist in schema — use date_applied only
            'credit_hours' => 'nullable|integer|min:0',
            'status'       => 'required|in:ACTIVE,INACTIVE',
        ]);

        $typeLower = strtolower(trim((string) $validated['credit_type']));

        // Canonicalize CTO so employee profile filters work reliably
        $isCtoInput = $typeLower === 'credited time-off'
            || $typeLower === 'credited time off'
            || str_contains($typeLower, 'cto');

        $credit->credit_type  = $isCtoInput ? 'Credited Time-Off' : $validated['credit_type'];
        $credit->start_date   = Carbon::parse($validated['start_date'])->toDateString();
        $credit->end_date     = $validated['end_date'] ? Carbon::parse($validated['end_date'])->toDateString() : null;
        $credit->date_applied = Carbon::parse($validated['date_applied'])->toDateString();
        $credit->status       = $validated['status'];

        $isCto = $credit->credit_type === 'Credited Time-Off';

        if ($isCto) {
            $credit->credit_hours = isset($validated['credit_hours']) ? (int) $validated['credit_hours'] : 0;
        } else {
            // Day-based credits: 1 day = 10 hours (inclusive)
            $start    = Carbon::parse($validated['start_date']);
            $end      = $validated['end_date'] ? Carbon::parse($validated['end_date']) : $start;
            $dayCount = (int) $start->diffInDays($end) + 1;
            $credit->credit_hours = $dayCount * 10;
        }

        // Refresh stored employee fields from relationship (source of truth)
        $credit->load('employee.division');
        if ($credit->employee) {
            $credit->name            = $credit->employee->full_name;
            // FIX: table column is 'departments', not 'division'
            $credit->departments     = optional($credit->employee->division)->code ?? 'N/A';
            // FIX: table column is 'role', not 'position'
            $credit->role            = $credit->employee->position;
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

    /**
     * Employee search — used by both the index modal (single) and the CTO modal (multi).
     * Accepts both ?q= and ?query= so both blade forms work without change.
     */
    public function search(Request $request): JsonResponse
    {
        // FIX: CTO blade sends ?query=, index blade sends ?q=  — accept both
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
                    // FIX: expose emp_no (the FK used by employee_leave_benefits) alongside id
                    'id'              => $emp->emp_no,   // used as the value stored in hidden input
                    'emp_no'          => $emp->emp_no,
                    'full_name'       => $emp->full_name,
                    'fullname'        => $emp->full_name, // CTO blade reads 'fullname'
                    'employee_id'     => $emp->employee_id,
                    'division_code'   => optional($emp->division)->code ?? 'N/A',
                    'department_name' => optional($emp->division)->code ?? 'N/A', // CTO blade reads 'department_name'
                    'position'        => $emp->position,
                    'employment_type' => $emp->employment_type,
                ];
            });

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        // FIX 1: removed 'employment_type' from required — CTO modal has no such field;
        //         it is derived per-employee from the DB inside the loop (source of truth).
        // FIX 2: 'employee_id' changed to nullable — CTO modal only sends 'employee_ids' (JSON array).
        //         Single-employee (index modal) still works via the nullable fallback in extractEmployeeIds().
        // FIX 3: 'date_applied' and 'date_effective' changed to nullable — CTO modal fills these
        //         via JS hidden fields; if JS fails they'd block the whole submission.
        $validated = $request->validate([
            'employee_id'  => 'nullable|exists:inventory.employees,emp_no',
            'employee_ids' => 'nullable',
            'start_date'   => 'required|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'credit_type'  => 'required|string',
            'date_applied' => 'nullable|date',
            'credit_hours' => 'nullable|integer|min:0',
            'remarks'      => 'nullable|string',
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

        // Canonicalize CTO so employee profile filters work reliably
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

                // Debug: log the raw employee attributes so you can confirm the real column names
                // Check storage/logs/laravel.log — look for 'CTO employee debug' after submitting.
                // Once confirmed, remove this log block.
                \Log::info('CTO employee debug', [
                    'emp_no'     => $employee->emp_no,
                    'attributes' => $employee->getAttributes(),
                ]);

                // Resolve position: try common column name variants used in DENR/HR systems
                $resolvedRole = $employee->position
                    ?? $employee->pos_title
                    ?? $employee->designation
                    ?? $employee->job_title
                    ?? 'N/A';

                // Resolve employment type: try common column name variants
                $resolvedEmploymentType = $employee->employment_type
                    ?? $employee->appointment_status
                    ?? $employee->emp_type
                    ?? $employee->type
                    ?? 'COS';   // safe default — change if your org defaults to PERMANENT

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

        // Multi-select path (CTO modal): employee_ids is a JSON-encoded array of emp_no values
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

        // Single-select path (index modal): fall back to employee_id scalar
        if (empty($employeeIds) && !empty($validated['employee_id'])) {
            $employeeIds = [$validated['employee_id']];
        }

        // FIX: cast to int only if values are numeric; emp_no may be stored as int in DB.
        // array_filter removes any nulls/empty strings that sneak in from JS.
        return array_values(
            array_unique(
                array_map('intval', array_filter($employeeIds, fn($v) => is_numeric($v)))
            )
        );
    }
}