@extends('user.layouts.layoutMaster')

@section('title', 'Marketplace Test')

@section('content')
<div class="container py-4">
    <h2>Marketplace Test</h2>
    
    <!-- Test Pending Verifications -->
    @if(isset($pendingVerifications) && $pendingVerifications->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Your Pending Verifications</h5>
        </div>
        <div class="card-body">
            @foreach($pendingVerifications as $verification)
            <div class="alert alert-info">
                <h6>{{ number_format($verification->amount, 2) }} USDT</h6>
                <p><strong>Listing ID:</strong> <code>{{ $verification->unique_id }}</code></p>
                <p><strong>Seller Username:</strong> {{ $verification->seller->username ?? 'Unknown' }}</p>
                <p><strong>Telegram Contact:</strong><br>
                    @if($verification->seller && $verification->seller->telegram_id)
                        @if(is_numeric($verification->seller->telegram_id))
                            <code>{{ $verification->seller->telegram_id }}</code>
                            <small class="text-muted">(Telegram ID - search manually)</small>
                        @else
                            <a href="https://t.me/{{ $verification->seller->telegram_id }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-telegram me-2"></i>
                                {{ '@' . $verification->seller->telegram_id }}
                            </a>
                            <small class="text-muted d-block">(Click to open in Telegram)</small>
                        @endif
                    @else
                        <span class="text-muted">Not provided</span>
                    @endif
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="alert alert-info">No pending verifications</div>
    @endif
    
    <!-- Test Verification Requests -->
    @if(isset($verificationRequests) && $verificationRequests->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Verification Requests</h5>
        </div>
        <div class="card-body">
            @foreach($verificationRequests as $request)
            <div class="alert alert-light">
                <h6>{{ number_format($request->amount, 2) }} USDT</h6>
                <p><strong>Listing ID:</strong> <code>{{ $request->unique_id }}</code></p>
                <p><strong>Buyer:</strong> {{ $request->buyer->username ?? 'Unknown' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="alert alert-warning">No verification requests</div>
    @endif
</div>
@endsection
