@extends('layouts.blankLayout')

@section('title', 'Account Recovery - Account Details')

@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">
        <!-- Logo -->
        <div class="card p-7">
            <!-- Title -->
            <div class="app-brand justify-content-center mb-6">
                <a href="{{ url('/') }}" class="app-brand-link gap-2">
                    <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20,"withbg"=>'fill: #ff6f61;'])</span>
                    <span class="app-brand-text demo text-heading fw-bold">Account Recovery</span>
                </a>
            </div>
            <!-- /Title -->

            <h4 class="mb-1">Account Details</h4>
            <p class="mb-6">Please provide your account information for verification</p>

            <form class="mb-6" action="{{ route('account-recovery.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="recovery_email" value="{{ session('recovery_email') }}">

                <div class="mb-4">
                    <label for="email" class="form-label">Email (Confirm)</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" placeholder="Re-enter your email address"
                           value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="deposit" class="form-label">Deposit Amount</label>
                    <input type="number" step="0.01" min="0" class="form-control @error('deposit') is-invalid @enderror"
                           id="deposit" name="deposit" placeholder="Enter your deposit amount"
                           value="{{ old('deposit') }}" required>
                    @error('deposit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="profit" class="form-label">Profit Amount</label>
                    <input type="number" step="0.01" min="0" class="form-control @error('profit') is-invalid @enderror"
                           id="profit" name="profit" placeholder="Enter your profit amount"
                           value="{{ old('profit') }}" required>
                    @error('profit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="referral_username" class="form-label">Referral Username (Optional)</label>
                    <input type="text" class="form-control @error('referral_username') is-invalid @enderror"
                           id="referral_username" name="referral_username" placeholder="Enter your referral's username"
                           value="{{ old('referral_username') }}">
                    @error('referral_username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary d-grid w-100" type="submit">Submit Recovery Request</button>
            </form>

            <div class="text-center">
                <a href="{{ route('account-recovery.email') }}" class="d-flex align-items-center justify-content-center">
                    <i class="icon-base ti ti-chevron-left"></i>
                    Back to email verification
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
