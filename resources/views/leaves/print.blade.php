@php
    use Illuminate\Support\Str;

    $employeeName = $employee?->full_name ?? 'N/A';
    $employeeId = $employee?->employee_id ?? 'N/A';
    $divisionName = $employee?->division?->name ?? $employee?->division?->code ?? 'N/A';
    $position = $employee?->position ?? 'N/A';
    $salary = $employee?->salary ?? 'â€”';

    $leaveType = $leaveApplication->leave_type ?? 'N/A';
    $dateFrom = $leaveApplication->date_from?->format('F d, Y') ?? 'N/A';
    $dateTo = $leaveApplication->date_to?->format('F d, Y') ?? 'Open ended';
    $dateApplied = $leaveApplication->created_at?->format('F d, Y') ?? 'N/A';
    $timeApplied = $leaveApplication->created_at?->format('h:i A') ?? 'N/A';
    $reason = $leaveApplication->reason ?: 'â€”';

    $statusKey = (string) $leaveApplication->status;
    $statusLabel = Str::headline(str_replace(['pending_', '_'], ['pending ', ' '], strtolower($statusKey)));
    $statusLabel = $statusLabel ?: 'N/A';

    $pageTitle = 'CS Form 6 - Application for Leave';

    $selected = strtolower($leaveType);
    $checklist = [
        'Vacation Leave (Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No. 292)' => ['vacation leave', 'vacation'],
        'Mandatory/Forced Leave (Sec. 25, Rule XVI, Omnibus Rules Implementing E.O. No. 292)' => ['mandatory', 'forced leave'],
        'Sick Leave (Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No. 292)' => ['sick leave', 'sick'],
        'Maternity Leave (R.A. No. 11210 / IRR issued by CSC, DOLE and SSS)' => ['maternity leave', 'maternity'],
        'Paternity Leave (R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended)' => ['paternity leave', 'paternity'],
        'Special Privilege Leave (Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No. 292)' => ['special privilege leave'],
        'Solo Parent Leave (RA No. 8972 / CSC MC No. 8, s. 2004)' => ['solo parent leave'],
        'Study Leave (Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No. 292)' => ['study leave'],
        '10-Day VAWC Leave (RA No. 9262 / CSC MC No. 15, s. 2005)' => ['vawc leave'],
        'Rehabilitation Privilege (Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No. 292)' => ['rehabilitation leave', 'rehabilitation'],
        'Special Leave Benefits for Women (RA No. 9710 / CSC MC No. 25, s. 2010)' => ['special leave benefits for women'],
        'Special Emergency (Calamity) Leave (CSC MC No. 2, s. 2012, as amended)' => ['special emergency leave', 'calamity leave'],
        'Adoption Leave (R.A. No. 8552)' => ['adoption leave'],
        'Wellness Leave' => ['wellness leave'],
        'Credited Time-Off' => ['credited time-off', 'credited time off', 'cto'],
    ];

    $isChecked = function ($needles) use ($selected) {
        return collect($needles)->contains(fn ($n) => str_contains($selected, $n));
    };

    $numberOfDays = $leaveApplication->date_from && $leaveApplication->date_to
        ? $leaveApplication->date_from->diffInDays($leaveApplication->date_to) + 1
        : '—';

    $certificationOfficerName = 'Laralournie Artajo';
    $certificationOfficerPosition = 'Administrative Officer V';
    $divisionChiefSignatoryMap = [
        1 => ['name' => 'Laralournie Artajo', 'position' => 'Administrative Officer V'],
        3 => ['name' => 'ORD Division Chief', 'position' => 'ORD Division Chief'],
        4 => ['name' => 'MSESDD Division Chief', 'position' => 'MSESDD Division Chief'],
        6 => ['name' => 'MMD Division Chief', 'position' => 'MMD Division Chief'],
    ];
    $employeeDeptNo = (int) ($employee?->department ?? 1);
    $recommendationOfficerName = optional($leaveApplication->divisionChiefSigner)->name ?? ($divisionChiefSignatoryMap[$employeeDeptNo]['name'] ?? 'Division Chief');
    $recommendationOfficerPosition = $divisionChiefSignatoryMap[$employeeDeptNo]['position'] ?? 'Division Chief';
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
            <div class="header-region">Regional Office VI</div>
            <div class="header-form-title">APPLICATION FOR LEAVE</div>
        </div>

        <!-- ===== SECTION 1-5: BASIC INFO ===== -->
        <div class="section">
            <div class="section-title">I. Employee Information</div>
            <div class="section-body">
                <table class="form-table">
                    <tr>
                        <th style="width:18%;">1. Office/Department</th>
                        <td style="width:32%;">{{ $divisionName }}</td>
                        <th style="width:18%;">2. Name</th>
                        <td style="width:32%;">
                            <span style="font-weight:400;font-size:5.5px;color:#6b7280;">(Last)</span>
                            <strong>{{ $employee?->last_name ?? 'â€”' }}</strong>
                            &nbsp;
                            <span style="font-weight:400;font-size:5.5px;color:#6b7280;">(First)</span>
                            <strong>{{ $employee?->first_name ?? 'â€”' }}</strong>
                            &nbsp;
                            <span style="font-weight:400;font-size:5.5px;color:#6b7280;">(Middle)</span>
                            <strong>{{ $employee?->middle_name ?? 'â€”' }}</strong>
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
                        <td style="vertical-align:top;padding:2px 3px;">
                            <table class="checkbox-grid">
                                @foreach(collect($checklist)->chunk(2) as $pair)
                                    <tr>
                                        @foreach($pair as $label => $needles)
                                            <td style="width:50%;padding:0.5px 2px 0.5px 0;">
                                                <div class="checkbox-item">
                                                    <span class="box">{{ $isChecked($needles) ? 'X' : '' }}</span>
                                                    <span>{{ $label }}</span>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="padding:0.5px 2px 0.5px 0;">
                                        <div class="checkbox-item">
                                            <span class="box"></span>
                                            <span>Others: ________________________</span>
                                        </div>
                                    </td>
                                    <td style="padding:0.5px 2px 0.5px 0;"></td>
                                </tr>
                            </table>
                        </td>
                        <td style="vertical-align:top;padding:2px 3px;">
                            <div class="label-sm">In case of Vacation/Special Privilege Leave:</div>
                            <div class="checkbox-item" style="margin:1px 0;">
                                <span class="box"></span>
                                <span>Within the Philippines _________________________</span>
                            </div>
                            <div class="checkbox-item" style="margin:1px 0;">
                                <span class="box"></span>
                                <span>Abroad (Specify) ____________________________</span>
                            </div>

                            <div class="label-sm" style="margin-top:3px;">In case of Sick Leave:</div>
                            <div class="checkbox-item" style="margin:1px 0;">
                                <span class="box"></span>
                                <span>In Hospital (Specify Illness) ____________________</span>
                            </div>
                            <div class="checkbox-item" style="margin:1px 0;">
                                <span class="box"></span>
                                <span>Out Patient (Specify Illness) ___________________</span>
                            </div>

                            <div class="label-sm" style="margin-top:3px;">In case of Special Leave Benefits for Women:</div>
                            <div class="label-sm">(Specify Illness) ________________________________</div>

                            <div class="label-sm" style="margin-top:3px;">In case of Study Leave:</div>
                            <div class="checkbox-item" style="margin:1px 0;">
                                <span class="box"></span>
                                <span>Completion of Master's Degree</span>
                            </div>
                            <div class="checkbox-item" style="margin:1px 0;">
                                <span class="box"></span>
                                <span>BAR/Board Examination Review</span>
                            </div>

                            <div class="label-sm" style="margin-top:3px;">Other purpose:</div>
                            <div class="checkbox-item" style="margin:1px 0;">
                                <span class="box"></span>
                                <span>Monetization of Leave Credits</span>
                            </div>
                            <div class="checkbox-item" style="margin:1px 0;">
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
                        <td>
                            <div class="field-value">{{ $numberOfDays }} working day(s)</div>
                            <div class="label-sm" style="margin-top:2px;">Inclusive Dates:</div>
                            <div class="field-value">{{ $dateFrom }} to {{ $dateTo }}</div>
                        </td>
                        <td>
                            <div class="checkbox-item" style="margin-bottom:2px;">
                                <span class="box">X</span>
                                <span>Not Requested</span>
                            </div>
                            <div class="checkbox-item" style="margin-bottom:2px;">
                                <span class="box"></span>
                                <span>Requested</span>
                            </div>
                            <div class="blank-line" style="margin-top:2px;"></div>
                            <div style="text-align:center;margin-top:3px;">
                                @if($applicantSignaturePath)
                                    <img src="{{ $applicantSignaturePath }}" alt="Applicant Signature" style="max-width:80px;max-height:22px;object-fit:contain;margin-bottom:1px;">
                                @endif
                                <div style="font-size:6.5px;font-weight:600;">{{ $employeeName }}</div>
                                <div style="border-top:1px solid #111827;margin-top:1px;padding-top:1px;"></div>
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
                        <td style="vertical-align:top;">
                            <div class="label-sm">As of _______________________</div>
                            <table style="width:100%;border-collapse:collapse;margin-top:3px;">
                                <tr>
                                    <td style="padding:1px 3px;border:1px solid #111827;font-size:7px;font-weight:700;background:#f3f4f6;">&nbsp;</td>
                                    <td style="padding:1px 3px;border:1px solid #111827;font-size:7px;font-weight:700;background:#f3f4f6;text-align:center;">Vacation Leave</td>
                                    <td style="padding:1px 3px;border:1px solid #111827;font-size:7px;font-weight:700;background:#f3f4f6;text-align:center;">Sick Leave</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px;border:1px solid #111827;font-size:7px;font-weight:600;">Total Earned</td>
                                    <td style="padding:1px 3px;border:1px solid #111827;text-align:center;">&nbsp;</td>
                                    <td style="padding:1px 3px;border:1px solid #111827;text-align:center;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px;border:1px solid #111827;font-size:7px;font-weight:600;">Less this application</td>
                                    <td style="padding:1px 3px;border:1px solid #111827;text-align:center;">&nbsp;</td>
                                    <td style="padding:1px 3px;border:1px solid #111827;text-align:center;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:1px 3px;border:1px solid #111827;font-size:7px;font-weight:600;">Balance</td>
                                    <td style="padding:1px 3px;border:1px solid #111827;text-align:center;">&nbsp;</td>
                                    <td style="padding:1px 3px;border:1px solid #111827;text-align:center;">&nbsp;</td>
                                </tr>
                            </table>
                            <div style="margin-top:6px;text-align:center;">
                                @if($hrSignaturePath)
                                    <img src="{{ $hrSignaturePath }}" alt="HR Signature" style="max-width:80px;max-height:22px;object-fit:contain;margin-bottom:1px;">
                                @else
                                    <div style="min-height:22px;"></div>
                                @endif
                                <div style="font-size:6.5px;font-weight:700;">{{ $certificationOfficerName }}</div>
                                <div style="font-size:6px;color:#374151;">{{ $certificationOfficerPosition }}</div>
                                <div style="border-top:1px solid #111827;margin-top:1px;padding-top:1px;"></div>
                                <div class="label-sm" style="text-align:center;">AUTHORIZED OFFICER</div>
                            </div>
                        </td>
                        <td style="vertical-align:top;">
                            <div class="checkbox-item" style="margin-bottom:3px;">
                                <span class="box">X</span>
                                <span>For approval</span>
                            </div>
                            <div class="checkbox-item" style="margin-bottom:3px;">
                                <span class="box"></span>
                                <span>For disapproval due to:</span>
                            </div>
                            <div class="blank-line" style="margin-bottom:3px;"></div>
                            <div class="blank-line" style="margin-bottom:3px;"></div>
                            <div class="blank-line" style="margin-bottom:3px;"></div>
                            <div style="margin-top:6px;text-align:center;">
                                @if($divisionChiefSignaturePath)
                                    <img src="{{ $divisionChiefSignaturePath }}" alt="Division Chief Signature" style="max-width:80px;max-height:22px;object-fit:contain;margin-bottom:1px;">
                                @else
                                    <div style="min-height:22px;"></div>
                                @endif
                                <div style="font-size:6.5px;font-weight:700;">{{ $recommendationOfficerName }}</div>
                                <div style="font-size:6px;color:#374151;">{{ $recommendationOfficerPosition }}</div>
                                <div style="border-top:1px solid #111827;margin-top:1px;padding-top:1px;"></div>
                                <div class="label-sm" style="text-align:center;">AUTHORIZED OFFICER</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>7.C Approved For:</th>
                        <th>7.D Disapproved Due To:</th>
                    </tr>
                    <tr>
                        <td>
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
                        <td>
                            <div class="blank-line" style="margin-bottom:3px;"></div>
                            <div class="blank-line" style="margin-bottom:3px;"></div>
                            <div class="blank-line" style="margin-bottom:3px;"></div>
                        </td>
                    </tr>
                </table>

                <div style="text-align:center;margin-top:4px;">
                    @if($regionalDirectorSignaturePath)
                        <img src="{{ $regionalDirectorSignaturePath }}" alt="Regional Director Signature" style="max-width:80px;max-height:22px;object-fit:contain;margin-bottom:1px;">
                    @else
                        <div style="min-height:22px;"></div>
                    @endif
                    <div style="font-size:6.5px;font-weight:700;">{{ optional($leaveApplication->regionalDirectorSigner)->name ?? 'Regional Director' }}</div>
                    <div style="font-size:6px;color:#374151;">Regional Director</div>
                    <div style="border-top:1px solid #111827;margin-top:1px;padding-top:1px;"></div>
                    <div class="label-sm" style="text-align:center;">AUTHORIZED OFFICER</div>
                </div>
            </div>
        </div>

        <!-- ===== STATUS ===== -->
        <div class="page-spacer"></div>
        <div style="text-align:center;margin-top:2px;font-size:6.5px;color:#4b5563;">
            <strong>Status:</strong> {{ $statusLabel }}
        </div>
    </div>
</body>
</html>
