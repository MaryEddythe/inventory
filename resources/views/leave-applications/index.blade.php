@extends('layout.app')
@section('title', 'Leave Applications')

@section('content')
@php
    $statusLabels = [
        'pending_hr' => ['label' => 'Pending HR', 'class' => 'bg-warning text-dark'],
        'pending_division_chief' => ['label' => 'Pending Division Chief', 'class' => 'bg-info text-dark'],
        'pending_regional_director' => ['label' => 'Pending Regional Director', 'class' => 'bg-primary'],
        'approved' => ['label' => 'Approved', 'class' => 'bg-success'],
        'completed' => ['label' => 'Completed', 'class' => 'bg-success'],
        'rejected' => ['label' => 'Rejected', 'class' => 'bg-danger'],
    ];
    $savedSignatureUrl = auth()->user()?->signature_path ? asset('storage/' . auth()->user()->signature_path) : null;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="page-title">Leave Applications</div>
        <div class="page-subtitle">{{ $employee->full_name }} | {{ $employee->employee_id }}</div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        @if($pendingApplications->isNotEmpty())
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">{{ $pendingApplicationsTitle ?? 'Pending Leave Applications' }}</h5>
                            <small class="text-muted">{{ $pendingApplicationsSubtitle ?? 'Leave requests waiting for approval.' }}</small>
                        </div>
                        <span class="badge bg-warning text-dark">{{ $pendingApplications->count() }} pending</span>
                    </div>

                    @if($pendingApplications->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Leave Application</th>
                                        <th>Type of Leave</th>
                                        <th>Start and End Date</th>
                                        <th>Date Applied</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingApplications as $application)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $application->employee?->full_name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $application->employee?->employee_id ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $application->leave_type }}</td>
                                            <td>
                                                <div>{{ $application->date_from?->format('M d, Y') ?? 'N/A' }}</div>
                                                <small class="text-muted">to {{ $application->date_to?->format('M d, Y') ?? 'Open ended' }}</small>
                                            </td>
                                            <td>{{ $application->created_at?->format('M d, Y h:i A') }}</td>
                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <a
                                                        href="{{ route('leave-applications.view', $application) }}"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        title="Preview leave application PDF"
                                                        aria-label="Preview leave application PDF"
                                                    >
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-primary"
                                                        @if(! $savedSignatureUrl) disabled title="Upload your signature in your profile first" @endif
                                                        data-leave-id="{{ $application->id }}"
                                                        data-employee-name="{{ $application->employee?->full_name ?? 'N/A' }}"
                                                        data-employee-id="{{ $application->employee?->employee_id ?? 'N/A' }}"
                                                        data-leave-type="{{ $application->leave_type }}"
                                                        data-date-from="{{ $application->date_from?->format('M d, Y') ?? 'N/A' }}"
                                                        data-date-to="{{ $application->date_to?->format('M d, Y') ?? 'Open ended' }}"
                                                        data-date-applied="{{ $application->created_at?->format('M d, Y h:i A') }}"
                                                        data-sign-url="{{ match ((string) $application->status) {
                                                            'pending_hr' => route('leave-applications.sign-hr', $application),
                                                            'pending_division_chief' => route('leave-applications.sign-division-chief', $application),
                                                            'pending_regional_director' => route('leave-applications.sign-regional-director', $application),
                                                            default => '#',
                                                        } }}"
                                                        onclick="openHrSignModal(this)"
                                                    >
                                                        Sign
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">No pending applications found.</div>
                    @endif
                </div>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="fw-bold mb-0">
                        {{ auth()->user()?->isSuperAdmin() || auth()->user()?->role?->slug === 'hr' ? 'All Leave Applications' : 'My Leave Applications' }}
                    </h5>
                    <span class="badge bg-secondary">{{ $applications->count() }} record(s)</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Date Range</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th class="text-end">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $application)
                                @php
                                    $statusKey = (string) $application->status;
                                    $status = $statusLabels[$statusKey] ?? [
                                        'label' => strtoupper(str_replace('_', ' ', $statusKey)),
                                        'class' => 'bg-secondary',
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $application->employee?->full_name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $application->employee?->employee_id ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $application->leave_type }}</td>
                                    <td>
                                        <div>{{ $application->date_from?->format('M d, Y') }}</div>
                                        <small class="text-muted">
                                            to {{ $application->date_to?->format('M d, Y') ?? 'Open ended' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                                    </td>
                                    <td>{{ $application->created_at?->format('M d, Y h:i A') }}</td>
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
                                @if($application->reason)
                                    <tr class="table-light">
                                        <td colspan="6">
                                            <div class="small text-muted fw-semibold mb-1">Reason</div>
                                            <div>{{ $application->reason }}</div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        No leave applications yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="leave-sign-modal-overlay" id="hrSignModal" aria-hidden="true">
    <div class="leave-sign-modal-content">
        <div class="leave-sign-modal-header">
            <div>
                <h5 class="mb-1 fw-bold">Sign Leave Application</h5>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Confirm the employee details, then sign using your saved profile signature.</p>
            </div>
            <button type="button" class="leave-sign-modal-close" onclick="closeHrSignModal()" aria-label="Close">Ã—</button>
        </div>

        <form method="POST" id="hrSignForm">
            @csrf
            <input type="hidden" name="leave_application_id" id="hrLeaveApplicationId">

            <div class="leave-sign-modal-body">
                <div class="alert alert-info mb-3">
                    This action will move the application to the next process flow.
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Employee</label>
                        <input type="text" class="form-control" id="hrLeaveEmployeeName" disabled>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Employee ID</label>
                        <input type="text" class="form-control" id="hrLeaveEmployeeId" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Leave Type</label>
                        <input type="text" class="form-control" id="hrLeaveType" disabled>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Date Range</label>
                        <input type="text" class="form-control" id="hrLeaveDateRange" disabled>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Date Applied</label>
                        <input type="text" class="form-control" id="hrLeaveDateApplied" disabled>
                    </div>
                </div>

                @if($savedSignatureUrl)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Your Signature Preview</label>
                        <div class="border rounded-3 bg-white p-2 d-inline-block">
                            <img src="{{ $savedSignatureUrl }}" alt="Saved Signature" style="max-width: 240px; max-height: 120px; object-fit: contain;">
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        Please upload your signature in your profile before signing leave applications.
                    </div>
                @endif

                <div class="mb-0">
                    <label class="form-label fw-semibold">Login Password</label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter your login password" required>
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="leave-sign-modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="closeHrSignModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" {{ $savedSignatureUrl ? '' : 'disabled' }}>Confirm &amp; Sign</button>
            </div>
        </form>
    </div>
</div>

<style>
    .leave-sign-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        align-items: center;
        justify-content: center;
        z-index: 1050;
        padding: 1rem;
    }

    .leave-sign-modal-overlay.active {
        display: flex;
    }

    .leave-sign-modal-content {
        width: 100%;
        max-width: 540px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
        overflow: hidden;
    }

    .leave-sign-modal-header,
    .leave-sign-modal-body,
    .leave-sign-modal-footer {
        padding: 1.25rem 1.4rem;
    }

    .leave-sign-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }

    .leave-sign-modal-close {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-size: 1.1rem;
        line-height: 1;
    }

    .leave-sign-modal-close:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .leave-sign-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        border-top: 1px solid #e2e8f0;
        background: #fff;
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
</style>

