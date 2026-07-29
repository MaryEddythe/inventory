<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeaveApplication;
use App\Models\EmployeeLeaveLedgerSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveLedgerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeHr();

        $search = trim((string) $request->query('search', ''));

        $employees = $this->permanentEmployeesQuery()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('inventory.employees.firstname', 'like', "%{$search}%")
                        ->orWhere('inventory.employees.lastname', 'like', "%{$search}%")
                        ->orWhere('inventory.employees.Role', 'like', "%{$search}%")
                        ->orWhere('inv_dept.department', 'like', "%{$search}%")
                        ->orWhere('inv_dept.description', 'like', "%{$search}%");
                });
            })
            ->orderBy('inventory.employees.lastname')
            ->orderBy('inventory.employees.firstname')
            ->paginate(15)
            ->withQueryString();

        return view('leave-ledgers.index', compact('employees', 'search'));
    }

    public function show(Employee $employee)
    {
        $this->authorizeHr();
        $this->abortUnlessPermanent($employee);

        $employee->load(['departmentRecord', 'leaveBenefits']);
        $setting = EmployeeLeaveLedgerSetting::firstOrCreate(['emp_no' => $employee->emp_no]);
        $ledger = $this->buildLedger($employee, $setting);
        $balanceCard = $this->buildBalanceCard($employee, $ledger);

        return view('leave-ledgers.show', compact('employee', 'setting', 'ledger', 'balanceCard'));
    }

    public function edit(Employee $employee)
    {
        $this->authorizeHr();
        $this->abortUnlessPermanent($employee);

        $setting = EmployeeLeaveLedgerSetting::firstOrCreate(['emp_no' => $employee->emp_no]);

        return view('leave-ledgers.edit', compact('employee', 'setting'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorizeHr();
        $this->abortUnlessPermanent($employee);

        $validated = $request->validate([
            'first_day_of_service' => 'nullable|date',
            'opening_balance_date' => 'nullable|date',
            'opening_vacation_balance' => 'required|numeric|min:0',
            'opening_sick_balance' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:2000',
        ]);

        EmployeeLeaveLedgerSetting::updateOrCreate(
            ['emp_no' => $employee->emp_no],
            $validated
        );

        return redirect()
            ->route('leave-ledgers.show', $employee)
            ->with('success', 'Leave ledger settings updated.');
    }

    protected function permanentEmployeesQuery()
    {
        return Employee::query()
            ->leftJoin('inventory.departments as inv_dept', DB::raw('CAST(inventory.employees.department AS UNSIGNED)'), '=', 'inv_dept.dept_no')
            ->select('inventory.employees.*', 'inv_dept.department as department_code', 'inv_dept.description as department_description')
            ->whereRaw('UPPER(TRIM(inventory.employees.employment_type)) = ?', ['PERMANENT'])
            ->where(function ($query) {
                $query->whereNull('inventory.employees.status')
                    ->orWhere('inventory.employees.status', '!=', 'inactive');
            });
    }

    protected function buildLedger(Employee $employee, EmployeeLeaveLedgerSetting $setting): array
    {
        $year = (int) now()->year;
        $today = now()->startOfDay();
        $vacationBalance = (float) $setting->opening_vacation_balance;
        $sickBalance = (float) $setting->opening_sick_balance;
        $usageByMonth = $this->approvedVacationAndSickUsageByMonth($employee, $year);
        $rows = [];

        $openingBalanceDate = $setting->opening_balance_date
            ? 'As of ' . $setting->opening_balance_date->format('F d, Y')
            : 'Opening Balance';

        $rows[] = [
            'period' => $openingBalanceDate,
            'particulars' => 'Opening Balance',
            'vacation_earned' => null,
            'vacation_absence' => null,
            'vacation_balance' => $vacationBalance,
            'sick_earned' => null,
            'sick_absence' => null,
            'sick_balance' => $sickBalance,
            'date_action' => $setting->remarks,
        ];

        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::create($year, $month, 1)->format('M');
            $monthStart = Carbon::create($year, $month, 1)->startOfDay();
            $monthEnd = $monthStart->copy()->endOfMonth()->startOfDay();
            $isStarted = $monthStart->lte($today);
            $isClosed = $monthEnd->lte($today);
            $vacationUsed = (float) ($usageByMonth[$month]['Vacation Leave'] ?? 0);
            $sickUsed = (float) ($usageByMonth[$month]['Sick Leave'] ?? 0);
            $vacationEarned = $isClosed ? 1.25 : 0;
            $sickEarned = $isClosed ? 1.25 : 0;

            if ($isStarted) {
                $vacationBalance = $vacationBalance - $vacationUsed + $vacationEarned;
                $sickBalance = $sickBalance - $sickUsed + $sickEarned;
            }

            $rows[] = [
                'period' => strtoupper($monthName),
                'particulars' => $isClosed ? 'Month-end accrual' : 'Pending month-end',
                'vacation_earned' => $isStarted ? $vacationEarned : null,
                'vacation_absence' => $isStarted ? ($vacationUsed ?: 0) : null,
                'vacation_balance' => $vacationBalance,
                'sick_earned' => $isStarted ? $sickEarned : null,
                'sick_absence' => $isStarted ? ($sickUsed ?: 0) : null,
                'sick_balance' => $sickBalance,
                'is_started' => $isStarted,
                'is_closed' => $isClosed,
                'month' => $month,
                'month_name' => $monthName,
                'date_action' => $this->monthActionText($usageByMonth[$month]['actions'] ?? []),
            ];
        }

        return [
            'year' => $year,
            'current_vacation_balance' => $vacationBalance,
            'current_sick_balance' => $sickBalance,
            'rows' => $rows,
        ];
    }

    protected function buildBalanceCard(Employee $employee, array $ledger): array
    {
        // Use the LAST row (current running balance) instead of last closed month
        $rows = collect($ledger['rows']);
        $lastRow = $rows->last();

        // Determine the as-of date: if the last row has a month, use that month's end
        // otherwise use the current date
        $asOfDate = isset($lastRow['month'])
            ? Carbon::create((int) $ledger['year'], (int) $lastRow['month'], 1)->endOfMonth()
            : now();

        // If the last row's month hasn't ended yet, use the previous closed month's end
        // for the "as of" label, but still show the current running balance
        $isLastRowClosed = ($lastRow['is_closed'] ?? false) === true;
        $balanceDate = $isLastRowClosed
            ? $asOfDate
            : ($rows->filter(fn (array $r) => ($r['is_closed'] ?? false) === true)->last()['month'] ?? null
                ? Carbon::create(
                    (int) $ledger['year'],
                    (int) ($rows->filter(fn (array $r) => ($r['is_closed'] ?? false) === true)->last()['month'] ?? now()->month),
                    1
                )->endOfMonth()
                : now()->subMonthNoOverflow()->endOfMonth());

        return [
            'month' => $balanceDate->format('F'),
            'year' => $balanceDate->format('Y'),
            'vacation_balance' => (float) ($lastRow['vacation_balance'] ?? 0),
            'sick_balance' => (float) ($lastRow['sick_balance'] ?? 0),
            'spl_balance' => $this->specialPrivilegeLeaveBalance($employee, (int) $ledger['year'], $balanceDate),
        ];
    }

    protected function specialPrivilegeLeaveBalance(Employee $employee, int $year, Carbon $balanceDate): float
    {
        $usedDays = EmployeeLeaveApplication::query()
            ->where('employee_id', $employee->emp_no)
            ->where('leave_type', 'Special Privilege Leave')
            ->whereNotNull('regional_director_signed_at')
            ->whereYear('date_from', $year)
            ->whereDate('date_from', '<=', $balanceDate)
            ->get()
            ->sum(function (EmployeeLeaveApplication $application) {
                return ((int) Carbon::parse($application->date_from)->diffInDays($application->date_to ?: $application->date_from)) + 1;
            });

        return max(0, 3 - $usedDays);
    }

    protected function approvedVacationAndSickUsageByMonth(Employee $employee, int $year): array
    {
        $applications = EmployeeLeaveApplication::query()
            ->where('employee_id', $employee->emp_no)
            ->whereIn('leave_type', ['Vacation Leave', 'Sick Leave'])
            ->whereNotNull('regional_director_signed_at')
            ->whereYear('date_from', $year)
            ->orderBy('date_from')
            ->get();

        $usage = [];

        foreach ($applications as $application) {
            $start = Carbon::parse($application->date_from);
            $end = Carbon::parse($application->date_to ?: $application->date_from);
            $cursor = $start->copy();

            while ($cursor->lte($end)) {
                if ((int) $cursor->year === $year) {
                    $month = (int) $cursor->month;

                    $usage[$month][$application->leave_type] = ($usage[$month][$application->leave_type] ?? 0) + 1.25;
                }

                $cursor->addDay();
            }

            $days = ((int) $start->diffInDays($end)) + 1;
            $actionMonth = (int) $start->month;
            $usage[$actionMonth]['actions'][] = $start->format('M. d') . ' ' . $application->leave_type . ' (' . $days . 'd)';
        }

        return $usage;
    }

    protected function monthActionText(array $actions): ?string
    {
        return empty($actions) ? null : implode('; ', $actions);
    }

    protected function authorizeHr(): void
    {
        abort_unless((int) auth()->user()?->role_id === 4, 403);
    }

    protected function abortUnlessPermanent(Employee $employee): void
    {
        abort_unless(strtoupper(trim((string) $employee->employment_type)) === 'PERMANENT', 404);
    }
}
