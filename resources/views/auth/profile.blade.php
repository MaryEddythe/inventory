@extends('layout.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">User Profile</h4>
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

                    <!-- Profile Information -->
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <img src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->username) . '&background=0D8ABC&color=fff&size=150' }}"
                                 alt="Profile Image"
                                 class="rounded-circle img-fluid mb-3"
                                 style="width: 150px; height: 150px; object-fit: cover;">
                            <h5>{{ Auth::user()->username }}</h5>
                        </div>
                        <div class="col-md-8">
                            <h5>Profile Information</h5>
                            <form id="profile-update-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="{{ Auth::user()->username }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Profile Image</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                    <small class="form-text text-muted">Leave empty to keep current image</small>
                                </div>

                                <button type="button" id="update-profile-btn" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <hr>
                    <h5>Change Password</h5>
                    <form action="{{ route('profile.change-password') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <span class="input-group-text" id="toggle-current-password" style="cursor: pointer;">
                                    <i class="bi bi-eye" id="icon-current-password"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <span class="input-group-text" id="toggle-new-password" style="cursor: pointer;">
                                    <i class="bi bi-eye" id="icon-new-password"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                <span class="input-group-text" id="toggle-confirm-password" style="cursor: pointer;">
                                    <i class="bi bi-eye" id="icon-confirm-password"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateBtn = document.getElementById('update-profile-btn');
    const form = document.getElementById('profile-update-form');

    updateBtn.addEventListener('click', function(e) {
        e.preventDefault();

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
                form.submit();
            }
        });
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
