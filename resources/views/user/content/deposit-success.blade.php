@extends('user.layouts.layoutMaster')

@section('title', 'Crypto Payment Details')

<style>
    /* Enhanced deposit success styling - Withdrawal crypto green theme */
    .success-card {
        cursor: default;
        transition: all 0.3s ease;
        border: 3px solid var(--bs-success);
        user-select: none;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, var(--bs-secondary-bg) 100%);
        box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.15);
    }
    
    .success-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
        transition: all 0.3s ease;
    }
    
    .success-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(var(--bs-success-rgb), 0.25);
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, rgba(var(--bs-success-rgb), 0.05) 100%);
    }
    
    .success-card .card-body {
        padding: 2rem 1.5rem;
        position: relative;
        z-index: 1;
    }
    
    .success-icon {
        font-size: 4rem;
        color: var(--bs-success);
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .success-card:hover .success-icon {
        transform: scale(1.05);
        color: rgba(var(--bs-success-rgb), 0.9);
    }
    
    .amount-card {
        border: 2px solid var(--bs-border-color);
        border-radius: 12px;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, var(--bs-secondary-bg) 100%);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .amount-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
        transition: all 0.3s ease;
    }
    
    .amount-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(var(--bs-success-rgb), 0.15);
        border-color: var(--bs-success);
    }
    
    .amount-card .card-body {
        padding: 1.5rem;
        position: relative;
        z-index: 1;
    }
    
    .payment-info-card {
        background: linear-gradient(135deg, var(--bs-secondary-bg) 0%, var(--bs-tertiary-bg) 100%);
        border: 3px solid var(--bs-success);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.15);
        transition: all 0.3s ease;
    }
    
    .payment-info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
        transition: all 0.3s ease;
    }
    
    .payment-info-card:hover {
        box-shadow: 0 8px 25px rgba(var(--bs-success-rgb), 0.25);
        transform: translateY(-2px);
    }
    
    .payment-info-card .card-body {
        padding: 1.5rem;
        position: relative;
        z-index: 1;
    }
    
    .payment-info-card h6 {
        color: var(--bs-success);
        font-weight: 700;
        margin-bottom: 0.75rem;
    }
    
    .address-code {
        background: var(--bs-secondary-bg);
        color: var(--bs-success);
        padding: 0.75rem;
        border-radius: 8px;
        font-size: 0.9rem;
        border: 2px solid var(--bs-border-color);
        position: relative;
        overflow: hidden;
        word-break: break-all;
    }
    
    .address-code::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
    }
    
    .btn {
        font-size: 1rem;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--bs-success) 0%, rgba(var(--bs-success-rgb), 0.8) 100%);
        border: none;
        color: white;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.9) 0%, var(--bs-success) 100%);
        color: white;
    }
    
    .btn-outline-primary {
        border: 2px solid var(--bs-success);
        color: var(--bs-success);
        background: var(--bs-body-bg);
    }
    
    .btn-outline-primary:hover {
        background: var(--bs-success);
        color: white;
        border-color: var(--bs-success);
    }
    
    .alert {
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        border: 2px solid;
        position: relative;
        overflow: hidden;
    }
    
    .alert::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        transition: all 0.3s ease;
    }
    
    .alert-info {
        background: linear-gradient(135deg, var(--bs-info-bg-subtle) 0%, rgba(var(--bs-info-rgb), 0.1) 100%);
        border-color: var(--bs-info);
        color: var(--bs-info-text-emphasis);
    }
    
    .alert-info::before {
        background: linear-gradient(90deg, var(--bs-info), var(--bs-success));
    }
    
    .page-title {
        color: var(--bs-success);
        font-weight: 700;
        margin-bottom: 1rem;
    }
    
    .page-subtitle {
        color: var(--bs-secondary-color);
        margin-bottom: 2rem;
    }
    
    /* Enhanced visibility for 40+ users */
    .text-primary {
        color: var(--bs-success) !important;
        font-weight: 700;
    }
    
    .text-warning {
        color: var(--bs-success) !important;
        font-weight: 700;
    }
    
    .text-success {
        color: var(--bs-success) !important;
        font-weight: 700;
    }

    /* Dark theme specific adjustments */
    [data-bs-theme="dark"] .success-card {
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, rgba(var(--bs-secondary-rgb), 0.3) 100%);
    }
    
    [data-bs-theme="dark"] .success-card:hover {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.15) 0%, rgba(var(--bs-success-rgb), 0.05) 100%);
    }
    
    [data-bs-theme="dark"] .amount-card {
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, rgba(var(--bs-secondary-rgb), 0.3) 100%);
    }
    
    [data-bs-theme="dark"] .payment-info-card {
        background: linear-gradient(135deg, rgba(var(--bs-secondary-rgb), 0.3) 0%, rgba(var(--bs-tertiary-rgb), 0.3) 100%);
    }
    
    [data-bs-theme="dark"] .address-code {
        background: rgba(var(--bs-secondary-rgb), 0.3);
    }
    
    [data-bs-theme="dark"] .alert-info {
        background: linear-gradient(135deg, rgba(13, 202, 240, 0.15) 0%, rgba(13, 202, 240, 0.05) 100%);
    }
