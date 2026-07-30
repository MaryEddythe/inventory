@php
    use App\Models\Role;
    use App\Models\User;

    $hr = $leaveApplication->hrSigner
        ?? User::query()->where(fn ($query) => $query->where('role_id', 4)->orWhereHas('role', fn ($role) => $role->where('slug', 'hr')))->first();
    $divisionChief = $leaveApplication->divisionChiefSigner;
    $roleFive = $leaveApplication->regionalDirectorSigner ?? User::query()->where('role_id', 5)->orderBy('id')->first();
    $duration = match ($leaveApplication->cto_duration) {
        'am' => 'AM (4 hours)',
        'pm' => 'PM (6 hours)',
        default => 'Whole Day (10 hours / 1 day)',
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CTO Application</title>
    <style>
        @page { margin: 34px 38px; }
        body { font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 11px; }
        .header { text-align: center; border-bottom: 2px solid #172033; padding-bottom: 15px; margin-bottom: 22px; }
        .header img { width: 54px; float: left; }
        .header .agency { font-size: 10px; margin-bottom: 3px; }
        .header h1 { font-size: 18px; margin: 4px 0; letter-spacing: .4px; }
        .header .sub { font-size: 10px; }
        .clear { clear: both; }
        .details { border: 1px solid #cbd5e1; border-radius: 5px; padding: 14px; margin-bottom: 26px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 6px 3px; vertical-align: top; }
        .label { color: #475569; width: 25%; }
        .cards { width: 100%; border-collapse: separate; border-spacing: 10px 0; }
        .cards td { width: 33.333%; vertical-align: top; }
        .card { border: 1px solid #94a3b8; min-height: 190px; padding: 13px; text-align: center; }
        .card h3 { font-size: 11px; margin: 0 0 18px; text-transform: uppercase; }
        .signature { height: 62px; max-width: 145px; object-fit: contain; display: block; margin: 0 auto; }
        .signature-space { height: 62px; }
        .name { border-top: 1px solid #172033; padding-top: 5px; font-weight: bold; min-height: 17px; }
        .position { margin-top: 5px; font-size: 10px; color: #334155; }
        .note { margin-top: 28px; color: #64748b; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <!-- ===== HEADER ===== -->
    <div class="header">
        @if(file_exists(public_path('assets/mgb logo.png')))<img src="{{ public_path('assets/mgb logo.png') }}" alt="MGB Logo">@endif
        <div class="agency">Republic of the Philippines · Department of Environment and Natural Resources</div>
        <h1>CREDITED TIME-OFF APPLICATION</h1>
        <div class="sub">Mines and Geosciences Bureau · Regional Office VI, Iloilo City</div>
        <div class="clear"></div>
    </div>

    <div class="details">
        <table>
            <tr><td class="label">Employee</td><td><strong>{{ $employee?->full_name ?? 'N/A' }}</strong></td><td class="label">Position</td><td>{{ $employee?->position ?? 'N/A' }}</td></tr>
            <tr><td class="label">Date of CTO</td><td>{{ $leaveApplication->date_from?->format('F d, Y') }}</td><td class="label">Time Requested</td><td>{{ $duration }}</td></tr>
            <tr><td class="label">CTO Credit / Remarks</td><td colspan="3">{{ $leaveApplication->cto_remarks ?: '—' }}</td></tr>
            <tr><td class="label">Reason</td><td colspan="3">{{ $leaveApplication->reason ?: '—' }}</td></tr>
        </table>
    </div>

    <table class="cards"><tr>
        <td><div class="card">
            <h3>Certified by HR</h3>
            @if($hrSignaturePath)<img class="signature" src="{{ $hrSignaturePath }}" alt="HR signature">@else<div class="signature-space"></div>@endif
            <div class="name">{{ $hr?->name ?? '________________' }}</div>
            <div class="position">Administrative V</div>
        </div></td>
        <td><div class="card">
            <h3>Recommended by</h3>
            @if($divisionChiefSignaturePath)<img class="signature" src="{{ $divisionChiefSignaturePath }}" alt="Division Chief signature">@else<div class="signature-space"></div>@endif
            <div class="name">{{ $divisionChief?->name ?? '________________' }}</div>
            <div class="position">{{ $divisionChief?->role?->name ?? 'Division Chief' }}</div>
        </div></td>
        <td><div class="card">
            <h3>Approved by</h3>
            @if($regionalDirectorSignaturePath)<img class="signature" src="{{ $regionalDirectorSignaturePath }}" alt="Approver signature">@else<div class="signature-space"></div>@endif
            <div class="name">{{ $roleFive?->name ?? '________________' }}</div>
            <div class="position">{{ $roleFive?->role?->name ?? 'Position' }}</div>
        </div></td>
    </tr></table>
    <div class="note">Generated {{ now()->format('F d, Y h:i A') }}</div>
</body>
</html>
