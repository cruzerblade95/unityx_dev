{{-- @include('components.coming-soon-overlay') --}}

@extends('user.layouts.layoutMaster')

@section('title', 'Crypto Bank Card Withdrawal')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/forms-pickers.js'])
@endsection

@section('content')
    @include('user.content.withdrawal.partials.styles')

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="withdrawal-page-header">
            <a href="{{ route('user.withdrawal.index') }}" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h4 class="py-3 mb-0">Crypto Bank Card Withdrawal</h4>
                <p class="text-muted mb-0">Withdraw directly to your Crypto Bank Card</p>
            </div>
        </div>

        <!-- Withdrawal Type Banner -->
        <div class="withdrawal-type-banner etc-banner">
            <div class="banner-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <h3 style="color: rgba(111, 66, 193, 1);" class="mb-2">Crypto Bank Card Withdrawal</h3>
            <p class="mb-0">Fast and convenient withdrawal to your Crypto Bank Card with 5.6% processing fee</p>
        </div>

        <!-- Wallet Balance -->
        @include('user.content.withdrawal.partials.wallet-balance')

        <!-- Pending Withdrawal Alert -->
        @if ($pendingWithdrawal)
            <div class="alert alert-warning alert-dismissible" role="alert">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Pending Withdrawal</h5>
                <p class="mb-2">You have a pending withdrawal request (#{{ $pendingWithdrawal->id }}) for
                    ${{ number_format($pendingWithdrawal->amount, 2) }}.</p>
                <p class="mb-0">Please wait for it to be processed before submitting a new request.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- KYC Verification Status -->
        @if (!$isKycApproved)
            <div class="alert alert-info alert-dismissible" role="alert">
                <h5 class="alert-heading"><i class="fas fa-shield-alt me-2"></i>KYC Verification Required</h5>
                <p class="mb-2">To ensure security and compliance, KYC verification is required for withdrawal requests.
                </p>
                <p class="mb-0">
                    <a href="{{ route('user.kyc.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-upload me-1"></i>Complete KYC Verification
                    </a>
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Withdrawal Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"
                                style="color: rgba(111, 66, 193, 1);"></i>Crypto Bank Card Withdrawal Form</h5>
                    </div>
                    <div class="card-body">
                        @if ($isKycApproved && !$pendingWithdrawal && $walletBalance > 0)
                            <form method="POST" action="{{ route('user.withdrawal.store') }}" id="etcWithdrawalForm">
                                @csrf
                                <input type="hidden" name="withdrawal_type" value="etc_black">

                                <!-- Wallet Type Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Withdraw From <span class="text-danger">*</span></label>
                                    <select class="form-select @error('wallet_type') is-invalid @enderror"
                                        name="wallet_type" id="wallet_type" required>
                                        <option value="">Select Wallet</option>
                                        <option value="profit" {{ old('wallet_type') == 'profit' ? 'selected' : '' }}>
                                            Profit Wallet (${{ number_format($user->wallet->profit ?? 0, 2) }})
                                        </option>
                                        <option value="bonus" {{ old('wallet_type') == 'bonus' ? 'selected' : '' }}>
                                            Bonus Wallet (${{ number_format($user->wallet->bonus ?? 0, 2) }})
                                        </option>
                                    </select>
                                    @error('wallet_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Choose which wallet to withdraw from</small>
                                </div>

                                <!-- Withdrawal Amount -->
                                <div class="mb-3">
                                    <label class="form-label">Withdrawal Amount (USD) <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                            name="amount" id="amount" step="0.01" min="100"
                                            max="{{ min(300, $walletBalance) }}" value="{{ old('amount') }}"
                                            placeholder="Enter amount" required>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted" id="amount-help">Minimum: $100 | Maximum:
                                        ${{ number_format(min(300, $walletBalance), 2) }} | Available:
                                        ${{ number_format($walletBalance, 2) }}</small>
                                </div>

                                <!-- Crypto Bank Card Information -->
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-credit-card me-2" style="color: rgba(111, 66, 193, 1);"></i>
                                            Your Crypto Bank Card Details
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <strong>Card Number:</strong><br>
                                                    <span class="font-monospace">
                                                        @if ($user->etc_black_card_number)
                                                            ****-****-****-{{ substr($user->etc_black_card_number, -4) }}
                                                        @else
                                                            <span class="text-danger">Not provided</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="mb-2">
                                                    <strong>Cardholder Name:</strong><br>
                                                    <span>{{ $user->cardholder_name ?? 'Not provided' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <strong>Account Number:</strong><br>
                                                    <span class="font-monospace">
                                                        @if ($user->account_number)
                                                            ****{{ substr($user->account_number, -4) }}
                                                        @else
                                                            <span class="text-danger">Not provided</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        @if (!$user->etc_black_card_number || !$user->cardholder_name || !$user->account_number)
                                            <div class="alert alert-warning mt-3 mb-0">
                                                <small>
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Your Crypto Bank Card information is incomplete. Please update your
                                                    payment information.
                                                    <a href="{{ route('user.payment-info.index') }}"
                                                        class="alert-link">Update Now</a>
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Reference -->
                                <div class="mb-3">
                                    <label class="form-label">Reference (Optional)</label>
                                    <input type="text" class="form-control" name="reference"
                                        value="{{ old('reference') }}" placeholder="Internal reference for your records">
                                    <small class="text-muted">For your personal tracking purposes</small>
                                </div>

                                <!-- Note -->
                                <div class="mb-4">
                                    <label class="form-label">Additional Notes (Optional)</label>
                                    <textarea class="form-control" name="note" rows="3" placeholder="Any additional information">{{ old('note') }}</textarea>
                                </div>

                                <!-- Fee Calculation -->
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title">Transaction Summary</h6>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="d-flex justify-content-between">
                                                    <span>Withdrawal Amount:</span>
                                                    <span id="displayAmount">$0.00</span>
                                                </div>
                                                <div class="d-flex justify-content-between text-muted">
                                                    <span>Processing Fee (5.6%):</span>
                                                    <span id="displayFee">$0.00</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="d-flex justify-content-between">
                                                    <strong>Net Amount:</strong>
                                                    <strong style="color: rgba(111, 66, 193, 1);"
                                                        id="displayNet">$0.00</strong>
                                                </div>
                                                <small class="text-muted">Amount credited to your card</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-lg"
                                        style="background-color: rgba(111, 66, 193, 1); color: white;"
                                        @if (!$user->etc_black_card_number || !$user->cardholder_name || !$user->account_number) disabled @endif>
                                        <i class="fas fa-credit-card me-2"></i>Submit Crypto Bank Card Withdrawal
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="text-center py-4">
                                @if (!$isKycApproved)
                                    <i class="fas fa-shield-alt text-warning fa-3x mb-3"></i>
                                    <h5>KYC Verification Required</h5>
                                    <p class="text-muted">Complete your KYC verification to access withdrawal features.</p>
                                @elseif($pendingWithdrawal)
                                    <i class="fas fa-clock text-warning fa-3x mb-3"></i>
                                    <h5>Pending Withdrawal</h5>
                                    <p class="text-muted">You have a pending withdrawal request. Please wait for it to be
                                        processed.</p>
                                @else
                                    <i class="fas fa-wallet text-muted fa-3x mb-3"></i>
                                    <h5>Insufficient Balance</h5>
                                    <p class="text-muted">You don't have sufficient withdrawable balance.</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Information Sidebar -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Crypto Bank Card Info</h5>
                    </div>
                    <div class="card-body">
                        <h6 style="color: rgba(111, 66, 193, 1);">Benefits</h6>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="fas fa-check text-success me-2"></i>Direct card credit</li>
                            <li><i class="fas fa-check text-success me-2"></i>Fast processing (1-2 business days)</li>
                            <li><i class="fas fa-check text-success me-2"></i>Immediate card availability</li>
                            <li><i class="fas fa-check text-success me-2"></i>No wallet address required</li>
                        </ul>

                        <h6 class="text-primary">Processing Details</h6>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="fas fa-percentage text-warning me-2"></i>5.6% processing fee</li>
                            <li><i class="fas fa-clock text-info me-2"></i>1-2 business days processing</li>
                            <li><i class="fas fa-shield-alt text-success me-2"></i>Secure bank transfer</li>
                        </ul>

                        <h6 class="text-warning">Requirements</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check-circle text-success me-2"></i>Valid Crypto Bank Card</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Complete payment information</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>KYC verification</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Minimum withdrawal: $10</li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-headset me-2"></i>Need Help?</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Contact support for Crypto Bank Card withdrawal assistance</p>
                        <div class="d-grid gap-2">
                            <a href="mailto:support@unityx.com" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-envelope me-1"></i>Email Support
                            </a>
                            <a href="#" class="btn btn-outline-success btn-sm">
                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdrawal History -->
        <div class="mt-5">
            @include('user.content.withdrawal.partials.withdrawal-history')
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount');
            const displayAmount = document.getElementById('displayAmount');
            const displayFee = document.getElementById('displayFee');
            const displayNet = document.getElementById('displayNet');
            const walletTypeSelect = document.getElementById('wallet_type');
            const amountHelp = document.getElementById('amount-help');

            // Wallet balances from server
            const walletBalances = {
                'profit': {{ $user->wallet->profit ?? 0 }},
                'bonus': {{ $user->wallet->bonus ?? 0 }}
            };

            // Update available balance and max amount when wallet type changes
            if (walletTypeSelect && amountInput && amountHelp) {
                walletTypeSelect.addEventListener('change', function() {
                    const walletType = this.value;
                    if (walletType && walletBalances[walletType] !== undefined) {
                        const availableBalance = walletBalances[walletType];
                        const minAmount = 100;
                        const maxAmount = Math.min(300, availableBalance);

                        // Update input max attribute
                        amountInput.setAttribute('max', maxAmount);

                        // Update help text
                        amountHelp.innerHTML =
                            `Minimum: $${minAmount.toFixed(2)} | Maximum: $${maxAmount.toFixed(2)} | Available: $${availableBalance.toFixed(2)}`;

                        // Clear amount if it exceeds new max
                        if (parseFloat(amountInput.value) > maxAmount) {
                            amountInput.value = '';
                            displayAmount.textContent = '$0.00';
                            displayFee.textContent = '$0.00';
                            displayNet.textContent = '$0.00';
                        }
                    }
                });
            }

            // Calculate fees on amount change
            if (amountInput) {
                amountInput.addEventListener('input', function() {
                    const amount = parseFloat(this.value) || 0;
                    const fee = amount * 0.056; // 5.6%
                    const net = amount - fee;

                    displayAmount.textContent = '$' + amount.toFixed(2);
                    displayFee.textContent = '$' + fee.toFixed(2);
                    displayNet.textContent = '$' + net.toFixed(2);
                });
            }

            // Form validation
            const form = document.getElementById('etcWithdrawalForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const amount = parseFloat(amountInput.value);
                    const walletType = walletTypeSelect.value;
                    const availableBalance = walletBalances[walletType] || 0;
                    const minAmount = 100;
                    const maxAmount = Math.min(300, availableBalance);

                    if (!walletType) {
                        e.preventDefault();
                        alert('Please select a wallet to withdraw from.');
                        return false;
                    }

                    if (amount > maxAmount) {
                        e.preventDefault();
                        alert(
                            `Maximum withdrawal amount is $${maxAmount.toFixed(2)} for the selected wallet.`
                        );
                        return false;
                    }

                    if (amount < minAmount) {
                        e.preventDefault();
                        alert(`Minimum withdrawal amount is $${minAmount.toFixed(2)}.`);
                        return false;
                    }

                    if (amount > availableBalance) {
                        e.preventDefault();
                        alert(
                            `Insufficient balance in selected wallet. Available: $${availableBalance.toFixed(2)}.`
                        );
                        return false;
                    }
                });
            }
        });
    </script>
@endsection
