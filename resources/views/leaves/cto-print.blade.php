@php
    use App\Models\EmployeeLeaveBenefit;
    use App\Models\User;

    $ctoCredits = EmployeeLeaveBenefit::query()
        ->where('emp_no', $employee?->emp_no)
        ->where(function ($query) {
            $query->whereRaw('LOWER(TRIM(credit_type)) IN (?, ?)', ['credited time-off', 'credited time off'])
                ->orWhereRaw('LOWER(credit_type) LIKE ?', ['%cto%']);
        })
        ->orderBy('start_date')
        ->orderBy('id')
        ->get();
    $sourceCredits = $ctoCredits->filter(fn ($credit) => (int) $credit->credit_hours > 0);
    $hoursApplied = match ($leaveApplication->cto_duration) {
        'am' => 4,
        'pm' => 6,
        default => 10,
    };
    $currentBalance = (int) $ctoCredits->sum('credit_hours');
    $isCompleted = (string) $leaveApplication->status === 'completed';
    $beginningBalance = $isCompleted ? $currentBalance + $hoursApplied : $currentBalance;
    $endingBalance = max(0, $beginningBalance - $hoursApplied);
    $duration = match ($leaveApplication->cto_duration) {
        'am' => 'AM / 4 Hours',
        'pm' => 'PM / 6 Hours',
        default => 'Whole Day / 10 Hours',
    };
    $hr = $leaveApplication->hrSigner
        ?? User::query()->where(fn ($query) => $query->where('role_id', 4)->orWhereHas('role', fn ($role) => $role->where('slug', 'hr')))->first();
    $divisionChief = $leaveApplication->divisionChiefSigner;
    $approver = $leaveApplication->regionalDirectorSigner ?? User::query()->where('role_id', 5)->orderBy('id')->first();
    $divisionName = optional($employee?->departmentRecord)->department
        ?? optional($employee?->departmentRecord)->description
        ?? optional($employee?->division)->department
        ?? optional($employee?->division)->description
        ?? '—';
    $dateApplied = $leaveApplication->created_at?->format('F d, Y') ?? '—';

    // Position: take it from the legacy employees table's "Role" column first
    // (it holds the civil-service position title), then from the Role table
    // through the employee's linked user account, then the plain position
    // attribute, and finally a dash.
    $position = collect([
        $employee?->Role,
        $employee?->user?->role?->name,
        $employee?->position,
    ])
        ->map(fn ($value) => is_string($value) ? trim($value) : '')
        ->reject(fn ($value) => $value === '')
        ->first() ?? '—';

    $salary = '—';
    $applicantName = $employee?->full_name ?? '______________';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CTO Application</title>
    {{-- All styling lives in public/leave-application-print.css. The controller
         reads that file and injects it below as $leavePrintCss because dompdf
         cannot reliably fetch external stylesheets. --}}
    <style>
        {!! $leavePrintCss ?? '' !!}
    </style>
