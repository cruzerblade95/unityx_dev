@include('components.coming-soon-overlay')
@extends('user.layouts.layoutMaster')

@section('title', 'Withdrawal')

<style>
    /* Enhanced withdrawal card styling - Theme adaptive */
    .withdrawal-type-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid transparent;
        user-select: none;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        min-height: 160px;
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, var(--bs-secondary-bg) 100%);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .withdrawal-type-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--bs-border-color), var(--bs-border-color-translucent));
        transition: all 0.3s ease;
    }

    .withdrawal-type-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
    }

    .withdrawal-type-card.crypto-card {
        border-color: var(--bs-success);
        box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.15);
    }

    .withdrawal-type-card.crypto-card::before {
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
    }

    .withdrawal-type-card.crypto-card:hover {
        border-color: rgba(var(--bs-success-rgb), 0.8);
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, rgba(var(--bs-success-rgb), 0.1) 100%);
        box-shadow: 0 8px 25px rgba(var(--bs-success-rgb), 0.3);
    }

    .withdrawal-type-card.crypto-card.border-primary {
        border-color: var(--bs-success) !important;
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, rgba(var(--bs-success-rgb), 0.1) 100%);
        box-shadow: 0 8px 30px rgba(var(--bs-success-rgb), 0.4);
    }

    .withdrawal-type-card.etc-card {
        border-color: rgba(var(--bs-purple-rgb, 111, 66, 193), 1);
        box-shadow: 0 4px 15px rgba(var(--bs-purple-rgb, 111, 66, 193), 0.15);
    }

    .withdrawal-type-card.etc-card::before {
        background: linear-gradient(90deg, rgba(var(--bs-purple-rgb, 111, 66, 193), 1), var(--bs-secondary));
    }

    .withdrawal-type-card.etc-card:hover {
        border-color: rgba(var(--bs-purple-rgb, 111, 66, 193), 0.8);
        background: linear-gradient(135deg, rgba(var(--bs-purple-rgb, 111, 66, 193), 0.1) 0%, var(--bs-secondary-bg-subtle) 100%);
        box-shadow: 0 8px 25px rgba(var(--bs-purple-rgb, 111, 66, 193), 0.3);
    }

    .withdrawal-type-card.etc-card.border-primary {
        border-color: rgba(var(--bs-purple-rgb, 111, 66, 193), 1) !important;
        background: linear-gradient(135deg, rgba(var(--bs-purple-rgb, 111, 66, 193), 0.1) 0%, var(--bs-secondary-bg-subtle) 100%);
        box-shadow: 0 8px 30px rgba(var(--bs-purple-rgb, 111, 66, 193), 0.4);
    }

    .withdrawal-type-card.gold-card {
        border-color: #fd7e14;
        box-shadow: 0 4px 15px rgba(253, 126, 20, 0.15);
    }

    .withdrawal-type-card.gold-card::before {
        background: linear-gradient(90deg, #fd7e14, #ffc107);
    }

    .withdrawal-type-card.gold-card:hover {
        border-color: #e55a00;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(253, 126, 20, 0.05) 100%);
        box-shadow: 0 8px 25px rgba(253, 126, 20, 0.3);
    }

    .withdrawal-type-card.gold-card.border-primary {
        border-color: #e55a00 !important;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(253, 126, 20, 0.05) 100%);
        box-shadow: 0 8px 30px rgba(253, 126, 20, 0.4);
    }

    .withdrawal-type-card .card-body {
        padding: 2rem 1.5rem;
        position: relative;
        z-index: 1;
    }

    .withdrawal-type-card .card-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .crypto-card .card-icon {
        color: var(--bs-success);
    }

    .crypto-card:hover .card-icon,
    .crypto-card.border-primary .card-icon {
        color: rgba(var(--bs-success-rgb), 0.8);
        transform: scale(1.1);
    }

    .etc-card .card-icon {
        color: rgba(var(--bs-purple-rgb, 111, 66, 193), 1);
    }

    .etc-card:hover .card-icon,
    .etc-card.border-primary .card-icon {
        color: rgba(var(--bs-purple-rgb, 111, 66, 193), 0.8);
        transform: scale(1.1);
    }

    .gold-card .card-icon {
        color: #fd7e14;
    }

    .gold-card:hover .card-icon,
    .gold-card.border-primary .card-icon {
        color: #e55a00;
        transform: scale(1.1);
    }

    .withdrawal-type-card .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--bs-emphasis-color);
    }

    .withdrawal-type-card .card-text {
        color: var(--bs-secondary-color);
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .withdrawal-type-card .fee-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        border-radius: 15px;
        font-weight: 700;
        opacity: 1;
        border: 2px solid rgba(255, 255, 255, 0.8);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .crypto-card .fee-badge {
        background-color: rgba(var(--bs-success-rgb), 0.8);
        color: white;
        box-shadow: 0 2px 8px rgba(var(--bs-success-rgb), 0.3);
    }

    .etc-card .fee-badge {
        background-color: rgba(var(--bs-purple-rgb, 111, 66, 193), 0.8);
        color: white;
        box-shadow: 0 2px 8px rgba(var(--bs-purple-rgb, 111, 66, 193), 0.3);
    }

    .gold-card .fee-badge {
        background-color: #e55a00;
        color: white;
        box-shadow: 0 2px 8px rgba(253, 126, 20, 0.3);
    }

    /* Withdrawal History Table Styles - Theme adaptive */
    .withdrawal-history-table {
        font-size: 0.9rem;
    }

    .withdrawal-history-table th {
        background-color: var(--bs-secondary-bg);
        border-bottom: 2px solid var(--bs-border-color);
        font-weight: 600;
        color: var(--bs-emphasis-color);
        white-space: nowrap;
    }

    .withdrawal-history-table td {
        vertical-align: middle;
        border-bottom: 1px solid var(--bs-border-color-translucent);
        color: var(--bs-body-color);
    }

    .withdrawal-history-table .type-badge {
        min-width: 110px;
        font-size: 0.75rem;
        padding: 0.375rem 0.5rem;
    }

    .withdrawal-history-table .payment-details {
        max-width: 200px;
        font-size: 0.8rem;
    }

    .withdrawal-history-table .amount-cell {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
        font-weight: 600;
    }

    .withdrawal-history-table .status-cell {
        min-width: 100px;
    }

    .withdrawal-history-table tbody tr:hover {
        background-color: var(--bs-tertiary-bg);
        transition: background-color 0.2s ease;
    }

    /* Dark theme specific adjustments */
    [data-bs-theme="dark"] .withdrawal-type-card {
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, rgba(var(--bs-secondary-rgb), 0.3) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.crypto-card:hover {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.15) 0%, rgba(25, 135, 84, 0.05) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.crypto-card.border-primary {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.15) 0%, rgba(25, 135, 84, 0.05) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.etc-card:hover {
        background: linear-gradient(135deg, rgba(111, 66, 193, 0.15) 0%, rgba(111, 66, 193, 0.05) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.etc-card.border-primary {
        background: linear-gradient(135deg, rgba(111, 66, 193, 0.15) 0%, rgba(111, 66, 193, 0.05) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.gold-card:hover {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(253, 126, 20, 0.08) 100%);
    }

    [data-bs-theme="dark"] .withdrawal-type-card.gold-card.border-primary {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(253, 126, 20, 0.08) 100%);
    }
</style>
</style>

<script>
    // Define functions immediately in the document head so they're available for onclick
    var paymentInfo = {
        national_id: '{{ $user->national_id ?? '' }}',
        usdt_bep20_address: '{{ $user->usdt_bep20_address ?? '' }}',
        usdc_bep20_address: '{{ $user->usdc_bep20_address ?? '' }}',
        fdusd_bep20_address: '{{ $user->fdusd_bep20_address ?? '' }}',
        etc_black_card_number: '{{ $user->etc_black_card_number ?? '' }}',
        account_number: '{{ $user->account_number ?? '' }}',
        cardholder_name: '{{ $user->cardholder_name ?? '' }}'
    };

    // Debug: Log payment info to console
    console.log('Payment Info:', paymentInfo);

    var selectedWithdrawalType = null;

    function selectWithdrawalType(type) {
        console.log('selectWithdrawalType called with:', type);
        selectedWithdrawalType = type;

        // Remove selected class from all cards
        document.querySelectorAll('.withdrawal-type-card').forEach(card => {
            card.classList.remove('border-primary', 'bg-light');
        });

        // Add selected class to clicked card based on type
        const selectedCard = event.currentTarget;
        selectedCard.classList.add('border-primary', 'bg-light');

        // Check the corresponding radio button
        document.getElementById(type + '_type').checked = true;

        // Show payment information and dynamic fields
        showPaymentInfo(type);

        // Update amount limits and fee display
        updateAmountLimits(type);

        // Update submit button state
        updateSubmitButton();
    }

    function updateAmountLimits(type) {
        const amountInput = document.getElementById('amount');
        const amountHelpText = amountInput ? amountInput.nextElementSibling : null;
        const feeInfoList = document.getElementById('feeInfoList');

        if (!amountInput) return;

        let minAmount = 100;
        let maxAmount = {{ $walletBalance }};
        let feePercent = 4.75;
        let typeName = '';

        switch (type) {
            case 'crypto':
                minAmount = 50;
                maxAmount = Math.min(300, {{ $walletBalance }});
                feePercent = 4.75;
                typeName = 'Crypto Wallet';
                break;
            case 'etc_black':
                minAmount = 100;
                maxAmount = Math.min(300, {{ $walletBalance }});
                feePercent = 5.6;
                typeName = 'Crypto Bank Card';
                break;
            case 'gold':
                minAmount = 300;
                maxAmount = Math.min(3000, {{ $walletBalance }});
                feePercent = 7.0;
                typeName = 'Gold Redeem';
                break;
        }

        // Update input attributes
        amountInput.setAttribute('min', minAmount);
        amountInput.setAttribute('max', maxAmount);

        // Update help text
        if (amountHelpText && amountHelpText.classList.contains('form-text')) {
            amountHelpText.innerHTML =
                `Minimum: $${minAmount}.00 | Maximum: $${maxAmount.toFixed(2)} | Fee: ${feePercent}%`;
        }

        // Update fee information
        if (feeInfoList) {
            feeInfoList.innerHTML = `
                <li><strong>${typeName}:</strong> ${feePercent}% service fee will be deducted</li>
                <li>Minimum withdrawal: $${minAmount} USD</li>
                <li>Maximum withdrawal: $${maxAmount.toFixed(2)} USD</li>
                <li>Processing time: 1-3 business days</li>
            `;
        }

        // Recalculate net amount if there's already a value
        const currentAmount = parseFloat(amountInput.value);
        if (currentAmount > 0) {
            const feeAmount = currentAmount * (feePercent / 100);
            const netAmount = currentAmount - feeAmount;

            const netAmountValue = document.getElementById('netAmountValue');
            if (netAmountValue) {
                netAmountValue.textContent = '$' + netAmount.toFixed(2);
                document.getElementById('netAmountDisplay').style.display = 'block';
            }
        }
    }

    function showPaymentInfo(type) {
        const paymentInfoDisplay = document.getElementById('paymentInfoDisplay');
        const paymentInfoContent = document.getElementById('paymentInfoContent');

        let infoHTML = '';
        let hasRequiredInfo = false;

        switch (type) {
            case 'crypto':
                // Check if user has any crypto wallet addresses
                const hasUsdt = paymentInfo.usdt_bep20_address;
                const hasUsdc = paymentInfo.usdc_bep20_address;
                const hasFdusd = paymentInfo.fdusd_bep20_address;

                if (hasUsdt || hasUsdc || hasFdusd) {
                    infoHTML = `
                        <div class="mb-3">
                            <strong>Available Crypto Wallets:</strong>
                            ${hasUsdt ? `<div class="mt-1"><small>USDT BEP20:</small> <span class="font-monospace">${paymentInfo.usdt_bep20_address}</span></div>` : ''}
                            ${hasUsdc ? `<div class="mt-1"><small>USDC BEP20:</small> <span class="font-monospace">${paymentInfo.usdc_bep20_address}</span></div>` : ''}
                            ${hasFdusd ? `<div class="mt-1"><small>FDUSD BEP20:</small> <span class="font-monospace">${paymentInfo.fdusd_bep20_address}</span></div>` : ''}
                        </div>
                        <div class="mt-2">
                            <label class="form-label">Select Currency & Wallet <span class="text-danger">*</span></label>
                            <select class="form-control" name="currency" required onchange="updateSelectedWallet(this.value)">
                                <option value="">Select Currency & Wallet</option>
                                ${hasUsdt ? '<option value="USDTBEP20">USDT BEP20</option>' : ''}
                                ${hasUsdc ? '<option value="USDCBEP20">USDC BEP20</option>' : ''}
                                ${hasFdusd ? '<option value="FDUSDBEP20">FDUSD BEP20</option>' : ''}
                            </select>
                            <input type="hidden" name="wallet_address" id="selected_wallet_address">
                        </div>
                    `;
                    hasRequiredInfo = true;
                } else {
                    infoHTML =
                        '<div class="text-danger">Please complete your crypto wallet addresses in Payment Information section.</div>';
                }
                break;

            case 'etc_black':
                if (paymentInfo.etc_black_card_number && paymentInfo.account_number && paymentInfo.cardholder_name) {
                    infoHTML = `
                        <div><strong>Crypto Bank Card:</strong> ****${paymentInfo.etc_black_card_number.slice(-4)}</div>
                        <div><strong>Account Number:</strong> ****${paymentInfo.account_number.slice(-4)}</div>
                        <div><strong>Cardholder:</strong> ${paymentInfo.cardholder_name}</div>
                    `;
                    hasRequiredInfo = true;
                } else {
                    infoHTML =
                        '<div class="text-danger">Please complete your Crypto Bank Card information and account number in Payment Information section.</div>';
                }
                break;

            case 'gold':
                if (paymentInfo.national_id) {
                    infoHTML = `
                        <div><strong>National ID:</strong> ${paymentInfo.national_id}</div>
                        <div class="mt-2">
                            <label class="form-label">Select Musleem Gold Branch <span class="text-danger">*</span></label>
                            <select class="form-control" name="branch_name" required onchange="updateSubmitButton()">
                                <option value="">Select Branch</option>
                                <option value="SHAH ALAM">SHAH ALAM</option>
                                <option value="BANGI">BANGI</option>
                                <option value="BANTING">BANTING</option>
                                <option value="ALOR SETAR">ALOR SETAR</option>
                                <option value="JELAWAT">JELAWAT</option>
                                <option value="KOTA TINGGI">KOTA TINGGI</option>
                                <option value="BANDAR BARU UDA">BANDAR BARU UDA</option>
                            </select>
                        </div>
                    `;
                    hasRequiredInfo = true;
                } else {
                    infoHTML =
                        '<div class="text-danger">Please complete your national ID in Payment Information section.</div>';
                }
                break;
        }

        paymentInfoContent.innerHTML = infoHTML;
        paymentInfoDisplay.style.display = 'block';

        return hasRequiredInfo;
    }

    function updateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');

        if (submitBtn) {
            // Always enable the button - let form validation handle required fields
            submitBtn.disabled = false;
        }
    }

    function updateSelectedWallet(currency) {
        const walletAddressInput = document.getElementById('selected_wallet_address');
        if (!walletAddressInput) return;

        switch (currency) {
            case 'USDTBEP20':
                walletAddressInput.value = paymentInfo.usdt_bep20_address;
                break;
            case 'USDCBEP20':
                walletAddressInput.value = paymentInfo.usdc_bep20_address;
                break;
            case 'FDUSDBEP20':
                walletAddressInput.value = paymentInfo.fdusd_bep20_address;
                break;
            default:
                walletAddressInput.value = '';
        }
        console.log('Selected wallet for', currency, ':', walletAddressInput.value);

        // Update submit button state after wallet selection
        updateSubmitButton();
    }
</script>

@section('content')
    <div class="container py-4">
        <h2>Withdrawal</h2>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->has('withdrawal_limit'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>Withdrawal Limit Reached
                </h4>
                {{ $errors->first('withdrawal_limit') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Wallet Balance Display -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="card-title">Profit Balance Overview</h5>
                        <div class="row">
                            <div class="col-sm-6">
                                @if ($user->wallet && $user->wallet->floating > 0)
                                    <h3 class="text-success">${{ number_format($user->wallet->profit, 2) }}</h3>
                                    <small class="text-muted">Balance After Withdrawal Approval</small>
                                @else
                                    <h3 class="text-success">${{ number_format($walletBalance, 2) }}</h3>
                                    <small class="text-muted">Available for Withdrawal</small>
                                @endif
                            </div>
                            @if ($user->wallet && $user->wallet->floating > 0)
                                <div class="col-sm-6">
                                    <h4 class="text-warning">${{ number_format($user->wallet->floating, 2) }}</h4>
                                    <small class="text-muted">Pending Withdrawals</small>
                                </div>
                            @endif
                        </div>
                        @if ($user->wallet && $user->wallet->floating > 0)
                            <div class="mt-2">
                                <small class="text-info">
                                    <i class="fas fa-info-circle me-1"></i>
                                    You have ${{ number_format($user->wallet->floating, 2) }} in pending withdrawals.
                                    Your balance will be ${{ number_format($user->wallet->profit, 2) }} after approval.
                                </small>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="small text-muted">
                            <div><strong>Balance System:</strong></div>
                            @if ($user->wallet && $user->wallet->floating > 0)
                                <div>• Shows your balance after withdrawal</div>
                                <div>• Pending amount is held temporarily</div>
                                <div>• Balance updates when processed</div>
                            @else
                                <div>• Current available balance</div>
                                <div>• Ready for withdrawal</div>
                                <div>• Updates after each transaction</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KYC Status Check -->
        @if (!$isKycApproved)
            <div class="alert alert-warning" role="alert">
                <h4 class="alert-heading">KYC Verification Required!</h4>
                <p>Your KYC verification must be approved before you can make withdrawal requests.</p>
                <hr>
                <p class="mb-0">
                    <a href="{{ route('user.kyc.index') }}" class="btn btn-warning">Complete KYC Verification</a>
                </p>
            </div>
        @endif

        <!-- Pending Withdrawal Notice -->
        @if ($pendingWithdrawal)
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading">
                    <i class="fas fa-clock me-2"></i>Pending Withdrawal Request
                </h4>
                <p>You currently have a pending withdrawal request that needs to be processed before you can submit a new
                    one.</p>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Request Details:</strong>
                        <ul class="list-unstyled mt-2">
                            <li><strong>ID:</strong> #{{ $pendingWithdrawal->id }}</li>
                            <li><strong>Amount:</strong> ${{ number_format($pendingWithdrawal->amount, 2) }}</li>
                            <li><strong>Type:</strong>
                                {{ ucfirst(str_replace('_', ' ', $pendingWithdrawal->withdrawal_type)) }}</li>
                            <li><strong>Net Amount:</strong> ${{ number_format($pendingWithdrawal->net_amount, 2) }}</li>
                            <li><strong>Submitted:</strong> {{ $pendingWithdrawal->created_at->format('M d, Y H:i') }}</li>
                    </div>
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong>
                    <div class="mt-2">
                        <span class="badge bg-warning fs-6 p-2">
                            <i class="fas fa-hourglass-half me-1"></i>
                            Pending Review
                        </span>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Your withdrawal will be processed within 1-3 business days.
                            You'll be able to submit new requests once this one is completed or rejected.
                        </small>
                    </div>
                </div>
            </div>
    </div>
    @endif

    <!-- Withdrawal Form -->
    <div class="card">
        <div class="card-header">
            <h4>Create Withdrawal Request</h4>
            @if ($pendingWithdrawal)
                <small class="text-muted">
                    <i class="fas fa-lock me-1"></i>
                    Form disabled - You have a pending withdrawal request
                </small>
            @endif
        </div>
        <div class="card-body">
            @if ($isKycApproved && !$pendingWithdrawal)
                <form method="POST" action="{{ route('user.withdrawal.store') }}" id="withdrawalForm">
                    @csrf

                    <!-- Withdrawal Type Selection -->
                    <div class="mb-4">
                        <label class="form-label">Withdrawal Type <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card withdrawal-type-card crypto-card" onclick="selectWithdrawalType('crypto')">
                                    <div class="card-body text-center">
                                        <input type="radio" name="withdrawal_type" value="crypto" id="crypto_type"
                                            class="d-none">
                                        <div class="fee-badge">4.75% Fee</div>
                                        <i class="fas fa-wallet card-icon"></i>
                                        <h6 class="card-title">Crypto Wallet</h6>
                                        <p class="card-text">Withdraw to USDT, USDC, or FDUSD wallet</p>
                                        <small class="text-success fw-bold">$50 - $300</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card withdrawal-type-card etc-card" onclick="selectWithdrawalType('etc_black')">
                                    <div class="card-body text-center">
                                        <input type="radio" name="withdrawal_type" value="etc_black" id="etc_black_type"
                                            class="d-none">
                                        <div class="fee-badge">5.6% Fee</div>
                                        <i class="fas fa-credit-card card-icon"></i>
                                        <h6 class="card-title">Crypto Bank Card</h6>
                                        <p class="card-text">Direct transfer to your Crypto Bank Card</p>
                                        <small class="text-success fw-bold">$100 - $300</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card withdrawal-type-card gold-card" onclick="selectWithdrawalType('gold')">
                                    <div class="card-body text-center">
                                        <input type="radio" name="withdrawal_type" value="gold" id="gold_type"
                                            class="d-none">
                                        <div class="fee-badge">7.0% Fee</div>
                                        <i class="fas fa-coins card-icon"></i>
                                        <h6 class="card-title">Gold Redeem</h6>
                                        <p class="card-text">Physical gold Redeem at Musleem Gold</p>
                                        <small class="text-success fw-bold">$300 - $3,000</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('withdrawal_type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Payment Information Display -->
                    <div id="paymentInfoDisplay" class="mb-4" style="display: none;">
                        <div class="alert alert-info">
                            <h6>Payment Information</h6>
                            <div id="paymentInfoContent"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Withdrawal Amount <span
                                        class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="100"
                                    class="form-control @error('amount') is-invalid @enderror" id="amount"
                                    name="amount" value="{{ old('amount') }}" max="{{ $walletBalance }}" required>
                                <div class="form-text">Minimum: $100.00 | Maximum:
                                    ${{ number_format($walletBalance, 2) }}</div>
                                <div id="netAmountDisplay" class="form-text text-success fw-bold" style="display: none;">
                                    Net amount after 4.75% fee: <span id="netAmountValue">$0.00</span>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference" class="form-label">Reference (Optional)</label>
                                <input type="text" class="form-control" id="reference" name="reference"
                                    value="{{ old('reference') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="note" name="note" rows="3">{{ old('note') }}</textarea>
                    </div>

                    <!-- Fee Information -->
                    <div class="alert alert-info" id="feeInfoAlert">
                        <strong>Fee Information:</strong>
                        <ul class="mb-0 mt-2" id="feeInfoList">
                            <li>Please select a withdrawal type to see fee information</li>
                            <li>Processing time: 1-3 business days</li>
                        </ul>
                        <hr class="my-2">
                        <small class="text-muted">
                            <strong>Note:</strong> When you submit a withdrawal request, it will be held for processing.
                            Your balance shown above reflects what you'll have after the withdrawal is approved.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitBtn">Submit Withdrawal Request</button>
                </form>
            @elseif ($pendingWithdrawal)
                <div class="text-center py-4">
                    <i class="fas fa-clock text-warning fa-3x mb-3"></i>
                    <h5 class="text-muted">Withdrawal Form Disabled</h5>
                    <p class="text-muted">
                        You have a pending withdrawal request (#{{ $pendingWithdrawal->id }}) that needs to be processed
                        first.
                    </p>
                    <p class="small text-muted">
                        Once your current withdrawal is approved or rejected, you'll be able to submit new requests.
                    </p>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-id-card text-warning fa-3x mb-3"></i>
                    <h5 class="text-muted">KYC Verification Required</h5>
                    <p class="text-muted">Please complete your KYC verification to create withdrawal requests.</p>
                </div>
            @endif
        </div>
    </div>

    <hr class="my-4">

    <!-- Withdrawal History -->
    <div class="card">
        <div class="card-header">
            <h4>Withdrawal History</h4>
        </div>
        <div class="card-body">
            @if ($withdrawals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped withdrawal-history-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Fee</th>
                                <th>Net Amount</th>
                                <th>Payment Details</th>
                                <th>Status</th>
                                <th>Reference</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($withdrawals as $withdrawal)
                                @php
                                    // Determine withdrawal type display name and icon
                                    $typeInfo = match ($withdrawal->withdrawal_type ?? 'crypto') {
                                        'crypto' => [
                                            'name' => 'Crypto Wallet',
                                            'icon' => 'fas fa-wallet',
                                            'color' => 'primary',
                                        ],
                                        'etc_black' => [
                                            'name' => 'Crypto Bank Card',
                                            'icon' => 'fas fa-credit-card',
                                            'color' => 'dark',
                                        ],
                                        'gold' => [
                                            'name' => 'Gold Redeem',
                                            'icon' => 'fas fa-coins',
                                            'color' => 'warning',
                                        ],
                                        default => [
                                            'name' => 'Crypto Wallet',
                                            'icon' => 'fas fa-wallet',
                                            'color' => 'primary',
                                        ],
                                    };

                                    // Calculate fee percentage based on type
                                    $feePercent = match ($withdrawal->withdrawal_type ?? 'crypto') {
                                        'crypto' => 4.75,
                                        'etc_black' => 5.6,
                                        'gold' => 7.0,
                                        default => 4.75,
                                    };
                                @endphp
                                <tr>
                                    <td><strong>#{{ $withdrawal->id }}</strong></td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $typeInfo['color'] }} type-badge d-flex align-items-center justify-content-center">
                                            <i class="{{ $typeInfo['icon'] }} me-1"></i>
                                            {{ $typeInfo['name'] }}
                                        </span>
                                    </td>
                                    <td class="amount-cell">${{ number_format($withdrawal->amount, 2) }}</td>
                                    <td>
                                        <small class="text-muted">{{ $feePercent }}%</small><br>
                                        <span
                                            class="text-danger amount-cell">${{ number_format($withdrawal->tax_amount, 2) }}</span>
                                    </td>
                                    <td class="amount-cell text-success">${{ number_format($withdrawal->net_amount, 2) }}
                                    </td>
                                    <td class="payment-details">
                                        @if ($withdrawal->withdrawal_type === 'crypto')
                                            @if ($withdrawal->bank_name && $withdrawal->account_number)
                                                <div class="mb-1">
                                                    <span
                                                        class="badge bg-light text-dark">{{ strtoupper($withdrawal->bank_name) }}</span>
                                                </div>
                                                <span class="font-monospace text-muted small">
                                                    {{ substr($withdrawal->account_number, 0, 8) }}...{{ substr($withdrawal->account_number, -8) }}
                                                </span>
                                            @else
                                                <small class="text-muted">Crypto wallet details not available</small>
                                            @endif
                                        @elseif ($withdrawal->withdrawal_type === 'etc_black')
                                            @if ($withdrawal->payment_details && is_array($withdrawal->payment_details))
                                                <div class="mb-1">
                                                    <span class="badge bg-dark text-white">Crypto Bank Card</span>
                                                </div>
                                                @if (isset($withdrawal->payment_details['etc_black_card_number']))
                                                    <div class="small">
                                                        ****{{ substr($withdrawal->payment_details['etc_black_card_number'], -4) }}
                                                    </div>
                                                @endif
                                                @if (isset($withdrawal->payment_details['cardholder_name']))
                                                    <div class="small text-muted">
                                                        {{ $withdrawal->payment_details['cardholder_name'] }}</div>
                                                @endif
                                            @else
                                                <small class="text-muted">Crypto Bank Card details not available</small>
                                            @endif
                                        @elseif ($withdrawal->withdrawal_type === 'gold')
                                            <div class="mb-1">
                                                <span class="badge bg-warning text-dark">Gold Redeem</span>
                                            </div>
                                            @if ($withdrawal->branch_name)
                                                <div class="small fw-bold">{{ $withdrawal->branch_name }}</div>
                                            @endif
                                            @if (
                                                $withdrawal->payment_details &&
                                                    is_array($withdrawal->payment_details) &&
                                                    isset($withdrawal->payment_details['national_id']))
                                                <div class="small text-muted">ID:
                                                    {{ $withdrawal->payment_details['national_id'] }}</div>
                                            @endif
                                        @else
                                            <small class="text-muted">Payment details not available</small>
                                        @endif
                                    </td>
                                    <td class="status-cell">
                                        @php
                                            $statusClass = match ($withdrawal->status) {
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'completed' => 'success',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span
                                            class="badge bg-{{ $statusClass }}">{{ ucfirst($withdrawal->status) }}</span>
                                        @if ($withdrawal->note)
                                            <br><small
                                                class="text-muted mt-1">{{ strlen($withdrawal->note) > 30 ? substr($withdrawal->note, 0, 30) . '...' : $withdrawal->note }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($withdrawal->reference)
                                            <span class="font-monospace small">{{ $withdrawal->reference }}</span>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">{{ $withdrawal->created_at->format('M d, Y') }}</div>
                                        <div class="text-muted small">{{ $withdrawal->created_at->format('H:i') }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-muted">No withdrawal requests found.</p>
                </div>
            @endif
        </div>
    </div>
    </div>

    @push('scripts')
        <script>
            // DOM Content Loaded event for DOM-dependent functionality
            document.addEventListener('DOMContentLoaded', function() {
                const amountInput = document.getElementById('amount');
                const maxBalance = {{ $walletBalance }};
                const minAmount = 100;

                if (amountInput) {
                    // Set minimum attribute
                    amountInput.setAttribute('min', minAmount);

                    amountInput.addEventListener('input', function() {
                        const amount = parseFloat(this.value);

                        // Calculate net amount based on withdrawal type and fee
                        if (amount && amount > 0 && selectedWithdrawalType) {
                            let feePercent = 0;

                            switch (selectedWithdrawalType) {
                                case 'crypto':
                                    feePercent = 4.75;
                                    break;
                                case 'etc_black':
                                    feePercent = 5.6;
                                    break;
                                case 'gold':
                                    feePercent = 7.0;
                                    break;
                                default:
                                    feePercent = 4.75;
                            }

                            const feeAmount = amount * (feePercent / 100);
                            const netAmount = amount - feeAmount;

                            document.getElementById('netAmountValue').textContent = '$' + netAmount.toFixed(2);
                            document.getElementById('netAmountDisplay').style.display = 'block';
                        } else {
                            document.getElementById('netAmountDisplay').style.display = 'none';
                        }

                        if (amount > maxBalance) {
                            this.value = maxBalance;
                        }

                        // Set validation based on withdrawal type
                        let minAmount = 100; // default
                        let maxAmount = maxBalance;

                        if (selectedWithdrawalType) {
                            switch (selectedWithdrawalType) {
                                case 'crypto':
                                    minAmount = 50;
                                    maxAmount = Math.min(300, maxBalance);
                                    break;
                                case 'etc_black':
                                    minAmount = 100;
                                    maxAmount = Math.min(300, maxBalance);
                                    break;
                                case 'gold':
                                    minAmount = 300;
                                    maxAmount = Math.min(3000, maxBalance);
                                    break;
                            }
                        }

                        this.setAttribute('min', minAmount);
                        this.setAttribute('max', maxAmount);

                        if (amount < minAmount && amount > 0) {
                            this.setCustomValidity(`Minimum withdrawal amount is $${minAmount}.00`);
                        } else if (amount > maxAmount) {
                            this.setCustomValidity(`Maximum withdrawal amount is $${maxAmount}.00`);
                        } else {
                            this.setCustomValidity('');
                        }

                        // Update submit button state
                        updateSubmitButton();
                    });
                }

                // Form submission validation
                const withdrawalForm = document.getElementById('withdrawalForm');
                if (withdrawalForm) {
                    withdrawalForm.addEventListener('submit', function(e) {
                        // Check withdrawal type
                        if (!selectedWithdrawalType) {
                            e.preventDefault();
                            alert('Please select a withdrawal type.');
                            return false;
                        }

                        // Check amount based on withdrawal type
                        const amount = parseFloat(document.getElementById('amount').value);
                        let minAmount = 100;
                        let maxAmount = 300;

                        switch (selectedWithdrawalType) {
                            case 'crypto':
                                minAmount = 50;
                                maxAmount = 300;
                                break;
                            case 'etc_black':
                                minAmount = 100;
                                maxAmount = 300;
                                break;
                            case 'gold':
                                minAmount = 300;
                                maxAmount = 3000;
                                break;
                        }

                        if (!amount || amount < minAmount) {
                            e.preventDefault();
                            alert(`Please enter a valid withdrawal amount (minimum $${minAmount}).`);
                            return false;
                        }

                        if (amount > maxAmount) {
                            e.preventDefault();
                            alert(`Maximum withdrawal amount for this type is $${maxAmount}.`);
                            return false;
                        }

                        // Check type-specific requirements
                        if (selectedWithdrawalType === 'crypto') {
                            const currencySelect = document.querySelector('select[name="currency"]');
                            const walletAddress = document.getElementById('selected_wallet_address');
                            if (!currencySelect || !currencySelect.value) {
                                e.preventDefault();
                                alert('Please select a currency and wallet.');
                                return false;
                            }
                            if (!walletAddress || !walletAddress.value) {
                                e.preventDefault();
                                alert('Wallet address is required. Please select a currency.');
                                return false;
                            }
                        } else if (selectedWithdrawalType === 'gold') {
                            const branchSelect = document.querySelector('select[name="branch_name"]');
                            if (!branchSelect || !branchSelect.value) {
                                e.preventDefault();
                                alert('Please select a Musleem Gold branch.');
                                return false;
                            }
                        }

                        return true;
                    });
                }
            });
        </script>
    @endpush
@endsection
