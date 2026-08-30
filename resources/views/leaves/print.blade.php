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

    $divisionChiefRoleMap = [];
    if (Schema::connection('inventory')->hasTable('departments') && Schema::connection('inventory')->hasColumn('departments', 'division_chief_role_id')) {
        $departments = Department::query()
            ->whereNotNull('division_chief_role_id')
            ->get(['dept_no', 'division_chief_role_id']);
        $roles = Role::query()
            ->whereIn('id', $departments->pluck('division_chief_role_id')->filter()->unique()->values())
            ->get(['id', 'name', 'slug'])
            ->keyBy('id');
        foreach ($departments as $dept) {
            $role = $roles->get($dept->division_chief_role_id);
            if ($role) {
                $divisionChiefRoleMap[(int) $dept->dept_no] = $role;
            }
        }
    }

    $divisionChiefRole = $divisionChiefRoleMap[$employeeDeptNo] ?? null;
    $divisionChiefUser = $divisionChiefRole
        ? User::query()->where('role_id', $divisionChiefRole->id)->orderBy('id')->first()
        : null;

    $recommendationOfficerName = optional($leaveApplication->divisionChiefSigner)->name
        ?? $divisionChiefUser?->name
        ?? 'Division Chief';
    $recommendationOfficerPosition = optional($leaveApplication->divisionChiefSigner?->role)->name
        ?? $divisionChiefRole?->name
        ?? 'Division Chief';

    $rdUser = User::query()
        ->where('role_id', 5)
        ->orderBy('id')
        ->first();
    $regionalDirectorName = $rdUser?->name
        ?? optional($leaveApplication->regionalDirectorSigner)->name
        ?? 'Regional Director';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle }}</title>
    <style>{!! $leavePrintCss ?? '' !!}</style>
    <style>
        @font-face {
            font-family: "Century Schoolbook";
            font-style: normal;
            font-weight: 700;
            src: url("{{ base_path('assets/font/SCHLBKB.TTF') }}") format("truetype");
        }

        body,
        .form-table td,
        .form-table th,
        .checkbox-item,
        .label-sm,
        .field-value,
        .signature-name,
        .details-of-leave,
        .details-of-leave * {
            font-family: "Century Schoolbook", "DejaVu Serif", serif;
        }

        .details-of-leave .leave-detail-copy,
        .details-of-leave .checkbox-item,
        .details-of-leave .label-sm {
            line-height: 1.15;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <!-- ===== HEADER ===== -->
        <div class="header">
            <img src="{{ public_path('assets/mgb logo.png') }}" alt="MGB Logo" class="header-logo">
            <div class="header-cs-form">CS Form No. 6<br>Revised 2020</div>
            <div class="header-republic">Republic of the Philippines</div>
            <div class="header-denr">Department of Environment and Natural Resources</div>
            <div class="header-mgb">MINES AND GEOSCIENCES BUREAU</div>
            <div class="header-region">Regional Office VI, Iloilo City</div>
            <div class="header-form-title">APPLICATION FOR LEAVE</div>
        </div>

        <!-- ===== SECTION 1-5: BASIC INFO ===== -->
        <div class="section employee-information-section">
            <div class="section-title">I. Employee Information</div>
            <div class="section-body">
                <table class="form-table">
                    <tr>
                        <th style="width:15%;">1. Office/Department</th>
                        <td style="width:35%;">{{ $divisionName }}</td>
                        <th style="width:10%;">2. Name</th>
                        <td style="width:40%;">
                            <span style="font-weight:400;font-size:7px;color:#6b7280;">(Last)</span>
                            <strong>{{ $employee?->last_name ?? '—' }}</strong>
                            &nbsp;
                            <span style="font-weight:400;font-size:7px;color:#6b7280;">(First)</span>
                            <strong>{{ $employee?->first_name ?? '—' }}</strong>
                            &nbsp;
                            <span style="font-weight:400;font-size:7px;color:#6b7280;">(Middle)</span>
                            <strong>{{ $employee?->middle_name ?? ($employee?->middlename ?? '—') }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <th>3. Date of Filing</th>
                        <td>{{ $dateApplied }}</td>
                        <th>4. Position</th>
                        <td>{{ $position }}</td>
                    </tr>
                    <tr>
                        <th>5. Salary</th>
                        <td colspan="3">{{ $salary }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ===== SECTION 6: DETAILS OF APPLICATION ===== -->
        <div class="section leave-details-section">
            <div class="section-title">II. Details of Application</div>
            <div class="section-body">
                <table class="form-table">
                    <tr>
                        <th style="width:50%;">6.A Type of Leave to be Availed Of</th>
                        <th style="width:50%;">6.B Details of Leave</th>
                    </tr>
                    <tr>
                        <td style="vertical-align:top;padding:3px 5px;">
                            @foreach($checklist as $label => $needles)
                                <div class="checkbox-item" style="height:8.4px;padding:4.4px 3px 0.8px 0;">
                                    <span class="box">{{ $isChecked($needles) ? 'X' : '' }}</span>
                                    <span>{{ $label }}</span>
                                </div>
                            @endforeach
                        </td>
                        <td class="details-of-leave" style="vertical-align:top;padding:3px 5px;">
                            <div class="leave-detail-copy">
                                <div class="label-sm" style="height:8.4px;padding:3.2px 0 2px 0;">VACATION / SPECIAL PRIVILEGE:</div>
                                <div class="checkbox-item" style="height:8.4px;padding:4.4px 0 0.8px 0;margin:0;">
                                    <span class="box"></span>
                                    <span>Within the Philippines</span>
                                </div>
                                <div class="checkbox-item" style="height:8.4px;padding:4.4px 0 0.8px 0;margin:0;">
                                    <span class="box"></span>
                                    <span>Abroad (Specify)</span>
                                </div>

                                <div class="label-sm" style="height:8.4px;padding:3.2px 0 2px 0;">SICK LEAVE:</div>
                                <div class="checkbox-item" style="height:8.4px;padding:4.4px 0 0.8px 0;margin:0;">
                                    <span class="box"></span>
                                    <span>In Hospital</span>
                                </div>
                                <div class="checkbox-item" style="height:8.4px;padding:4.4px 0 0.8px 0;margin:0;">
                                    <span class="box"></span>
                                    <span>Out Patient</span>
                                </div>

                                <div class="label-sm" style="height:8.4px;padding:3.2px 0 2px 0;">SPECIAL LEAVE BENEFITS:</div>
                                <div class="checkbox-item" style="height:8.4px;padding:4.4px 0 0.8px 0;margin:0;">
                                    <span class="box"></span>
                                    <span>Specify Illness</span>
                                </div>

                                <div class="label-sm" style="height:8.4px;padding:3.2px 0 2px 0;">STUDY LEAVE:</div>
                                <div class="checkbox-item" style="height:8.4px;padding:4.4px 0 0.8px 0;margin:0;">
                                    <span class="box"></span>
                                    <span>Master's Degree</span>
                                </div>
                                <div class="checkbox-item" style="height:8.4px;padding:4.4px 0 0.8px 0;margin:0;">
                                    <span class="box"></span>
                                    <span>Board Review</span>
                                </div>

                                <div class="label-sm" style="height:8.4px;padding:3.2px 0 2px 0;">OTHER PURPOSE:</div>
                                <div class="checkbox-item" style="height:8.4px;padding:4.4px 0 0.8px 0;margin:0;">
                                    <span class="box"></span>
                                    <span>Monetization of Leave Credits</span>
                                </div>
                                <div class="checkbox-item" style="height:8.4px;padding:4.4px 0 0.8px 0;margin:0;">
                                    <span class="box"></span>
                                    <span>Terminal Leave</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>6.C Number of Working Days Applied For</th>
                        <th>6.D Commutation</th>
                    </tr>
                    <tr>
                        <td style="padding:2px 5px;">
                            <div class="field-value" style="font-size:10px;text-align:center;min-height:14px;">{{ $numberOfDays }} working day(s)</div>
                            <div class="label-sm" style="margin-top:3px;">Inclusive Dates:</div>
                            <div class="field-value" style="text-align:center;">{{ $dateFrom }} to {{ $dateTo }}</div>
                        </td>
                        <td style="padding:2px 5px;vertical-align:top;">
                            <div class="checkbox-item" style="margin-bottom:2px;">
                                <span class="box">X</span>
                                <span>Not Requested</span>
                            </div>
                            <div class="checkbox-item" style="margin-bottom:2px;">
                                <span class="box"></span>
                                <span>Requested</span>
                            </div>
                            <div class="blank-line" style="margin-top:1px;min-height:10px;"></div>
                            <div class="signature-block" style="text-align:center;margin-top:3px;">
                                @if($applicantSignaturePath)
                                    <img src="{{ $applicantSignaturePath }}" alt="Applicant Signature" class="signature-img" style="max-width:100px;max-height:22px;object-fit:contain;display:block;margin:0 auto 2px auto;">
                                @else
                                    <div style="min-height:22px;"></div>
                                @endif
                                <div class="signature-line"></div>
                                <div class="signature-name" style="font-size:8px;font-weight:700;">{{ $employeeName }}</div>
                                <div class="label-sm" style="text-align:center;margin-top:1px;">(SIGNATURE OF APPLICANT)</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ===== SECTION 7: DETAILS OF ACTION ON APPLICATION ===== -->
        <div class="section action-on-application-section">
            <div class="section-title">III. Details of Action on Application</div>
            <div class="section-body">
                <table class="form-table">
                    <tr>
                        <th style="width:50%;">7.A Certification of Leave Credits</th>
                        <th style="width:50%;">7.B Recommendation</th>
                    </tr>
                    <tr>
                        <td class="row-tall" style="vertical-align:top;padding:2px 5px;">
                            <div class="label-sm">As of _______________________</div>
                            <table style="width:100%;border-collapse:collapse;margin-top:3px;">
                                <tr>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;font-size:7.5px;font-weight:700;background:#f3f4f6;">&nbsp;</td>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;font-size:7.5px;font-weight:700;background:#f3f4f6;text-align:center;">Vacation Leave</td>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;font-size:7.5px;font-weight:700;background:#f3f4f6;text-align:center;">Sick Leave</td>
                                </tr>
                                <tr>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;font-size:8px;font-weight:600;">Total Earned</td>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;font-size:8px;font-weight:600;">Less this application</td>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;font-size:8px;font-weight:600;">Balance</td>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                    <td style="padding:1.5px 3px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                </tr>
                            </table>
                            <div class="sig-spacer"></div>
                            <div class="signature-block" style="margin-top:5px;text-align:center;">
                                @if($hrSignaturePath)
                                    <img src="{{ $hrSignaturePath }}" alt="HR Signature" class="signature-img" style="max-width:100px;max-height:22px;object-fit:contain;display:block;margin:0 auto 2px auto;">
                                @else
                                    <div style="min-height:22px;"></div>
                                @endif
                                <div class="signature-line"></div>
                                <div class="signature-name" style="font-size:8px;font-weight:700;">{{ $certificationOfficerName }}</div>
                                <div style="font-size:7px;color:#374151;">{{ $certificationOfficerPosition }}</div>
                                <div class="label-sm" style="text-align:center;">AUTHORIZED OFFICER</div>
                            </div>
                        </td>
                        <td class="row-tall" style="vertical-align:top;padding:2px 5px;">
                            <div class="checkbox-item" style="margin-bottom:2px;">
                                <span class="box">X</span>
                                <span>For approval</span>
                            </div>
                            <div class="checkbox-item" style="margin-bottom:2px;">
                                <span class="box"></span>
                                <span>For disapproval due to:</span>
                            </div>
                            <div class="blank-line" style="margin-bottom:2px;min-height:9px;"></div>
                            <div class="blank-line" style="margin-bottom:2px;min-height:9px;"></div>
                            <div class="blank-line" style="margin-bottom:4px;min-height:9px;"></div>
                            <div class="sig-spacer"></div>
                            <div class="signature-block" style="margin-top:5px;text-align:center;">
                                @if($divisionChiefSignaturePath)
                                    <img src="{{ $divisionChiefSignaturePath }}" alt="Division Chief Signature" class="signature-img" style="max-width:100px;max-height:22px;object-fit:contain;display:block;margin:0 auto 2px auto;">
                                @else
                                    <div style="min-height:22px;"></div>
                                @endif
                                <div class="signature-line"></div>
                                <div class="signature-name" style="font-size:8px;font-weight:700;">{{ $recommendationOfficerName }}</div>
                                <div style="font-size:7px;color:#374151;">{{ $recommendationOfficerPosition }}</div>
                                <div class="label-sm" style="text-align:center;">AUTHORIZED OFFICER</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>7.C Approved For:</th>
                        <th>7.D Disapproved Due To:</th>
                    </tr>
                    <tr>
                        <td class="row-short" style="padding:2px 5px;">
                            <div class="checkbox-item" style="margin-bottom:2px;">
                                <span class="box"></span>
                                <span>_______ days with pay</span>
                            </div>
                            <div class="checkbox-item" style="margin-bottom:2px;">
                                <span class="box"></span>
                                <span>_______ days without pay</span>
                            </div>
                            <div class="checkbox-item">
                                <span class="box"></span>
                                <span>_______ others (Specify): ________________</span>
                            </div>
                        </td>
                        <td class="row-short" style="padding:2px 5px;">
                            <div class="blank-line" style="margin-bottom:3px;min-height:10px;"></div>
                            <div class="blank-line" style="margin-bottom:3px;min-height:10px;"></div>
                            <div class="blank-line" style="margin-bottom:3px;min-height:10px;"></div>
                        </td>
                    </tr>
                </table>

                <div class="signature-block rd-signature" style="text-align:center;">
                    @if($regionalDirectorSignaturePath)
                        <img src="{{ $regionalDirectorSignaturePath }}" alt="Regional Director Signature" class="signature-img" style="max-width:100px;max-height:22px;object-fit:contain;display:block;margin:0 auto 2px auto;">
                    @else
                        <div style="min-height:22px;"></div>
                    @endif
                    <div class="signature-line"></div>
                    <div class="signature-name" style="font-size:8px;font-weight:700;">{{ $regionalDirectorName }}</div>
                    <div style="font-size:7px;color:#374151;">Regional Director</div>
                    <div class="label-sm" style="text-align:center;">AUTHORIZED OFFICER</div>
                </div>
            </div></div></div></body>
</html>