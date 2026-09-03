@php
    use App\Models\User;
    use App\Models\Role;
    use App\Models\Department;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Str;

    $employeeName = $employee?->full_name ?? 'N/A';
    $employeeId = $employee?->employee_id ?? 'N/A';
    $divisionName = optional($employee->departmentRecord)->department ?? optional($employee->departmentRecord)->description ?? optional($employee->division)->name ?? optional($employee->division)->code ?? 'N/A';
    $position = $employee?->position ?? 'N/A';
    $salary = '—';

    $leaveType = $leaveApplication->leave_type ?? 'N/A';
    $dateFrom = $leaveApplication->date_from?->format('F d, Y') ?? 'N/A';
    $dateTo = $leaveApplication->date_to?->format('F d, Y') ?? 'Open ended';
    $dateApplied = $leaveApplication->created_at?->format('F d, Y') ?? 'N/A';
    $timeApplied = $leaveApplication->created_at?->format('h:i A') ?? 'N/A';
    $reason = $leaveApplication->reason ?: '—';
    $leaveCredits = $leaveCredits ?? [
        'as_of' => $dateApplied,
        'vacation_earned' => 0,
        'sick_earned' => 0,
        'vacation_less' => 0,
        'sick_less' => 0,
        'vacation_balance' => 0,
        'sick_balance' => 0,
    ];

    $pageTitle = 'CS Form 6 - Application for Leave';

    $selected = strtolower($leaveType);

    $checklist = [
        'Vacation Leave' => ['vacation leave', 'vacation'],
        'Mandatory/Forced Leave' => ['mandatory/forced leave', 'mandatory'],
        'Sick Leave' => ['sick leave', 'sick'],
        'Maternity Leave' => ['maternity leave', 'maternity'],
        'Paternity Leave' => ['paternity leave', 'paternity'],
        'Special Privilege Leave' => ['special privilege leave', 'special privilege'],
        'Solo Parent Leave' => ['solo parent leave', 'solo parent'],
        'Study Leave' => ['study leave', 'study'],
        '10-Day VAWC Leave' => ['10-day vawc leave', 'vawc'],
        'Rehabilitation Privilege' => ['rehabilitation leave', 'rehabilitation'],
        'Special Leave Benefits for Women' => ['special leave benefits for women', 'special leave'],
        'Special Emergency Leave' => ['special emergency leave', 'special emergency', 'calamity'],
        'Adoption Leave' => ['adoption leave', 'adoption'],
        'Others: Wellness Leave' => ['wellness leave', 'wellness'],
    ];

    $isChecked = function ($needles) use ($selected) {
        return collect($needles)->contains(fn ($n) => str_contains($selected, $n));
    };

    $numberOfDays = $leaveApplication->date_from && $leaveApplication->date_to
        ? $leaveApplication->date_from->diffInDays($leaveApplication->date_to) + 1
        : '—';

    // ── Signatories from users/roles ──
    $hrUser = User::query()
        ->where('role_id', 4)
        ->orWhereHas('role', fn ($q) => $q->where('slug', 'hr'))
        ->orderBy('id')
        ->first();

    $certificationOfficerName = $hrUser?->name ?? 'HR Officer';
    $certificationOfficerPosition = optional($hrUser?->role)->name ?? 'Administrative Officer V';

    $employeeDeptNo = (int) ($employee?->department ?? 1);

    $divisionChiefRole = null;
    $divisionChiefUser = null;

    if (Schema::connection('inventory')->hasTable('departments') && Schema::connection('inventory')->hasColumn('departments', 'division_chief_role_id')) {
        $department = Department::query()
            ->where('dept_no', $employeeDeptNo)
            ->with('divisionChiefRole')
            ->first();

        $divisionChiefRole = $department?->divisionChiefRole;
    }

    if ($divisionChiefRole) {
        $divisionChiefUser = User::query()
            ->where('role_id', $divisionChiefRole->id)
            ->orWhereHas('role', fn ($query) => $query->where('id', $divisionChiefRole->id)->orWhere('slug', $divisionChiefRole->slug))
            ->orderBy('id')
            ->first();
    }

    $recommendationOfficerName = $divisionChiefUser?->name ?? 'Division Chief';
    $recommendationOfficerPosition = $divisionChiefRole?->name ?? 'Division Chief';

    $rdUser = User::query()
        ->where('role_id', 5)
        ->orderBy('id')
        ->first();
    $regionalDirectorName = $rdUser?->name
        ?? optional($leaveApplication->regionalDirectorSigner)->name
        ?? 'Regional Director';

    // ── Local file URLs (the print HTML is converted to PDF by headless
    //    Chromium from a file:// context, so assets must be file:// URLs) ──
    $toFileUrl = function (?string $path): ?string {
        if (! $path) {
            return null;
        }
        $normalized = str_replace('\\', '/', $path);
        if (preg_match('#^file://#i', $normalized)) {
            return $normalized;
        }
        return 'file:///' . ltrim($normalized, '/');
    };

    $mgbLogoUrl = $toFileUrl(public_path('assets/mgb logo.png'));
    $fontUrl = $toFileUrl(base_path('assets/font/SCHLBKB.TTF'));
    $signatureFileUrls = [
        'applicant' => $toFileUrl($applicantSignaturePath),
        'hr' => $toFileUrl($hrSignaturePath),
        'divisionChief' => $toFileUrl($divisionChiefSignaturePath),
        'regionalDirector' => $toFileUrl($regionalDirectorSignaturePath),
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle }}</title>
    <style>
        @page { size: A4 portrait; margin: 0.5in; }

        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }

        body {
            font-family: "Century Schoolbook", "Book Antiqua", "Century Schoolbook L", Georgia, "Times New Roman", serif;
            font-size: 8.5pt;
            line-height: 1.5;
            color: #111827;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        @font-face {
            font-family: "Century Schoolbook";
            font-style: normal;
            font-weight: 700;
            src: url("{!! $fontUrl !!}") format("truetype");
        }

        /* The page: A4 with margins set by @page */
        .sheet {
            width: 100%;
            height: auto;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        /* ===== HEADER ===== */
        .form-header {
            position: relative;
            text-align: center;
            padding: 0 0.95in;
            line-height: 1.3;
            flex: none;
        }
        .form-header .logo { position: absolute; left: 0; top: 2px; width: 0.44in; }
        .form-header .cs-tag {
            position: absolute; right: 0; top: 0;
            border: 1px solid #111827;
            padding: 1px 5px;
            font-size: 7pt; font-weight: 700; line-height: 1.3;
        }
        .form-header .rep    { font-size: 8.5pt; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
        .form-header .denr   { font-size: 7.5pt; font-weight: 600; letter-spacing: .03em; text-transform: uppercase; }
        .form-header .mgb    { font-size: 9.5pt; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
        .form-header .region { font-size: 7.5pt; font-weight: 600; }
        .form-header .form-title {
            font-size: 11pt; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
            border-top: 1.5px solid #111827;
            border-bottom: 1.5px solid #111827;
            padding: 2px 0;
            margin-top: 3px;
        }

        /* ===== PANELS ===== */
        .panel { border: 1px solid #111827; margin-top: 6px; display: flex; flex-direction: column; flex: none; }
        .panel--grow { flex: 1 1 auto; min-height: 0; }
        .panel-title {
            background: #e5e7eb;
            border-bottom: 1px solid #111827;
            padding: 0 6px;
            font-size: 7.5pt; font-weight: 700;
            text-transform: uppercase; letter-spacing: .02em;
            line-height: 1.6;
            flex: none;
        }

        /* ===== GRIDS ===== */
        .grid { display: grid; flex: 1 1 auto; min-height: 0; }
        .grid > .cell {
            border-right: 1px solid #111827;
            border-bottom: 1px solid #111827;
            padding: 2px 7px;
            min-width: 0;
            min-height: 0;
        }
        .grid > .cell:last-child { border-right: none; }
        .grid > .cell.head {
            background: #f3f4f6;
            font-size: 7pt; font-weight: 700;
            text-transform: uppercase; letter-spacing: .02em;
        }
        .grid-2 { grid-template-columns: 1fr 1fr; }
        .grid-2 > .cell:nth-child(even) { border-right: none; }
        .grid-4 > .cell:nth-child(4n) { border-right: none; }

        /* ===== CHECK ROWS ===== */
        .check { display: flex; align-items: center; gap: 7px; line-height: 1.4; }
        .check .box {
            flex: none;
            width: 10px; height: 10px;
            border: 1px solid #111827;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 6.5pt; font-weight: 700; line-height: 1;
        }
        .check-list { display: flex; flex-direction: column; justify-content: space-evenly; flex: 1 1 auto; min-height: 0; }
        .label-sm { font-size: 6.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; }
        .field-value { font-weight: 600; }

        /* ===== WRITING LINES (stretch to occupy the space) ===== */
        .write-lines { flex: 1 1 auto; display: flex; flex-direction: column; min-height: 0.25in; }
        .write-lines > div { flex: 1 1 0; border-bottom: 1px solid #6b7280; }

        /* ===== SIGNATURES ===== */
        .sig-block {
            display: flex; flex-direction: column; align-items: center;
            margin-top: auto; padding-top: 4px;
            text-align: center;
        }
        .sig-img { max-height: 0.32in; max-width: 1.15in; object-fit: contain; }
        .sig-space { height: 0.32in; }
        .sig-line { width: 82%; border-top: 1px solid #111827; }
        .sig-name { font-weight: 700; font-size: 7.5pt; line-height: 1.35; }
        .sig-role { font-size: 6.5pt; color: #374151; line-height: 1.35; }
        .sig-caption { font-size: 6pt; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; line-height: 1.35; }

        /* ===== 7.A CREDITS TABLE ===== */
        .credits { width: 100%; border-collapse: collapse; margin-top: 3px; font-size: 7.5pt; line-height: 1.4; }
        .credits th, .credits td { border: 1px solid #111827; padding: 0 5px; }
        .credits th { background: #f3f4f6; text-align: center; font-weight: 700; }
        .credits td { text-align: center; }
        .credits td.lbl { text-align: left; font-weight: 600; }

        /* name cell hints */
        .hint { font-weight: 400; font-size: 6.5pt; color: #6b7280; }
    </style>
</head>
<body>
    <div class="sheet">
        <!-- ===== HEADER ===== -->
        <header class="form-header">
            @if ($mgbLogoUrl)
                <img class="logo" src="{{ $mgbLogoUrl }}" alt="MGB Logo">
            @endif
            <div class="cs-tag">CS Form No. 6<br>Revised 2020</div>
            <div class="rep">Republic of the Philippines</div>
            <div class="denr">Department of Environment and Natural Resources</div>
            <div class="mgb">Mines and Geosciences Bureau</div>
            <div class="region">Regional Office VI, Iloilo City</div>
            <div class="form-title">Application for Leave</div>
        </header>

        <!-- ===== I. EMPLOYEE INFORMATION ===== -->
        <section class="panel">
            <div class="panel-title">I. Employee Information</div>
            <div class="grid grid-4" style="grid-template-columns: 17% 33% 12% 38%;">
                <div class="cell head">1. Division</div>
                <div class="cell">{{ $divisionName }}</div>
                <div class="cell head">2. Name</div>
                <div class="cell">
                    <span class="hint">(Last)</span> <strong>{{ $employee?->last_name ?? '—' }}</strong>
                    <span class="hint">(First)</span> <strong>{{ $employee?->first_name ?? '—' }}</strong>
                    <span class="hint">(Middle)</span> <strong>{{ $employee?->middle_name ?? ($employee?->middlename ?? '—') }}</strong>
                </div>
                <div class="cell head">3. Date of Filing</div>
                <div class="cell">{{ $dateApplied }}</div>
                <div class="cell head">4. Position</div>
                <div class="cell">{{ $position }}</div>
                <div class="cell head edge-b">5. Salary</div>
                <div class="cell edge-b" style="grid-column: 2 / 5;">{{ $salary }}</div>
            </div>
        </section>
        <!-- ===== II. DETAILS OF APPLICATION ===== -->
        <section class="panel">
            <div class="panel-title">II. Details of Application</div>
            <div class="grid grid-2">
                <div class="cell head">6.A Type of Leave to be Availed Of</div>
                <div class="cell head">6.B Details of Leave</div>

                <div class="cell">
                    <div class="check-list">
                        @foreach ($checklist as $label => $needles)
                            <div class="check">
                                <span class="box">{{ $isChecked($needles) ? 'X' : '' }}</span>
                                <span>{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="cell" style="display: flex; flex-direction: column;">
                    <div class="check-list">
                        <div class="label-sm">VACATION / SPECIAL PRIVILEGE:</div>
                        <div class="check"><span class="box"></span><span>Within the Philippines</span></div>
                        <div class="check"><span class="box"></span><span>Abroad (Specify)</span></div>

                        <div class="label-sm">SICK LEAVE:</div>
                        <div class="check"><span class="box"></span><span>In Hospital</span></div>
                        <div class="check"><span class="box"></span><span>Out Patient</span></div>

                        <div class="label-sm">SPECIAL LEAVE BENEFITS:</div>
                        <div class="check"><span class="box"></span><span>Specify Illness</span></div>

                        <div class="label-sm">STUDY LEAVE:</div>
                        <div class="check"><span class="box"></span><span>Master's Degree</span></div>
                        <div class="check"><span class="box"></span><span>Board Review</span></div>

                        <div class="label-sm">OTHER PURPOSE:</div>
                        <div class="check"><span class="box"></span><span>Monetization of Leave Credits</span></div>
                        <div class="check"><span class="box"></span><span>Terminal Leave</span></div>
                    </div>
                </div>

                <div class="cell head">6.C Number of Working Days Applied For</div>
                <div class="cell head">6.D Commutation</div>

                <div class="cell edge-b">
                    <div class="field-value">{{ $numberOfDays }} working day(s)</div>
                    <div class="label-sm">Inclusive Dates:</div>
                    <div class="field-value">{{ $dateFrom }} to {{ $dateTo }}</div>
                </div>

                <div class="cell edge-b" style="display: flex; flex-direction: column;">
                    <div>
                        <div class="check"><span class="box">X</span><span>Not Requested</span></div>
                        <div class="check"><span class="box"></span><span>Requested</span></div>
                    </div>
                    <div class="sig-block">
                        @if ($signatureFileUrls['applicant'])
                            <img class="sig-img" src="{{ $signatureFileUrls['applicant'] }}" alt="Applicant Signature">
                        @else
                            <div class="sig-space"></div>
                        @endif
                        <div class="sig-line"></div>
                        <div class="sig-name">{{ $employeeName }}</div>
                        <div class="sig-caption">(Signature of Applicant)</div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ===== III. DETAILS OF ACTION ON APPLICATION ===== -->
        <section class="panel panel--grow">
            <div class="panel-title">III. Details of Action on Application</div>
            <div class="grid grid-2" style="grid-template-rows: auto 1fr auto auto;">
                <div class="cell head">7.A Certification of Leave Credits</div>
                <div class="cell head">7.B Recommendation</div>

                <div class="cell tall" style="display: flex; flex-direction: column;">
                    <div class="label-sm">As of {{ $leaveCredits['as_of'] }}</div>
                    <table class="credits">
                        <tr>
                            <td class="lbl">&nbsp;</td>
                            <th>Vacation Leave</th>
                            <th>Sick Leave</th>
                        </tr>
                        <tr>
                            <td class="lbl">Total Earned</td>
                            <td>{{ number_format($leaveCredits['vacation_earned'], 2) }}</td>
                            <td>{{ number_format($leaveCredits['sick_earned'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Less this application</td>
                            <td>{{ $leaveCredits['vacation_less'] > 0 ? '-' : '' }}{{ number_format($leaveCredits['vacation_less'], 2) }}</td>
                            <td>{{ $leaveCredits['sick_less'] > 0 ? '-' : '' }}{{ number_format($leaveCredits['sick_less'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl">Balance</td>
                            <td>{{ number_format($leaveCredits['vacation_balance'], 2) }}</td>
                            <td>{{ number_format($leaveCredits['sick_balance'], 2) }}</td>
                        </tr>
                    </table>
                    <div class="sig-block">
                        @if ($signatureFileUrls['hr'])
                            <img class="sig-img" src="{{ $signatureFileUrls['hr'] }}" alt="HR Signature">
                        @else
                            <div class="sig-space"></div>
                        @endif
                        <div class="sig-line"></div>
                        <div class="sig-name">{{ $certificationOfficerName }}</div>
                        <div class="sig-role">{{ $certificationOfficerPosition }}</div>
                        <div class="sig-caption">Authorized Officer</div>
                    </div>
                </div>

                <div class="cell tall" style="display: flex; flex-direction: column;">
                    <div class="check"><span class="box">X</span><span>For approval</span></div>
                    <div class="check"><span class="box"></span><span>For disapproval due to:</span></div>
                    <div class="write-lines"><div></div><div></div><div></div></div>
                    <div class="sig-block">
                        @if ($signatureFileUrls['divisionChief'])
                            <img class="sig-img" src="{{ $signatureFileUrls['divisionChief'] }}" alt="Division Chief Signature">
                        @else
                            <div class="sig-space"></div>
                        @endif
                        <div class="sig-line"></div>
                        <div class="sig-name">{{ $recommendationOfficerName }}</div>
                        <div class="sig-role">{{ $recommendationOfficerPosition }}</div>
                        <div class="sig-caption">Authorized Officer</div>
                    </div>
                </div>

                <div class="cell head">7.C Approved For:</div>
                <div class="cell head">7.D Disapproved Due To:</div>

                <div class="cell edge-b">
                    <div class="check"><span class="box"></span><span>_______ days with pay</span></div>
                    <div class="check"><span class="box"></span><span>_______ days without pay</span></div>
                    <div class="check"><span class="box"></span><span>_______ others (Specify): ________________</span></div>
                </div>

                <div class="cell edge-b">
                    <div class="write-lines" style="min-height: 0.75in;"><div></div><div></div><div></div></div>
                </div>
            </div>

            <div class="sig-block" style="padding-top: 6px;">
                @if ($signatureFileUrls['regionalDirector'])
                    <img class="sig-img" src="{{ $signatureFileUrls['regionalDirector'] }}" alt="Regional Director Signature">
                @else
                    <div class="sig-space"></div>
                @endif
                <div class="sig-line"></div>
                <div class="sig-name">{{ $regionalDirectorName }}</div>
                <div class="sig-role">Regional Director</div>
                <div class="sig-caption">Authorized Officer</div>
            </div>
        </section>
    </div>
</body>
</html>
