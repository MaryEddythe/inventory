<div class="text-center mb-3">
    <div>Region Office No. 6, Iloilo City</div>
    <h1 class="h5 fw-bold mt-2 mb-0">EMPLOYEE'S LEAVE LEDGER SHEET</h1>
</div>

<div class="ledger-meta-grid mb-3">
    <div><span>NAME OF EMPLOYEE:</span> <strong>{{ strtoupper($employee->lastname . ', ' . $employee->firstname) }}</strong></div>
    <div><span>DIVISION/OFFICE:</span> <strong>{{ optional($employee->departmentRecord)->department ?? optional($employee->departmentRecord)->description ?? $employee->department ?? 'N/A' }}</strong></div>
    <div><span>1st DAY OF SERVICE:</span> <strong>{{ optional($setting->first_day_of_service)->format('M d, Y') ?? '-' }}</strong></div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle ledger-table">
        <thead>
            <tr>
                <th rowspan="2">Period</th>
                <th rowspan="2">Particulars</th>
                <th colspan="3" class="text-center vl-divider">VACATION LEAVE</th>
                <th colspan="3" class="text-center">SICK LEAVE</th>
                <th rowspan="2">Date and Action Taken on Application for Leave</th>
            </tr>
            <tr>
                <th>Earned</th>
                <th>Absence</th>
                <th class="vl-divider">Balance</th>
                <th>Earned</th>
                <th>Absence</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledger['rows'] as $row)
                @php($isCurrentMonth = ($row['month'] ?? null) === (int) now()->month)
                <tr class="{{ $isCurrentMonth ? 'current-month' : '' }}">
                    <td>{{ $row['period'] ?: '-' }}</td>
                    <td>{{ $row['particulars'] }}</td>
                    <td class="text-end">{{ is_null($row['vacation_earned']) ? '' : number_format($row['vacation_earned'], 3) }}</td>
                    <td class="text-end">{{ is_null($row['vacation_absence']) ? '' : number_format($row['vacation_absence'], 3) }}</td>
                    <td class="text-end fw-semibold vl-divider">{{ number_format($row['vacation_balance'], 3) }}</td>
                    <td class="text-end">{{ is_null($row['sick_earned']) ? '' : number_format($row['sick_earned'], 3) }}</td>
                    <td class="text-end">{{ is_null($row['sick_absence']) ? '' : number_format($row['sick_absence'], 3) }}</td>
                    <td class="text-end fw-semibold">{{ number_format($row['sick_balance'], 3) }}</td>
                    <td>{{ $row['date_action'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
