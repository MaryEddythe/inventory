@extends('layout.app')
@section('title', $employee->full_name)

@section('content')
<div class="employee-page-toolbar">
    <a href="{{ route('employees.index') }}" class="btn btn-outline-primary btn-sm employee-back-link">← Back to List</a>
</div>

<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        {{-- Avatar --}}
                        @php
                            $user = $employee->user;
                            $initials = strtoupper(
                                substr($employee->first_name ?? '', 0, 1) .
                                substr($employee->last_name ?? '', 0, 1)
                            );
                        @endphp

                        <div class="position-relative d-inline-block">
                            @if($employee->profile_image)
                                <img src="{{ asset('storage/' . $employee->profile_image) }}"
                                     alt="{{ $employee->full_name }}"
                                     id="profileAvatar"
                                     class="rounded-circle employee-avatar-image">
                            @else
                                <div id="profileAvatar"
                                     class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white fw-bold employee-avatar-fallback">
                                    {{ $initials ?: '?' }}
                                </div>
                            @endif

                            @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isHr())
                                <label for="profileImageUpload"
                                       class="position-absolute rounded-circle d-flex align-items-center justify-content-center employee-avatar-upload">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                        <circle cx="12" cy="13" r="4"/>
                                    </svg>
                                </label>
                                <form id="profileImageForm" method="POST" action="{{ route('employees.upload-profile-image', $employee) }}" enctype="multipart/form-data" class="visually-hidden">
                                    @csrf
                                    <input type="file" id="profileImageUpload" name="profile_image" accept="image/*" onchange="this.form.submit()">
                                </form>
                            @endif
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2">
                                <h3 class="card-title h5 fw-bold mb-0">{{ $employee->full_name }}</h3>
                                @if(auth()->id() === optional($employee->user)->id || auth()->user()?->isSuperAdmin() || auth()->user()?->isHr())
                                    <button type="button" onclick="openProfileEditModal()" class="btn btn-sm p-0 border-0 bg-transparent" title="Edit Profile Settings">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="3"/>
                                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            <small class="text-muted">{{ $employee->position }}</small>
                        </div>
                    </div>

                    <dl class="employee-details-grid mb-0">
                        <div class="employee-detail-tile">
                            <dt>Employee ID</dt>
                            <dd><span class="badge bg-primary">{{ $employee->employee_id }}</span></dd>
                        </div>
                        <div class="employee-detail-tile">
                            <dt>Division</dt>
                            <dd>{{ optional($employee->division)->department ?? optional($employee->division)->description ?? 'N/A' }}</dd>
                        </div>
                        <div class="employee-detail-tile">
                            <dt>Employment Type</dt>
                            <dd>{{ $employee->employment_type === 'PERMANENT' ? 'Permanent' : (($employee->employment_type === 'COS') ? 'COS' : ($employee->employment_type ?? 'N/A')) }}</dd>
                        </div>
                        <div class="employee-detail-tile">
                            <dt>Position</dt>
                            <dd>{{ $employee->position }}</dd>
                        </div>
                        <div class="employee-detail-tile">
                            <dt>Date of Birth</dt>
                            <dd>{{ optional($employee->dob)->format('F d, Y') ?? '-' }}</dd>
                        </div>
                        <div class="employee-detail-tile">
                            <dt>Added On</dt>
                            <dd>{{ optional($employee->created_at)->format('M d, Y h:i A') ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="employee-action-bar">
                        <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="d-inline"
                              onsubmit="return confirm('Delete {{ $employee->full_name }}? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete Employee</button>
                        </form>

                        @auth
                            <button type="button" onclick="openSignatureModal()" class="btn btn-outline-secondary btn-sm">
                                {{ auth()->user()->signature_path ? 'Update My Signature' : 'Save My Signature' }}
                            </button>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title h5 fw-bold mb-3">Drive Folder</h3>

                    @if($employee->drive_folder_url)
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                            <span class="badge bg-success">Ready</span>
                            <a href="{{ $employee->drive_folder_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                Open in Google Drive
                            </a>
                        </div>
                    @else
                        <div class="alert alert-warning mb-3" role="alert">
                            Drive folder is being created. Refresh in a few seconds.
                            <a href="{{ route('employees.show', $employee) }}" class="ms-2 drive-refresh-link">Refresh</a>
                        </div>
                    @endif

                    @if($employee->drive_folder_id)
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="text-uppercase fw-bold text-muted file-upload-label">File Upload</div>

                            <form method="POST" action="{{ route('employees.upload', $employee) }}" enctype="multipart/form-data" class="mt-3">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">File Type</label>
                                    @php
                                        $uploadedTypes = $employee->files ? $employee->files->pluck('file_type')->all() : [];
                                        $types = ['PDS', 'SALN', 'NBI Clearance', 'Medical Certificate', 'PAG-IBIG', 'PhilHealth'];
                                        $availableTypes = array_values(array_diff($types, $uploadedTypes));
                                    @endphp
                                    <select name="file_type" class="form-select form-select-sm" required {{ empty($availableTypes) ? 'disabled' : '' }}>
                                        <option value="">-- Select File Type --</option>
                                        @foreach($availableTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    @error('file_type') <div class="text-danger mt-1 fw-semibold">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Upload File <span class="text-muted">(max 20MB)</span></label>
                                    <label class="btn btn-outline-secondary w-100 text-start">
                                        <i class="bi bi-upload me-2"></i> Choose file
                                        <input type="file" name="file" required class="d-none" />
                                    </label>
                                    @error('file') <div class="text-danger mt-1 fw-semibold">{{ $message }}</div> @enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    Upload to Google Drive
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0">
                            File upload will be available once the Drive folder is ready.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @php
        $leaveBenefits = [
            'PERMANENT' => [
                ['Vacation Leave', 15],
                ['Sick Leave', 15],
                ['Wellness Leave', 5],
                ['Special Privilege Leave', 3],
                ['Maternity Leave', 105],
                ['Paternity Leave', 7],
                ['Solo Parent Leave', 7],
                ['Special Leave Benefits for Women', null],
                ['Rehabilitation Leave', null],
                ['Special Emergency Leave', 5],
                ['Credited Time-off', null],
            ],
            'COS' => [
                ['Wellness Leave', 5],
                ['Credited Time-off', null],
            ],
        ];

        $employmentType = $employee->employment_type;
        $benefitRows = $leaveBenefits[$employmentType] ?? $leaveBenefits['PERMANENT'];

        $benefits = $employee->leaveBenefits()->orderBy('start_date', 'desc')->get();
        $benefitsByType = $benefits->groupBy('credit_type');

        $ctoCredits = $benefits->filter(function ($benefit) {
            $type = strtolower(trim((string) $benefit->credit_type));

            return $type === 'credited time-off'
                || $type === 'credited time off'
                || str_contains($type, 'cto');
        });

        $ctoTotalHours = (int) $ctoCredits->sum('credit_hours');
        $dayBasedCreditFactor = 10;
    @endphp

<div class="card mt-4 leave-overview-card">
        <div class="card-body">
            <div class="leave-overview-heading">
                <div>
                    <p class="leave-section-eyebrow">Time off</p>
                    <h3 class="card-title h5 fw-bold mb-0">Leave Benefits</h3>
                </div>
                <button type="button" onclick="openLeaveModal()" class="btn btn-primary btn-sm">Apply Leave</button>
            </div>

            <div class="leave-credit-grid">
            @foreach($benefitRows as $row)
                @php
                    $label = $row[0];
                    $annualDays = $row[1];
                    $isCtoBenefit = in_array(strtolower($label), ['credited time-off', 'credited time off'], true);

                    // Override Vacation Leave and Sick Leave with ledger-based accumulated days
                    if (in_array($label, ['Vacation Leave', 'Sick Leave'])) {
                        $ledgerDaysData = $ledgerDays ?? [];
                        if ($label === 'Vacation Leave') {
                            $remainingDays = $ledgerDaysData['vacation_days'] ?? $annualDays;
                            $displayUnit = 'days accumulated';
                        } else {
                            $remainingDays = $ledgerDaysData['sick_days'] ?? $annualDays;
                            $displayUnit = 'days accumulated';
                        }
                    } elseif (is_int($annualDays)) {
                        $usedHours = (int) ($benefitsByType->get($label)?->sum('credit_hours') ?? 0);
                        $usedDays = intdiv($usedHours, $dayBasedCreditFactor);
                        $remainingDays = max(0, (int) $annualDays - $usedDays);
                        $displayUnit = 'days annually';
                    } else {
                        $remainingDays = $annualDays;
                        $displayUnit = 'As per policy';
                    }
                @endphp

                <div class="leave-credit-tile">
                    <div class="leave-credit-label">{{ $label }}</div>
                    <div class="leave-credit-value">
                        @if(is_int($remainingDays) || is_float($remainingDays))
                            <span class="leave-credit-number">{{ $remainingDays }}</span> <span class="leave-credit-unit">{{ $displayUnit ?? 'days annually' }}</span>
                        @elseif($isCtoBenefit)
                            <span class="leave-credit-number">{{ $ctoTotalHours }}</span> <span class="leave-credit-unit">hours</span>
                        @else
                            {{ $remainingDays ?? 'As per policy' }}
                        @endif
                    </div>
                </div>
            @endforeach
            </div>

            <div class="leave-records-stack mt-4">
                <details class="leave-record-panel" open>
                    <summary><span>Leave Credits History</span><span class="leave-record-count">{{ $benefits->count() }} record{{ $benefits->count() === 1 ? '' : 's' }}</span></summary>
                    <div class="leave-record-panel-content">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 leave-records-table">
                        <thead class="table-light">
                            <tr>
                                <th>Credit Type</th>
                                <th>S.O / T.O No</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Hours</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($benefits as $benefit)
                                <tr>
                                    <td>{{ $benefit->credit_type }}</td>
                                    <td>{{ $benefit->so_to_no ?: '-' }}</td>
                                    <td>{{ $benefit->start_date?->format('M d, Y') }}</td>
                                    <td>{{ $benefit->end_date ? $benefit->end_date->format('M d, Y') : '-' }}</td>
                                    <td>{{ $benefit->credit_hours }}</td>
                                    <td>{{ $benefit->remarks ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No leave credits found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                    </div>
                </details>

                <details class="leave-record-panel" @if(session('leave_application_submitted')) open @endif>
                    <summary><span>Leave Applications</span><span class="leave-record-count">{{ $leaveApplications->count() }} application{{ $leaveApplications->count() === 1 ? '' : 's' }}</span></summary>
                    <div class="leave-record-panel-content">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0 leave-records-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Leave Type</th>
                                    <th>S.O / T.O No</th>
                                    <th>Date From</th>
                                    <th>Date To</th>
                                    <th>Status</th>
                                    <th>HR</th>
                                    <th>Div Chief</th>
                                    <th>RD</th>
                                    <th class="text-end">View</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaveApplications as $application)
                                    <tr>
                                    <td>
                                        {{ $application->leave_type }}
                                        @if($application->cto_duration)
                                            <div class="small text-muted">
                                                {{ match($application->cto_duration) { 'am' => 'AM · 4 hours', 'pm' => 'PM · 6 hours', default => 'Whole Day · 10 hours' } }}
                                            </div>
                                        @endif
                                    </td>
                                        <td>{{ $application->cto_so_to_no ?: '-' }}</td>
                                        <td>{{ $application->date_from?->format('M d, Y') }}</td>
                                        <td>{{ $application->date_to ? $application->date_to->format('M d, Y') : '-' }}</td>
                                        <td>
                                            @php
                                                $statusClass = match (true) {
                                                    str_contains($application->status, 'pending_hr') || str_contains($application->status, 'PendingHr') => 'warning',
                                                    str_contains($application->status, 'pending_division') || str_contains($application->status, 'PendingDivision') => 'info',
                                                    str_contains($application->status, 'pending_regional') || str_contains($application->status, 'PendingRegional') => 'primary',
                                                    str_contains($application->status, 'Approved') => 'success',
                                                    str_contains($application->status, 'completed') || str_contains($application->status, 'Completed') => 'success',
                                                    str_contains($application->status, 'Denied') => 'danger',
                                                    default => 'secondary',
                                                };
                                                $statusLabel = match (true) {
                                                    str_contains($application->status, 'pending_hr') || str_contains($application->status, 'PendingHr') => 'Pending HR',
                                                    str_contains($application->status, 'pending_division') || str_contains($application->status, 'PendingDivision') => 'Pending Div Chief',
                                                    str_contains($application->status, 'pending_regional') || str_contains($application->status, 'PendingRegional') => 'Pending RD',
                                                    str_contains($application->status, 'Approved') => 'Approved',
                                                    str_contains($application->status, 'completed') || str_contains($application->status, 'Completed') => 'Completed',
                                                    str_contains($application->status, 'Denied') => 'Denied',
                                                    default => $application->status,
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td>@if($application->hr_signature_path)<span class="text-success" title="{{ optional($application->hrSigner)->name ?? 'HR' }}">✓</span>@else<span class="text-muted">—</span>@endif</td>
                                        <td>@if($application->division_chief_signature_path)<span class="text-success" title="{{ optional($application->divisionChiefSigner)->name ?? 'Div Chief' }}">✓</span>@else<span class="text-muted">—</span>@endif</td>
                                        <td>@if($application->regional_director_signature_path)<span class="text-success" title="{{ optional($application->regionalDirectorSigner)->name ?? 'RD' }}">✓</span>@else<span class="text-muted">—</span>@endif</td>
                                        <td class="text-end">
                                            <a
                                                href="{{ route('leave-applications.view', $application) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                title="Open filled leave form"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center">No leave applications</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>

{{-- Leave Application Modal --}}
<style>
    .employee-page-toolbar { margin-bottom: 1.5rem; }
    .employee-back-link { border-radius: 0.5rem; }
    .employee-avatar-image, .employee-avatar-fallback { width: 72px; height: 72px; border: 2px solid #dee2e6; }
    .employee-avatar-image { object-fit: cover; }
    .employee-avatar-fallback { font-size: 1.5rem; }
    .employee-avatar-upload { width: 28px; height: 28px; right: -4px; bottom: -4px; border: 2px solid #fff; background: #0066cc; color: #fff; cursor: pointer; font-size: 14px; }
    .employee-details-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
    .employee-detail-tile, .leave-credit-tile { padding: 0.875rem 1rem; border: 1px solid #e2e8f0; border-radius: 0.625rem; background: #f8fafc; }
    .employee-detail-tile dt, .leave-credit-label, .file-upload-label { margin-bottom: 0.35rem; color: #64748b; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; }
    .employee-detail-tile dd { margin: 0; color: #1e293b; font-weight: 600; }
    .employee-action-bar { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1.25rem; }
    .employee-action-bar .ms-2 { margin-left: 0 !important; }
    .drive-refresh-link { color: #92400e; text-decoration: underline; }
    .leave-overview-heading { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
    .leave-section-eyebrow { margin: 0 0 0.25rem; color: #64748b; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
    .leave-credit-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 0.75rem; }
    .leave-credit-value { color: #0f172a; font-size: 1.1rem; font-weight: 700; display: flex; align-items: baseline; gap: 0.25rem; }
    .leave-credit-number { font-size: 1.5rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .leave-credit-unit { color: #64748b; font-size: 0.75rem; font-weight: 500; }
    .leave-records-stack { display: grid; gap: 0.75rem; }
    .leave-record-panel { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 0.625rem; background: #fff; }
    .leave-record-panel summary { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem; color: #0f172a; cursor: pointer; font-weight: 700; list-style: none; }
    .leave-record-panel summary::-webkit-details-marker { display: none; }
    .leave-record-panel summary::after { content: '⌄'; margin-left: 0.25rem; color: #64748b; font-size: 1.25rem; line-height: 1; }
    .leave-record-panel[open] summary::after { transform: rotate(180deg); }
    .leave-record-count { margin-left: auto; color: #64748b; font-size: 0.8rem; font-weight: 600; }
    .leave-record-panel-content { padding: 0 1rem 1rem; }
    .leave-records-table { font-size: 0.875rem; }
    .leave-records-table th { border-top: 0; color: #64748b; font-size: 0.75rem; letter-spacing: 0.04em; text-transform: uppercase; }
    .leave-sign-heading { color: #0f172a; }
    .leave-sign-hint { font-size: 0.88rem; }
    .leave-sign-panel { display: none; }
    .leave-sign-panel.is-open { display: block; }
    .leave-signature-upload.is-hidden { display: none; }
    .signature-preview-wide { max-width: 240px; max-height: 120px; object-fit: contain; }
    .signature-preview-medium { max-width: 180px; max-height: 80px; object-fit: contain; }
    .signature-preview-large { max-width: 220px; max-height: 110px; object-fit: contain; }
    @media (max-width: 575.98px) {
        .employee-details-grid { grid-template-columns: 1fr; }
        .leave-overview-heading { align-items: flex-start; flex-direction: column; }
    }
    .leave-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .leave-modal-overlay.active {
        display: flex;
    }
    .leave-modal-content {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }
    .leave-modal-header {
        padding: 1.75rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .leave-modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
    }
    .leave-modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #64748b;
        cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    .leave-modal-close:hover {
        color: #0f172a;
    }
    .leave-modal-body {
        padding: 1.75rem;
    }
    .leave-modal-footer {
        padding: 1.75rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }
    .leave-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .leave-form-grid.full {
        grid-template-columns: 1fr;
    }
    .leave-form-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .leave-form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 5px;
        font-size: 0.9rem;
        font-family: inherit;
        color: #111827;
        background: #fff;
        transition: all 0.2s ease;
    }
    .leave-form-control:focus {
        outline: none;
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.08);
    }
    .leave-form-control:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .leave-sign-option {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 0.95rem 1rem;
        border: 1px solid #dbe4ee;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fff;
    }
    .leave-sign-option:hover,
    .leave-sign-option.active {
        border-color: #0d6efd;
        background: #f7fbff;
        box-shadow: 0 4px 16px rgba(13, 110, 253, 0.08);
    }
    .leave-sign-option input[type="radio"] {
        margin-top: 0.2rem;
        accent-color: #0d6efd;
        flex-shrink: 0;
    }
    .leave-sign-section .fw-bold {
        color: #0f172a;
    }
</style>

<div class="leave-modal-overlay" id="leaveModal">
    <div class="leave-modal-content">
        <div class="leave-modal-header">
            <h2 class="leave-modal-title">Apply Leave</h2>
            <button class="leave-modal-close" onclick="closeLeaveModal()">×</button>
        </div>
        <form method="POST" action="{{ route('leave-applications.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="leave-modal-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <div class="fw-semibold mb-1">Your leave application was not submitted.</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Employee</label>
                        <input type="text" id="leaveEmployeeName" class="leave-form-control" disabled>
                        <input type="hidden" id="leaveEmployeeId" name="employee_id">
                    </div>
                </div>

                <div class="leave-form-grid">
                    <div>
                        <label class="leave-form-label">Division</label>
                        <input type="text" id="leaveDivision" class="leave-form-control" disabled>
                    </div>
                    <div>
                        <label class="leave-form-label">Position</label>
                        <input type="text" id="leavePosition" class="leave-form-control" disabled>
                    </div>
                </div>

                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Employment Type</label>
                        <input type="text" id="leaveEmploymentType" class="leave-form-control" disabled>
                    </div>
                </div>

                <div class="leave-form-grid">
                    <div>
                        <label class="leave-form-label">Date From *</label>
                        <input type="date" name="date_from" class="leave-form-control" required>
                    </div>
                    <div>
                        <label class="leave-form-label">Date To</label>
                        <input type="date" name="date_to" class="leave-form-control">
                    </div>
                </div>

                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Leave Type *</label>
                        <select name="leave_type" id="leaveType" class="leave-form-control" required>
                            <option value="">-- Select Leave Type --</option>
                        </select>
                    </div>
                </div>

                @include('leaves.usecto', ['ctoHistory' => $ctoHistory])

                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Date Applied</label>
                        <input type="date" class="leave-form-control" value="{{ now()->toDateString() }}" readonly>
                    </div>
                </div>

                <div class="mt-3 p-3 border rounded-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <div>
                            <div class="fw-bold leave-sign-heading">Sign Leave Application</div>
                            <div class="text-muted leave-sign-hint">Use your saved signature or upload a new one, then confirm with your password.</div>
                        </div>
                        <button type="button" onclick="toggleLeaveSignSection()" class="btn btn-outline-primary btn-sm">Sign</button>
                    </div>

                    <div id="leaveSignPanel" class="mt-3 leave-sign-panel">
                        <div class="mb-3">
                            <label class="leave-form-label">Signature Option</label>
                            <div class="d-grid gap-2">
                                <label class="leave-sign-option {{ auth()->user()?->signature_path ? 'active' : '' }}">
                                    <input type="radio" name="signature_mode" value="saved" {{ auth()->user()?->signature_path ? 'checked' : '' }} {{ auth()->user()?->signature_path ? '' : 'disabled' }}>
                                    <div>
                                        <div class="fw-bold">Use my saved signature</div>
                                        <small class="text-muted">Stored in your profile and reused for leave documents.</small>
                                    </div>
                                </label>

                                <label class="leave-sign-option">
                                    <input type="radio" name="signature_mode" value="upload" {{ auth()->user()?->signature_path ? '' : 'checked' }}>
                                    <div>
                                        <div class="fw-bold">Upload a new signature</div>
                                        <small class="text-muted">This updates your saved signature for future use.</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3 leave-signature-upload {{ auth()->user()?->signature_path ? 'is-hidden' : '' }}" id="leaveSignatureUploadWrapper">
                            <label class="leave-form-label">Signature Image</label>
                            <input type="file" name="signature_path" id="leaveSignatureInput" class="leave-form-control" accept="image/png,image/jpeg">
                            <small class="text-muted">Upload a PNG or JPG if you want to replace your saved signature.</small>
                        </div>

                        @if(auth()->user()?->signature_path)
                            <div class="mb-3">
                                <label class="leave-form-label">Current Signature Preview</label>
                                <div class="border rounded-3 bg-white p-2 d-inline-block">
                                    <img src="{{ asset('storage/' . auth()->user()->signature_path) }}" alt="Saved Signature" class="signature-preview-wide">
                                </div>
                            </div>
                        @endif

                        <div class="mb-0">
                            <label class="leave-form-label">Login Password</label>
                            <input type="password" name="current_password" class="leave-form-control" autocomplete="current-password" placeholder="Enter your login password" required>
                            <small class="text-muted">This must match the password you use to log in.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="leave-modal-footer">
                <button type="button" onclick="closeLeaveModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Sign &amp; Submit Leave</button>
            </div>
        </form>
    </div>
</div>

{{-- Profile Edit Modal --}}
<div class="leave-modal-overlay" id="profileEditModal">
    <div class="leave-modal-content">
        <div class="leave-modal-header">
            <h2 class="leave-modal-title">Edit Profile Settings</h2>
            <button class="leave-modal-close" onclick="closeProfileEditModal()">×</button>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="leave-modal-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Username</label>
                        <input type="text" name="username" class="leave-form-control" value="{{ old('username', auth()->user()->username ?: auth()->user()->name) }}" required>
                    </div>
                </div>

                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Email</label>
                        <input type="email" name="email" class="leave-form-control" value="{{ old('email', auth()->user()->email) }}" required>
                    </div>
                </div>

                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Signature Image</label>
                        <input type="file" name="signature_path" class="leave-form-control" accept="image/png,image/jpeg">
                        @if(auth()->user()->signature_path)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . auth()->user()->signature_path) }}" alt="Signature" class="img-fluid border rounded bg-white p-1 signature-preview-medium">
                            </div>
                        @endif
                    </div>
                </div>

                <hr class="my-3">

                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Current Password</label>
                        <input type="password" name="current_password" class="leave-form-control" minlength="8">
                    </div>
                </div>

                <div class="leave-form-grid">
                    <div>
                        <label class="leave-form-label">New Password</label>
                        <input type="password" name="new_password" class="leave-form-control" minlength="8">
                    </div>
                    <div>
                        <label class="leave-form-label">Confirm Password</label>
                        <input type="password" name="new_password_confirmation" class="leave-form-control" minlength="8">
                    </div>
                </div>
            </div>

            <div class="leave-modal-footer">
                <button type="button" onclick="closeProfileEditModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- Signature Upload Modal --}}
<div class="leave-modal-overlay" id="signatureModal">
    <div class="leave-modal-content">
        <div class="leave-modal-header">
            <h2 class="leave-modal-title">Save My Signature</h2>
            <button class="leave-modal-close" onclick="closeSignatureModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('profile.signature.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="leave-modal-body">
                <p class="text-muted mb-3">
                    Upload your signature once here. The system will reuse it for leave approvals and signed documents.
                </p>

                <div class="leave-form-grid full">
                    <div>
                        <label class="leave-form-label">Signature Image</label>
                        <input type="file" name="signature_path" class="leave-form-control" accept="image/png,image/jpeg" required>
                    </div>
                </div>

                @if(auth()->user()?->signature_path)
                    <div class="mt-3">
                        <label class="leave-form-label">Current Signature</label>
                        <img src="{{ asset('storage/' . auth()->user()->signature_path) }}" alt="Current Signature" class="img-fluid border rounded bg-white p-2 signature-preview-large">
                    </div>
                @endif
            </div>

            <div class="leave-modal-footer">
                <button type="button" onclick="closeSignatureModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Signature</button>
            </div>
        </form>
    </div>
</div>

@php
    $leaveTypesForModal = [
        'PERMANENT' => [
            'Vacation Leave',
            'Sick Leave',
            'Wellness Leave',
            'Special Privilege Leave',
            'Maternity Leave',
            'Paternity Leave',
            'Solo Parent Leave',
            'Rehabilitation Leave',
            'Special Emergency Leave',
            'Credited Time-Off',
        ],
        'COS' => [
            'Wellness Leave',
            'Credited Time-Off',
        ],
    ];
@endphp

<script>
    function openProfileEditModal() {
        document.getElementById('profileEditModal').classList.add('active');
    }

    function closeProfileEditModal() {
        document.getElementById('profileEditModal').classList.remove('active');
    }

    function openSignatureModal() {
        document.getElementById('signatureModal').classList.add('active');
    }

    function closeSignatureModal() {
        document.getElementById('signatureModal').classList.remove('active');
    }

    @php
        $ctoCreditSourcesJson = $ctoHistory->map(function ($b) {
            return [
                'id' => $b->id,
                'remarks' => $b->remarks ?: 'No remarks provided',
                'hours' => $b->credit_hours,
                'date' => optional($b->start_date)->format('M d, Y'),
            ];
        })->values();
    @endphp
    const leaveTypesByEmploymentType = JSON.parse('{!! json_encode($leaveTypesForModal) !!}');
    const ctoCreditSources = JSON.parse('{!! json_encode($ctoCreditSourcesJson) !!}');

    const currentEmployee = JSON.parse('{!! json_encode([
        'id' => $employee->emp_no,
        'full_name' => $employee->full_name,
        'division_code' => optional($employee->division)->department ?? optional($employee->division)->description ?? 'N/A',
        'position' => $employee->position,
        'employment_type' => $employee->employment_type,
    ]) !!}');

    function openLeaveModal() {
        const emp = currentEmployee;

        document.getElementById('leaveEmployeeName').value = emp.full_name;
        document.getElementById('leaveEmployeeId').value = emp.id;
        document.getElementById('leaveDivision').value = emp.division_code;
        document.getElementById('leavePosition').value = emp.position;
        document.getElementById('leaveEmploymentType').value = emp.employment_type;

        updateLeaveTypeOptions(emp.employment_type);
        toggleCtoFields();
        closeLeaveSignSection();
        document.getElementById('leaveModal').classList.add('active');
    }

    function closeLeaveModal() {
        document.getElementById('leaveModal').classList.remove('active');
        const form = document.querySelector('#leaveModal form');
        if (form) form.reset();
        document.getElementById('leaveEmployeeId').value = '';
        document.getElementById('leaveEmployeeName').value = '';
        document.getElementById('leaveDivision').value = '';
        document.getElementById('leavePosition').value = '';
        document.getElementById('leaveEmploymentType').value = '';
        document.getElementById('leaveType').innerHTML = '<option value="">-- Select Leave Type --</option>';
        toggleCtoFields();
        closeLeaveSignSection();
        syncLeaveSignatureMode();
    }

    function updateLeaveTypeOptions(employmentType) {
        const upper = (employmentType || '').toString().trim().toUpperCase();
        const key = upper.includes('PER') ? 'PERMANENT' : 'COS';
        const allowed = leaveTypesByEmploymentType[key] || [];
        const select = document.getElementById('leaveType');

        select.innerHTML = '<option value="">-- Select Leave Type --</option>';
        allowed.forEach(type => {
            const opt = document.createElement('option');
            opt.value = type;
            opt.textContent = type;
            select.appendChild(opt);
        });
    }

    function toggleCtoFields() {
        const isCto = document.getElementById('leaveType')?.value === 'Credited Time-Off';
        const wrapper = document.getElementById('ctoLeaveFields');
        const source = document.getElementById('ctoLeaveHistoryId');
        const soToNo = document.getElementById('ctoSoToNo');
        const duration = document.getElementById('ctoDuration');

        if (wrapper) wrapper.classList.toggle('d-none', !isCto);
        [source, soToNo, duration].forEach(field => {
            if (!field) return;
            field.disabled = !isCto;
            field.required = isCto;
            if (!isCto) field.value = '';
        });
    }

    function toggleLeaveSignSection() {
        const panel = document.getElementById('leaveSignPanel');
        if (!panel) return;

        const isVisible = panel.classList.contains('is-open');
        panel.classList.toggle('is-open', !isVisible);

        if (!isVisible) {
            syncLeaveSignatureMode();
        }
    }

    function closeLeaveSignSection() {
        const panel = document.getElementById('leaveSignPanel');
        if (panel) {
            panel.classList.remove('is-open');
        }
    }

    function syncLeaveSignatureMode() {
        const selected = document.querySelector('input[name="signature_mode"]:checked')?.value;
        const uploadWrapper = document.getElementById('leaveSignatureUploadWrapper');
        const uploadInput = document.getElementById('leaveSignatureInput');
        const panel = document.getElementById('leaveSignPanel');

        if (!panel || !panel.classList.contains('is-open')) {
            return;
        }

        if (uploadWrapper) {
            uploadWrapper.classList.toggle('is-hidden', selected !== 'upload');
        }

        if (uploadInput) {
            uploadInput.required = selected === 'upload';
        }

        document.querySelectorAll('.leave-sign-option').forEach(option => {
            const radio = option.querySelector('input[type="radio"]');
            option.classList.toggle('active', !!radio && radio.checked);
        });
    }

    document.addEventListener('change', function (event) {
        if (event.target && event.target.id === 'leaveType') {
            toggleCtoFields();
        }
        if (event.target && event.target.name === 'signature_mode') {
            syncLeaveSignatureMode();
        }
    });

    @if($errors->hasAny(['employee_id', 'leave_type', 'date_from', 'date_to', 'cto_leave_history_id', 'cto_duration', 'signature_mode', 'signature_path', 'current_password']))
    document.addEventListener('DOMContentLoaded', function () {
        openLeaveModal();
        toggleLeaveSignSection();
        syncLeaveSignatureMode();
    });
    @endif
</script>
@endsection
