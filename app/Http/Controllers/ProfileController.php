@extends('layout.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Update Profile</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Profile Information and Forms in Side-by-Side Cards -->
                    <div class="row">
                        <!-- Left Card: User Profile -->
                        <div class="col-lg-6 col-md-12 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>User Profile</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <img src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->username ?: Auth::user()->name) . '&background=0D8ABC&color=fff&size=150' }}"
                                             alt="Profile Image"
                                             class="rounded-circle img-fluid mb-3 border"
                                             style="width: 150px; height: 150px; object-fit: cover;"
                                             id="profile-image-preview">
                                        <h5>{{ Auth::user()->username ?: Auth::user()->name }}</h5>
                                        <p class="text-muted">{{ Auth::user()->email }}</p>
                                    </div>

                                    <form id="profile-update-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', Auth::user()->username ?: Auth::user()->name) }}" required>
                                            <div class="invalid-feedback" id="username-feedback"></div>
                                            @error('username')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                                            <div class="invalid-feedback" id="email-feedback"></div>
                                            @error('email')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="profile_image" class="form-label">Profile Image</label>
                                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                            <small class="form-text text-muted">Leave empty to keep current image</small>
                                            <div class="mt-2" id="image-preview-container" style="display: none;">
                                                <img id="image-preview" class="img-fluid rounded" style="max-width: 200px; max-height: 200px;">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="signature_path" class="form-label">Signature Image</label>
                                            <input type="file" class="form-control" id="signature_path" name="signature_path" accept="image/png,image/jpeg">
                                            <small class="form-text text-muted">Upload this once. The system will reuse it when you sign leave documents.</small>
                                            @if(Auth::user()->signature_path)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . Auth::user()->signature_path) }}"
                                                         alt="Signature Preview"
                                                         class="img-fluid border rounded bg-white p-2"
                                                         style="max-width: 240px; max-height: 120px; object-fit: contain;">
                                                </div>
                                            @endif
                                        </div>

                                        <button type="button" id="update-profile-btn" class="btn btn-primary w-100" disabled>
                                            <span class="spinner-border spinner-border-sm me-2" id="update-spinner" style="display: none;"></span>
                                            <i class="bi bi-check-circle me-2"></i>Update Profile
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Right Card: Change Password -->
                        <div class="col-lg-6 col-md-12 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
                                </div>
                                <div class="card-body">
                                    <form id="change-password-form" action="{{ route('profile.change-password') }}" method="POST">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                                <span class="input-group-text" id="toggle-current-password" style="cursor: pointer;">
                                                    <i class="bi bi-eye" id="icon-current-password"></i>
                                                </span>
                                            </div>
                                            <div class="invalid-feedback" id="current-password-feedback"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                                                <span class="input-group-text" id="toggle-new-password" style="cursor: pointer;">
                                                    <i class="bi bi-eye" id="icon-new-password"></i>
                                                </span>
                                            </div>
                                            <div class="invalid-feedback" id="new-password-feedback"></div>
                                            <small class="form-text text-muted">Password must be at least 8 characters long</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                                <span class="input-group-text" id="toggle-confirm-password" style="cursor: pointer;">
                                                    <i class="bi bi-eye" id="icon-confirm-password"></i>
                                                </span>
                                            </div>
                                            <div class="invalid-feedback" id="confirm-password-feedback"></div>
                                        </div>

                                        <button type="submit" id="change-password-btn" class="btn btn-warning w-100" disabled>
                                            <span class="spinner-border spinner-border-sm me-2" id="change-spinner" style="display: none;"></span>
                                            <i class="bi bi-key me-2"></i>Change Password
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <style>
                        .leave-stepper {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 0.75rem;
                            margin-top: 0.75rem;
                        }

                        .leave-step {
                            display: flex;
                            align-items: flex-start;
                            gap: 0.65rem;
                            padding: 0.85rem;
                            border-radius: 0.9rem;
                            border: 1px solid #e2e8f0;
                            background: #f8fafc;
                            min-height: 100%;
                        }

                        .leave-step.is-done {
                            background: #ecfdf5;
                            border-color: #86efac;
                        }

                        .leave-step.is-current {
                            background: #eff6ff;
                            border-color: #93c5fd;
                        }

                        .leave-step.is-pending {
                            opacity: 0.65;
                        }

                        .leave-step-icon {
                            width: 1.85rem;
                            height: 1.85rem;
                            border-radius: 999px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            flex: 0 0 auto;
                            background: #cbd5e1;
                            color: #fff;
                            font-weight: 800;
                            font-size: 0.92rem;
                        }

                        .leave-step.is-done .leave-step-icon {
                            background: #16a34a;
                        }

                        .leave-step.is-current .leave-step-icon {
                            background: #2563eb;
                        }

                        .leave-step-title {
                            font-weight: 700;
                            color: #0f172a;
                            line-height: 1.15;
                        }

                        .leave-step-meta {
                            font-size: 0.78rem;
                            color: #64748b;
                            margin-top: 0.2rem;
                        }

                        @media (max-width: 991.98px) {
                            .leave-stepper {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                        }

                        @media (max-width: 575.98px) {
                            .leave-stepper {
                                grid-template-columns: 1fr;
                            }
                        }
                    </style>

                    @php
                        $profileLeaveStatusLabels = [
                            'pending_hr' => ['label' => 'Pending HR', 'class' => 'bg-warning text-dark'],
                            'pending_division_chief' => ['label' => 'Pending Division Chief', 'class' => 'bg-info text-dark'],
                            'pending_regional_director' => ['label' => 'Pending Regional Director', 'class' => 'bg-primary'],
                            'approved' => ['label' => 'Approved', 'class' => 'bg-success'],
                            'completed' => ['label' => 'Completed', 'class' => 'bg-success'],
                            'rejected' => ['label' => 'Rejected', 'class' => 'bg-danger'],
                        ];
                    @endphp

                                        @if($user?->employee)
                        <div class="card mt-4 shadow-sm">
                            <div class="card-header bg-info bg-opacity-10 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Employee Information</h5>
                                    <small class="text-muted">Division / role / employment details.</small>
                                </div>
                                @if($user->role)
                                    <span class="badge bg-info text-dark text-uppercase">{{ $user->role->name }}</span>
                                @endif
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0 align-middle">
                                        <tbody>
                                            <tr>
                                                <th class="w-50 text-end">Full Name</th>
                                                <td>{{ $user->employee->full_name ?: 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-end">Employee ID</th>
                                                <td>{{ $user->employee->employee_id ?: $user->employee->emp_no }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-end">Position</th>
                                                <td>{{ $user->employee->position ?: '<span class="text-muted">—</span>' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-end">Division</th>
                                                <td>
                                                    @if($user->employee->division)
                                                        {{ $user->employee->division->name }}
                                                        @if($user->employee->division->code)
                                                            <span class="text-muted">({{ $user->employee->division->code }})</span>
                                                        @endif
                                                        @if($user->employee->division->description)
                                                            <div class="small text-muted">{{ $user->employee->division->description }}</div>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-end">Employment Type</th>
                                                <td>{{ $user->employee->employment_type ?: '<span class="text-muted">—</span>' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($user?->employee)
                        @php
                            $employeeNo = $user->employee->emp_no ?: $user->employee->employee_id;
                            $employeeFiles = \App\Models\EmployeeFile::where(
                                'emp_no',
                                $user->employee?->emp_no ?: $user->employee?->employee_id
                            )->latest()->get();

                            $employeeFileTypes = [
                                'PDS',
                                'SALN',
                                'POLICE CLEARANCE CLEARANCE',
                                'MEDICAL CERTIFICATE',
                                'PAG-IBIG',
                                'PHILHEALTH',
                                'TIN',
                                'GSIS',
                                'PRC',
                                'Civil Service Eligibility',
                                'Contract of Employment',
                            ];
                        @endphp

                        <div class="card mt-4 shadow-sm">
                            <div class="card-header bg-success bg-opacity-10">
                                <h5 class="mb-0">
                                    <i class="bi bi-folder2-open me-2"></i>Employee File Folder
                                </h5>
                                <small class="text-muted">
                                    Files are stored privately under employee ID {{ $employeeNo }}.
                                </small>
                            </div>

                            <div class="card-body">
                                <form action="{{ route('employee-files.store') }}" method="POST" enctype="multipart/form-data" class="row g-3 mb-4">
                                    @csrf

                                    <div class="col-md-5">
                                        <label for="file_type" class="form-label">Document Type</label>
                                        <select name="file_type" id="file_type" class="form-select" required>
                                            <option value="">Select document type</option>
                                            @foreach($employeeFileTypes as $fileType)
                                                <option value="{{ $fileType }}">{{ $fileType }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-5">
                                        <label for="employee_file" class="form-label">File</label>
                                        <input type="file" name="file" id="employee_file" class="form-control"
                                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                        <small class="text-muted">Maximum size: 10 MB</small>
                                    </div>

                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="bi bi-upload me-1"></i>Upload
                                        </button>
                                    </div>
                                </form>

                                @if($employeeFiles->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Document Type</th>
                                                    <th>File Name</th>
                                                    <th>Uploaded</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($employeeFiles as $employeeFile)
                                                    <tr>
                                                        <td>{{ $employeeFile->file_type }}</td>
                                                        <td>{{ $employeeFile->file_name }}</td>
                                                        <td>{{ $employeeFile->created_at?->format('M d, Y h:i A') }}</td>
                                                        <td class="text-end">
                                                            <a href="{{ route('employee-files.download', $employeeFile) }}"
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-download"></i>
                                                            </a>

                                                            <form action="{{ route('employee-files.destroy', $employeeFile) }}"
                                                                  method="POST" class="d-inline"
                                                                  onsubmit="return confirm('Delete this file?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info mb-0">
                                        No employee files have been uploaded.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($user?->employee)
                        <div class="card mt-4 shadow-sm">
                            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Leave Applications</h5>
                                    <small class="text-muted">Your submitted leave requests and approvals.</small>
                                </div>

                                <a href="{{ route('leave-applications.index') }}" class="btn btn-sm btn-outline-primary">
                                    Open Leave Applications
                                </a>
                            </div>

                            <div class="card-body">
                                @if($leaveApplications->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Leave Type</th>
                                                    <th>Date Range</th>
                                                    <th>Status</th>
                                                    <th>Current Step</th>
                                                    <th>Submitted</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($leaveApplications as $application)
                                                    @php
                                                        $statusKey = (string) $application->status;
                                                        $status = $profileLeaveStatusLabels[$statusKey] ?? [
                                                            'label' => strtoupper(str_replace('_', ' ', $statusKey)),
                                                            'class' => 'bg-secondary',
                                                        ];
                                                        $steps = [
                                                            [
                                                                'key' => 'submitted',
                                                                'label' => 'Submitted',
                                                                'done' => true,
                                                                'at' => $application->created_at,
                                                                'by' => $application->employee?->full_name ?? $user?->name,
                                                            ],
                                                            [
                                                                'key' => 'hr',
                                                                'label' => 'HR',
                                                                'done' => filled($application->hr_signed_at),
                                                                'at' => $application->hr_signed_at,
                                                                'by' => $application->hrSigner?->name,
                                                            ],
                                                            [
                                                                'key' => 'division_chief',
                                                                'label' => 'Division Chief',
                                                                'done' => filled($application->division_chief_signed_at),
                                                                'at' => $application->division_chief_signed_at,
                                                                'by' => $application->divisionChiefSigner?->name,
                                                            ],
                                                            [
                                                                'key' => 'regional_director',
                                                                'label' => 'Regional Director',
                                                                'done' => filled($application->regional_director_signed_at),
                                                                'at' => $application->regional_director_signed_at,
                                                                'by' => $application->regionalDirectorSigner?->name,
                                                            ],
                                                        ];
                                                        $currentStepKey = (string) ($application->current_step ?? 'hr');
                                                    @endphp
                                                    <tr>
                                                        <td class="fw-semibold">{{ $application->leave_type }}</td>
                                                        <td>
                                                            <div>{{ $application->date_from?->format('M d, Y') }}</div>
                                                            <small class="text-muted">to {{ $application->date_to?->format('M d, Y') ?? 'Open ended' }}</small>
                                                        </td>
                                                        <td><span class="badge {{ $status['class'] }}">{{ $status['label'] }}</span></td>
                                                        <td class="text-capitalize">{{ str_replace('_', ' ', $application->current_step ?? 'hr') }}</td>
                                                        <td>{{ $application->created_at?->format('M d, Y h:i A') }}</td>
                                                    </tr>
                                                    <tr class="table-light">
                                                        <td colspan="5">
                                                            <div class="leave-stepper">
                                                                @foreach($steps as $step)
                                                                    @php
                                                                        $isCurrent = ! $step['done'] && $currentStepKey === $step['key'];
                                                                    @endphp
                                                                    <div class="leave-step {{ $step['done'] ? 'is-done' : ($isCurrent ? 'is-current' : 'is-pending') }}">
                                                                        <div class="leave-step-icon">
                                                                            @if($step['done'])
                                                                                ✓
                                                                            @elseif($isCurrent)
                                                                                •
                                                                            @else
                                                                                •
                                                                            @endif
                                                                        </div>
                                                                        <div>
                                                                            <div class="leave-step-title">{{ $step['label'] }}</div>
                                                                            @if($step['done'])
                                                                                <div class="leave-step-meta">
                                                                                    {{ $step['by'] ?? 'System' }}
                                                                                    @if($step['at'])
                                                                                        · {{ $step['at']->format('M d, Y h:i A') }}
                                                                                    @endif
                                                                                </div>
                                                                            @elseif($isCurrent)
                                                                                <div class="leave-step-meta">Currently awaiting this approval.</div>
                                                                            @else
                                                                                <div class="leave-step-meta">Pending next stage.</div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @if($application->reason)
                                                        <tr class="table-light">
                                                            <td colspan="5">
                                                                <div class="small text-muted fw-semibold mb-1">Reason</div>
                                                                <div>{{ $application->reason }}</div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info mb-0">
                                        You have not submitted any leave applications yet.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mt-4 mb-0">
                            No linked employee record was found for this account, so leave applications cannot be shown here.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const updateBtn = document.getElementById('update-profile-btn');
    const changePasswordBtn = document.getElementById('change-password-btn');
    const profileForm = document.getElementById('profile-update-form');
    const changePasswordForm = document.getElementById('change-password-form');
    const updateSpinner = document.getElementById('update-spinner');
    const changeSpinner = document.getElementById('change-spinner');
    const profileImageInput = document.getElementById('profile_image');
    const signatureInput = document.getElementById('signature_path');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const imagePreview = document.getElementById('image-preview');

    // Real-time validation for profile form
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const usernameFeedback = document.getElementById('username-feedback');
    const emailFeedback = document.getElementById('email-feedback');

    function validateProfileForm() {
        let isValid = true;

        // Username validation
        if (usernameInput.value.trim().length < 3) {
            usernameInput.classList.add('is-invalid');
            usernameFeedback.textContent = 'Username must be at least 3 characters long';
            isValid = false;
        } else {
            usernameInput.classList.remove('is-invalid');
            usernameFeedback.textContent = '';
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            emailInput.classList.add('is-invalid');
            emailFeedback.textContent = 'Please enter a valid email address';
            isValid = false;
        } else {
            emailInput.classList.remove('is-invalid');
            emailFeedback.textContent = '';
        }

        // Check if any field has changed
        const originalUsername = @js(Auth::user()->username ?: Auth::user()->name);
        const originalEmail = @js(Auth::user()->email);
        const hasChanges = usernameInput.value !== originalUsername
            || emailInput.value !== originalEmail
            || profileImageInput.files.length > 0
            || signatureInput.files.length > 0;

        updateBtn.disabled = !isValid || !hasChanges;
        return isValid && hasChanges;
    }

    usernameInput.addEventListener('input', validateProfileForm);
    emailInput.addEventListener('input', validateProfileForm);
    profileImageInput.addEventListener('change', validateProfileForm);
    signatureInput.addEventListener('change', validateProfileForm);

    // Real-time validation for change password form
    const currentPasswordInput = document.getElementById('current_password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('new_password_confirmation');
    const currentPasswordFeedback = document.getElementById('current-password-feedback');
    const newPasswordFeedback = document.getElementById('new-password-feedback');
    const confirmPasswordFeedback = document.getElementById('confirm-password-feedback');

    function validateChangePasswordForm() {
        let isValid = true;

        // Current password validation
        if (currentPasswordInput.value.length === 0) {
            currentPasswordInput.classList.add('is-invalid');
            currentPasswordFeedback.textContent = 'Current password is required';
            isValid = false;
        } else {
            currentPasswordInput.classList.remove('is-invalid');
            currentPasswordFeedback.textContent = '';
        }

        // New password validation
        if (newPasswordInput.value.length < 8) {
            newPasswordInput.classList.add('is-invalid');
            newPasswordFeedback.textContent = 'Password must be at least 8 characters long';
            isValid = false;
        } else {
            newPasswordInput.classList.remove('is-invalid');
            newPasswordFeedback.textContent = '';
        }

        // Confirm password validation
        if (confirmPasswordInput.value !== newPasswordInput.value) {
            confirmPasswordInput.classList.add('is-invalid');
            confirmPasswordFeedback.textContent = 'Passwords do not match';
            isValid = false;
        } else {
            confirmPasswordInput.classList.remove('is-invalid');
            confirmPasswordFeedback.textContent = '';
        }

        changePasswordBtn.disabled = !isValid;
        return isValid;
    }

    currentPasswordInput.addEventListener('input', validateChangePasswordForm);
    newPasswordInput.addEventListener('input', validateChangePasswordForm);
    confirmPasswordInput.addEventListener('input', validateChangePasswordForm);

    // Profile update with loading state
    updateBtn.addEventListener('click', function(e) {
        e.preventDefault();

        if (!validateProfileForm()) return;

        updateBtn.disabled = true;
        updateSpinner.style.display = 'inline-block';

        Swal.fire({
            title: 'Profile Update Confirmation',
            text: 'Are you sure you want to update your profile information?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                profileForm.submit();
            } else {
                updateBtn.disabled = false;
                updateSpinner.style.display = 'none';
            }
        });
    });

    // Change password with loading state
    changePasswordForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validateChangePasswordForm()) return;

        Swal.fire({
            title: 'Change Password Confirmation',
            text: 'Are you sure you want to change your password?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!'
        }).then((result) => {
            if (result.isConfirmed) {
                changePasswordBtn.disabled = true;
                changeSpinner.style.display = 'inline-block';
                this.submit();
            }
        });
    });

    // Image preview functionality
    profileImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreviewContainer.style.display = 'none';
        }
    });

    // Password toggle functionality
    const passwordFields = [
        { input: 'current_password', toggle: 'toggle-current-password', icon: 'icon-current-password' },
        { input: 'new_password', toggle: 'toggle-new-password', icon: 'icon-new-password' },
        { input: 'new_password_confirmation', toggle: 'toggle-confirm-password', icon: 'icon-confirm-password' }
    ];

    passwordFields.forEach(field => {
        const toggleBtn = document.getElementById(field.toggle);
        const input = document.getElementById(field.input);
        const icon = document.getElementById(field.icon);

        toggleBtn.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    });
});
</script>
@endsection

<style>
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn {
    transition: all 0.2s ease-in-out;
}

.btn:hover:not(:disabled) {
    transform: translateY(-1px);
}

.btn:disabled {
    opacity: 0.6;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

@media (max-width: 991.98px) {
    .col-lg-6 {
        margin-bottom: 1.5rem;
    }
}

@media (max-width: 575.98px) {
    .card-header h5 {
        font-size: 1.25rem;
    }

    .card-body {
        padding: 1rem;
    }

    .btn {
        font-size: 0.9rem;
    }
}

.invalid-feedback {
    display: block;
}

.is-invalid {
    border-color: #dc3545;
}

.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>