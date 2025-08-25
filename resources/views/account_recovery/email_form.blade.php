@extends('layouts.blankLayout')

@section('title', 'Account Recovery - Email Verification')

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

            <h4 class="mb-1">Email Verification</h4>
            <p class="mb-6">Enter your email address to start the account recovery process</p>

            <form class="mb-6" action="{{ route('account-recovery.validate-email') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" placeholder="Enter your email address"
                           value="{{ old('email') }}" autofocus required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary d-grid w-100" type="submit">Verify Email</button>
            </form>

            <div class="text-center">
                <a href="{{ route('user.login') }}" class="d-flex align-items-center justify-content-center">
                    <i class="icon-base ti ti-chevron-left"></i>
                    Back to login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
