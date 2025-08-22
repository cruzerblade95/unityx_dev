@extends('user.layouts.layoutMaster')

@section('title', 'Deposit')

@section('vendor-style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
{{-- @include('components.coming-soon-overlay') --}}
<div class="container py-4 position-relative" style="z-index: 1;">
    <h2>Deposit</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row">
        <!-- Manual Deposit Form -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-wallet me-2"></i>
                        USDT Deposit
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('user.deposit.store') }}" id="usdtDepositForm">
                        @csrf
                        <div class="mb-3">
                            <label for="amount" class="form-label">USDT Amount</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required min="100">
                                <span class="input-group-text">USDT</span>
                            </div>
                            <div class="form-text">Minimum deposit: 100 USDT</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reference" class="form-label">Reference</label>
                            <input type="text" class="form-control" id="reference" name="reference" placeholder="Optional reference">
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
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('walletAddress')">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                    <code class="text-dark d-block" id="walletAddress">Loading...</code>
                                </div>
                                <div class="alert alert-success">
                                    <small><strong>✅ Reference code will be automatically included when you submit this form</strong></small>
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
                                <label for="payment_id" class="form-label">Payment ID / Transaction Hash <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="payment_id" name="payment_id" placeholder="Enter your transaction hash after sending USDT">
                                <div class="form-text">Please enter the transaction hash from your USDT transfer</div>
                            </div>
                            
                            <!-- Show auto-generated reference for user records -->
                            <div class="alert alert-light border">
                                <small class="text-muted"><strong>Auto-generated Reference (for your records):</strong></small>
                                <div class="d-flex justify-content-between align-items-center">
                                    <code class="text-primary" id="referencePreview">Generating...</code>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('referencePreview')">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">This reference will be automatically included in your deposit request</small>
                            </div>
                        </div>
                        
                        <!-- Hidden fields for auto-generated reference -->
                        <input type="hidden" id="auto_reference" name="auto_reference" value="">
                        <input type="hidden" id="deposit_address" name="deposit_address" value="">
                        
                        <button type="button" class="btn btn-primary w-100" id="showInstructionsBtn">
                            <i class="fas fa-arrow-right me-2"></i>
                            Continue to Payment
                        </button>
                        
                        <button type="submit" class="btn btn-success w-100" id="submitDepositBtn" style="display: none;">
                            <i class="fas fa-check me-2"></i>
                            Submit Deposit Request
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cryptocurrency Deposit Form -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fab fa-bitcoin me-2"></i>
                        Crypto to USDT Deposit
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <small><i class="fas fa-info-circle me-1"></i><strong>How it works:</strong> Send any supported cryptocurrency and it will be automatically converted to USDT in your wallet.</small>
                    </div>
                    
                    <form method="POST" action="{{ route('user.deposit.crypto') }}" id="cryptoDepositForm">
                        @csrf
                        <div class="mb-3">
                            <label for="crypto_amount" class="form-label">Amount (USD Value)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="crypto_amount" name="amount" required min="100">
                                <span class="input-group-text">USD</span>
                            </div>
                            <div class="form-text">Minimum deposit: $100 USD equivalent</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="crypto_currency" class="form-label">Send From Currency</label>
                            <select class="form-select" id="crypto_currency" name="crypto_currency" required>
                                <option value="">Select cryptocurrency to send</option>
                                <option value="btc">Bitcoin (BTC) → USDT</option>
                                <option value="eth">Ethereum (ETH) → USDT</option>
                                <option value="ltc">Litecoin (LTC) → USDT</option>
                                <option value="bch">Bitcoin Cash (BCH) → USDT</option>
                                <option value="xrp">Ripple (XRP) → USDT</option>
                                <option value="ada">Cardano (ADA) → USDT</option>
                                <option value="dot">Polkadot (DOT) → USDT</option>
                                <option value="bnb">Binance Coin (BNB) → USDT</option>
                            </select>
                            <div class="form-text">Choose the cryptocurrency you want to send. It will be converted to USDT.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="crypto_note" class="form-label">Note</label>
                            <textarea class="form-control" id="crypto_note" name="note" placeholder="Optional note for this deposit"></textarea>
                        </div>
                        
                        <div class="alert alert-warning">
                            <small><strong>Important:</strong></small>
                            <ul class="mb-0 mt-1 small">
                                <li>You'll receive a unique address for your selected cryptocurrency</li>
                                <li>Your deposit will be converted to USDT at current market rates</li>
                                <li>Conversion rate is locked when you send the transaction</li>
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
                Deposit History
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
                        @forelse($deposits as $deposit)
                            <tr>
                                <td>{{ $deposit->id }}</td>
                                <td>${{ number_format($deposit->amount, 2) }}</td>
                                <td>{{ $deposit->tax_percent }}%</td>
                                <td>${{ number_format($deposit->tax_amount, 2) }}</td>
                                <td>${{ number_format($deposit->net_amount, 2) }}</td>
                                <td>
                                    @if($deposit->payment_method === 'crypto')
                                        @php
                                            $cryptoDisplay = str_replace('_TO_USDT', '', $deposit->crypto_currency ?? 'CRYPTO');
                                            $cryptoDisplay = str_replace('_', ' ', $cryptoDisplay);
                                        @endphp
                                        <span class="badge bg-info">
                                            <i class="fab fa-bitcoin me-1"></i>
                                            {{ strtoupper($cryptoDisplay) }} → USDT
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-wallet me-1"></i>
                                            USDT Direct
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($deposit->status === 'completed')
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
                                    @if($deposit->unique_address || $deposit->payment_address)
                                        <small>
                                            {{ substr($deposit->unique_address ?: $deposit->payment_address, 0, 6) }}...{{ substr($deposit->unique_address ?: $deposit->payment_address, -6) }}
                                            @if($deposit->address_memo)
                                                <br><code class="small">{{ $deposit->address_memo }}</code>
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $deposit->payment_id ? substr($deposit->payment_id, 0, 10) . '...' : 'N/A' }}</td>
                                <td>{{ $deposit->reference }}</td>
                                <td>{{ $deposit->note }}</td>
                                <td>{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    No deposits found
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
    const paymentInstructions = document.getElementById('paymentInstructions');
    const paymentIdField = document.getElementById('paymentIdField');
    const amountInput = document.getElementById('amount');
    const paymentIdInput = document.getElementById('payment_id');
    const form = document.getElementById('usdtDepositForm');
    
    // Show payment instructions when continue button is clicked
    showInstructionsBtn.addEventListener('click', function() {
        const amount = amountInput.value;
        
        if (!amount || amount < 100) {
            alert('Please enter a valid USDT amount (minimum 100 USDT).');
            amountInput.focus();
            return;
        }
        
        // Show payment instructions
        paymentInstructions.style.display = 'block';
        
        // Hide continue button
        showInstructionsBtn.style.display = 'none';
        
        // Disable amount editing
        amountInput.readOnly = true;
        
        // Fetch unique wallet address
        fetchUniqueAddress(amount);
    });
    
    // Function to fetch unique wallet address
    function fetchUniqueAddress(amount) {
        const loadingDiv = document.getElementById('loadingAddress');
        const contentDiv = document.getElementById('addressContent');
        
        fetch('{{ route("user.deposit.unique-address") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
                document.getElementById('referencePreview').textContent = data.memo || 'AUTO_REF_' + Date.now();
                
                contentDiv.style.display = 'block';
                
                // Show payment ID field and submit button
                paymentIdField.style.display = 'block';
                submitDepositBtn.style.display = 'block';
                paymentIdInput.required = true;
                
            } else {
                loadingDiv.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Failed to generate address: ' + data.message + '</div>';
                
                // Show fallback address
                setTimeout(() => {
                    const fallbackRef = 'FALLBACK_' + Date.now();
                    document.getElementById('walletAddress').textContent = '0x8cAA7c9988E731F68598941503C41E00339de533';
                    document.getElementById('auto_reference').value = fallbackRef;
                    document.getElementById('deposit_address').value = '0x8cAA7c9988E731F68598941503C41E00339de533';
                    document.getElementById('referencePreview').textContent = fallbackRef;
                    loadingDiv.style.display = 'none';
                    contentDiv.style.display = 'block';
                    paymentIdField.style.display = 'block';
                    submitDepositBtn.style.display = 'block';
                    paymentIdInput.required = true;
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingDiv.innerHTML = '<div class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Network error. Using fallback address.</div>';
            
            // Show fallback address
            setTimeout(() => {
                const fallbackRef = 'FALLBACK_' + Date.now();
                document.getElementById('walletAddress').textContent = '0x8cAA7c9988E731F68598941503C41E00339de533';
                document.getElementById('auto_reference').value = fallbackRef;
                document.getElementById('deposit_address').value = '0x8cAA7c9988E731F68598941503C41E00339de533';
                document.getElementById('referencePreview').textContent = fallbackRef;
                loadingDiv.style.display = 'none';
                contentDiv.style.display = 'block';
                paymentIdField.style.display = 'block';
                submitDepositBtn.style.display = 'block';
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
    
    // Crypto form validation
    const cryptoForm = document.getElementById('cryptoDepositForm');
    const cryptoAmountInput = document.getElementById('crypto_amount');
    
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
                button.classList.add(elementId === 'memoCode' ? 'btn-outline-dark' : 'btn-outline-primary');
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
