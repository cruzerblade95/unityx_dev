@extends('user.layouts.layoutMaster')

@section('title', 'Deposit Crypto')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

<style>
    /* Enhanced deposit form styling - Crypto green theme like withdrawal */
    .deposit-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid var(--bs-success);
        user-select: none;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, var(--bs-secondary-bg) 100%);
        box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.15);
    }

    .deposit-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
        transition: all 0.3s ease;
    }

    .deposit-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(var(--bs-success-rgb), 0.3);
        border-color: rgba(var(--bs-success-rgb), 0.8);
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, rgba(var(--bs-success-rgb), 0.1) 100%);
    }

    .card-header-custom {
        background: linear-gradient(135deg, var(--bs-success) 0%, rgba(var(--bs-success-rgb), 0.8) 100%);
        color: white;
        text-align: center;
        font-weight: 700;
        font-size: 1.5rem;
        padding: 2rem 1.5rem;
        border: none;
        position: relative;
        z-index: 1;
    }

    .card-body {
        padding: 2rem 1.5rem;
        position: relative;
        z-index: 1;
    }

    .form-control {
        font-size: 1.1rem;
        padding: 1rem 1.25rem;
        border: 2px solid var(--bs-border-color);
        border-radius: 10px;
        transition: all 0.3s ease;
        background-color: var(--bs-body-bg);
        color: var(--bs-body-color);
    }

    .form-control:focus {
        border-color: var(--bs-success);
        box-shadow: 0 0 0 0.3rem rgba(var(--bs-success-rgb), 0.25);
        transform: scale(1.02);
        background-color: var(--bs-body-bg);
    }

    .form-label {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--bs-emphasis-color);
        margin-bottom: 0.75rem;
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

    .btn-danger {
        background: linear-gradient(135deg, var(--bs-danger) 0%, rgba(var(--bs-danger-rgb), 0.8) 100%);
        border: none;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, rgba(var(--bs-danger-rgb), 0.9) 0%, var(--bs-danger) 100%);
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

    .alert-danger {
        background: linear-gradient(135deg, var(--bs-danger-bg-subtle) 0%, rgba(var(--bs-danger-rgb), 0.1) 100%);
        border-color: var(--bs-danger);
        color: var(--bs-danger-text-emphasis);
    }

    .alert-danger::before {
        background: linear-gradient(90deg, var(--bs-danger), #dc3545);
    }

    .input-group-text {
        background: var(--bs-tertiary-bg);
        border: 2px solid var(--bs-border-color);
        color: var(--bs-body-color);
        font-weight: 600;
        font-size: 1.1rem;
    }

    .deposit-section {
        background: var(--bs-body-bg);
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .conversion-info {
        background: linear-gradient(135deg, var(--bs-secondary-bg) 0%, var(--bs-tertiary-bg) 100%);
        border: 3px solid var(--bs-success);
        border-radius: 12px;
        padding: 1.5rem;
        margin: 1.5rem 0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.15);
    }

    .conversion-info::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
        transition: all 0.3s ease;
    }

    .conversion-info:hover {
        box-shadow: 0 8px 25px rgba(var(--bs-success-rgb), 0.25);
        transform: translateY(-2px);
    }

    .conversion-info h6 {
        color: var(--bs-success);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .conversion-rate {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--bs-success);
        text-align: center;
        padding: 1rem;
        background: rgba(var(--bs-success-rgb), 0.1);
        border-radius: 8px;
        margin-bottom: 1rem;
        border: 1px solid rgba(var(--bs-success-rgb), 0.2);
        position: relative;
        overflow: hidden;
    }

    .conversion-rate::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
    }

    /* Enhanced visibility for required fields */
    .text-danger {
        font-weight: 700;
        font-size: 1.2rem;
    }

    /* Better error styling */
    .is-invalid {
        border-color: var(--bs-danger);
        box-shadow: 0 0 0 0.3rem rgba(var(--bs-danger-rgb), 0.25);
    }

    .invalid-feedback {
        font-size: 1rem;
        font-weight: 700;
    }

    /* Loading state styling */
    .loading-overlay {
        background: rgba(var(--bs-body-color-rgb), 0.1);
        backdrop-filter: blur(5px);
    }

    /* Better spacing for older users */
    .form-group {
        margin-bottom: 2rem;
    }

    .d-grid {
        margin-top: 2rem;
    }

    /* Dark theme specific adjustments */
    [data-bs-theme="dark"] .deposit-card {
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, rgba(var(--bs-secondary-rgb), 0.3) 100%);
    }

    [data-bs-theme="dark"] .deposit-card:hover {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.15) 0%, rgba(var(--bs-primary-rgb), 0.05) 100%);
    }

    [data-bs-theme="dark"] .conversion-info {
        background: linear-gradient(135deg, rgba(var(--bs-secondary-rgb), 0.3) 0%, rgba(var(--bs-tertiary-rgb), 0.3) 100%);
    }

    [data-bs-theme="dark"] .conversion-rate {
        background: rgba(var(--bs-success-rgb), 0.15);
    }

    [data-bs-theme="dark"] .alert-info {
        background: linear-gradient(135deg, rgba(13, 202, 240, 0.15) 0%, rgba(13, 202, 240, 0.05) 100%);
    }

    [data-bs-theme="dark"] .alert-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(255, 193, 7, 0.05) 100%);
    }

    [data-bs-theme="dark"] .alert-success {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.15) 0%, rgba(25, 135, 84, 0.05) 100%);
    }

    [data-bs-theme="dark"] .alert-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.15) 0%, rgba(220, 53, 69, 0.05) 100%);
    }
