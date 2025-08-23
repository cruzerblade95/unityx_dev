@extends('user.layouts.layoutMaster')

@section('title', 'Crypto Payment Details')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if (isset($deposit) && $deposit)
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="fas fa-wallet text-warning" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="text-warning mb-3">Payment Details</h2>
                            <p class="text-muted mb-4">
                                Please send the exact amount of cryptocurrency to the address below to complete your
                                deposit.
                            </p>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-body">
                                            <h6 class="text-muted">Amount to Send</h6>
                                            <h4 class="text-primary">
                                                {{ $deposit->crypto_amount ?? 'Calculating...' }}
                                                @php
                                                    // Clean up currency display for USDT variants
                                                    $displayCurrency = $deposit->crypto_currency;
                                                    if (str_contains($displayCurrency, '_TO_USDT')) {
                                                        $displayCurrency = str_replace(
                                                            '_TO_USDT',
                                                            '',
                                                            $displayCurrency,
                                                        );
                                                        $displayCurrency = str_replace('_', ' ', $displayCurrency);
                                                    }
                                                    $displayCurrency = strtoupper($displayCurrency);
                                                @endphp
                                                {{ $displayCurrency }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-body">
                                            <h6 class="text-muted">USD Amount</h6>
                                            <h4 class="text-secondary">${{ number_format($deposit->amount, 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($deposit->payment_address)
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">Payment Address</h6>
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <code class="bg-white p-2 rounded border flex-grow-1 text-break">
                                                {{ $deposit->payment_address }}
                                            </code>
                                            <button class="btn btn-outline-primary btn-sm"
                                                onclick="copyToClipboard('{{ $deposit->payment_address }}')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($deposit->address_memo)
                                <div class="card bg-light mb-4">
                                    <div class="card-body">
                                        <h6 class="text-muted mb-2">Reference / Memo</h6>
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <code class="bg-white p-2 rounded border flex-grow-1 text-break">
                                                {{ $deposit->address_memo }}
                                            </code>
                                            <button class="btn btn-outline-primary btn-sm"
                                                onclick="copyToClipboard('{{ $deposit->address_memo }}')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Use this reference when sending your transaction for faster processing
                                        </small>
                                    </div>
                                </div>
                            @endif

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Important:</strong> Send only
                                @php
                                    $alertCurrency = $deposit->crypto_currency;
                                    if (str_contains($alertCurrency, '_TO_USDT')) {
                                        $alertCurrency = str_replace('_TO_USDT', '', $alertCurrency);
                                        $alertCurrency = str_replace('_', ' ', $alertCurrency);
                                    }
                                    $alertCurrency = strtoupper($alertCurrency);
                                @endphp
                                {{ $alertCurrency }} to this address.
                                Sending any other cryptocurrency will result in permanent loss.
                            </div>

                            <div class="d-flex gap-3 justify-content-center">
                                <a href="{{ route('user.deposit.index') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back to Deposits
                                </a>
                                <a href="{{ route('user.dashboard') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Go to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="text-success mb-3">Payment Initiated!</h2>
                            <p class="text-muted mb-4">
                                Your cryptocurrency payment has been initiated. Please check your deposit history for
                                payment details.
                            </p>
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="{{ route('user.deposit.index') }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back to Deposits
                                </a>
                                <a href="{{ route('user.dashboard') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    Go to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // You can add a toast notification here
                alert('Address copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
@endsection
