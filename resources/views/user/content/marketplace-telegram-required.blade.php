@extends('user.layouts.layoutMaster')

@section('title', 'Telegram Connection Required')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h4 class="card-title mb-0">
                            <i class="fab fa-telegram-plane text-primary me-2"></i>
                            Telegram Connection Required
                        </h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fab fa-telegram-plane text-primary" style="font-size: 4rem;"></i>
                        </div>

                        <h5 class="text-primary mb-3">Connect Your Telegram Account</h5>

                        <p class="text-muted mb-4">
                            To access the marketplace and communicate with other traders, you need to connect your Telegram
                            account.
                            This ensures secure communication during transactions.
                        </p>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Why Telegram is required:</strong>
                            <ul class="text-start mt-2 mb-0">
                                <li>Secure communication with buyers/sellers</li>
                                <li>Real-time transaction verification</li>
                                <li>Protection against fraud</li>
                                <li>Direct contact for marketplace transactions</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark">How to connect:</h6>
                            <ol class="text-start">
                                <li>Go to your <strong>Profile Settings</strong></li>
                                <li>Find the <strong>Telegram Connection</strong> section</li>
                                <li>Follow the instructions to link your account</li>
                                <li>Return here to access the marketplace</li>
                            </ol>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ route('user.profile') }}" class="btn btn-primary">
                                <i class="fas fa-user-cog me-2"></i>
                                Go to Profile Settings
                            </a>
                            <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-question-circle me-2"></i>
                            Need Help?
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fab fa-telegram-plane text-primary me-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="mb-1">Telegram Support</h6>
                                        <small class="text-muted">@unityx_notification_bot</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-envelope text-primary me-3" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="mb-1">Email Support</h6>
                                        <small class="text-muted">support@unityx.dev</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        // Check if user came from marketplace link and show info
        @if (request()->has('from') && request()->get('from') === 'marketplace')
            Swal.fire({
                title: 'Telegram Required',
                text: 'Please connect your Telegram account to access the marketplace features.',
                icon: 'info',
                confirmButtonText: 'Understand'
            });
        @endif
    </script>
@endsection
