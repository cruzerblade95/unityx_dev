@extends('user.layouts.layoutMaster')

@section('title', 'Payment Information')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

<style>
    /* Enhanced form styling for better visibility - Theme adaptive */
    .payment-info-card {
        border: 2px solid var(--bs-border-color);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        background: var(--bs-body-bg);
    }

    .payment-info-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .current-info-card {
        border: 2px solid var(--bs-success);
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, var(--bs-body-bg) 100%);
    }

    .section-header {
        background: linear-gradient(135deg, var(--bs-secondary-bg) 0%, var(--bs-tertiary-bg) 100%);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--bs-primary);
    }

    .form-control {
        font-size: 1rem;
        padding: 0.75rem 1rem;
        border: 2px solid var(--bs-border-color);
        border-radius: 8px;
        transition: all 0.3s ease;
        background-color: var(--bs-body-bg);
        color: var(--bs-body-color);
    }

    .form-control:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
        transform: scale(1.02);
        background-color: var(--bs-body-bg);
    }

    .form-label {
        font-weight: 600;
        font-size: 1rem;
        color: var(--bs-emphasis-color);
        margin-bottom: 0.5rem;
    }

    .form-text {
        font-size: 0.9rem;
        color: var(--bs-secondary-color);
        margin-top: 0.5rem;
    }

    .crypto-section {
        background: linear-gradient(135deg, var(--bs-warning-bg-subtle) 0%, rgba(var(--bs-warning-rgb), 0.1) 20%, var(--bs-body-bg) 100%);
        border: 2px solid var(--bs-warning);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .crypto-section h6 {
        color: var(--bs-warning-text-emphasis);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .card-section {
        background: linear-gradient(135deg, rgba(var(--bs-purple-rgb, 111, 66, 193), 0.1) 0%, var(--bs-secondary-bg) 20%, var(--bs-body-bg) 100%);
        border: 2px solid rgba(var(--bs-purple-rgb, 111, 66, 193), 1);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .card-section .form-label {
        color: rgba(var(--bs-purple-rgb, 111, 66, 193), 1);
    }

    .info-alert {
        background: linear-gradient(135deg, var(--bs-info-bg-subtle) 0%, rgba(var(--bs-info-rgb), 0.1) 100%);
        border: 2px solid var(--bs-info);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-alert h6 {
        color: var(--bs-info-text-emphasis);
        font-weight: 700;
    }

    .info-alert ul li {
        font-size: 1rem;
        margin-bottom: 0.5rem;
        color: var(--bs-success-text-emphasis);
    }

    .btn {
        font-size: 1rem;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.8) 100%);
        border: none;
    }

    .btn-outline-secondary {
        border: 2px solid var(--bs-secondary);
        color: var(--bs-secondary);
        font-weight: 600;
    }

    .btn-outline-secondary:hover {
        background: var(--bs-secondary);
        color: var(--bs-white);
    }

    /* Enhanced visibility for required fields */
    .text-danger {
        font-weight: 700;
        font-size: 1.1rem;
    }

    /* Better error styling */
    .is-invalid {
        border-color: var(--bs-danger);
        box-shadow: 0 0 0 0.2rem rgba(var(--bs-danger-rgb), 0.25);
    }

    .invalid-feedback {
        font-size: 0.9rem;
        font-weight: 600;
    }

    /* Success state styling */
    .current-info-display {
        background: rgba(var(--bs-success-rgb), 0.1);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid rgba(var(--bs-success-rgb), 0.2);
    }

    .current-info-display strong {
        color: var(--bs-success-text-emphasis);
        font-size: 1rem;
    }

    .current-info-display .text-muted {
        color: var(--bs-secondary-color) !important;
        font-size: 1rem;
    }

    /* Dark theme specific adjustments */
    [data-bs-theme="dark"] .crypto-section {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(255, 193, 7, 0.05) 20%, var(--bs-body-bg) 100%);
    }

    [data-bs-theme="dark"] .card-section {
        background: linear-gradient(135deg, rgba(111, 66, 193, 0.15) 0%, rgba(111, 66, 193, 0.05) 20%, var(--bs-body-bg) 100%);
    }

    [data-bs-theme="dark"] .info-alert {
        background: linear-gradient(135deg, rgba(13, 202, 240, 0.15) 0%, rgba(13, 202, 240, 0.05) 100%);
    }
</style>

@section('content')
    <div class="container py-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                @if (auth()->user()->payment_info_completed)
                    <!-- Current Payment Information Display -->
                    <div class="card payment-info-card current-info-card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                Current Payment Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="current-info-display">
                                        <strong>National ID / Passport:</strong><br>
                                        <span class="text-muted">{{ auth()->user()->national_id }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="current-info-display">
                                        <strong>Account Holder Name:</strong><br>
                                        <span class="text-muted">{{ auth()->user()->cardholder_name }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="current-info-display">
                                <strong>Crypto Wallet Addresses:</strong><br>
                                <div class="mt-2">
                                    @if (auth()->user()->usdt_bep20_address)
                                        <small class="text-muted d-block"><strong>USDT BEP20:</strong></small>
                                        <span
                                            class="text-muted font-monospace d-block">{{ auth()->user()->usdt_bep20_address }}</span>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    @if (auth()->user()->usdc_bep20_address)
                                        <small class="text-muted d-block"><strong>USDC BEP20:</strong></small>
                                        <span
                                            class="text-muted font-monospace d-block">{{ auth()->user()->usdc_bep20_address }}</span>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    @if (auth()->user()->fdusd_bep20_address)
                                        <small class="text-muted d-block"><strong>FDUSD BEP20:</strong></small>
                                        <span
                                            class="text-muted font-monospace d-block">{{ auth()->user()->fdusd_bep20_address }}</span>
                                    @endif
                                </div>
                                @if (!auth()->user()->usdt_bep20_address && !auth()->user()->usdc_bep20_address && !auth()->user()->fdusd_bep20_address)
                                    <span class="text-muted">No crypto wallet addresses set</span>
                                @endif
                            </div>
                            @if (auth()->user()->etc_black_card_number)
                                <div class="current-info-display">
                                    <strong>Crypto Bank Card:</strong><br>
                                    <span class="text-muted">**** **** ****
                                        {{ substr(auth()->user()->etc_black_card_number, -4) }}</span>
                                </div>
                            @endif
                            @if (auth()->user()->account_number)
                                <div class="current-info-display">
                                    <strong>Account Number:</strong><br>
                                    <span class="text-muted">****{{ substr(auth()->user()->account_number, -4) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="card payment-info-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            {{ auth()->user()->payment_info_completed ? 'Update Payment Information' : 'Payment Information' }}
                        </h5>
                        <p class="text-muted mb-0 mt-2">
                            {{ auth()->user()->payment_info_completed
                                ? 'Update your payment information below.'
                                : 'Please provide your payment information to access withdrawal features.' }}
                        </p>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('user.payment-info.store') }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="national_id" class="form-label">
                                            National ID / Passport ID <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                            class="form-control @error('national_id') is-invalid @enderror" id="national_id"
                                            name="national_id"
                                            value="{{ old('national_id', auth()->user()->national_id) }}" required>
                                        @error('national_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Enter your National ID or Passport number</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cardholder_name" class="form-label">
                                            Account Holder Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                            class="form-control @error('cardholder_name') is-invalid @enderror"
                                            id="cardholder_name" name="cardholder_name"
                                            value="{{ old('cardholder_name', auth()->user()->cardholder_name) }}" required>
                                        @error('cardholder_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Full name as it appears on your card</div>
                                    </div>
                                </div>
                            </div>

                            <div class="crypto-section">
                                <h6 class="mb-3">
                                    <i class="fab fa-bitcoin me-2"></i>
                                    Crypto Wallet Addresses <span class="text-danger">*</span>
                                </h6>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Please provide at least one crypto wallet address. You can add more wallet addresses for
                                    different cryptocurrencies.
                                </p>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="usdt_bep20_address" class="form-label">
                                                <i class="fas fa-wallet me-1"></i>
                                                USDT BEP20 Address
                                            </label>
                                            <input type="text"
                                                class="form-control @error('usdt_bep20_address') is-invalid @enderror"
                                                id="usdt_bep20_address" name="usdt_bep20_address"
                                                value="{{ old('usdt_bep20_address', auth()->user()->usdt_bep20_address) }}">
                                            @error('usdt_bep20_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Your USDT BEP20 wallet address</div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="usdc_bep20_address" class="form-label">
                                                <i class="fas fa-wallet me-1"></i>
                                                USDC BEP20 Address
                                            </label>
                                            <input type="text"
                                                class="form-control @error('usdc_bep20_address') is-invalid @enderror"
                                                id="usdc_bep20_address" name="usdc_bep20_address"
                                                value="{{ old('usdc_bep20_address', auth()->user()->usdc_bep20_address) }}">
                                            @error('usdc_bep20_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Your USDC BEP20 wallet address</div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="fdusd_bep20_address" class="form-label">
                                                <i class="fas fa-wallet me-1"></i>
                                                FDUSD BEP20 Address
                                            </label>
                                            <input type="text"
                                                class="form-control @error('fdusd_bep20_address') is-invalid @enderror"
                                                id="fdusd_bep20_address" name="fdusd_bep20_address"
                                                value="{{ old('fdusd_bep20_address', auth()->user()->fdusd_bep20_address) }}">
                                            @error('fdusd_bep20_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Your FDUSD BEP20 wallet address</div>
                                        </div>
                                    </div>
                                </div>

                                @error('crypto_addresses')
                                    <div class="alert alert-danger mt-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="card-section">
                                <h6 class="mb-3">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Crypto Bank Card Information <span class="badge bg-secondary ms-2">Optional</span>
                                </h6>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    This information is optional. You can add it now or later if you have a Crypto Bank
                                    Card.
                                </p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="etc_black_card_number" class="form-label">
                                                <i class="fas fa-credit-card me-1"></i>
                                                Crypto Bank Card Number
                                            </label>
                                            <input type="text"
                                                class="form-control @error('etc_black_card_number') is-invalid @enderror"
                                                id="etc_black_card_number" name="etc_black_card_number"
                                                value="{{ old('etc_black_card_number', auth()->user()->etc_black_card_number) }}"
                                                maxlength="19" placeholder="1234 5678 9012 3456">
                                            @error('etc_black_card_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Your Crypto Bank Card number (optional)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="account_number" class="form-label">
                                                <i class="fas fa-university me-1"></i>
                                                Account Number
                                            </label>
                                            <input type="text"
                                                class="form-control @error('account_number') is-invalid @enderror"
                                                id="account_number" name="account_number"
                                                value="{{ old('account_number', auth()->user()->account_number) }}"
                                                maxlength="20" placeholder="1234567890123456">
                                            @error('account_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Your bank account number (optional)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-alert alert">
                                <h6><i class="fas fa-info-circle me-2"></i>Important Notes</h6>
                                <ul class="mb-0">
                                    <li>National ID/Passport and Account Holder Name are required</li>
                                    <li>At least one crypto wallet address (USDT, USDC, or FDUSD) is required</li>
                                    <li>Crypto Bank Card and Account Number information is optional</li>
                                    <li>All information must be accurate to process withdrawals</li>
                                    <li>Payment information can be updated later if needed</li>
                                    <li>Your data is encrypted and stored securely</li>
                                </ul>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('user.profile') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Payment Information
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Format card number input
            const cardInput = document.getElementById('etc_black_card_number');
            if (cardInput) {
                cardInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                    e.target.value = formattedValue;
                });
            }

            // Uppercase input for National ID
            const nationalIdInput = document.getElementById('national_id');
            if (nationalIdInput) {
                nationalIdInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.toUpperCase();
                });
            }

            // Proper case for cardholder name
            const cardholderInput = document.getElementById('cardholder_name');
            if (cardholderInput) {
                cardholderInput.addEventListener('input', function(e) {
                    let value = e.target.value;
                    e.target.value = value.replace(/\w\S*/g, (txt) => {
                        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                    });
                });
            }

            // Form validation - ensure at least one crypto wallet is provided
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const usdtAddress = document.getElementById('usdt_bep20_address').value.trim();
                    const usdcAddress = document.getElementById('usdc_bep20_address').value.trim();
                    const fdusdAddress = document.getElementById('fdusd_bep20_address').value.trim();

                    if (!usdtAddress && !usdcAddress && !fdusdAddress) {
                        e.preventDefault();
                        alert('Please provide at least one crypto wallet address (USDT, USDC, or FDUSD).');

                        // Highlight the crypto section
                        const cryptoSection = document.querySelector('.crypto-section');
                        if (cryptoSection) {
                            cryptoSection.scrollIntoView({
                                behavior: 'smooth'
                            });
                            cryptoSection.style.borderColor = '#dc3545';
                            setTimeout(() => {
                                cryptoSection.style.borderColor = 'var(--bs-warning)';
                            }, 3000);
                        }
                        return false;
                    }
                });
            }
        });
    </script>

@endsection