</head>
<body>
    <!-- ===== HEADER ===== -->
    <div class="header">
        @if(file_exists(public_path('assets/mgb logo.png')))<img src="{{ public_path('assets/mgb logo.png') }}" alt="MGB Logo" class="header-logo">@endif
        <div class="header-cs-form">CS Form No. 6<br>Revised 2020</div>
        <div class="header-republic">Republic of the Philippines</div>
        <div class="header-denr">Department of Environment and Natural Resources</div>
        <div class="header-mgb">MINES AND GEOSCIENCES BUREAU</div>
        <div class="header-region">Regional Office VI, Iloilo City</div>
        <div class="header-form-title">REQUEST FOR WORK OFFSETTING / COMPENSATORY TIME-OFF</div>
    </div>

    <div class="section">
        <div class="section-title">I. Employee Information</div>
        <div class="section-body">
            <table class="form-table">
                <tr>
                    <th class="w-15">1. Office/Department</th><td class="w-35">{{ $divisionName }}</td>
                    <th class="w-10">2. Name</th>
                    <td class="w-40"><span class="muted">(Last)</span> <strong>{{ $employee?->last_name ?? '—' }}</strong>&nbsp; <span class="muted">(First)</span> <strong>{{ $employee?->first_name ?? '—' }}</strong>&nbsp; <span class="muted">(Middle)</span> <strong>{{ $employee?->middle_name ?? ($employee?->middlename ?? '—') }}</strong></td>
                </tr>
                <tr><th>3. Date of Filing</th><td>{{ $dateApplied }}</td><th>4. Position</th><td>{{ $position }}</td></tr>
                <tr><th>5. Salary</th><td colspan="3">{{ $salary }}</td></tr>
            </table>
        </div>
    </div>

    <table class="cards"><tr>
        <td><div class="card">
            <h2>REMAINING CTO / SPECIAL ORDER CREDITS</h2>
            <table>
                <thead><tr><th>SO</th><th>DATE</th><th class="num">HOURS ADDED</th></tr></thead>
                <tbody>
                    @forelse($sourceCredits as $credit)
                        <tr class="{{ (string) $credit->id === (string) $leaveApplication->cto_leave_history_id ? 'selected' : '' }}">
                            <td>{{ $credit->remarks ?: '—' }}</td>
                            <td>{{ $credit->start_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="num">{{ $credit->credit_hours }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted">No CTO credits recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></td>
        <td><div class="card">
            <h2>COMPENSATORY DAY / TIME OFF</h2>
            <table class="detail-table">
                <tr><td>Hours / Days Applied</td><td>{{ $duration }}</td></tr>
                <tr><td>Date Covered</td><td>{{ $leaveApplication->date_from?->format('M d, Y') }}@if($leaveApplication->date_to && !$leaveApplication->date_to->isSameDay($leaveApplication->date_from)) to {{ $leaveApplication->date_to->format('M d, Y') }}@endif</td></tr>
                <tr><td>Special Order</td><td>{{ $leaveApplication->cto_remarks ?: '—' }}</td></tr>
                <tr><td>S.O / T.O No.</td><td>{{ $leaveApplication->cto_so_to_no ?: '—' }}</td></tr>
            </table>
        </div></td>
    </tr></table>

    <div class="overtime-card">
        <h2>OVERTIME SERVICES</h2>
        <table>
            <thead><tr><th>DATE RENDERED</th><th>AM (IN)</th><th>AM (OUT)</th><th>PM (IN)</th><th>PM (OUT)</th><th>OT (IN)</th><th>OT (OUT)</th><th>NO. OF HOURS</th></tr></thead>
            <tbody><tr><td>—</td><td>—</td><td>—</td><td>—</td><td>—</td><td>—</td><td>—</td><td class="num">—</td></tr></tbody>
        </table>
    </div>

    <table class="balance">
        <tr><td>COC Balance Beginning</td><td class="num">{{ $beginningBalance }} hours</td></tr>
        <tr><td>Less: This Application</td><td class="num">({{ $hoursApplied }} hours)</td></tr>
        <tr class="total"><td>COC Balance End</td><td class="num">{{ $endingBalance }} hours</td></tr>
    </table>

    <table class="signature-row"><tr>
        <td><div class="sig-block">
            @if($applicantSignaturePath)
                <img class="sig-img" src="{{ $applicantSignaturePath }}" alt="Applicant Signature">
            @else
                <div class="sig-space"></div>
            @endif
            <div class="sig-line"></div>
            <div class="sig-name">{{ $applicantName }}</div>
            <div class="sig-caption">(Signature of Applicant)</div>
        </div></td>
        <td><div class="sig-block">
            @if($hrSignaturePath)
                <img class="sig-img" src="{{ $hrSignaturePath }}" alt="HR Signature">
            @else
                <div class="sig-space"></div>
            @endif
            <div class="sig-line"></div>
            <div class="sig-name">{{ $hr?->name ?? '______________' }}</div>
            <div class="sig-role">Administrative V</div>
            <div class="sig-caption">Authorized Officer</div>
        </div></td>
        <td><div class="sig-block">
            @if($divisionChiefSignaturePath)
                <img class="sig-img" src="{{ $divisionChiefSignaturePath }}" alt="Division Chief Signature">
            @else
                <div class="sig-space"></div>
            @endif
            <div class="sig-line"></div>
            <div class="sig-name">{{ $divisionChief?->name ?? '______________' }}</div>
            <div class="sig-role">{{ $divisionChief?->role?->name ?? 'Division Chief' }}</div>
            <div class="sig-caption">Authorized Officer</div>
        </div></td>
    </tr></table>

    <div class="rd-signature">
        @if($regionalDirectorSignaturePath)
            <img class="rd-signature-img" src="{{ $regionalDirectorSignaturePath }}" alt="Regional Director Signature">
        @else
            <div class="rd-signature-space"></div>
        @endif
        <div class="rd-signature-line"></div>
        <div class="rd-signature-name">Cecilia Ochavo-Saycon</div>
        <div class="rd-signature-position">Regional Director</div>
        <div class="rd-signature-caption">Authorized Officer</div>
    </div>
</body>
</html>