<script>
    function openHrSignModal(button) {
        if (!button) return;

        const modal = document.getElementById('hrSignModal');
        const form = document.getElementById('hrSignForm');
        const leaveIdInput = document.getElementById('hrLeaveApplicationId');
        const employeeName = document.getElementById('hrLeaveEmployeeName');
        const employeeId = document.getElementById('hrLeaveEmployeeId');
        const leaveType = document.getElementById('hrLeaveType');
        const dateRange = document.getElementById('hrLeaveDateRange');
        const dateApplied = document.getElementById('hrLeaveDateApplied');

        if (form) {
            form.action = button.dataset.signUrl || '';
        }

        if (leaveIdInput) leaveIdInput.value = button.dataset.leaveId || '';
        if (employeeName) employeeName.value = button.dataset.employeeName || '';
        if (employeeId) employeeId.value = button.dataset.employeeId || '';
        if (leaveType) leaveType.value = button.dataset.leaveType || '';
        if (dateRange) {
            const fromDate = button.dataset.dateFrom || '';
            const toDate = button.dataset.dateTo || '';
            dateRange.value = `${fromDate}${toDate ? ' - ' + toDate : ''}`;
        }
        if (dateApplied) dateApplied.value = button.dataset.dateApplied || '';

        modal?.classList.add('active');
    }

    function closeHrSignModal() {
        document.getElementById('hrSignModal')?.classList.remove('active');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const hrModal = document.getElementById('hrSignModal');

        const pendingSignIds = [
            @json(session('hr_sign_leave_id')),
            @json(session('division_chief_sign_leave_id')),
            @json(session('regional_director_sign_leave_id')),
        ].filter(Boolean);

        pendingSignIds.forEach(signLeaveId => {
            const signButton = document.querySelector(`[data-leave-id="${signLeaveId}"]`);
            if (signButton) {
                openHrSignModal(signButton);
            }
        });

        const requestedApplicationId = @json(request('application'));
        if (requestedApplicationId) {
            const signButton = document.querySelector(`[data-leave-id="${requestedApplicationId}"]`);
            if (signButton) {
                signButton.closest('tr')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                openHrSignModal(signButton);
            }
        }

        @if(session('hr_sign_leave_id') || session('division_chief_sign_leave_id') || session('regional_director_sign_leave_id'))
            hrModal?.classList.add('active');
        @endif
    });
</script>
@endsection
