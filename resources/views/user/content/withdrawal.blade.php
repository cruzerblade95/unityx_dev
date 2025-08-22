{{-- @include('components.coming-soon-overlay') --}}
@extends('user.layouts.layoutMaster')

@section('title', 'Withdrawal')

@section('content')
<div class="container py-4">
    <h2>Withdrawal</h2>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Wallet Balance Display -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Available Deposit Balance</h5>
            <h3 class="text-success">${{ number_format($walletBalance, 2) }}</h3>
            <small class="text-muted">Only deposit balance is available for withdrawal</small>
        </div>
    </div>

    <!-- KYC Status Check -->
    @if(!$isKycApproved)
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">KYC Verification Required!</h4>
            <p>Your KYC verification must be approved before you can make withdrawal requests.</p>
            <hr>
            <p class="mb-0">
                <a href="{{ route('user.kyc.index') }}" class="btn btn-warning">Complete KYC Verification</a>
            </p>
        </div>
    @endif

    <!-- Withdrawal Form -->
    <div class="card">
        <div class="card-header">
            <h4>Create Withdrawal Request</h4>
        </div>
        <div class="card-body">
            @if($isKycApproved)
                <form method="POST" action="{{ route('user.withdrawal.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Withdrawal Amount <span class="text-danger">*</span></label>
                                <input type="number" 
                                       step="0.01" 
                                       min="100"
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" 
                                       name="amount" 
                                       value="{{ old('amount') }}"
                                       max="{{ $walletBalance }}"
                                       required>
                                <div class="form-text">Minimum: $100.00 | Maximum: ${{ number_format($walletBalance, 2) }}</div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                                <select class="form-control @error('currency') is-invalid @enderror" 
                                        id="currency" 
                                        name="currency" 
                                        required>
                                    <option value="">Select Currency</option>
                                    <option value="USDT" {{ old('currency') == 'USDT' ? 'selected' : '' }}>USDT</option>
                                    <option value="USDTBEP20" {{ old('currency') == 'USDTBEP20' ? 'selected' : '' }}>USDT BEP20</option>
                                    <option value="USDC" {{ old('currency') == 'USDC' ? 'selected' : '' }}>USDC</option>
                                    <option value="FDUSD" {{ old('currency') == 'FDUSD' ? 'selected' : '' }}>FDUSD</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="wallet_address" class="form-label">Wallet Address <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('wallet_address') is-invalid @enderror" 
                                       id="wallet_address" 
                                       name="wallet_address" 
                                       value="{{ old('wallet_address') }}"
                                       placeholder="Enter your wallet address"
                                       required>
                                <div class="form-text">Please ensure your wallet address is correct for the selected currency</div>
                                @error('wallet_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference" class="form-label">Reference (Optional)</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="reference" 
                                       name="reference" 
                                       value="{{ old('reference') }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="note" class="form-label">Note (Optional)</label>
                        <textarea class="form-control" 
                                  id="note" 
                                  name="note" 
                                  rows="3">{{ old('note') }}</textarea>
                    </div>
                    
                    <!-- Fee Information -->
                    <div class="alert alert-info">
                        <strong>Fee Information:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Service fee: 4.75% will be deducted from your withdrawal amount</li>
                            <li>Processing time: 1-3 business days</li>
                            <li>Minimum withdrawal: $100 USD</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Withdrawal Request</button>
                </form>
            @else
                <p class="text-muted">Please complete your KYC verification to create withdrawal requests.</p>
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
            @if($withdrawals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount</th>
                                <th>Fee (4.75%)</th>
                                <th>Net Amount</th>
                                <th>Crypto Details</th>
                                <th>Status</th>
                                <th>Reference</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($withdrawals as $withdrawal)
                                <tr>
                                    <td>#{{ $withdrawal->id }}</td>
                                    <td>${{ number_format($withdrawal->amount, 2) }}</td>
                                    <td>${{ number_format($withdrawal->tax_amount, 2) }}</td>
                                    <td>${{ number_format($withdrawal->net_amount, 2) }}</td>
                                    <td>
                                        @if($withdrawal->bank_name && $withdrawal->account_number)
                                            <small>
                                                <strong>Currency:</strong> {{ $withdrawal->bank_name }}<br>
                                                <strong>Wallet:</strong> {{ substr($withdrawal->account_number, 0, 6) }}...{{ substr($withdrawal->account_number, -6) }}
                                            </small>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($withdrawal->status) {
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                'completed' => 'success',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst($withdrawal->status) }}</span>
                                    </td>
                                    <td>{{ $withdrawal->reference ?? 'N/A' }}</td>
                                    <td>{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
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
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const maxBalance = {{ $walletBalance }};
    const minAmount = 100;
    
    if (amountInput) {
        // Set minimum attribute
        amountInput.setAttribute('min', minAmount);
        
        amountInput.addEventListener('input', function() {
            const amount = parseFloat(this.value);
            if (amount > maxBalance) {
                this.value = maxBalance;
            }
            if (amount < minAmount && amount > 0) {
                this.setCustomValidity('Minimum withdrawal amount is $100.00');
            } else {
                this.setCustomValidity('');
            }
        });
    }
});
</script>
@endpush
@endsection
