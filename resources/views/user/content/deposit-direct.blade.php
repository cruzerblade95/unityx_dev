@extends('user.layouts.layoutMaster')

@section('title', 'Deposit Direct')

<style>
    /* Enhanced deposit-direct form styling - Crypto green theme like withdrawal */
    .card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid #28a745;
        user-select: none;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, var(--bs-secondary-bg) 100%);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.12);
    }
    
    .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #28a745, #20c997);
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2);
        border-color: #20c997;
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, rgba(32, 201, 151, 0.03) 100%);
    }
    
    .card-header {
        background: linear-gradient(135deg, #6f42c1 20%, #0d6efd 100%) !important;
        color: white !important;
        text-align: center !important;
        font-weight: 700 !important;
        font-size: 1.1rem !important;
        padding: 0.5rem 1rem !important;
        border: none !important;
        position: relative;
        z-index: 1;
    }
    
    .card-header h5 {
        margin: 0;
        font-size: 1.1rem;
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
        border-color: #28a745;
        box-shadow: 0 0 0 0.3rem rgba(40, 167, 69, 0.15);
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
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        color: white;
    }
    
    .btn-success {
        background: linear-gradient(135deg, var(--bs-success) 0%, rgba(var(--bs-success-rgb), 0.8) 100%);
        border: none;
    }
    
    .btn-success:hover {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.9) 0%, var(--bs-success) 100%);
    }
    
    .btn-outline-secondary {
        border: 2px solid var(--bs-secondary);
        color: var(--bs-secondary);
        font-weight: 600;
        background: var(--bs-body-bg);
    }
    
    .btn-outline-secondary:hover {
        background: var(--bs-secondary);
        color: var(--bs-white);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(var(--bs-secondary-rgb), 0.3);
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
        background: linear-gradient(90deg, var(--bs-info), #20c997);
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
    
    .alert-light {
        background: linear-gradient(135deg, var(--bs-light-bg-subtle, var(--bs-secondary-bg)) 0%, rgba(var(--bs-light-rgb, var(--bs-secondary-rgb)), 0.1) 100%);
        border-color: var(--bs-light, var(--bs-secondary));
        color: var(--bs-emphasis-color);
        position: relative;
        overflow: hidden;
        border: 3px solid var(--bs-secondary);
        box-shadow: 0 4px 15px rgba(var(--bs-secondary-rgb), 0.15);
    }
    
    .alert-light::before {
        background: linear-gradient(90deg, var(--bs-secondary), #20c997);
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
    
    .payment-info {
        background: linear-gradient(135deg, var(--bs-secondary-bg) 0%, var(--bs-tertiary-bg) 100%);
        border: 3px solid #28a745;
        border-radius: 12px;
        padding: 1.5rem;
        margin: 1.5rem 0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.12);
    }
    
    .payment-info::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #28a745, #20c997);
        transition: all 0.3s ease;
    }
    
    .payment-info:hover {
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.18);
        transform: translateY(-2px);
    }
    
    .payment-info h6 {
        color: #28a745;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    
    .fee-display {
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
    
    .fee-display::before {
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
    
    /* Better spacing for older users */
    .form-group {
        margin-bottom: 2rem;
    }
    
    .mb-3 {
        margin-bottom: 2rem !important;
    }
    
    .d-grid {
        margin-top: 2rem;
    }
    
    /* Code and address styling */
    code {
        background: var(--bs-secondary-bg);
        color: #28a745;
        padding: 0.5rem;
        border-radius: 6px;
        font-size: 0.9rem;
        border: 1px solid var(--bs-border-color);
        position: relative;
        overflow: hidden;
    }
    
    code::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #28a745, #20c997);
    }
    
    /* Table styling for history */
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        background-color: var(--bs-secondary-bg);
        border-bottom: 2px solid var(--bs-border-color);
        font-weight: 600;
        color: var(--bs-emphasis-color);
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: middle;
        border-bottom: 1px solid var(--bs-border-color-translucent);
        color: var(--bs-body-color);
    }
    
    .table-striped > tbody > tr:nth-of-type(odd) > td {
        background-color: var(--bs-tertiary-bg);
    }
    
    .table tbody tr:hover {
        background-color: var(--bs-secondary-bg);
        transition: background-color 0.2s ease;
    }
    
    /* Copy button styling */
    .btn-sm {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    
    .btn-sm:hover {
        transform: translateY(-1px);
    }

    /* Dark theme specific adjustments */
    [data-bs-theme="dark"] .card {
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, rgba(var(--bs-secondary-rgb), 0.3) 100%);
    }
    
    [data-bs-theme="dark"] .card:hover {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.15) 0%, rgba(var(--bs-primary-rgb), 0.05) 100%);
    }
    
    [data-bs-theme="dark"] .payment-info {
        background: linear-gradient(135deg, rgba(var(--bs-secondary-rgb), 0.3) 0%, rgba(var(--bs-tertiary-rgb), 0.3) 100%);
    }
    
    [data-bs-theme="dark"] .fee-display {
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
    
    [data-bs-theme="dark"] .alert-light {
        background: linear-gradient(135deg, rgba(108, 117, 125, 0.15) 0%, rgba(108, 117, 125, 0.05) 100%);
    }
    
    [data-bs-theme="dark"] code {
        background: rgba(var(--bs-secondary-rgb), 0.3);
    }
</style>

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    {{-- @include('components.coming-soon-overlay') --}}
    <div class="container py-4 position-relative" style="z-index: 1;">
        <h2>Deposit Direct</h2>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="row">
            <!-- Manual Deposit Form -->
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-wallet me-2"></i>
                            USDT Direct Deposit
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('user.deposit.store') }}" id="usdtDepositForm">
                            @csrf
                            <div class="mb-3">
                                <label for="amount" class="form-label">USDT Amount</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                                        required min="100">
                                    <span class="input-group-text">USDT</span>
                                </div>
                                <div class="form-text">Minimum deposit: 100 USDT</div>

                                <!-- Fee Calculation Display -->
                                <div id="feeCalculation" class="mt-2" style="display: none;">
                                    <div class="card card-body bg-light border-0">
                                        <div class="row g-2 small">
                                            <div class="col-6">
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Deposit Amount:</span>
                                                    <span class="fw-medium">$<span id="displayAmount">0.00</span></span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Processing Fee (2.5%):</span>
                                                    <span class="text-warning fw-medium">-$<span
                                                            id="displayFee">0.00</span></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between">
                                                    <span class="fw-semibold">You will receive:</span>
                                                    <span class="fw-semibold text-success">$<span
                                                            id="displayNetAmount">0.00</span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="reference" class="form-label">Reference</label>
                                <input type="text" class="form-control" id="reference" name="reference"
                                    placeholder="Optional reference">
                            </div>

                            <div class="mb-3">
                                <label for="note" class="form-label">Note</label>
                                <textarea class="form-control" id="note" name="note" placeholder="Optional note for this deposit"></textarea>
                            </div>

                            <!-- Payment Instructions (Initially Hidden) -->
                            <div id="paymentInstructions" class="alert alert-info" style="display: none;">
                                <h6><i class="fas fa-info-circle me-2"></i>Payment Instructions</h6>
                                <div id="loadingAddress" class="text-center py-3">
                                    <i class="fas fa-spinner fa-spin me-2"></i>Generating unique wallet address...
                                </div>
                                <div id="addressContent" style="display: none;">
                                    <p><strong>Please transfer your USDT to the following wallet address:</strong></p>
                                    <div class="bg-light p-3 rounded mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">Wallet Address:</small>
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="copyToClipboard('walletAddress')">
                                                <i class="fas fa-copy"></i> Copy
                                            </button>
                                        </div>
                                        <code class="text-dark d-block" id="walletAddress">Loading...</code>
                                    </div>
                                    <div class="alert alert-success">
                                        <small><strong>✅ Reference code will be automatically included when you submit this
                                                form</strong></small>
                                        <ul class="mb-0 mt-1 small">
                                            <li>No need to manually add memo or reference</li>
                                            <li>Just send USDT to the address above</li>
                                            <li>Enter your transaction hash below and submit</li>
                                        </ul>
                                    </div>
                                    <div class="alert alert-warning">
                                        <small><strong>Important Security Notes:</strong></small>
                                        <ul class="mb-0 mt-1 small">
                                            <li>This address is unique to your deposit and expires in 24 hours</li>
                                            <li>Only send USDT to this address</li>
                                            <li>Sending other cryptocurrencies may result in permanent loss</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment ID Field (Initially Hidden) -->
                            <div id="paymentIdField" style="display: none;">
                                <div class="mb-3">
                                    <label for="payment_id" class="form-label">Payment ID / Transaction Hash <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="payment_id" name="payment_id"
                                        placeholder="Enter your transaction hash after sending USDT">
                                    <div class="form-text">Please enter the transaction hash from your USDT transfer</div>
                                </div>

                                <!-- Show auto-generated reference for user records -->
                                <div class="alert alert-light border">
                                    <small class="text-muted"><strong>Auto-generated Reference (for your
                                            records):</strong></small>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <code class="text-primary" id="referencePreview">Generating...</code>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="copyToClipboard('referencePreview')">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1">This reference will be automatically included in
                                        your deposit request</small>
                                </div>
                            </div>

                            <!-- Hidden fields for auto-generated reference -->
                            <input type="hidden" id="auto_reference" name="auto_reference" value="">
                            <input type="hidden" id="deposit_address" name="deposit_address" value="">

                            <button type="button" class="btn btn-primary w-100" id="showInstructionsBtn">
                                <i class="fas fa-arrow-right me-2"></i>
                                Continue to Payment
                            </button>

                            <div id="paymentButtons" style="display: none;">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary flex-fill"
                                        id="cancelPaymentBtn">
                                        <i class="fas fa-times me-2"></i>
                                        Cancel
                                    </button>
                                    <button type="submit" class="btn btn-success flex-fill" id="submitDepositBtn">
                                        <i class="fas fa-check me-2"></i>
                                        Submit Deposit Request
                                    </button>
                                </div>
                            </div>
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
                    Direct Deposit History
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
                                <th>Status</th>
                                <th>Payment Address</th>
                                <th>Payment ID</th>
                                <th>Reference</th>
                                <th>Note</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deposits->where('payment_method', '!=', 'crypto') as $deposit)
                                <tr>
                                    <td>{{ $deposit->id }}</td>
                                    <td>${{ number_format($deposit->amount, 2) }}</td>
                                    <td>{{ $deposit->tax_percent }}%</td>
                                    <td>${{ number_format($deposit->tax_amount, 2) }}</td>
                                    <td>${{ number_format($deposit->net_amount, 2) }}</td>
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
                                    <td colspan="11" class="text-center text-muted py-4">
                                        No direct deposits found
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
            const showInstructionsBtn = document.getElementById('showInstructionsBtn');
            const submitDepositBtn = document.getElementById('submitDepositBtn');
            const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
            const paymentButtons = document.getElementById('paymentButtons');
            const paymentInstructions = document.getElementById('paymentInstructions');
            const paymentIdField = document.getElementById('paymentIdField');
            const amountInput = document.getElementById('amount');
            const referenceInput = document.getElementById('reference');
            const noteInput = document.getElementById('note');
            const paymentIdInput = document.getElementById('payment_id');
            const form = document.getElementById('usdtDepositForm');
            const feeCalculation = document.getElementById('feeCalculation');

            // Fee calculation on amount input
            amountInput.addEventListener('input', function() {
                calculateFee();
            });

            function calculateFee() {
                const amount = parseFloat(amountInput.value) || 0;

                if (amount >= 100) {
                    const feePercent = 2.5;
                    const feeAmount = amount * (feePercent / 100);
                    const netAmount = amount - feeAmount;

                    document.getElementById('displayAmount').textContent = amount.toFixed(2);
                    document.getElementById('displayFee').textContent = feeAmount.toFixed(2);
                    document.getElementById('displayNetAmount').textContent = netAmount.toFixed(2);

                    feeCalculation.style.display = 'block';
                } else {
                    feeCalculation.style.display = 'none';
                }
            }

            // Show payment instructions when continue button is clicked
            showInstructionsBtn.addEventListener('click', function() {
                const amount = amountInput.value;

                if (!amount || amount < 100) {
                    alert('Please enter a valid USDT amount (minimum 100 USDT).');
                    amountInput.focus();
                    return;
                }

                // Lock the form fields
                lockFormFields();

                // Show payment instructions
                paymentInstructions.style.display = 'block';

                // Hide continue button, show payment buttons
                showInstructionsBtn.style.display = 'none';
                paymentButtons.style.display = 'block';

                // Fetch unique wallet address
                fetchUniqueAddress(amount);
            });

            // Cancel payment and reset form
            cancelPaymentBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel? This will reset the form.')) {
                    resetForm();
                }
            });

            function lockFormFields() {
                amountInput.readOnly = true;
                referenceInput.readOnly = true;
                noteInput.readOnly = true;

                // Add visual indication that fields are locked
                amountInput.classList.add('bg-light');
                referenceInput.classList.add('bg-light');
                noteInput.classList.add('bg-light');
            }

            function unlockFormFields() {
                amountInput.readOnly = false;
                referenceInput.readOnly = false;
                noteInput.readOnly = false;

                // Remove visual indication
                amountInput.classList.remove('bg-light');
                referenceInput.classList.remove('bg-light');
                noteInput.classList.remove('bg-light');
            }

            function resetForm() {
                // Reset form fields
                form.reset();

                // Unlock fields
                unlockFormFields();

                // Hide payment sections
                paymentInstructions.style.display = 'none';
                paymentIdField.style.display = 'none';
                paymentButtons.style.display = 'none';
                feeCalculation.style.display = 'none';

                // Show continue button
                showInstructionsBtn.style.display = 'block';

                // Reset payment ID requirement
                paymentIdInput.required = false;

                // Clear hidden fields
                document.getElementById('auto_reference').value = '';
                document.getElementById('deposit_address').value = '';

                // Reset loading states
                document.getElementById('loadingAddress').style.display = 'block';
                document.getElementById('addressContent').style.display = 'none';
                document.getElementById('loadingAddress').innerHTML =
                    '<i class="fas fa-spinner fa-spin me-2"></i>Generating unique wallet address...';
            }

            // Function to fetch unique wallet address
            function fetchUniqueAddress(amount) {
                const loadingDiv = document.getElementById('loadingAddress');
                const contentDiv = document.getElementById('addressContent');

                fetch('{{ route('user.deposit.unique-address') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            amount: amount
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        loadingDiv.style.display = 'none';

                        if (data.success) {
                            // Update address and store auto-generated reference
                            document.getElementById('walletAddress').textContent = data.address;
                            document.getElementById('auto_reference').value = data.memo || '';
                            document.getElementById('deposit_address').value = data.address;
                            document.getElementById('referencePreview').textContent = data.memo || 'AUTO_REF_' +
                                Date.now();

                            contentDiv.style.display = 'block';

                            // Show payment ID field and submit button
                            paymentIdField.style.display = 'block';
                            paymentIdInput.required = true;

                        } else {
                            loadingDiv.innerHTML =
                                '<div class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Failed to generate address: ' +
                                data.message + '</div>';

                            // Show fallback address
                            setTimeout(() => {
                                const fallbackRef = 'FALLBACK_' + Date.now();
                                document.getElementById('walletAddress').textContent =
                                    '0x8cAA7c9988E731F68598941503C41E00339de533';
                                document.getElementById('auto_reference').value = fallbackRef;
                                document.getElementById('deposit_address').value =
                                    '0x8cAA7c9988E731F68598941503C41E00339de533';
                                document.getElementById('referencePreview').textContent = fallbackRef;
                                loadingDiv.style.display = 'none';
                                contentDiv.style.display = 'block';
                                paymentIdField.style.display = 'block';
                                paymentIdInput.required = true;
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loadingDiv.innerHTML =
                            '<div class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Network error. Using fallback address.</div>';

                        // Show fallback address
                        setTimeout(() => {
                            const fallbackRef = 'FALLBACK_' + Date.now();
                            document.getElementById('walletAddress').textContent =
                                '0x8cAA7c9988E731F68598941503C41E00339de533';
                            document.getElementById('auto_reference').value = fallbackRef;
                            document.getElementById('deposit_address').value =
                                '0x8cAA7c9988E731F68598941503C41E00339de533';
                            document.getElementById('referencePreview').textContent = fallbackRef;
                            loadingDiv.style.display = 'none';
                            contentDiv.style.display = 'block';
                            paymentIdField.style.display = 'block';
                            paymentIdInput.required = true;
                        }, 2000);
                    });
            }

            // Form validation before submit
            form.addEventListener('submit', function(e) {
                const paymentId = paymentIdInput.value.trim();

                if (!paymentId) {
                    e.preventDefault();
                    alert('Please enter your payment ID / transaction hash.');
                    paymentIdInput.focus();
                    return;
                }

                if (paymentId.length < 10) {
                    e.preventDefault();
                    alert('Please enter a valid transaction hash.');
                    paymentIdInput.focus();
                    return;
                }
            });
        });

        // Function to copy text to clipboard
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    // Show success message
                    const button = event.target.closest('button');
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    button.classList.remove('btn-outline-primary', 'btn-outline-dark');
                    button.classList.add('btn-success');

                    setTimeout(function() {
                        button.innerHTML = originalText;
                        button.classList.remove('btn-success');
                        button.classList.add(elementId === 'memoCode' ? 'btn-outline-dark' :
                            'btn-outline-primary');
                    }, 2000);
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Copied to clipboard: ' + text);
            }
        }
    </script>

@endsection
