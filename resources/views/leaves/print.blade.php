@php
    use App\Models\EmployeeLeaveBenefit;
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

    // ── Build checklist dynamically from employee_leave_benefits.credit_type ──
    $creditTypes = EmployeeLeaveBenefit::distinct()
        ->pluck('credit_type')
        ->filter()
        ->values()
        ->all();

    $checklistLabels = [
        'Vacation Leave' => 'Vacation Leave (Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'Sick Leave' => 'Sick Leave (Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'Maternity Leave' => 'Maternity Leave (R.A. No. 11210 / IRR issued by CSC, DOLE and SSS)',
        'Paternity Leave' => 'Paternity Leave (R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended)',
        'Special Privilege Leave' => 'Special Privilege Leave (Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'Solo Parent Leave' => 'Solo Parent Leave (RA No. 8972 / CSC MC No. 8, s. 2004)',
        'Study Leave' => 'Study Leave (Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'Rehabilitation Leave' => 'Rehabilitation Privilege (Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No. 292)',
        'Special Emergency Leave' => 'Special Emergency (Calamity) Leave (CSC MC No. 2, s. 2012, as amended)',
        'Wellness Leave' => 'Wellness Leave',
        'Credited Time-Off' => 'Credited Time-Off',
    ];

    $checklist = [];
    foreach ($creditTypes as $ct) {
        $label = $checklistLabels[$ct] ?? $ct;
        $needles = [strtolower($ct)];
        if (str_contains(strtolower($ct), 'vacation')) $needles[] = 'vacation';
        if (str_contains(strtolower($ct), 'sick')) $needles[] = 'sick';
        if (str_contains(strtolower($ct), 'maternity')) $needles[] = 'maternity';
        if (str_contains(strtolower($ct), 'paternity')) $needles[] = 'paternity';
        if (str_contains(strtolower($ct), 'privilege')) $needles[] = 'special privilege';
        if (str_contains(strtolower($ct), 'solo')) $needles[] = 'solo parent';
        if (str_contains(strtolower($ct), 'rehabilitation')) $needles[] = 'rehabilitation';
        if (str_contains(strtolower($ct), 'emergency')) $needles[] = 'calamity';
        if (str_contains(strtolower($ct), 'wellness')) $needles[] = 'wellness';
        if (str_contains(strtolower($ct), 'credited')) { $needles[] = 'credited time-off'; $needles[] = 'cto'; }
        $checklist[$label] = $needles;
    }

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

    $rdRole = Role::query()->where('slug', 'rd')->first();
    $rdUser = $rdRole ? User::query()->where('role_id', $rdRole->id)->orderBy('id')->first() : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle }}</title>
    <style>{!! $leavePrintCss ?? '' !!}</style>
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
        <div class="section">
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
        <div class="section">
            <div class="section-title">II. Details of Application</div>
            <div class="section-body">
                <table class="form-table">
                    <tr>
                        <th style="width:50%;">6.A Type of Leave to be Availed Of</th>
                        <th style="width:50%;">6.B Details of Leave</th>
                    </tr>
                    <tr>
                        <td style="vertical-align:top;padding:3px 5px;">
                            <table class="checkbox-grid">
                                @foreach(collect($checklist)->chunk(2) as $pair)
                                    <tr>
                                        @foreach($pair as $label => $needles)
                                            <td style="width:50%;padding:1.5px 3px 1.5px 0;">
                                                <div class="checkbox-item">
                                                    <span class="box">{{ $isChecked($needles) ? 'X' : '' }}</span>
                                                    <span>{{ $label }}</span>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="padding:1.5px 3px 1.5px 0;">
                                        <div class="checkbox-item">
                                            <span class="box"></span>
                                            <span>Others: ________________________</span>
                                        </div>
                                    </td>
                                    <td style="padding:1.5px 3px 1.5px 0;"></td>
                                </tr>
                            </table>
                        </td>
                        <td style="vertical-align:top;padding:3px 5px;">
                            <div class="label-sm">In case of Vacation/Special Privilege Leave:</div>
                            <div class="checkbox-item" style="margin:2px 0;">
                                <span class="box"></span>
                                <span>Within the Philippines _________________________</span>
                            </div>
                            <div class="checkbox-item" style="margin:2px 0;">
                                <span class="box"></span>
                                <span>Abroad (Specify) ____________________________</span>
                            </div>

                            <div class="label-sm" style="margin-top:4px;">In case of Sick Leave:</div>
                            <div class="checkbox-item" style="margin:2px 0;">
                                <span class="box"></span>
                                <span>In Hospital (Specify Illness) ____________________</span>
                            </div>
                            <div class="checkbox-item" style="margin:2px 0;">
                                <span class="box"></span>
                                <span>Out Patient (Specify Illness) ___________________</span>
                            </div>

                            <div class="label-sm" style="margin-top:4px;">In case of Special Leave Benefits for Women:</div>
                            <div class="label-sm">(Specify Illness) ________________________________</div>

                            <div class="label-sm" style="margin-top:4px;">In case of Study Leave:</div>
                            <div class="checkbox-item" style="margin:2px 0;">
                                <span class="box"></span>
                                <span>Completion of Master's Degree</span>
                            </div>
                            <div class="checkbox-item" style="margin:2px 0;">
                                <span class="box"></span>
                                <span>BAR/Board Examination Review</span>
                            </div>

                            <div class="label-sm" style="margin-top:4px;">Other purpose:</div>
                            <div class="checkbox-item" style="margin:2px 0;">
                                <span class="box"></span>
                                <span>Monetization of Leave Credits</span>
                            </div>
                            <div class="checkbox-item" style="margin:2px 0;">
                                <span class="box"></span>
                                <span>Terminal Leave</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>6.C Number of Working Days Applied For</th>
                        <th>6.D Commutation</th>
                    </tr>
                    <tr>
                        <td style="padding:3px 5px;">
                            <div class="field-value" style="font-size:11px;text-align:center;min-height:18px;">{{ $numberOfDays }} working day(s)</div>
                            <div class="label-sm" style="margin-top:3px;">Inclusive Dates:</div>
                            <div class="field-value" style="text-align:center;">{{ $dateFrom }} to {{ $dateTo }}</div>
                        </td>
                        <td style="padding:3px 5px;vertical-align:top;">
                            <div class="checkbox-item" style="margin-bottom:3px;">
                                <span class="box">X</span>
                                <span>Not Requested</span>
                            </div>
                            <div class="checkbox-item" style="margin-bottom:3px;">
                                <span class="box"></span>
                                <span>Requested</span>
                            </div>
                            <div class="blank-line" style="margin-top:2px;min-height:14px;"></div>
                            <div style="text-align:center;margin-top:5px;">
                                @if($applicantSignaturePath)
                                    <img src="{{ $applicantSignaturePath }}" alt="Applicant Signature" style="max-width:100px;max-height:28px;object-fit:contain;margin-bottom:2px;">
                                @endif
                                <div style="font-size:8px;font-weight:600;">{{ $employeeName }}</div>
                                <div style="border-top:1.5px solid #111827;margin-top:2px;padding-top:2px;"></div>
                                <div class="label-sm" style="text-align:center;margin-top:1px;">(SIGNATURE OF APPLICANT)</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- ===== SECTION 7: DETAILS OF ACTION ON APPLICATION ===== -->
        <div class="section">
            <div class="section-title">III. Details of Action on Application</div>
            <div class="section-body">
                <table class="form-table">
                    <tr>
                        <th style="width:50%;">7.A Certification of Leave Credits</th>
                        <th style="width:50%;">7.B Recommendation</th>
                    </tr>
                    <tr>
                        <td style="vertical-align:top;padding:3px 5px;">
                            <div class="label-sm">As of _______________________</div>
                            <table style="width:100%;border-collapse:collapse;margin-top:4px;">
                                <tr>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;font-size:7.5px;font-weight:700;background:#f3f4f6;">&nbsp;</td>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;font-size:7.5px;font-weight:700;background:#f3f4f6;text-align:center;">Vacation Leave</td>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;font-size:7.5px;font-weight:700;background:#f3f4f6;text-align:center;">Sick Leave</td>
                                </tr>
                                <tr>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;font-size:8px;font-weight:600;">Total Earned</td>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;font-size:8px;font-weight:600;">Less this application</td>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;font-size:8px;font-weight:600;">Balance</td>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                    <td style="padding:2px 4px;border:1.5px solid #111827;text-align:center;">&nbsp;</td>
                                </tr>
                            </table>
                            <div style="margin-top:8px;text-align:center;">
                                @if($hrSignaturePath)
                                    <img src="{{ $hrSignaturePath }}" alt="HR Signature" style="max-width:100px;max-height:28px;object-fit:contain;margin-bottom:2px;">
                                @else
                                    <div style="min-height:28px;"></div>
                                @endif
                                <div style="font-size:8px;font-weight:700;">{{ $certificationOfficerName }}</div>
                                <div style="font-size:7px;color:#374151;">{{ $certificationOfficerPosition }}</div>
                                <div style="border-top:1.5px solid #111827;margin-top:2px;padding-top:2px;"></div>
                                <div class="label-sm" style="text-align:center;">AUTHORIZED OFFICER</div>
                            </div>
                        </td>
                        <td style="vertical-align:top;padding:3px 5px;">
                            <div class="checkbox-item" style="margin-bottom:3px;">
                                <span class="box">X</span>
                                <span>For approval</span>
                            </div>
                            <div class="checkbox-item" style="margin-bottom:3px;">
                                <span class="box"></span>
                                <span>For disapproval due to:</span>
                            </div>
                            <div class="blank-line" style="margin-bottom:3px;min-height:12px;"></div>
                            <div class="blank-line" style="margin-bottom:3px;min-height:12px;"></div>
                            <div class="blank-line" style="margin-bottom:6px;min-height:12px;"></div>
                            <div style="margin-top:8px;text-align:center;">
                                @if($divisionChiefSignaturePath)
                                    <img src="{{ $divisionChiefSignaturePath }}" alt="Division Chief Signature" style="max-width:100px;max-height:28px;object-fit:contain;margin-bottom:2px;">
                                @else
                                    <div style="min-height:28px;"></div>
                                @endif
                                <div style="font-size:8px;font-weight:700;">{{ $recommendationOfficerName }}</div>
                                <div style="font-size:7px;color:#374151;">{{ $recommendationOfficerPosition }}</div>
                                <div style="border-top:1.5px solid #111827;margin-top:2px;padding-top:2px;"></div>
                                <div class="label-sm" style="text-align:center;">AUTHORIZED OFFICER</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>7.C Approved For:</th>
                        <th>7.D Disapproved Due To:</th>
                    </tr>
                    <tr>
                        <td style="padding:3px 5px;">
                            <div class="checkbox-item" style="margin-bottom:3px;">
                                <span class="box"></span>
                                <span>_______ days with pay</span>
                            </div>
                            <div class="checkbox-item" style="margin-bottom:3px;">
                                <span class="box"></span>
                                <span>_______ days without pay</span>
                            </div>
                            <div class="checkbox-item">
                                <span class="box"></span>
                                <span>_______ others (Specify): ________________</span>
                            </div>
                        </td>
                        <td style="padding:3px 5px;">
                            <div class="blank-line" style="margin-bottom:4px;min-height:14px;"></div>
                            <div class="blank-line" style="margin-bottom:4px;min-height:14px;"></div>
                            <div class="blank-line" style="margin-bottom:4px;min-height:14px;"></div>
                        </td>
                    </tr>
                </table>

                <div style="text-align:center;margin-top:8px;">
                    @if($regionalDirectorSignaturePath)
                        <img src="{{ $regionalDirectorSignaturePath }}" alt="Regional Director Signature" style="max-width:100px;max-height:28px;object-fit:contain;margin-bottom:2px;">
                    @else
                        <div style="min-height:28px;"></div>
                    @endif
                    <div style="font-size:8px;font-weight:700;">{{ optional($leaveApplication->regionalDirectorSigner)->name ?? 'Regional Director' }}</div>
                    <div style="font-size:7px;color:#374151;">Regional Director</div>
                    <div style="border-top:1.5px solid #111827;margin-top:2px;padding-top:2px;"></div>
                    <div class="label-sm" style="text-align:center;">AUTHORIZED OFFICER</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

