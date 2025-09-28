{{-- @include('components.coming-soon-overlay') --}}

@extends('user.layouts.layoutMaster')

@section('title', 'Gold Redemption Withdrawal')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/notyf/notyf.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/notyf/notyf.js'])
@endsection

@section('page-script')
    {{-- forms-pickers.js removed as it's not needed and causes flatpickr errors --}}
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
                <h4 class="py-3 mb-0">Gold Redemption Withdrawal</h4>
                <p class="text-muted mb-0">Redeem your profits for physical gold at Musleem Gold branches</p>
            </div>
        </div>

        <!-- Withdrawal Type Banner -->
        <div class="withdrawal-type-banner gold-banner">
            <div class="banner-icon">
                <i class="fas fa-coins"></i>
            </div>
            <h3 class="text-warning mb-2">Gold Redemption</h3>
            <p class="mb-0">Convert your digital profits into physical gold with 7.0% processing fee</p>
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
                        <h5 class="mb-0"><i class="fas fa-coins me-2 text-warning"></i>Gold Redemption Form</h5>
                    </div>
                    <div class="card-body">
                        @if ($isKycApproved && !$pendingWithdrawal && $walletBalance > 0 && $canWithdraw)
                            <form method="POST" action="{{ route('user.withdrawal.store') }}" id="goldWithdrawalForm">
                                @csrf
                                <input type="hidden" name="withdrawal_type" value="gold">

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
                                    <label class="form-label">Redemption Amount (USD) <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                            name="amount" id="amount" step="0.01"
                                            min="{{ $withdrawalSettings->min_amount ?? 300 }}"
                                            max="{{ min($withdrawalSettings->max_amount ?? 3000, $walletBalance) }}"
                                            value="{{ old('amount') }}" placeholder="Enter amount" required>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted" id="amount-help">Minimum:
                                        ${{ number_format($withdrawalSettings->min_amount ?? 300, 2) }} | Maximum:
                                        ${{ number_format(min($withdrawalSettings->max_amount ?? 3000, $walletBalance), 2) }}
                                        | Available:
                                        ${{ number_format($walletBalance, 2) }}</small>
                                </div>

                                <!-- Gold Information -->
                                <div class="card border-warning mb-4">
                                    <div class="card-body bg-light">
                                        <h6 class="card-title text-dark">
                                            <i class="fas fa-coins me-2 text-warning"></i>
                                            Gold Redemption Details
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <strong class="text-dark">Pickup Location:</strong><br>
                                                    <span class="text-primary fw-bold">Musleem Gold Branches</span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <strong class="text-dark">Processing Time:</strong><br>
                                                    <span class="text-info fw-bold">3-5 business days</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- National ID Information -->
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-id-card me-2 text-primary"></i>
                                            Your Identity Information
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-2">
                                                    <strong>National ID:</strong><br>
                                                    <span class="font-monospace">
                                                        @if ($user->national_id)
                                                            {{ $user->national_id }}
                                                        @else
                                                            <span class="text-danger">Not provided</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        @if (!$user->national_id)
                                            <div class="alert alert-warning mt-3 mb-0">
                                                <small>
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Your National ID is required for gold redemption. Please update your
                                                    payment information.
                                                    <a href="{{ route('user.payment-info.index') }}"
                                                        class="alert-link">Update Now</a>
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Branch Assignment Information -->
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-map-marker-alt me-2 text-info"></i>
                                            Branch Assignment Process
                                        </h6>
                                        <p class="mb-2">After your redemption request is approved, our admin will assign
                                            the most suitable Musleem Gold branch for your pickup based on your location and
                                            branch availability.</p>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="small text-primary">Available Branches:</h6>
                                                <ul class="list-unstyled small">
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>UTC</li>
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>BANGI</li>
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>JELAWAT</li>
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>KOTA BHARU</li>
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>ADDA HEIGHT</li>
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>IPOH</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled small">
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>SHAH ALAM</li>
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>BBU</li>
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>KOTA TINGGI</li>
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>BANTING</li>
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>SETAPAK</li>
                                                    <li><i class="fas fa-map-pin text-success me-1"></i>KUALA TERENGGANU
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="alert alert-info mt-3 mb-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Note:</strong> You will be contacted by the assigned branch manager with
                                            pickup details once your request is approved and processed.
                                        </div>
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
                                    <textarea class="form-control" name="note" rows="3" placeholder="Any special instructions or requests">{{ old('note') }}</textarea>
                                </div>

                                <!-- Fee Calculation -->
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="card-title">Transaction Summary</h6>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="d-flex justify-content-between">
                                                    <span>Redemption Amount:</span>
                                                    <span id="displayAmount">$0.00</span>
                                                </div>
                                                <div class="d-flex justify-content-between text-muted">
                                                    <span>Processing Fee (7.0%):</span>
                                                    <span id="displayFee">$0.00</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="d-flex justify-content-between">
                                                    <strong>Net Gold Value:</strong>
                                                    <strong class="text-warning" id="displayNet">$0.00</strong>
                                                </div>
                                                <small class="text-muted">Value of gold you'll receive</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-warning btn-lg"
                                        @if (!$user->national_id) disabled @endif>
                                        <i class="fas fa-coins me-2"></i>Submit Gold Redemption Request
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
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Gold Redemption Info</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-warning">Benefits</h6>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="fas fa-check text-success me-2"></i>Physical gold ownership</li>
                            <li><i class="fas fa-check text-success me-2"></i>Hedge against inflation</li>
                            <li><i class="fas fa-check text-success me-2"></i>Tangible asset value</li>
                            <li><i class="fas fa-check text-success me-2"></i>Multiple pickup locations</li>
                        </ul>

                        <h6 class="text-primary">Process Details</h6>
                        <ul class="list-unstyled small mb-3">
                            <li><i class="fas fa-percentage text-warning me-2"></i>7.0% processing fee</li>
                            <li><i class="fas fa-clock text-info me-2"></i>3-5 business days processing</li>
                            <li><i class="fas fa-map-marker-alt text-success me-2"></i>Branch assigned by admin after
                                approval
                            </li>
                            <li><i class="fas fa-shield-alt text-success me-2"></i>Secure verification required</li>
                        </ul>

                        <h6 class="text-warning">Requirements</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check-circle text-success me-2"></i>Valid National ID</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>KYC verification</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Minimum redemption:
                                ${{ number_format($withdrawalSettings->min_amount ?? 300, 2) }}</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>In-person pickup required</li>
                        </ul>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-coins me-2"></i>Gold Price Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <h4 class="text-warning">
                                ${{ number_format(App\Models\WithdrawalSettings::getMalaysianGoldPrice(), 2) }}
                            </h4>
                            <p class="small text-muted mb-0">Current price per ounce (Malaysian Market)</p>
                            <small class="text-muted">
                                <i class="fas fa-chart-line me-1"></i>
                                Updated:
                                {{ $withdrawalSettings->updated_at ? $withdrawalSettings->updated_at->format('M d, Y H:i') : 'Recently' }}
                            </small>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Price per gram:</span>
                                    <span class="fw-bold">RM
                                        {{ number_format((App\Models\WithdrawalSettings::getMalaysianGoldPrice() / 31.1035) * App\Models\WithdrawalSettings::getMyrConversionRate(), 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between small mb-2">
                                    <span class="text-muted">Exchange rate:</span>
                                    <span class="fw-bold">1 USD = RM
                                        {{ number_format(App\Models\WithdrawalSettings::getMyrConversionRate(), 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted">
                            <p class="mb-1"><strong>Note:</strong> Gold prices fluctuate based on Malaysian market
                                conditions. The
                                actual amount of gold received will be calculated at the time of processing based on current
                                market rates.</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-headset me-2"></i>Need Help?</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Contact support for gold redemption assistance</p>
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

            // Withdrawal settings from server
            const withdrawalSettings = {
                minAmount: {{ $withdrawalSettings->min_amount ?? 300 }},
                maxAmount: {{ $withdrawalSettings->max_amount ?? 3000 }}
            };

            // Update available balance and max amount when wallet type changes
            if (walletTypeSelect && amountInput && amountHelp) {
                walletTypeSelect.addEventListener('change', function() {
                    const walletType = this.value;
                    if (walletType && walletBalances[walletType] !== undefined) {
                        const availableBalance = walletBalances[walletType];
                        const minAmount = withdrawalSettings.minAmount;
                        const maxAmount = Math.min(withdrawalSettings.maxAmount, availableBalance);

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
                    const fee = amount * 0.07; // 7.0%
                    const net = amount - fee;

                    displayAmount.textContent = '$' + amount.toFixed(2);
                    displayFee.textContent = '$' + fee.toFixed(2);
                    displayNet.textContent = '$' + net.toFixed(2);
                });
            }

            // Form validation
            const form = document.getElementById('goldWithdrawalForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const amount = parseFloat(amountInput.value);
                    const walletType = walletTypeSelect.value;
                    const availableBalance = walletBalances[walletType] || 0;
                    const minAmount = withdrawalSettings.minAmount;
                    const maxAmount = Math.min(withdrawalSettings.maxAmount, availableBalance);

                    if (!walletType) {
                        e.preventDefault();
                        alert('Please select a wallet to withdraw from.');
                        return false;
                    }

                    if (amount > maxAmount) {
                        e.preventDefault();
                        alert(
                            `Maximum redemption amount is $${maxAmount.toFixed(2)} for the selected wallet.`
                        );
                        return false;
                    }

                    if (amount < minAmount) {
                        e.preventDefault();
                        alert(`Minimum redemption amount is $${minAmount.toFixed(2)}.`);
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
