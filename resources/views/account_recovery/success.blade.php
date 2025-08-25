@extends('layouts.blankLayout')

@section('title', 'Account Recovery - Success')

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

            <div class="text-center">
                <div class="mb-4">
                    <i class="icon-base ti ti-check-circle text-success" style="font-size: 5rem;"></i>
                </div>

                <h4 class="mb-1">Recovery Request Submitted</h4>
                <p class="mb-6">Your account recovery request has been submitted successfully. Our administrators will review your request and contact you via email within 24-48 hours.</p>

                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading mb-2">What happens next?</h6>
                    <ul class="mb-0 text-start">
                        <li>Our team will verify the information you provided</li>
                        <li>You will receive an email notification about the status</li>
                        <li>If approved, you will receive instructions to access your account</li>
                    </ul>
                </div>

                <a href="{{ route('user.login') }}" class="btn btn-primary">
                    <i class="icon-base ti ti-arrow-left me-2"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