</style>

@section('content')
    {{-- @include('components.coming-soon-overlay') --}}
    <div class="container py-4 position-relative" style="z-index: 1;">
        <div class="deposit-header">
            <h2>
                <i class="fab fa-bitcoin me-2"></i>
                Deposit Crypto
            </h2>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('warning') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="row">
            <!-- Cryptocurrency Deposit Form -->
            <div class="col-md-8 mx-auto">
                <div class="card deposit-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fab fa-bitcoin me-2"></i>
                            NOWPayments Crypto Conversion
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="info-alert">
                            <small><i class="fas fa-info-circle me-1"></i><strong>How it works:</strong> Send any supported
                                cryptocurrency via NOWPayments and it will be automatically converted to USDT in your
                                wallet.
                                A 1.8% fee is added to the amount you need to send.</small>
                        </div>

                        <form method="POST" action="{{ route('user.deposit.crypto') }}" id="cryptoDepositForm">
                            @csrf
                            <div class="mb-3">
                                <label for="crypto_amount" class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>
                                    Amount (USD Value) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="crypto_amount"
                                        name="amount" required min="100">
                                    <span class="input-group-text">USD</span>
                                </div>
                                <div class="form-text">Minimum deposit: $100 USD equivalent</div>

                                <!-- Fee Calculation Display -->
                                <div id="cryptoFeeCalculation" class="mt-2" style="display: none;">
                                    <div class="card card-body bg-light border-0">
                                        <div class="row g-2 small">
                                            <div class="col-6">
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Requested Amount:</span>
                                                    <span class="fw-medium">$<span
                                                            id="cryptoDisplayAmount">0.00</span></span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">NOWPayments Fee (1.8%):</span>
                                                    <span class="text-info fw-medium">+$<span
                                                            id="cryptoDisplayFee">0.00</span></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between">
                                                    <span class="fw-semibold">Total crypto value needed:</span>
                                                    <span class="fw-semibold text-primary">$<span
                                                            id="cryptoDisplayTotalAmount">0.00</span> worth</span>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <span class="fw-semibold text-success">You will receive:</span>
                                                    <span class="fw-semibold text-success">$<span
                                                            id="cryptoDisplayReceived">0.00</span> USDT</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="crypto_currency" class="form-label">
                                    <i class="fab fa-bitcoin me-1"></i>
                                    Send From Currency <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="crypto_currency" name="crypto_currency" required>
                                    <option value="">Select cryptocurrency to send</option>

                                    <!-- USDT Variants -->
                                    <optgroup label="USDT (Tether) - Popular Stablecoins">
                                        <option value="usdttrc20">USDT TRC-20 (Tron) → USDT</option>
                                        <option value="usdterc20">USDT ERC-20 (Ethereum) → USDT</option>
                                        <option value="usdtbsc">USDT BEP-20 (BSC) → USDT</option>
                                        <option value="usdtpoly">USDT Polygon → USDT</option>
                                        <option value="usdtsol">USDT Solana → USDT</option>
                                    </optgroup>

                                    <!-- Other Stablecoins -->
                                    <optgroup label="Other Stablecoins">
                                        <option value="usdcsol">USDC Solana → USDT</option>
                                        <option value="usdcerc20">USDC ERC-20 → USDT</option>
                                        <option value="usdcbsc">USDC BEP-20 → USDT</option>
                                    </optgroup>

                                    <!-- Major Cryptocurrencies -->
                                    <optgroup label="Major Cryptocurrencies">
                                        <option value="btc">Bitcoin (BTC) → USDT</option>
                                        <option value="eth">Ethereum (ETH) → USDT</option>
                                        <option value="bnb">Binance Coin (BNB) → USDT</option>
                                        <option value="ltc">Litecoin (LTC) → USDT</option>
                                        <option value="bch">Bitcoin Cash (BCH) → USDT</option>
                                        <option value="xrp">Ripple (XRP) → USDT</option>
                                        <option value="ada">Cardano (ADA) → USDT</option>
                                        <option value="dot">Polkadot (DOT) → USDT</option>
                                    </optgroup>
                                </select>
                                <div class="form-text">Choose the cryptocurrency you want to send. All payments are
                                    processed through NOWPayments and converted to USDT.</div>
                                <div class="text-success mt-2">
                                    <small><i class="fas fa-info-circle me-1"></i><strong>USDT users:</strong>
                                        You can now send USDT directly! Choose your preferred network (TRC-20, ERC-20,
                                        BEP-20, etc.)</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="crypto_note" class="form-label">
                                    <i class="fas fa-sticky-note me-1"></i>
                                    Note (Optional)
                                </label>
                                <textarea class="form-control" id="crypto_note" name="note" placeholder="Optional note for this deposit"></textarea>
                            </div>

                            <div class="warning-alert">
                                <h6><strong>Important Information</strong></h6>
                                <ul class="mb-0 mt-1" id="cryptoWarningList">
                                    <li>You'll be redirected to NOWPayments to complete the crypto transaction</li>
                                    <li>Your deposit will be converted to USDT at current market rates</li>
                                    <li>A 1.8% fee is added to the crypto amount you need to send</li>
                                    <li>You will receive the full requested amount after conversion</li>
                                    <li>Processing time: 10-60 minutes depending on network</li>
                                </ul>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="fab fa-bitcoin me-2"></i>
                                Generate Crypto Address
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Crypto Deposit History
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount</th>
                                <th>Tax %</th>
                                <th>Tax Amount</th>
                                <th>Net Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Payment Address</th>
                                <th>Payment ID</th>
                                <th>Reference</th>
                                <th>Note</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deposits->where('payment_method', 'crypto') as $deposit)
                                <tr>
                                    <td>{{ $deposit->id }}</td>
                                    <td>${{ number_format($deposit->amount, 2) }}</td>
                                    <td>{{ $deposit->tax_percent }}%</td>
                                    <td>${{ number_format($deposit->tax_amount, 2) }}</td>
                                    <td>${{ number_format($deposit->net_amount, 2) }}</td>
                                    <td>
                                        @php
                                            $cryptoDisplay = str_replace(
                                                '_TO_USDT',
                                                '',
                                                $deposit->crypto_currency ?? 'CRYPTO',
                                            );
                                            $cryptoDisplay = str_replace('_', ' ', $cryptoDisplay);
                                        @endphp
                                        <span class="badge bg-info">
                                            <i class="fab fa-bitcoin me-1"></i>
                                            {{ strtoupper($cryptoDisplay) }} → USDT
                                        </span>
                                    </td>
                                    <td>
                                        @if ($deposit->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($deposit->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($deposit->status === 'processing')
                                            <span class="badge bg-info">Processing</span>
                                        @elseif($deposit->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($deposit->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($deposit->unique_address || $deposit->payment_address)
                                            <small>
                                                {{ substr($deposit->unique_address ?: $deposit->payment_address, 0, 6) }}...{{ substr($deposit->unique_address ?: $deposit->payment_address, -6) }}
                                                @if ($deposit->address_memo)
                                                    <br><code class="small">{{ $deposit->address_memo }}</code>
                                                @endif
                                            </small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $deposit->payment_id ? substr($deposit->payment_id, 0, 10) . '...' : 'N/A' }}
                                    </td>
                                    <td>{{ $deposit->reference }}</td>
                                    <td>{{ $deposit->note }}</td>
                                    <td>{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">
                                        No crypto deposits found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Crypto form validation
            const cryptoForm = document.getElementById('cryptoDepositForm');
            const cryptoAmountInput = document.getElementById('crypto_amount');
            const cryptoCurrencySelect = document.getElementById('crypto_currency');
            const cryptoFeeCalculation = document.getElementById('cryptoFeeCalculation');

            // Fee calculation on amount input
            if (cryptoAmountInput) {
                cryptoAmountInput.addEventListener('input', function() {
                    calculateCryptoFee();
                });
            }

            function calculateCryptoFee() {
                const amount = parseFloat(cryptoAmountInput.value) || 0;

                if (amount >= 100) {
                    // NOWPayments fee is 1.8% ADDED to the amount
                    const feePercent = 1.8;
                    const feeAmount = amount * (feePercent / 100);
                    const totalAmount = amount + feeAmount; // User pays MORE

                    document.getElementById('cryptoDisplayAmount').textContent = amount.toFixed(2);
                    document.getElementById('cryptoDisplayFee').textContent = feeAmount.toFixed(2);
                    document.getElementById('cryptoDisplayTotalAmount').textContent = totalAmount.toFixed(2);
                    document.getElementById('cryptoDisplayReceived').textContent = amount.toFixed(2);

                    cryptoFeeCalculation.style.display = 'block';
                } else {
                    cryptoFeeCalculation.style.display = 'none';
                }
            }

            if (cryptoForm && cryptoAmountInput) {
                cryptoForm.addEventListener('submit', function(e) {
                    const amount = parseFloat(cryptoAmountInput.value);

                    if (!amount || amount < 100) {
                        e.preventDefault();
                        alert('Minimum deposit amount is $100 USD equivalent.');
                        cryptoAmountInput.focus();
                        return;
                    }
                });
            }

            // Handle crypto currency selection change
            if (cryptoCurrencySelect) {
                cryptoCurrencySelect.addEventListener('change', function() {
                    updateCryptoForm();
                });

                function updateCryptoForm() {
                    const selectedCurrency = cryptoCurrencySelect.value;
                    const isUsdtVariant = ['usdt_bsc', 'usdt_trc', 'usdt_erc'].includes(selectedCurrency);

                    // Update form labels and help text
                    const amountLabel = document.querySelector('label[for="crypto_amount"]');
                    const amountInputGroup = cryptoAmountInput.parentElement;
                    const helpText = amountInputGroup.nextElementSibling;
                    const warningAlert = document.getElementById('cryptoWarningAlert');

                    if (isUsdtVariant) {
                        // For USDT variants, show USDT amount instead of USD
                        amountLabel.textContent = 'USDT Amount';
                        amountInputGroup.querySelector('.input-group-text').textContent = 'USDT';
                        helpText.textContent = 'Minimum deposit: 100 USDT (1:1 transfer, no conversion)';

                        // Update warning text for USDT variants
                        warningAlert.innerHTML = `
                            <small><strong>USDT Transfer Information:</strong></small>
                            <ul class="mb-0 mt-1 small">
                                <li>This is a direct USDT transfer (1:1 ratio)</li>
                                <li>No conversion needed - send exactly the amount you specify</li>
                                <li>You'll receive a specific address for the selected network</li>
                                <li>Processing time: 10-30 minutes depending on network confirmation</li>
                            </ul>
                        `;
                    } else {
                        // For other cryptocurrencies, show USD amount
                        amountLabel.textContent = 'Amount (USD Value)';
                        amountInputGroup.querySelector('.input-group-text').textContent = 'USD';
                        helpText.textContent = 'Minimum deposit: $100 USD equivalent';

                        // Update warning text for crypto conversions
                        warningAlert.innerHTML = `
                            <small><strong>Important:</strong></small>
                            <ul class="mb-0 mt-1 small">
                                <li>You'll receive a unique address for your selected cryptocurrency</li>
                                <li>Your deposit will be converted to USDT at current market rates</li>
                                <li>Conversion rate is locked when you send the transaction</li>
                                <li>Processing time: 10-60 minutes depending on network</li>
                            </ul>
                        `;
                    }
                }
            }
        });
    </script>

@endsection
