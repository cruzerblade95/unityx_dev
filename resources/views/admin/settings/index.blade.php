@extends('admin/layouts/layoutMaster')

@section('title', 'Admin Settings')

@section('vendor-style')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endsection

@section('vendor-script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Admin /</span> Settings
</h4>

<div class="row">
    <!-- Profile Settings -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-cog me-2"></i>Profile Settings
                </h5>
            </div>
            <div class="card-body">
                <form id="profileForm">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="{{ $admin->username }}" required>
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Username must be unique and can contain letters, numbers, dashes, and underscores.</small>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="{{ $admin->email }}" required>
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Email address must be unique and valid.</small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="updateProfileBtn">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Settings -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">
                    <i class="fas fa-lock me-2"></i>Change Password
                </h5>
            </div>
            <div class="card-body">
                <form id="passwordForm">
                    @csrf
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password"
                                   minlength="8" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                        <small class="text-muted">Password must be at least 8 characters long.</small>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                                   minlength="8" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning" id="updatePasswordBtn">
                            <i class="fas fa-key me-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Account Information -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Account Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-label-primary">
                            <div class="card-body text-center">
                                <div class="avatar mb-2">
                                    <span class="avatar-initial rounded-circle bg-primary">
                                        <i class="fas fa-user-shield"></i>
                                    </span>
                                </div>
                                <h5 class="mb-1">{{ $admin->username }}</h5>
                                <small>Current Username</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-label-info">
                            <div class="card-body text-center">
                                <div class="avatar mb-2">
                                    <span class="avatar-initial rounded-circle bg-info">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                </div>
                                <h5 class="mb-1">{{ $admin->email }}</h5>
                                <small>Current Email</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-label-success">
                            <div class="card-body text-center">
                                <div class="avatar mb-2">
                                    <span class="avatar-initial rounded-circle bg-success">
                                        <i class="fas fa-calendar"></i>
                                    </span>
                                </div>
                                <h5 class="mb-1">{{ $admin->created_at ? $admin->created_at->format('M d, Y') : 'N/A' }}</h5>
                                <small>Account Created</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-label-warning">
                            <div class="card-body text-center">
                                <div class="avatar mb-2">
                                    <span class="avatar-initial rounded-circle bg-warning">
                                        <i class="fas fa-clock"></i>
                                    </span>
                                </div>
                                <h5 class="mb-1">{{ $admin->updated_at ? $admin->updated_at->format('M d, Y') : 'N/A' }}</h5>
                                <small>Last Updated</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-shield-alt me-2"></i>Security Information</h6>
                        <p class="mb-2">Keep your admin credentials secure and follow these best practices:</p>
                        <ul class="mb-0">
                            <li>Use a strong, unique password that you don't use elsewhere</li>
                            <li>Change your password regularly (recommended every 3-6 months)</li>
                            <li>Keep your username and email information up to date</li>
                            <li>Log out from shared computers and clear browser data</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Password toggle functionality
    $('#toggleCurrentPassword').on('click', function() {
        togglePasswordVisibility('#current_password', this);
    });

    $('#toggleNewPassword').on('click', function() {
        togglePasswordVisibility('#password', this);
    });

    $('#toggleConfirmPassword').on('click', function() {
        togglePasswordVisibility('#password_confirmation', this);
    });

    function togglePasswordVisibility(inputSelector, buttonElement) {
        const passwordField = $(inputSelector);
        const icon = $(buttonElement).find('i');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    }

    // Profile form submission
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('#updateProfileBtn');
        const originalBtnText = submitBtn.html();

        // Clear previous validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Disable submit button and show loading state
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
        submitBtn.prop('disabled', true);

        // Prepare form data
        const formData = {
            username: $('#username').val(),
            email: $('#email').val()
        };

        // Send AJAX request
        $.ajax({
            url: '{{ route("admin.settings.profile.update") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        timer: 3000
                    }).then(() => {
                        // Reload page to show updated information
                        location.reload();
                    });
                } else {
                    showError(response.message || 'Failed to update profile.');
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;

                    // Display field-specific errors
                    Object.keys(errors).forEach(function(field) {
                        const input = $(`#${field}`);
                        const feedback = input.siblings('.invalid-feedback');

                        input.addClass('is-invalid');
                        feedback.text(errors[field][0]);
                    });

                    // Show general error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check the form and correct the errors.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    let errorMessage = 'An error occurred while updating profile.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    showError(errorMessage);
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Password form submission
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('#updatePasswordBtn');
        const originalBtnText = submitBtn.html();

        // Clear previous validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Check if passwords match
        const password = $('#password').val();
        const confirmPassword = $('#password_confirmation').val();

        if (password !== confirmPassword) {
            $('#password_confirmation').addClass('is-invalid');
            $('#password_confirmation').siblings('.invalid-feedback').text('Passwords do not match.');
            return;
        }

        // Disable submit button and show loading state
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
        submitBtn.prop('disabled', true);

        // Prepare form data
        const formData = {
            current_password: $('#current_password').val(),
            password: password,
            password_confirmation: confirmPassword
        };

        // Send AJAX request
        $.ajax({
            url: '{{ route("admin.settings.password.update") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Clear the form
                    form[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        timer: 3000
                    });
                } else {
                    showError(response.message || 'Failed to update password.');
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;

                    // Display field-specific errors
                    Object.keys(errors).forEach(function(field) {
                        const input = $(`#${field}`);
                        const feedback = input.siblings('.invalid-feedback');

                        input.addClass('is-invalid');
                        feedback.text(errors[field][0]);
                    });

                    // Show general error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: xhr.responseJSON.message || 'Please check the form and correct the errors.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    let errorMessage = 'An error occurred while updating password.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    showError(errorMessage);
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            confirmButtonText: 'OK'
        });
    }
});
</script>
@endsection
