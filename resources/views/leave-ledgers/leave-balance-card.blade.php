<div class="balance-card">
    <div class="balance-card-title">MINES AND GEOSCIENCES BUREAU RO VI</div>

    <div class="balance-card-meta">
        <div>
            <span>Name of Employee:</span>
            <strong>{{ strtoupper($employee->lastname . ', ' . $employee->firstname) }}</strong>
        </div>
        <div>
            <span>Division/Office:</span>
            <strong>{{ optional($employee->departmentRecord)->department ?? optional($employee->departmentRecord)->description ?? $employee->department ?? 'N/A' }}</strong>
        </div>
    </div>

    <div class="balance-card-meta">
        <div>
            <span>Leave Balances as of:</span>
            <strong>{{ $balanceCard['month'] }} {{ $balanceCard['year'] }} (Month-End)</strong>
        </div>
        <div></div>
    </div>

    <div class="balance-card-values">
        <div class="bcv-vl">
            <span>Vacation Leave</span>
            <strong>{{ number_format($balanceCard['vacation_balance'], 3) }}</strong>
        </div>
        <div class="bcv-sl">
            <span>Sick Leave</span>
            <strong>{{ number_format($balanceCard['sick_balance'], 3) }}</strong>
        </div>
        <div class="bcv-spl">
            <span>SPL</span>
            <strong>{{ number_format($balanceCard['spl_balance'], 0) }}</strong>
        </div>
    </div>

    <p class="balance-card-note mb-0">
        *Note:  The Leave Credits Balances reflected in this record is subject for verification of the employee and the Human Resource Management Unit of this office, and may have variations upon verification considering factors such as completeness of leave applications received and processed, and the recording of lates and undertime computed from the employee's daily time record submitted monthly.
    </p>
</div>
