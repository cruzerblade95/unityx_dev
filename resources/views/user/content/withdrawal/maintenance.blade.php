@extends('user.layouts.layoutMaster')

@section('title', 'Withdrawal Maintenance')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Maintenance Notice -->
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-body text-center py-5">
                    <!-- Maintenance Icon -->
                    <div class="mb-4">
                        <i class="fas fa-tools text-warning" style="font-size: 4rem;"></i>
                    </div>

                    <!-- Title -->
                    <h3 class="card-title mb-3">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Withdrawal System Under Maintenance
                    </h3>

                    <!-- Message -->
                    <div class="alert alert-warning mb-4">
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $maintenanceMessage }}</p>
                    </div>

                    <!-- Additional Info -->
                    <div class="mb-4">
                        <p class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            We apologize for any inconvenience. Please check back later.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('user.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home me-1"></i>
                            Go to Dashboard
                        </a>
                        <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh Page
                        </button>
                    </div>

                    <!-- Contact Support -->
                    <hr class="my-4">
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-headset me-1"></i>
                            Need urgent assistance? <a href="mailto:support@example.com">Contact Support</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: none;
    border-radius: 15px;
}

.alert-warning {
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
}

.btn {
    border-radius: 8px;
    padding: 10px 20px;
}

.fas.fa-tools {
    animation: maintenance-pulse 2s infinite;
}

@keyframes maintenance-pulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
@endsection
