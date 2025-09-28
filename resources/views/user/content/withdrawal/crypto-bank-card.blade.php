{{-- @include('components.coming-soon-overlay') --}}

@extends('user.layouts.layoutMaster')

@section('title', 'Crypto Bank Card Withdrawal')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/notyf/notyf.scss'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/notyf/notyf.js'])
@endsection

@section('page-script')
    {{-- forms-pickers.js removed as it's not needed for crypto bank card withdrawal and causes flatpickr errors --}}
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

        <!-- Withdrawal Cooldown Alert -->
        @if (!$canWithdraw && $hoursRemaining > 0)
            <div class="alert alert-warning" role="alert" id="withdrawalCooldownAlert">
                <h5 class="alert-heading"><i class="fas fa-clock me-2"></i>Withdrawal Cooldown Period</h5>
                <p class="mb-2">You must wait {{ $cooldownHours }} hours between withdrawal requests for security
                    purposes.</p>
                @if ($lastWithdrawal)
                    <p class="mb-2">
                        <strong>Last withdrawal:</strong> {{ $lastWithdrawal->created_at->format('M d, Y H:i:s') }}
                        <br>
                        <strong>Next withdrawal available:</strong> {{ $nextWithdrawalTime->format('M d, Y H:i:s') }}
                    </p>
                @endif
                <div class="countdown-display">
                    <div class="countdown-item">
                        <span class="countdown-number" id="hours">{{ floor($hoursRemaining) }}</span>
                        <span class="countdown-label">Hours</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number"
                            id="minutes">{{ floor(($hoursRemaining - floor($hoursRemaining)) * 60) }}</span>
                        <span class="countdown-label">Minutes</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="seconds">00</span>
                        <span class="countdown-label">Seconds</span>
                    </div>
                </div>
                <div class="progress mt-3">
                    <div class="progress-bar bg-warning" role="progressbar"
                        style="width: {{ (($cooldownHours - $hoursRemaining) / $cooldownHours) * 100 }}%"></div>
                </div>
                <p class="mb-0 mt-2"><small class="text-muted">This countdown will automatically refresh when the cooldown
                        period ends.</small></p>
            </div>

            <style>
                .countdown-display {
                    display: flex;
                    gap: 20px;
                    margin: 15px 0;
                    justify-content: center;
                }

                .countdown-item {
                    text-align: center;
                    background: rgba(255, 255, 255, 0.1);
                    padding: 15px 20px;
                    border-radius: 8px;
                    min-width: 80px;
                }

                .countdown-number {
                    display: block;
                    font-size: 2rem;
                    font-weight: bold;
                    color: #fff;
                }

                .countdown-label {
                    display: block;
                    font-size: 0.8rem;
                    text-transform: uppercase;
                    color: #fff;
                    margin-top: 5px;
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    @if ($nextWithdrawalTime)
                        const nextWithdrawalTime = new Date('{{ $nextWithdrawalTime->toISOString() }}');

                        function updateCountdown() {
                            const now = new Date();
                            const difference = nextWithdrawalTime - now;

                            if (difference <= 0) {
                                // Countdown finished, reload the page
                                location.reload();
                                return;
                            }

                            const hours = Math.floor(difference / (1000 * 60 * 60));
                            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((difference % (1000 * 60)) / 1000);

                            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');

                            // Update progress bar
                            const totalHours = {{ $cooldownHours }};
                            const remainingHours = difference / (1000 * 60 * 60);
                            const progressPercentage = ((totalHours - remainingHours) / totalHours) * 100;
                            document.querySelector('.progress-bar').style.width = progressPercentage + '%';
                        }

                        // Update countdown every second
                        updateCountdown();
                        const countdownInterval = setInterval(updateCountdown, 1000);
                    @endif
                });
            </script>
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
                        @if ($isKycApproved && !$pendingWithdrawal && $walletBalance > 0 && $canWithdraw)
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
                                            name="amount" id="amount" step="0.01"
                                            min="{{ $withdrawalSettings->min_amount ?? 100 }}"
                                            max="{{ min($withdrawalSettings->max_amount ?? 300, $walletBalance) }}"
                                            value="{{ old('amount') }}" placeholder="Enter amount" required>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted" id="amount-help">Minimum:
                                        ${{ number_format($withdrawalSettings->min_amount ?? 100, 2) }} | Maximum:
                                        ${{ number_format(min($withdrawalSettings->max_amount ?? 300, $walletBalance), 2) }}
                                        | Available:
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
                                                <div class="mb-2">
                                                    <strong>Card Wallet Address (USDT BEP20):</strong><br>
                                                    <span class="font-monospace small">
                                                        @if ($user->card_wallet_address)
                                                            {{ substr($user->card_wallet_address, 0, 6) }}...{{ substr($user->card_wallet_address, -6) }}
                                                        @else
                                                            <span class="text-danger">Not provided</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        @if (!$user->etc_black_card_number || !$user->cardholder_name || !$user->account_number || !$user->card_wallet_address)
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
                                    <button type="submit" class="btn btn-lg" id="submitWithdrawalBtn"
                                        style="background-color: rgba(111, 66, 193, 1); color: white;"
                                        @if (!$user->etc_black_card_number || !$user->cardholder_name || !$user->account_number || !$user->card_wallet_address) disabled @endif>
                                        <i class="fas fa-credit-card me-2"></i>Submit Crypto Bank Card Withdrawal
                                    </button>
                                </div>
                            </form>

                            <!-- OTP Modal -->
                            <div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="otpModalLabel">
                                                <i class="fab fa-telegram me-2"></i>Telegram OTP Verification
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="text-center mb-4">
                                                <i class="fab fa-telegram text-primary fa-3x mb-3"></i>
                                                <p class="mb-0">We've sent a 6-digit OTP code to your Telegram account.
                                                </p>
                                                <p class="text-muted small">Please enter the code below to complete your
                                                    withdrawal.</p>
                                            </div>

                                            <form id="otpVerificationForm">
                                                <div class="mb-3">
                                                    <label for="otpCode" class="form-label">OTP Code</label>
                                                    <input type="text" class="form-control text-center" id="otpCode"
                                                        name="otp" maxlength="6" placeholder="000000"
                                                        style="font-size: 1.5rem; letter-spacing: 0.5rem;">
                                                    <div class="invalid-feedback" id="otpError"></div>
                                                </div>

                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-primary" id="verifyOtpBtn">
                                                        <i class="fas fa-check me-2"></i>Verify & Complete Withdrawal
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        id="resendOtpBtn">
                                                        <i class="fas fa-redo me-2"></i><span id="resendBtnText">Resend
                                                            OTP</span>
                                                        <span id="resendCountdown" class="d-none">(<span
                                                                id="countdownTimer">60</span>s)</span>
                                                    </button>
                                                    <small class="text-muted text-center" id="otpExpiryInfo">
                                                        OTP expires in <span id="otpExpiryTimer">5:00</span> minutes
                                                    </small>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                @elseif(!$canWithdraw)
                                    <i class="fas fa-clock text-warning fa-3x mb-3"></i>
                                    <h5>Withdrawal Cooldown Active</h5>
                                    <p class="text-muted">Please wait for the cooldown period to end before making another
                                        withdrawal request.</p>
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
                            <li><i class="fas fa-check text-success me-2"></i>Direct Crypto Card Bank</li>
                            <li><i class="fas fa-check text-success me-2"></i>Fast processing (1-3 business days)</li>
                            <li><i class="fas fa-check text-success me-2"></i>Immediate card availability</li>
                            {{-- <li><i class="fas fa-check text-success me-2"></i>No wallet address required</li> --}}
                        </ul>

                        <h6 class="text-primary">Processing Details</h6>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="fas fa-percentage text-warning me-2"></i>5.6% processing fee</li>
                            <li><i class="fas fa-clock text-info me-2"></i>1-3 business days processing</li>
                            <li><i class="fas fa-shield-alt text-success me-2"></i>Secure bank transfer</li>
                        </ul>

                        <h6 class="text-warning">Requirements</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check-circle text-success me-2"></i>Valid Crypto Bank Card</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Complete payment information</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>KYC verification</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Minimum withdrawal:
                                ${{ number_format($withdrawalSettings->min_amount ?? 100, 2) }}</li>
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
                            <a href="mailto:support@unityx.pro" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-envelope me-1"></i>Email Support
                            </a>
                            <a href="https://t.me/unityxpro" class="btn btn-outline-success btn-sm">
                                <i class="fab fa-telegram me-1"></i>Telegram Support
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
                        const minAmount = {{ $withdrawalSettings->min_amount ?? 100 }};
                        const maxAmount = Math.min({{ $withdrawalSettings->max_amount ?? 300 }},
                            availableBalance);

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

            // Form validation and OTP handling
            const form = document.getElementById('etcWithdrawalForm');
            const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
            let resendCountdownTimer = null;
            let otpExpiryTimer = null;
            let resendCountdownActive = false;

            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault(); // Always prevent default submission

                    const amount = parseFloat(amountInput.value);
                    const walletType = walletTypeSelect.value;
                    const availableBalance = walletBalances[walletType] || 0;
                    const minAmount = {{ $withdrawalSettings->min_amount ?? 100 }};
                    const maxAmount = Math.min({{ $withdrawalSettings->max_amount ?? 300 }},
                        availableBalance);

                    if (!walletType) {
                        Swal.fire('Error', 'Please select a wallet to withdraw from.', 'error');
                        return false;
                    }

                    if (amount > maxAmount) {
                        Swal.fire('Error',
                            `Maximum withdrawal amount is $${maxAmount.toFixed(2)} for the selected wallet.`,
                            'error');
                        return false;
                    }

                    if (amount < minAmount) {
                        Swal.fire('Error', `Minimum withdrawal amount is $${minAmount.toFixed(2)}.`,
                            'error');
                        return false;
                    }

                    if (amount > availableBalance) {
                        Swal.fire('Error',
                            `Insufficient balance in selected wallet. Available: $${availableBalance.toFixed(2)}.`,
                            'error');
                        return false;
                    }

                    // All validations passed, send OTP
                    sendOtp();
                });
            }

            // Countdown functions
            function startResendCountdown(seconds = 60) {
                resendCountdownActive = true;
                const resendBtn = document.getElementById('resendOtpBtn');
                const resendText = document.getElementById('resendBtnText');
                const countdown = document.getElementById('resendCountdown');
                const timer = document.getElementById('countdownTimer');

                resendBtn.disabled = true;
                resendBtn.classList.remove('btn-outline-secondary');
                resendBtn.classList.add('btn-secondary');
                resendText.textContent = 'Resend OTP';
                countdown.classList.remove('d-none');

                let timeLeft = seconds;
                timer.textContent = timeLeft;

                resendCountdownTimer = setInterval(() => {
                    timeLeft--;
                    timer.textContent = timeLeft;

                    if (timeLeft <= 0) {
                        clearInterval(resendCountdownTimer);
                        resendCountdownActive = false;
                        resendBtn.disabled = false;
                        resendBtn.classList.remove('btn-secondary');
                        resendBtn.classList.add('btn-outline-secondary');
                        countdown.classList.add('d-none');
                    }
                }, 1000);
            }

            function startOtpExpiryCountdown(expiresAt) {
                const otpExpiryElement = document.getElementById('otpExpiryTimer');
                const expirationTime = new Date(expiresAt);

                function updateDisplay() {
                    const now = new Date();
                    const timeLeft = Math.max(0, Math.floor((expirationTime - now) / 1000));

                    if (timeLeft <= 0) {
                        otpExpiryElement.textContent = 'Expired';
                        clearInterval(otpExpiryTimer);

                        // Disable verify button and show expired message
                        const verifyBtn = document.getElementById('verifyOtpBtn');
                        verifyBtn.disabled = true;
                        verifyBtn.innerHTML = '<i class="fas fa-times me-2"></i>OTP Expired';
                        verifyBtn.classList.remove('btn-primary');
                        verifyBtn.classList.add('btn-danger');

                        // Show expiry message
                        Swal.fire({
                            title: 'OTP Expired',
                            text: 'Your OTP has expired. Please request a new one.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            otpModal.hide();
                        });
                        return;
                    }

                    const mins = Math.floor(timeLeft / 60);
                    const secs = timeLeft % 60;
                    otpExpiryElement.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
                }

                updateDisplay();

                otpExpiryTimer = setInterval(updateDisplay, 1000);
            }

            function clearAllTimers() {
                if (resendCountdownTimer) {
                    clearInterval(resendCountdownTimer);
                    resendCountdownTimer = null;
                }
                if (otpExpiryTimer) {
                    clearInterval(otpExpiryTimer);
                    otpExpiryTimer = null;
                }
                resendCountdownActive = false;
            }

            function sendOtp() {
                const submitBtn = document.getElementById('submitWithdrawalBtn');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending OTP...';

                // Get form data
                const formData = new FormData(form);

                fetch('/user/withdrawal/send-otp', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;

                        if (data.success) {
                            // Clear any existing timers
                            clearAllTimers();

                            // Show OTP modal
                            otpModal.show();
                            document.getElementById('otpCode').focus();

                            // Start countdown timers
                            startResendCountdown(60); // 60 seconds before resend is allowed
                            startOtpExpiryCountdown(data.expires_at); // Use actual expiration time
                        } else {
                            // Handle "OTP already sent" case by showing modal again
                            if (data.message && data.message.includes('An OTP has already been sent')) {
                                // Clear any existing timers
                                clearAllTimers();

                                // Show OTP modal instead of error
                                otpModal.show();
                                document.getElementById('otpCode').focus();

                                // Start countdown timers for existing OTP
                                startResendCountdown(60);
                                startOtpExpiryCountdown(data.expires_at); // Use actual expiration time

                                // Show info toast that OTP is still valid
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'info',
                                    title: 'Previous OTP is still valid',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } else if (data.error === 'telegram_not_verified') {
                                Swal.fire({
                                    title: 'Telegram Not Verified',
                                    text: data.message,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Go to Profile',
                                    cancelButtonText: 'Cancel'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '/user/profile';
                                    }
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        }
                    })
                    .catch(error => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        console.error('Error:', error);
                        Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                    });
            }

            function resendOtp() {
                const resendBtn = document.getElementById('resendOtpBtn');
                const originalText = resendBtn.innerHTML;

                resendBtn.disabled = true;
                resendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

                // Get form data
                const formData = new FormData(form);

                fetch('/user/withdrawal/send-otp', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        resendBtn.disabled = false;
                        resendBtn.innerHTML = originalText;

                        if (data.success) {
                            // Clear any existing timers
                            clearAllTimers();

                            // Reset OTP input
                            document.getElementById('otpCode').value = '';
                            document.getElementById('otpCode').classList.remove('is-invalid');
                            document.getElementById('otpError').textContent = '';

                            // Start countdown timers again
                            startResendCountdown(60);
                            startOtpExpiryCountdown(data.expires_at); // Use actual expiration time

                            // Show success toast
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'New OTP sent to your Telegram',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            // Handle "OTP already sent" case silently for better UX
                            if (data.message && data.message.includes('An OTP has already been sent')) {
                                // Show a friendly message instead of error
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'info',
                                    title: 'Please wait before requesting a new OTP',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                // Reset timers since existing OTP is still valid
                                clearAllTimers();
                                startResendCountdown(60);
                                startOtpExpiryCountdown(data.expires_at); // Use actual expiration time
                            } else if (data.error === 'telegram_not_verified') {
                                Swal.fire({
                                    title: 'Telegram Not Verified',
                                    text: data.message,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Go to Profile',
                                    cancelButtonText: 'Cancel'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = '/user/profile';
                                    }
                                });
                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: data.message || 'Failed to send OTP',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        }
                    })
                    .catch(error => {
                        resendBtn.disabled = false;
                        resendBtn.innerHTML = originalText;
                        console.error('Error:', error);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'Something went wrong. Please try again.',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    });
            }

            // OTP verification
            document.getElementById('otpVerificationForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const otpCode = document.getElementById('otpCode').value;
                const verifyBtn = document.getElementById('verifyOtpBtn');
                const originalText = verifyBtn.innerHTML;

                if (otpCode.length !== 6) {
                    document.getElementById('otpCode').classList.add('is-invalid');
                    document.getElementById('otpError').textContent = 'Please enter a 6-digit OTP code.';
                    return;
                }

                verifyBtn.disabled = true;
                verifyBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';

                fetch('/user/withdrawal/verify-otp', {
                        method: 'POST',
                        body: JSON.stringify({
                            otp: otpCode
                        }),
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        verifyBtn.disabled = false;
                        verifyBtn.innerHTML = originalText;

                        if (data.success) {
                            otpModal.hide();
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            document.getElementById('otpCode').classList.add('is-invalid');
                            document.getElementById('otpError').textContent = data.message;
                        }
                    })
                    .catch(error => {
                        verifyBtn.disabled = false;
                        verifyBtn.innerHTML = originalText;
                        console.error('Error:', error);
                        Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                    });
            });

            // Resend OTP
            document.getElementById('resendOtpBtn').addEventListener('click', function() {
                if (resendCountdownActive) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Please wait for the countdown to finish',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    return;
                }

                // Call resendOtp function without closing modal
                resendOtp();
            });

            // Clear OTP error on input
            document.getElementById('otpCode').addEventListener('input', function() {
                this.classList.remove('is-invalid');
                document.getElementById('otpError').textContent = '';
            });

            // Clean up timers when modal is hidden
            document.getElementById('otpModal').addEventListener('hidden.bs.modal', function() {
                clearAllTimers();

                // Reset modal state
                const verifyBtn = document.getElementById('verifyOtpBtn');
                const resendBtn = document.getElementById('resendOtpBtn');
                const otpCode = document.getElementById('otpCode');
                const resendText = document.getElementById('resendBtnText');
                const countdown = document.getElementById('resendCountdown');

                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="fas fa-check me-2"></i>Verify & Complete Withdrawal';
                verifyBtn.classList.remove('btn-danger');
                verifyBtn.classList.add('btn-primary');

                resendBtn.disabled = false;
                resendBtn.classList.remove('btn-secondary');
                resendBtn.classList.add('btn-outline-secondary');
                resendText.textContent = 'Resend OTP';
                countdown.classList.add('d-none');

                otpCode.value = '';
                otpCode.classList.remove('is-invalid');
                document.getElementById('otpError').textContent = '';
            });
        });
    </script>
@endsection
