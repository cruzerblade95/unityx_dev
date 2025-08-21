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
            <h5 class="card-title">Available Balance</h5>
            <h3 class="text-success">${{ number_format($walletBalance, 2) }}</h3>
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
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       id="amount" 
                                       name="amount" 
                                       value="{{ old('amount') }}"
                                       max="{{ $walletBalance }}"
                                       required>
                                <div class="form-text">Maximum: ${{ number_format($walletBalance, 2) }}</div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('bank_name') is-invalid @enderror" 
                                       id="bank_name" 
                                       name="bank_name" 
                                       value="{{ old('bank_name') }}"
                                       required>
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('account_number') is-invalid @enderror" 
                                       id="account_number" 
                                       name="account_number" 
                                       value="{{ old('account_number') }}"
                                       required>
                                @error('account_number')
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
                            <li>Service fee: 2.5% will be deducted from your withdrawal amount</li>
                            <li>Processing time: 1-3 business days</li>
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
                                <th>Tax (2.5%)</th>
                                <th>Net Amount</th>
                                <th>Bank Details</th>
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
                                                <strong>Bank:</strong> {{ $withdrawal->bank_name }}<br>
                                                <strong>Account:</strong> {{ substr($withdrawal->account_number, 0, 4) }}****{{ substr($withdrawal->account_number, -4) }}
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
    
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            const amount = parseFloat(this.value);
            if (amount > maxBalance) {
                this.value = maxBalance;
            }
        });
    }
});
</script>
@endpush
@endsection
