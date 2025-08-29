@extends('user.layouts.layoutMaster')

@section('title', 'Crypto Payment Details')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

<style>
    /* Enhanced crypto payment details styling - matching deposit theme */
    .crypto-payment-card {
        border: 3px solid var(--bs-success);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, var(--bs-secondary-bg) 100%);
        box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.15);
        margin-bottom: 2rem;
    }

    .crypto-payment-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
    }

    .card-header-crypto {
        background: linear-gradient(135deg, var(--bs-success) 0%, rgba(var(--bs-success-rgb), 0.8) 100%);
        color: white;
        text-align: center;
        font-weight: 700;
        padding: 2rem 1.5rem;
        border: none;
        position: relative;
        z-index: 1;
    }

    .card-header-crypto h4 {
        margin-bottom: 0.5rem;
        font-size: 1.8rem;
    }

    .card-header-crypto .card-description {
        margin: 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .info-box {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.1) 0%, rgba(var(--bs-info-rgb), 0.05) 100%);
        border: 2px solid rgba(var(--bs-success-rgb), 0.2);
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .info-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(var(--bs-success-rgb), 0.2);
        border-color: var(--bs-success);
    }

    .info-box-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.5rem;
        color: white;
        flex-shrink: 0;
    }

    .info-box-content {
        flex: 1;
    }

    .info-box-text {
        display: block;
        font-weight: 600;
        color: var(--bs-body-color);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-box-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--bs-emphasis-color);
    }

    .payment-details-card {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05) 0%, rgba(var(--bs-success-rgb), 0.05) 100%);
        border: 2px solid rgba(var(--bs-primary-rgb), 0.2);
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .payment-details-card .card-header {
        background: linear-gradient(135deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.8) 100%);
        color: white;
        font-weight: 700;
        border: none;
        padding: 1rem 1.5rem;
    }

    .qr-card {
        background: linear-gradient(135deg, rgba(var(--bs-info-rgb), 0.05) 0%, rgba(var(--bs-success-rgb), 0.05) 100%);
        border: 2px solid rgba(var(--bs-info-rgb), 0.2);
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .qr-card .card-header {
        background: linear-gradient(135deg, var(--bs-info) 0%, rgba(var(--bs-info-rgb), 0.8) 100%);
        color: white;
        font-weight: 700;
        border: none;
        padding: 1rem 1.5rem;
    }

    .fee-breakdown-card {
        background: linear-gradient(135deg, rgba(var(--bs-warning-rgb), 0.05) 0%, rgba(var(--bs-success-rgb), 0.05) 100%);
        border: 2px solid rgba(var(--bs-warning-rgb), 0.2);
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .fee-breakdown-card .card-header {
        background: linear-gradient(135deg, var(--bs-warning) 0%, #fd7e14 100%);
        color: white;
        font-weight: 700;
        border: none;
        padding: 1rem 1.5rem;
    }

    .form-control {
        font-size: 1.1rem;
        padding: 1rem 1.25rem;
        border: 2px solid var(--bs-border-color);
        border-radius: 10px;
        transition: all 0.3s ease;
        background-color: var(--bs-body-bg);
        color: var(--bs-body-color);
        font-weight: 600;
    }

    .form-control:focus {
        border-color: var(--bs-success);
        box-shadow: 0 0 0 0.3rem rgba(var(--bs-success-rgb), 0.25);
        background-color: var(--bs-body-bg);
    }

    .input-group .btn {
        border: 2px solid var(--bs-border-color);
        border-left: none;
        background: var(--bs-success);
        color: white;
        transition: all 0.3s ease;
    }

    .input-group .btn:hover {
        background: rgba(var(--bs-success-rgb), 0.8);
        transform: scale(1.05);
    }

    .btn {
        font-size: 1.1rem;
        padding: 1rem 2rem;
        border-radius: 10px;
        font-weight: 700;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
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

    .btn-secondary {
        background: linear-gradient(135deg, var(--bs-secondary) 0%, rgba(var(--bs-secondary-rgb), 0.8) 100%);
        border: none;
        color: white;
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, rgba(var(--bs-secondary-rgb), 0.9) 0%, var(--bs-secondary) 100%);
        color: white;
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

    .alert-warning {
        background: linear-gradient(135deg, var(--bs-warning-bg-subtle) 0%, rgba(var(--bs-warning-rgb), 0.1) 100%);
        border-color: var(--bs-warning);
        color: var(--bs-warning-text-emphasis);
    }

    .alert-warning::before {
        background: linear-gradient(90deg, var(--bs-warning), #fd7e14);
    }

    .alert-success {
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, rgba(var(--bs-success-rgb), 0.1) 100%);
        border-color: var(--bs-success);
        color: var(--bs-success-text-emphasis);
    }

    .alert-success::before {
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
    }

    .badge {
        font-size: 1rem;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        font-weight: 600;
    }

    .fee-amount-display {
        text-align: center;
        padding: 1.5rem;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.1) 0%, rgba(var(--bs-info-rgb), 0.1) 100%);
        border: 2px solid rgba(var(--bs-success-rgb), 0.2);
        margin-bottom: 1rem;
    }

    .fee-amount-display h6 {
        font-weight: 700;
        margin-bottom: 0.8rem;
        color: var(--bs-body-color);
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    #qrcode canvas {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
</style>

@section('content')
    <div class="container py-4 position-relative" style="z-index: 1;">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <div class="card crypto-payment-card">
                    <div class="card-header card-header-crypto">
                        <h4 class="card-title">
                            <i class="fas fa-coins me-2"></i>
                            Crypto Payment Details
                        </h4>
                        <p class="card-description">Complete your {{ $deposit->crypto_currency }} deposit by sending the
                            exact amount to the address below</p>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Payment Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-hourglass-half"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Payment Status</span>
                                        <span class="info-box-number">
                                            <span class="badge bg-warning">{{ ucfirst($deposit->status) }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-wallet"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">You will receive</span>
                                        <span class="info-box-number">${{ number_format($deposit->amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Instructions -->
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Important Instructions</h5>
                            <ul class="mb-0">
                                <li>Send <strong>exactly {{ $deposit->crypto_amount }}</strong>
                                    {{ strtoupper(str_replace('_TO_USDT', '', $deposit->crypto_currency)) }}</li>
                                <li>Use the payment address provided below</li>
                                <li>Do not send any other cryptocurrency or amount</li>
                                <li>Your deposit will be credited after network confirmation</li>
                            </ul>
                        </div>

                        <!-- Payment Details -->
                        <div class="card payment-details-card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-credit-card me-2"></i>Payment Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Cryptocurrency</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control"
                                                    value="{{ strtoupper(str_replace('_TO_USDT', '', $deposit->crypto_currency)) }}"
                                                    readonly>
                                                <button class="btn btn-outline-secondary" type="button"
                                                    onclick="copyToClipboard('{{ strtoupper(str_replace('_TO_USDT', '', $deposit->crypto_currency)) }}')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Amount to Send</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control"
                                                    value="{{ $deposit->crypto_amount }}" readonly>
                                                <button class="btn btn-outline-secondary" type="button"
                                                    onclick="copyToClipboard('{{ $deposit->crypto_amount }}')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Payment Address</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="{{ $deposit->payment_address }}"
                                            readonly>
                                        <button class="btn btn-outline-secondary" type="button"
                                            onclick="copyToClipboard('{{ $deposit->payment_address }}')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Click the copy button to copy the address to your
                                        clipboard</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Order Reference</label>
                                            <input type="text" class="form-control" value="{{ $deposit->reference }}"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Payment ID</label>
                                            <input type="text" class="form-control" value="{{ $deposit->payment_id }}"
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <div class="card qr-card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-qrcode me-2"></i>QR Code</h5>
                            </div>
                            <div class="card-body text-center">
                                <div id="qrcode" class="mb-3"></div>
                                <p class="text-muted">Scan this QR code with your crypto wallet to auto-fill the payment
                                    address</p>
                            </div>
                        </div>

                        <!-- Fee Breakdown -->
                        <div class="card fee-breakdown-card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-calculator me-2"></i>Fee Breakdown</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="fee-amount-display">
                                            <h6>Requested Amount</h6>
                                            <span class="h5 text-primary">${{ number_format($deposit->amount, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="fee-amount-display">
                                            <h6>Processing Fee ({{ $deposit->tax_percent }}%)</h6>
                                            <span
                                                class="h5 text-warning">${{ number_format($deposit->tax_amount, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="fee-amount-display">
                                            <h6>Total Paid</h6>
                                            <span
                                                class="h5 text-success">${{ number_format($deposit->amount + $deposit->tax_amount, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="{{ route('user.deposit.index') }}" class="btn btn-secondary w-100">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Deposits
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <button type="button" class="btn btn-primary w-100" onclick="checkPaymentStatus()">
                                        <i class="fas fa-sync-alt me-2"></i>Check Payment Status
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('vendor-script')
        <!-- Include QR Code library -->
        <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    @endsection

    @section('page-script')
        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    // Show success message
                    showToast('Copied to clipboard!', 'success');
                }, function(err) {
                    console.error('Could not copy text: ', err);
                    showToast('Failed to copy', 'error');
                });
            }

            function showToast(message, type) {
                // Create toast element
                const toast = document.createElement('div');
                toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible`;
                toast.style.position = 'fixed';
                toast.style.top = '20px';
                toast.style.right = '20px';
                toast.style.zIndex = '9999';
                toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

                document.body.appendChild(toast);

                // Auto remove after 3 seconds
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 3000);
            }

            function checkPaymentStatus() {
                // Refresh the page to check for updates
                location.reload();
            }

            // Generate QR Code
            document.addEventListener('DOMContentLoaded', function() {
                const address = '{{ $deposit->payment_address }}';
                const amount = '{{ $deposit->crypto_amount }}';
                const currency = '{{ strtoupper(str_replace('_TO_USDT', '', $deposit->crypto_currency)) }}';

                // Create QR code with payment URI format
                const qrData = `${currency.toLowerCase()}:${address}?amount=${amount}`;

                QRCode.toCanvas(document.getElementById('qrcode'), qrData, {
                    width: 200,
                    height: 200,
                    colorDark: '#000000',
                    colorLight: '#ffffff'
                }, function(error) {
                    if (error) {
                        console.error('QR Code generation failed:', error);
                        document.getElementById('qrcode').innerHTML =
                            '<p class="text-muted">QR code generation failed</p>';
                    }
                });
            });
        </script>
    @endsection
