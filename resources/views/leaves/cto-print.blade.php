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
    $position = $employee?->position ?? '—';
    $salary = '—';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CTO Application</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 9px; }
        .header { position: relative; text-align: center; border-bottom: 3px solid #172033; padding: 0 58px 11px; margin-bottom: 13px; }
        .header-logo { position: absolute; left: 0; top: 0; width: 45px; }
        .header-cs-form { position: absolute; right: 0; top: 0; text-align: right; font-size: 8px; }
        .header-republic, .header-denr, .header-region { font-size: 8px; }
        .header-mgb { font-size: 12px; font-weight: bold; margin: 2px 0; }
        .header-form-title { font-size: 12px; font-weight: bold; margin-top: 8px; }
        .cards, .signatories { width: 100%; border-collapse: separate; border-spacing: 8px 0; }
        .cards td { width: 50%; vertical-align: top; } .signatories td { width: 33.33%; vertical-align: top; }
        .card { border: 1px solid #64748b; padding: 9px; min-height: 168px; }
        .section { border: 1px solid #64748b; margin: 0 8px 12px; }
        .section-title { background: #e2e8f0; padding: 5px 7px; font-weight: bold; }
        .section-body { padding: 6px; }
        .form-table th { background: #f1f5f9; }
        .overtime-card { border: 1px solid #64748b; padding: 9px; margin: 10px 8px 14px; }
        h2 { font-size: 10px; text-align: center; margin: 0 0 8px; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #94a3b8; padding: 4px; text-align: left; }
        th { background: #e2e8f0; font-weight: bold; } .num { text-align: right; }
        .selected td { background: #fef3c7; font-weight: bold; }
        .detail-table td:first-child { width: 42%; background: #f8fafc; font-weight: bold; }
        .balance { margin: 0 8px 16px; width: calc(100% - 16px); }
        .balance td { border: none; padding: 3px 6px; } .balance .total td { border-top: 1px solid #172033; font-weight: bold; }
        .signature-card { text-align: center; min-height: 105px; padding: 7px; }
        .signature { height: 40px; max-width: 125px; display: block; object-fit: contain; margin: 5px auto; }
        .signature-space { height: 50px; } .name { border-top: 1px solid #172033; padding-top: 4px; font-weight: bold; }
        .position { margin-top: 3px; font-size: 8px; } .muted { color: #64748b; font-style: italic; }
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
                    <th style="width:15%;">1. Office/Department</th><td style="width:35%;">{{ $divisionName }}</td>
                    <th style="width:10%;">2. Name</th>
                    <td style="width:40%;"><span class="muted">(Last)</span> <strong>{{ $employee?->last_name ?? '—' }}</strong>&nbsp; <span class="muted">(First)</span> <strong>{{ $employee?->first_name ?? '—' }}</strong>&nbsp; <span class="muted">(Middle)</span> <strong>{{ $employee?->middle_name ?? ($employee?->middlename ?? '—') }}</strong></td>
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
            <div class="muted" style="margin-top:6px;">Highlighted row: CTO credit used for this application.</div>
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

    <table class="signatories"><tr>
        <td><div class="signature-card">@if($hrSignaturePath)<img class="signature" src="{{ $hrSignaturePath }}">@else<div class="signature-space"></div>@endif<div class="name">{{ $hr?->name ?? '________________' }}</div><div class="position">Administrative V</div></div></td>
        <td><div class="signature-card">@if($divisionChiefSignaturePath)<img class="signature" src="{{ $divisionChiefSignaturePath }}">@else<div class="signature-space"></div>@endif<div class="name">{{ $divisionChief?->name ?? '________________' }}</div><div class="position">{{ $divisionChief?->role?->name ?? 'Division Chief' }}</div></div></td>
        <td><div class="signature-card">@if($regionalDirectorSignaturePath)<img class="signature" src="{{ $regionalDirectorSignaturePath }}">@else<div class="signature-space"></div>@endif<div class="name">{{ $approver?->name ?? '________________' }}</div><div class="position">{{ $approver?->role?->name ?? 'Position' }}</div></div></td>
    </tr></table>
</body>
</html>