</style>

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <!-- Success Header -->
            <div class="text-center mb-4">
                <h3 class="page-title">Payment Successful!</h3>
                <p class="page-subtitle">Your crypto deposit has been initiated successfully</p>
            </div>

            @if (isset($deposit) && $deposit)
                <!-- Main Success Card -->
                <div class="card success-card mb-4">
                    <div class="card-body text-center">
                        <div class="success-icon mb-3">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <h4 class="text-success mb-3">Crypto Deposit Initiated!</h4>
                        <p class="text-muted mb-0">
                            Please send the exact amount of cryptocurrency to the address below to complete your deposit.
                        </p>
                    </div>
                </div>

                <!-- Payment Amount Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card amount-card">
                            <div class="card-body text-center">
                                <h6 class="text-success">Amount to Send</h6>
                                <h3 class="text-primary mb-0">
                                    {{ $deposit->crypto_amount ?? 'Calculating...' }}
                                    @php
                                        $displayCurrency = $deposit->crypto_currency;
                                        if (str_contains($displayCurrency, '_TO_USDT')) {
                                            $displayCurrency = str_replace('_TO_USDT', '', $displayCurrency);
                                            $displayCurrency = str_replace('_', ' ', $displayCurrency);
                                        }
                                        $displayCurrency = strtoupper($displayCurrency);
                                    @endphp
                                    {{ $displayCurrency }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card amount-card">
                            <div class="card-body text-center">
                                <h6 class="text-success">USD Amount</h6>
                                <h3 class="text-warning mb-0">${{ number_format($deposit->amount, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Instructions Alert -->
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class='bx bx-info-circle me-2' style="font-size: 1.25rem;"></i>
                        <div>
                            <strong>Important:</strong> Send only
                            @php
                                $alertCurrency = $deposit->crypto_currency;
                                if (str_contains($alertCurrency, '_TO_USDT')) {
                                    $alertCurrency = str_replace('_TO_USDT', '', $alertCurrency);
                                    $alertCurrency = str_replace('_', ' ', $alertCurrency);
                                }
                                $alertCurrency = strtoupper($alertCurrency);
                            @endphp
                            {{ $alertCurrency }} to this address. Sending any other cryptocurrency will result in permanent loss.
                        </div>
                    </div>
                </div>

                @if ($deposit->payment_address)
                    <!-- Payment Address Card -->
                    <div class="card payment-info-card mb-4">
                        <div class="card-body">
                            <h6><i class='bx bx-wallet me-2'></i>Payment Address</h6>
                            
                            <div class="mb-3">
                                <small class="text-muted">Send {{ $displayCurrency }} to this address:</small>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="address-code flex-grow-1">
                                        {{ $deposit->payment_address }}
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="copyToClipboard('{{ $deposit->payment_address }}')">
                                        <i class='bx bx-copy'></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($deposit->address_memo)
                    <!-- Memo/Reference Card -->
                    <div class="card payment-info-card mb-4">
                        <div class="card-body">
                            <h6><i class='bx bx-note me-2'></i>Reference / Memo</h6>
                            
                            <div class="mb-3">
                                <small class="text-muted">Include this reference with your transaction:</small>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <div class="address-code flex-grow-1">
                                        {{ $deposit->address_memo }}
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm"
                                            onclick="copyToClipboard('{{ $deposit->address_memo }}')">
                                        <i class='bx bx-copy'></i>
                                    </button>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    <i class='bx bx-info-circle me-1'></i>
                                    Use this reference when sending your transaction for faster processing
                                </small>
                            </div>
                        </div>
                    </div>
                @endif

            @else
                <!-- Simple Success Card -->
                <div class="card success-card mb-4">
                    <div class="card-body text-center">
                        <div class="success-icon mb-3">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <h4 class="text-success mb-3">Payment Initiated!</h4>
                        <p class="text-muted mb-0">
                            Your cryptocurrency payment has been initiated. Please check your deposit history for payment details.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="row g-3">
                <div class="col-sm-6">
                    <a href="{{ route('user.deposit.index') }}" class="btn btn-primary w-100">
                        <i class='bx bx-arrow-back me-2'></i>
                        Back to Deposits
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('user.dashboard') }}" class="btn btn-outline-primary w-100">
                        <i class='bx bx-tachometer-alt me-2'></i>
                        Go to Dashboard
                    </a>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class='bx bx-shield-check me-1'></i>
                    All transactions are secured and encrypted
                </small>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = 'alert alert-success position-fixed';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
            toast.innerHTML = '<i class="bx bx-check me-2"></i>Address copied to clipboard!';
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }, function(err) {
            console.error('Could not copy text: ', err);
            alert('Failed to copy address. Please copy manually.');
        });
    }
</script>
@endsection
