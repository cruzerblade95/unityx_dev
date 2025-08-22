@extends('admin/layouts/layoutMaster')

@section('title', 'User Profile')

@section('vendor-style')
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
});
</script>
@endsection
