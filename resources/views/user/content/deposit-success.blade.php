@extends('user.layouts.layoutMaster')

@section('title', 'Payment Success')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="ti ti-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="text-success mb-3">Payment Successful!</h2>
                    <p class="text-muted mb-4">
                        Your cryptocurrency payment has been received and is being processed. 
                        The funds will be added to your wallet once the transaction is confirmed on the blockchain.
                    </p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="{{ route('user.deposit.index') }}" class="btn btn-primary">
                            <i class="ti ti-arrow-left me-2"></i>
                            Back to Deposits
                        </a>
                        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-primary">
                            <i class="ti ti-dashboard me-2"></i>
                            Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
