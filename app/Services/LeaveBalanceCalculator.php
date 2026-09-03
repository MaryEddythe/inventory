<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLeaveApplication;
use App\Models\EmployeeLeaveBenefit;
use App\Models\EmployeeLeaveLedgerSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeaveBalanceCalculator
{
    public const MONTHLY_ACCRUAL_DAYS = 1.25;
    public const HOURS_PER_DAY = 10;
    public const HOURS_PER_MONTH = 160;
    public const DAYS_PER_HOUR = 0.0078125;

    public function buildLedger(Employee $employee, ?EmployeeLeaveLedgerSetting $setting = null, ?int $year = null): array
    {
        $setting ??= EmployeeLeaveLedgerSetting::firstOrCreate(['emp_no' => $employee->emp_no]);
        $year ??= (int) now()->year;

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
            $monthStart = Carbon::create($year, $month, 1)->startOfDay();
            $monthEnd = $monthStart->copy()->endOfMonth()->startOfDay();
            $isStarted = $monthStart->lte($today);
            $isClosed = $monthEnd->lte($today);
            $vacationUsed = (float) ($usageByMonth[$month]['Vacation Leave'] ?? 0);
            $sickUsed = (float) ($usageByMonth[$month]['Sick Leave'] ?? 0);
            $vacationEarned = $isClosed ? self::MONTHLY_ACCRUAL_DAYS : 0;
            $sickEarned = $isClosed ? self::MONTHLY_ACCRUAL_DAYS : 0;

            if ($isStarted) {
                $vacationBalance = $vacationBalance + $vacationEarned - $vacationUsed;
                $sickBalance = $sickBalance + $sickEarned - $sickUsed;
            }

            $rows[] = [
                'period' => strtoupper(Carbon::create($year, $month, 1)->format('M')),
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
                'month_name' => Carbon::create($year, $month, 1)->format('M'),
                'date_action' => $this->monthActionText($usageByMonth[$month]['actions'] ?? []),
            ];
        }

        return [
            'year' => $year,
            'current_vacation_balance' => round($vacationBalance, 3),
            'current_sick_balance' => round($sickBalance, 3),
            'rows' => $rows,
        ];
    }

    public function buildBalanceCard(Employee $employee, array $ledger): array
    {
        $rows = collect($ledger['rows'] ?? []);
        $lastRow = $rows->last() ?? [];

        $closedRows = $rows->filter(fn (array $row) => ($row['is_closed'] ?? false) === true);
        $lastClosedRow = $closedRows->last();

        $balanceDate = isset($lastRow['month'])
            ? Carbon::create((int) $ledger['year'], (int) $lastRow['month'], 1)->endOfMonth()
            : now();

        if (($lastRow['is_closed'] ?? false) !== true) {
            $balanceDate = $lastClosedRow && isset($lastClosedRow['month'])
                ? Carbon::create((int) $ledger['year'], (int) $lastClosedRow['month'], 1)->endOfMonth()
                : now()->subMonthNoOverflow()->endOfMonth();
        }

        return [
            'month' => $balanceDate->format('F'),
            'year' => $balanceDate->format('Y'),
            'vacation_balance' => (float) ($lastRow['vacation_balance'] ?? 0),
            'sick_balance' => (float) ($lastRow['sick_balance'] ?? 0),
            'spl_balance' => $this->specialPrivilegeLeaveBalance($employee, (int) $ledger['year'], $balanceDate),
        ];
    }

    public function buildEmployeeSummary(Employee $employee, ?int $year = null): array
    {
        $setting = EmployeeLeaveLedgerSetting::firstOrCreate(['emp_no' => $employee->emp_no]);
        $ledger = $this->buildLedger($employee, $setting, $year);
        $balanceCard = $this->buildBalanceCard($employee, $ledger);

        return compact('setting', 'ledger', 'balanceCard');
    }

    /**
     * Build the leave-credit figures printed on an application form.
     * The current application is shown separately as a pending deduction.
     */
    public function certificationForApplication(EmployeeLeaveApplication $application): array
    {
        $employee = $application->employee;
        $asOf = $application->created_at?->copy()->startOfDay() ?? now()->startOfDay();

        if (! $employee) {
            return [
                'as_of' => $asOf->format('F d, Y'),
                'vacation_earned' => 0,
                'sick_earned' => 0,
                'vacation_less' => 0,
                'sick_less' => 0,
                'vacation_balance' => 0,
                'sick_balance' => 0,
            ];
        }

        $setting = EmployeeLeaveLedgerSetting::firstOrCreate(['emp_no' => $employee->emp_no]);
        $vacationBalance = (float) $setting->opening_vacation_balance;
        $sickBalance = (float) $setting->opening_sick_balance;

        $approvedApplications = EmployeeLeaveApplication::query()
            ->where('employee_id', $employee->emp_no)
            ->whereNotNull('regional_director_signed_at')
            ->whereDate('date_from', '<=', $asOf)
            ->whereKeyNot($application->getKey())
            ->get();

        foreach ($approvedApplications as $approvedApplication) {
            $days = $this->calendarDays($approvedApplication);
            $leaveType = $this->canonicalLeaveType((string) $approvedApplication->leave_type);

            if ($leaveType === 'Vacation Leave') {
                $vacationBalance -= $days * self::MONTHLY_ACCRUAL_DAYS;
            } elseif ($leaveType === 'Sick Leave') {
                $sickBalance -= $days * self::MONTHLY_ACCRUAL_DAYS;
            }
        }

        for ($month = $asOf->copy()->startOfYear(); $month->lte($asOf); $month->addMonth()) {
            if ($month->copy()->endOfMonth()->lte($asOf)) {
                $vacationBalance += self::MONTHLY_ACCRUAL_DAYS;
                $sickBalance += self::MONTHLY_ACCRUAL_DAYS;
            }
        }

        $requestedDays = $this->calendarDays($application);
        $leaveType = $this->canonicalLeaveType((string) $application->leave_type);
        $vacationLess = $leaveType === 'Vacation Leave'
            ? $requestedDays * self::MONTHLY_ACCRUAL_DAYS
            : 0;
        $sickLess = $leaveType === 'Sick Leave'
            ? $requestedDays * self::MONTHLY_ACCRUAL_DAYS
            : 0;

        return [
            'as_of' => $asOf->format('F d, Y'),
            'vacation_earned' => round($vacationBalance, 3),
            'sick_earned' => round($sickBalance, 3),
            'vacation_less' => round($vacationLess, 3),
            'sick_less' => round($sickLess, 3),
            'vacation_balance' => round($vacationBalance - $vacationLess, 3),
            'sick_balance' => round($sickBalance - $sickLess, 3),
        ];
    }

    public function getDailyAccrualRows(): array
    {
        return [
            ['hours' => 1, 'days' => self::DAYS_PER_HOUR],
            ['hours' => 2, 'days' => self::DAYS_PER_HOUR * 2],
            ['hours' => 3, 'days' => self::DAYS_PER_HOUR * 3],
            ['hours' => 4, 'days' => self::DAYS_PER_HOUR * 4],
            ['hours' => 5, 'days' => self::DAYS_PER_HOUR * 5],
            ['hours' => 6, 'days' => self::DAYS_PER_HOUR * 6],
            ['hours' => 7, 'days' => self::DAYS_PER_HOUR * 7],
            ['hours' => 8, 'days' => self::DAYS_PER_HOUR * 8],
            ['hours' => 9, 'days' => self::DAYS_PER_HOUR * 9],
            ['hours' => 10, 'days' => self::DAYS_PER_HOUR * 10],
            ['hours' => 160, 'days' => self::DAYS_PER_HOUR * 160],
        ];
    }

    public function calculateDailyAccrual(float $hoursWorked): float
    {
        return round($hoursWorked * self::DAYS_PER_HOUR, 7);
    }

    /**
     * Return only CTO credits that still have remaining hours available.
     */
    public function availableCtoCredits(Employee $employee): Collection
    {
        $credits = EmployeeLeaveBenefit::query()
            ->where('emp_no', $employee->emp_no)
            ->where(function ($query) {
                $query->whereRaw('LOWER(TRIM(credit_type)) IN (?, ?)', [
                    'credited time-off',
                    'credited time off',
                ])->orWhereRaw('LOWER(credit_type) LIKE ?', ['%cto%']);
            })
            ->where('credit_hours', '>', 0)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();

        $usedHoursByCreditId = EmployeeLeaveApplication::query()
            ->where('employee_id', $employee->emp_no)
            ->whereNotNull('cto_leave_history_id')
            ->whereNotNull('regional_director_signed_at')
            ->get()
            ->filter(function (EmployeeLeaveApplication $application) {
                $type = strtolower(trim((string) $application->leave_type));

                return $type === 'credited time-off'
                    || $type === 'credited time off'
                    || str_contains($type, 'cto');
            })
            ->groupBy('cto_leave_history_id')
            ->map(function (Collection $applications) {
                return (int) $applications->sum(function (EmployeeLeaveApplication $application) {
                    return $this->ctoApplicationHours((string) $application->cto_duration);
                });
            });

        return $credits
            ->map(function (EmployeeLeaveBenefit $credit) use ($usedHoursByCreditId) {
                $usedHours = (int) ($usedHoursByCreditId->get($credit->id) ?? 0);
                $remainingHours = max(0, (int) $credit->credit_hours - $usedHours);

                if ($remainingHours <= 0) {
                    return null;
                }

                $record = clone $credit;
                $record->used_hours = $usedHours;
                $record->remaining_hours = $remainingHours;

                return $record;
            })
            ->filter()
            ->values();
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
                    $usage[$month][$application->leave_type] = ($usage[$month][$application->leave_type] ?? 0) + 1;
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

    protected function calendarDays(EmployeeLeaveApplication $application): int
    {
        $start = Carbon::parse($application->date_from);
        $end = $application->date_to ? Carbon::parse($application->date_to) : $start;

        return ((int) $start->diffInDays($end)) + 1;
    }

    protected function canonicalLeaveType(string $leaveType): string
    {
        $type = strtolower(trim($leaveType));

        return match (true) {
            $type === 'vacation leave' => 'Vacation Leave',
            $type === 'sick leave' => 'Sick Leave',
            default => trim($leaveType),
        };
    }

    protected function ctoApplicationHours(string $duration): int
    {
        return match ($duration) {
            'am' => 4,
            'pm' => 6,
            'whole_day' => 10,
            default => 0,
        };
    }
}
