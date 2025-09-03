@extends('admin/layouts/layoutMaster')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'User Profile')

@section('vendor-style')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js'])
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">User Profile /</span> {{ $user->firstname }} {{ $user->lastname }}
</h4>

<div class="row">
    <!-- User Information -->
    <div class="col-12 col-md-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="avatar avatar-xl">
                            <img src="{{ $user->profile_photo_path ? Storage::url($user->profile_photo_path) : asset('assets/img/avatars/1.png') }}"
                                 alt="Profile Photo"
                                 class="rounded-circle">
                        </div>
                    </div>
                    <h4 class="mb-2">{{ $user->firstname }} {{ $user->lastname }}</h4>
                    <span class="badge bg-label-{{ $user->role === 'admin' ? 'primary' : 'success' }}">
                        <i class="fas fa-{{ $user->role === 'admin' ? 'shield-alt' : 'user' }} me-1"></i>
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                <div class="user-info-list">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="fas fa-at"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Username</small>
                            <div class="text-body fw-medium">{{ $user->username }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="fas fa-envelope"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Email</small>
                            <div class="text-body fw-medium">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="fas fa-calendar"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Joined Date</small>
                            <div class="text-body fw-medium">{{ $user->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm flex-shrink-0 me-3">
                            <span class="avatar-initial rounded-circle bg-label-{{ $user->last_active_at && $user->last_active_at >= now()->subMinutes(15) ? 'success' : 'secondary' }}">
                                <i class="fas fa-circle"></i>
                            </span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Status</small>
                            <div class="text-body fw-medium">
                                {{ $user->last_active_at && $user->last_active_at >= now()->subMinutes(15) ? 'Online' : 'Offline' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet Information -->
    <div class="col-12 col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Wallet Information</h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-label-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Users
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($user->wallet)
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border-0 shadow-none bg-label-primary">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-circle bg-primary">
                                                <i class="fas fa-wallet"></i>
                                            </span>
                                        </div>
                                        <span class="text-primary fw-medium">Deposit</span>
                                    </div>
                                    <h4 class="mb-1">${{ number_format($user->wallet->deposit, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border-0 shadow-none bg-label-success">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-circle bg-success">
                                                <i class="fas fa-chart-line"></i>
                                            </span>
                                        </div>
                                        <span class="text-success fw-medium">Profit</span>
                                    </div>
                                    <h4 class="mb-1">${{ number_format($user->wallet->profit, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border-0 shadow-none bg-label-warning">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-circle bg-warning">
                                                <i class="fas fa-gift"></i>
                                            </span>
                                        </div>
                                        <span class="text-warning fw-medium">Bonus</span>
                                    </div>
                                    <h4 class="mb-1">${{ number_format($user->wallet->bonus, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card border-0 shadow-none bg-label-info">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded-circle bg-info">
                                                <i class="fas fa-coins"></i>
                                            </span>
                                        </div>
                                        <span class="text-info fw-medium">Cash</span>
                                    </div>
                                    <h4 class="mb-1">${{ number_format($user->wallet->cash, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0">Total Balance</h6>
                            <span class="badge bg-label-primary">
                                <i class="fas fa-wallet me-1"></i>
                                Overall
                            </span>
                        </div>
                        <h3 class="mb-0">${{ number_format($user->wallet->total_balance, 2) }}</h3>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl mb-3">
                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                <i class="fas fa-wallet fa-2x"></i>
                            </span>
                        </div>
                        <h6 class="text-muted">No wallet information available</h6>
                        <button type="button" id="createWalletBtn" class="btn btn-primary mt-3" data-user-id="{{ $user->id }}">
                            <i class="fas fa-plus me-1"></i>
                            Create Wallet
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- KYC Information Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card me-2"></i>KYC Verification
                    </h5>
                    @if($user->kycVerification)
                        <a href="{{ route('admin.kyc.show', $user->kycVerification->id) }}" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i>View KYC Documents
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($user->kycVerification)
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card bg-label-{{ $user->kycVerification->status_color }}">
                                <div class="card-body text-center">
                                    <div class="avatar mb-2">
                                        <span class="avatar-initial rounded-circle bg-{{ $user->kycVerification->status_color }}">
                                            <i class="fas fa-{{ $user->kycVerification->kyc_status == 2 ? 'check-circle' : ($user->kycVerification->kyc_status == 1 ? 'clock' : 'times-circle') }}"></i>
                                        </span>
                                    </div>
                                    <h5 class="mb-1">{{ $user->kycVerification->status_text }}</h5>
                                    <small>Verification Status</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-label-info">
                                <div class="card-body text-center">
                                    <div class="avatar mb-2">
                                        <span class="avatar-initial rounded-circle bg-info">
                                            <i class="fas fa-file-alt"></i>
                                        </span>
                                    </div>
                                    <h5 class="mb-1">
                                        {{ ($user->kycVerification->kyc_doc1 ? 1 : 0) + ($user->kycVerification->kyc_doc2 ? 1 : 0) + ($user->kycVerification->kyc_doc3 ? 1 : 0) }}
                                    </h5>
                                    <small>Documents Submitted</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center h-100">
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">Submitted:</small>
                                        <span class="text-body fw-medium">{{ $user->kycVerification->created_at->format('M d, Y g:i A') }}</span>
                                    </div>
                                    @if($user->kycVerification->updated_at != $user->kycVerification->created_at)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">Last Updated:</small>
                                            <span class="text-body fw-medium">{{ $user->kycVerification->updated_at->format('M d, Y g:i A') }}</span>
                                        </div>
                                    @endif
                                    @if($user->kycVerification->kyc_reason)
                                        <div class="mt-2">
                                            <small class="text-muted d-block">Admin Note:</small>
                                            <span class="text-body">{{ $user->kycVerification->kyc_reason }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Document Status -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="mb-3">Document Status</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-id-card me-2 text-{{ $user->kycVerification->kyc_doc1 ? 'success' : 'muted' }}"></i>
                                    <span>ID Front {{ $user->kycVerification->kyc_doc1 ? '✓' : '✗' }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-id-card me-2 text-{{ $user->kycVerification->kyc_doc2 ? 'success' : 'muted' }}"></i>
                                    <span>ID Back {{ $user->kycVerification->kyc_doc2 ? '✓' : '✗' }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-passport me-2 text-{{ $user->kycVerification->kyc_doc3 ? 'success' : 'muted' }}"></i>
                                    <span>Passport/Additional {{ $user->kycVerification->kyc_doc3 ? '✓' : '✗' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl mb-3">
                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                <i class="fas fa-id-card fa-2x"></i>
                            </span>
                        </div>
                        <h6 class="text-muted">No KYC verification submitted</h6>
                        <p class="text-muted small">User has not submitted any KYC documents yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- User Management Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Downline Management
                    </h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus me-1"></i>Add User
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Downline Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-label-primary">
                            <div class="card-body text-center">
                                <div class="avatar mb-2">
                                    <span class="avatar-initial rounded-circle bg-primary">
                                        <i class="fas fa-users"></i>
                                    </span>
                                </div>
                                <h4 class="mb-1">{{ $downlineCount ?? 0 }}</h4>
                                <small>Direct Downlines</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-label-success">
                            <div class="card-body text-center">
                                <div class="avatar mb-2">
                                    <span class="avatar-initial rounded-circle bg-success">
                                        <i class="fas fa-user-check"></i>
                                    </span>
                                </div>
                                <h4 class="mb-1">{{ $activeDownlines ?? 0 }}</h4>
                                <small>Active Users</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-label-info">
                            <div class="card-body text-center">
                                <div class="avatar mb-2">
                                    <span class="avatar-initial rounded-circle bg-info">
                                        <i class="fas fa-network-wired"></i>
                                    </span>
                                </div>
                                <h4 class="mb-1">{{ $totalNetworkCount ?? 0 }}</h4>
                                <small>Total Network</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-label-warning">
                            <div class="card-body text-center">
                                <div class="avatar mb-2">
                                    <span class="avatar-initial rounded-circle bg-warning">
                                        <i class="fas fa-dollar-sign"></i>
                                    </span>
                                </div>
                                <h4 class="mb-1">${{ number_format($totalDownlineEarnings ?? 0, 2) }}</h4>
                                <small>Network Earnings</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Downline Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="downlineTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Wallet Balance</th>
                                <th>Joined Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($downlines ?? [] as $index => $downline)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <img src="{{ $downline->profile_photo_path ? Storage::url($downline->profile_photo_path) : asset('assets/img/avatars/1.png') }}"
                                                     alt="Avatar" class="rounded-circle">
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $downline->firstname }} {{ $downline->lastname }}</h6>
                                                <small class="text-muted">ID: {{ $downline->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $downline->username }}</td>
                                    <td>{{ $downline->email }}</td>
                                    <td>
                                        @if($downline->wallet)
                                            <span class="badge bg-success">${{ number_format($downline->wallet->total_balance, 2) }}</span>
                                        @else
                                            <span class="badge bg-secondary">No Wallet</span>
                                        @endif
                                    </td>
                                    <td>{{ $downline->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $downline->last_active_at && $downline->last_active_at >= now()->subMinutes(15) ? 'success' : 'secondary' }}">
                                            {{ $downline->last_active_at && $downline->last_active_at >= now()->subMinutes(15) ? 'Online' : 'Offline' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('admin.users.profile', $downline->id) }}"><i class="fas fa-eye me-2"></i>View Profile</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit User</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash me-2"></i>Delete User</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="avatar avatar-lg mb-2">
                                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                                <i class="fas fa-users fa-2x"></i>
                                            </span>
                                        </div>
                                        <h6 class="text-muted">No downlines found</h6>
                                        <small class="text-muted">This user doesn't have any direct downlines yet.</small>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    @csrf
                    <input type="hidden" name="upline_id" value="{{ $user->id }}">

                    <!-- User Information -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> This user will be added as a direct downline of <strong>{{ $user->firstname }} {{ $user->lastname }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_firstname" name="firstname" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_lastname" name="lastname" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_username" name="username" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="add_email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="add_password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add_password_confirmation" name="password_confirmation" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> The new user will receive login credentials via email. Make sure to provide a valid email address.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="submit" form="addUserForm" class="btn btn-primary">
                    <i class="fas fa-user-plus me-1"></i>Add User
                </button>
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

    // Handle create wallet button click
    $('#createWalletBtn').on('click', function(e) {
        e.preventDefault();

        const button = $(this);
        const userId = button.data('user-id');
        const originalBtnText = button.html();

        // Disable button and show loading state
        button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...');
        button.prop('disabled', true);

        // Send AJAX request to create wallet
        $.ajax({
            url: `/admin/wallets/${userId}/create`,
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Reload the page to show the new wallet
                            location.reload();
                        }
                    });
                } else {
                    showError(response.message || 'Failed to create wallet.');
                    resetButton();
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred while creating the wallet.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    // Validation errors
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.length > 0 ? errors[0] : errorMessage;
                }

                showError(errorMessage);
                resetButton();
            }
        });

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: message,
                confirmButtonText: 'OK'
            });
        }

        function resetButton() {
            button.html(originalBtnText);
            button.prop('disabled', false);
        }
    });

    // Handle password toggle
    $('#togglePassword').on('click', function() {
        const passwordField = $('#add_password');
        const icon = $(this).find('i');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Handle add user form submission
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('button[type="submit"][form="addUserForm"]');
        const originalBtnText = submitBtn.html();

        // Clear previous validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Disable submit button and show loading state
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding User...');
        submitBtn.prop('disabled', true);

        // Prepare form data
        const formData = {
            firstname: $('#add_firstname').val(),
            lastname: $('#add_lastname').val(),
            username: $('#add_username').val(),
            email: $('#add_email').val(),
            password: $('#add_password').val(),
            password_confirmation: $('#add_password_confirmation').val(),
            upline_id: $('input[name="upline_id"]').val()
        };

        // Send AJAX request
        $.ajax({
            url: '{{ route("admin.users.store") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Close modal and reload page
                            $('#addUserModal').modal('hide');
                            location.reload();
                        }
                    });
                } else {
                    showError(response.message || 'Failed to add user.');
                    resetAddUserButton();
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;

                    // Display field-specific errors
                    Object.keys(errors).forEach(function(field) {
                        const input = $(`#add_${field}`);
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
                    let errorMessage = 'An error occurred while adding the user.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    showError(errorMessage);
                }

                resetAddUserButton();
            }
        });

        function resetAddUserButton() {
            submitBtn.html(originalBtnText);
            submitBtn.prop('disabled', false);
        }
    });

    // Reset form when modal is hidden
    $('#addUserModal').on('hidden.bs.modal', function() {
        $('#addUserForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
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
