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
                                        <img src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->username) . '&background=0D8ABC&color=fff&size=150' }}"
                                             alt="Profile Image"
                                             class="rounded-circle img-fluid mb-3 border"
                                             style="width: 150px; height: 150px; object-fit: cover;"
                                             id="profile-image-preview">
                                        <h5>{{ Auth::user()->username }}</h5>
                                        <p class="text-muted">{{ Auth::user()->email }}</p>
                                    </div>

                                    <form id="profile-update-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" value="{{ Auth::user()->username }}" required>
                                            <div class="invalid-feedback" id="username-feedback"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}" required>
                                            <div class="invalid-feedback" id="email-feedback"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="profile_image" class="form-label">Profile Image</label>
                                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                            <small class="form-text text-muted">Leave empty to keep current image</small>
                                            <div class="mt-2" id="image-preview-container" style="display: none;">
                                                <img id="image-preview" class="img-fluid rounded" style="max-width: 200px; max-height: 200px;">
                                            </div>
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

        updateBtn.disabled = !isValid;
        return isValid;
    }

    usernameInput.addEventListener('input', validateProfileForm);
    emailInput.addEventListener('input', validateProfileForm);

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

        changePasswordBtn.disabled = true;
        changeSpinner.style.display = 'inline-block';

        // Simulate form submission (replace with actual submission)
        setTimeout(() => {
            changePasswordBtn.disabled = false;
            changeSpinner.style.display = 'none';
            Swal.fire('Success!', 'Password changed successfully!', 'success');
        }, 2000);
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
