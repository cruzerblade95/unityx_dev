@extends('user.layouts.layoutMaster')

@section('title', 'Deposit')

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
                        <i class="ti ti-credit-card me-2"></i>
                        Manual Deposit
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('user.deposit.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (USD)</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="reference" class="form-label">Reference</label>
                            <input type="text" class="form-control" id="reference" name="reference">
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>
                            <textarea class="form-control" id="note" name="note"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Create Manual Deposit</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cryptocurrency Deposit Form -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-currency-bitcoin me-2"></i>
                        Cryptocurrency Deposit
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('user.deposit.crypto') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="crypto_amount" class="form-label">Amount (USD)</label>
                            <input type="number" step="0.01" class="form-control" id="crypto_amount" name="amount" required>
                        </div>
                        <div class="mb-3">
                            <label for="crypto_currency" class="form-label">Cryptocurrency</label>
                            <select class="form-select" id="crypto_currency" name="crypto_currency" required>
                                <option value="">Select Cryptocurrency</option>
                                @if(isset($cryptoCurrencies) && is_array($cryptoCurrencies))
                                    @foreach($cryptoCurrencies as $currency)
                                        <option value="{{ $currency }}">{{ strtoupper($currency) }}</option>
                                    @endforeach
                                @else
                                    <option value="btc">Bitcoin (BTC)</option>
                                    <option value="eth">Ethereum (ETH)</option>
                                    <option value="ltc">Litecoin (LTC)</option>
                                    <option value="bch">Bitcoin Cash (BCH)</option>
                                    <option value="xrp">Ripple (XRP)</option>
                                @endif
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="crypto_note" class="form-label">Note</label>
                            <textarea class="form-control" id="crypto_note" name="note" placeholder="Optional note for this deposit"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="ti ti-currency-bitcoin me-2"></i>
                            Pay with Crypto
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
                <i class="ti ti-list me-2"></i>
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
                                <td>${{ number_format($deposit->tax_amount, 2) }}</td>
                                <td>${{ number_format($deposit->net_amount, 2) }}</td>
                                <td>
                                    @if($deposit->payment_method === 'crypto')
                                        <span class="badge bg-info">
                                            <i class="ti ti-currency-bitcoin me-1"></i>
                                            Crypto
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="ti ti-credit-card me-1"></i>
                                            Manual
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
                                <td>{{ $deposit->reference }}</td>
                                <td>{{ $deposit->note }}</td>
                                <td>{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
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
@endsection
