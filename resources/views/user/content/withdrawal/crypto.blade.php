{{-- @include('components.coming-soon-overlay') --}}
@extends('user.layouts.layoutMaster')

@section('title', 'Cryptocurrency Withdrawal')

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
                <h4 class="py-3 mb-0">Cryptocurrency Withdrawal</h4>
                <p class="text-muted mb-0">Withdraw to your crypto wallet via BEP20 network</p>
            </div>
        </div>

        <!-- Withdrawal Type Banner -->
        <div class="withdrawal-type-banner crypto-banner">
            <div class="banner-icon">
                <i class="fab fa-bitcoin"></i>
            </div>
            <h3 class="text-success mb-2">Cryptocurrency Withdrawal</h3>
            <p class="mb-0">Secure and fast withdrawal to your crypto wallet with 4.75% processing fee</p>
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
                <p class="mb-2">You must wait 30 hours between withdrawal requests for security purposes.</p>
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
                        <span class="countdown-number" id="minutes">{{ floor(($hoursRemaining - floor($hoursRemaining)) * 60) }}</span>
                        <span class="countdown-label">Minutes</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="seconds">00</span>
                        <span class="countdown-label">Seconds</span>
                    </div>
                </div>
                <div class="progress mt-3">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ (30 - $hoursRemaining) / 30 * 100 }}%"></div>
                </div>
                <p class="mb-0 mt-2"><small class="text-muted">This countdown will automatically refresh when the cooldown period ends.</small></p>
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

                @media (max-width: 768px) {
                    .countdown-display {
                        gap: 10px;
                    }

                    .countdown-item {
                        padding: 10px 15px;
                        min-width: 60px;
                    }

                    .countdown-number {
                        font-size: 1.5rem;
                    }
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
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
                        const totalHours = 30;
                        const remainingHours = difference / (1000 * 60 * 60);
                        const progressPercentage = ((totalHours - remainingHours) / totalHours) * 100;
                        document.querySelector('.progress-bar').style.width = progressPercentage + '%';
                    }

                    // Update countdown every second
                    updateCountdown();
                    const countdownInterval = setInterval(updateCountdown, 1000);
                });
            </script>
        @endif

        <div class="row">
            <!-- Withdrawal Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fab fa-bitcoin me-2 text-success"></i>Crypto Withdrawal Form</h5>
                    </div>
                    <div class="card-body">
                        @if ($isKycApproved && !$pendingWithdrawal && $walletBalance > 0 && $canWithdraw)
                            <form method="POST" action="{{ route('user.withdrawal.store') }}" id="cryptoWithdrawalForm">
                                @csrf
                                <input type="hidden" name="withdrawal_type" value="crypto">

                                <div class="row">
                                    <!-- Currency Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Cryptocurrency <span class="text-danger">*</span></label>
                                        <select class="form-control @error('currency') is-invalid @enderror" name="currency"
                                            id="currency" required>
                                            <option value="">Select cryptocurrency...</option>
                                            <option value="USDT" {{ old('currency') == 'USDT' ? 'selected' : '' }}>USDT
                                                (Tether) - BEP20</option>
                                            <option value="USDC" {{ old('currency') == 'USDC' ? 'selected' : '' }}>USDC
                                                (USD Coin) - BEP20</option>
                                            <option value="FDUSD" {{ old('currency') == 'FDUSD' ? 'selected' : '' }}>FDUSD
                                                (First Digital USD) - BEP20</option>
                                        </select>
                                        @error('currency')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">All transactions use BEP20 (BSC) network</small>
                                    </div>

                                    <!-- Withdrawal Amount -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Withdrawal Amount (USD) <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                                name="amount" id="amount" step="0.01"
                                                min="{{ $withdrawalSettings->min_amount ?? 50 }}"
                                                max="{{ min($withdrawalSettings->max_amount ?? 300, $walletBalance) }}"
                                                value="{{ old('amount') }}"
                                                placeholder="Enter amount" required>
                                        </div>
                                        @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Minimum: ${{ number_format($withdrawalSettings->min_amount ?? 50, 2) }} | Maximum:
                                            ${{ number_format(min($withdrawalSettings->max_amount ?? 300, $walletBalance), 2) }} | Available:
                                            ${{ number_format($walletBalance, 2) }}</small>
                                    </div>
                                </div>

                                <!-- Wallet Address -->
                                <div class="mb-3">
                                    <label class="form-label">Wallet Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control @error('wallet_address') is-invalid @enderror"
                                            name="wallet_address" id="wallet_address" value="{{ old('wallet_address') }}"
                                            placeholder="Enter your BEP20 wallet address" required>
                                        <button type="button" class="btn btn-outline-secondary" id="pasteAddress">
                                            <i class="fas fa-paste"></i>
                                        </button>
                                    </div>
                                    @error('wallet_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Ensure this is a valid BEP20 address. Incorrect addresses may result in permanent
                                        loss of funds.
                                    </small>
                                </div>

                                <!-- Saved Addresses -->
                                @if ($user->usdt_bep20_address || $user->usdc_bep20_address || $user->fdusd_bep20_address)
                                    <div class="mb-3">
                                        <label class="form-label">Or use saved addresses:</label>
                                        <div class="row">
                                            @if ($user->usdt_bep20_address)
                                                <div class="col-md-4 mb-2">
                                                    <button type="button"
                                                        class="btn btn-outline-success btn-sm w-100 saved-address"
                                                        data-address="{{ $user->usdt_bep20_address }}"
                                                        data-currency="USDT">
                                                        <i class="fab fa-bitcoin me-1"></i>USDT Address
                                                    </button>
                                                </div>
                                            @endif
                                            @if ($user->usdc_bep20_address)
                                                <div class="col-md-4 mb-2">
                                                    <button type="button"
                                                        class="btn btn-outline-primary btn-sm w-100 saved-address"
                                                        data-address="{{ $user->usdc_bep20_address }}"
                                                        data-currency="USDC">
                                                        <i class="fab fa-ethereum me-1"></i>USDC Address
                                                    </button>
                                                </div>
                                            @endif
                                            @if ($user->fdusd_bep20_address)
                                                <div class="col-md-4 mb-2">
                                                    <button type="button"
                                                        class="btn btn-outline-warning btn-sm w-100 saved-address"
                                                        data-address="{{ $user->fdusd_bep20_address }}"
                                                        data-currency="FDUSD">
                                                        <i class="fas fa-dollar-sign me-1"></i>FDUSD Address
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

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
                                                    <span>Processing Fee (4.75%):</span>
                                                    <span id="displayFee">$0.00</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="d-flex justify-content-between">
                                                    <strong>Net Amount:</strong>
                                                    <strong class="text-success" id="displayNet">$0.00</strong>
                                                </div>
                                                <small class="text-muted">Amount you'll receive</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fab fa-bitcoin me-2"></i>Submit Crypto Withdrawal
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
                                @elseif(!$canWithdraw)
                                    <i class="fas fa-hourglass-half text-warning fa-3x mb-3"></i>
                                    <h5>Withdrawal Cooldown Active</h5>
                                    <p class="text-muted">Please wait for the cooldown period to end before making another withdrawal request.</p>
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
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Crypto Withdrawal Info</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-success">Benefits</h6>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="fas fa-check text-success me-2"></i>Lowest processing fee (4.75%)</li>
                            <li><i class="fas fa-check text-success me-2"></i>Fast processing (usually within hours)</li>
                            <li><i class="fas fa-check text-success me-2"></i>Global accessibility</li>
                            <li><i class="fas fa-check text-success me-2"></i>Secure blockchain transactions</li>
                        </ul>

                        <h6 class="text-primary">Supported Networks</h6>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="fas fa-link text-warning me-2"></i>BEP20 (Binance Smart Chain)</li>
                            <li><i class="fas fa-bolt text-info me-2"></i>Low transaction fees</li>
                            <li><i class="fas fa-clock text-success me-2"></i>Fast confirmation times</li>
                        </ul>

                        <h6 class="text-warning">Important Notes</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Only BEP20 network supported
                            </li>
                            <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Double-check wallet addresses
                            </li>
                            <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Incorrect addresses =
                                permanent loss</li>
                            <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>Minimum withdrawal: ${{ number_format($withdrawalSettings->min_amount ?? 50, 2) }}</li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-headset me-2"></i>Need Help?</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Contact support for crypto withdrawal assistance</p>
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
            const pasteButton = document.getElementById('pasteAddress');
            const walletAddressInput = document.getElementById('wallet_address');
            const savedAddressButtons = document.querySelectorAll('.saved-address');
            const currencySelect = document.getElementById('currency');

            // Calculate fees on amount change
            if (amountInput) {
                amountInput.addEventListener('input', function() {
                    const amount = parseFloat(this.value) || 0;
                    const fee = amount * 0.0475; // 4.75%
                    const net = amount - fee;

                    displayAmount.textContent = '$' + amount.toFixed(2);
                    displayFee.textContent = '$' + fee.toFixed(2);
                    displayNet.textContent = '$' + net.toFixed(2);
                });
            }

            // Paste functionality
            if (pasteButton && walletAddressInput) {
                pasteButton.addEventListener('click', async function() {
                    try {
                        const text = await navigator.clipboard.readText();
                        walletAddressInput.value = text;
                    } catch (err) {
                        console.error('Failed to read clipboard:', err);
                    }
                });
            }

            // Saved address buttons
            savedAddressButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const address = this.dataset.address;
                    const currency = this.dataset.currency;

                    walletAddressInput.value = address;
                    currencySelect.value = currency;

                    // Highlight the selected button temporarily
                    this.classList.add('btn-success');
                    this.classList.remove('btn-outline-success', 'btn-outline-primary',
                        'btn-outline-warning');

                    setTimeout(() => {
                        this.classList.remove('btn-success');
                        if (currency === 'USDT') {
                            this.classList.add('btn-outline-success');
                        } else if (currency === 'USDC') {
                            this.classList.add('btn-outline-primary');
                        } else {
                            this.classList.add('btn-outline-warning');
                        }
                    }, 1000);
                });
            });

            // Form validation
            const form = document.getElementById('cryptoWithdrawalForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const amount = parseFloat(amountInput.value);
                    const walletBalance = {{ $walletBalance }};
                    const minAmount = {{ $withdrawalSettings->min_amount ?? 50 }};
                    const maxAmount = Math.min({{ $withdrawalSettings->max_amount ?? 300 }}, walletBalance);

                    if (amount > maxAmount) {
                        e.preventDefault();
                        alert(`Maximum withdrawal amount is $${maxAmount.toFixed(2)}.`);
                        return false;
                    }

                    if (amount < minAmount) {
                        e.preventDefault();
                        alert(`Minimum withdrawal amount is $${minAmount.toFixed(2)}.`);
                        return false;
                    }
                });
            }
        });
    </script>
@endsection
