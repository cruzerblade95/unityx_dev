@extends('layouts.layoutMaster')

@section('title', 'Account Recovery Status')

@php
$configData = Helper::appClasses();
@endphp

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
  <!-- Logo -->
  <a href="{{ url('/') }}" class="app-brand auth-cover-brand">
    <span class="app-brand-logo demo">
      <img src="{{ asset('assets/img/icons/logo-new-unityx.png') }}" alt="UnityX Logo" width="200">
    </span>
    <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
  </a>
  <!-- /Logo -->

  <div class="authentication-inner row m-0">
    <!-- Left Text -->
    <div class="d-none d-xl-flex col-xl-8 p-0">
      <div class="auth-cover-bg d-flex justify-content-center align-items-center">
        <img src="{{ asset('assets/img/illustrations/auth-forgot-password-illustration-' . $configData['theme'] . '.png') }}"
          alt="auth-forgot-password-cover" class="my-5 auth-illustration"
          data-app-light-img="illustrations/auth-forgot-password-illustration-light.png"
          data-app-dark-img="illustrations/auth-forgot-password-illustration-dark.png" />
        <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['theme'] . '.png') }}"
          alt="auth-forgot-password-cover" class="platform-bg"
          data-app-light-img="illustrations/bg-shape-image-light.png"
          data-app-dark-img="illustrations/bg-shape-image-dark.png" />
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Recovery Status -->
    <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
      <div class="w-px-400 mx-auto mt-12 pt-5">
        <h4 class="mb-1">Account Recovery Status 📋</h4>
        <p class="mb-6">Your recovery request has already been submitted!</p>

        <div class="card">
          <div class="card-body">
            <div class="mb-4">
              <h6 class="card-title mb-3">
                <i class="fas fa-info-circle me-2"></i>Recovery Request Details
              </h6>

              <div class="row mb-3">
                <div class="col-4">
                  <strong>Email:</strong>
                </div>
                <div class="col-8">
                  {{ $existingRecovery->email }}
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-4">
                  <strong>Deposit:</strong>
                </div>
                <div class="col-8">
                  ${{ number_format($existingRecovery->deposit, 2) }}
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-4">
                  <strong>Profit:</strong>
                </div>
                <div class="col-8">
                  ${{ number_format($existingRecovery->profit, 2) }}
                </div>
              </div>

              @if($existingRecovery->referral_username)
              <div class="row mb-3">
                <div class="col-4">
                  <strong>Referral:</strong>
                </div>
                <div class="col-8">
                  {{ $existingRecovery->referral_username }}
                </div>
              </div>
              @endif

              <div class="row mb-3">
                <div class="col-4">
                  <strong>Status:</strong>
                </div>
                <div class="col-8">
                  @if($existingRecovery->status == 'pending')
                    <span class="badge bg-warning">
                      <i class="fas fa-clock me-1"></i>Pending Review
                    </span>
                  @elseif($existingRecovery->status == 'approved')
                    <span class="badge bg-success">
                      <i class="fas fa-check me-1"></i>Approved
                    </span>
                  @else
                    <span class="badge bg-danger">
                      <i class="fas fa-times me-1"></i>Rejected
                    </span>
                  @endif
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-4">
                  <strong>Submitted:</strong>
                </div>
                <div class="col-8">
                  {{ $existingRecovery->created_at->format('M d, Y H:i') }}
                </div>
              </div>

              @if($existingRecovery->processed_at)
              <div class="row mb-3">
                <div class="col-4">
                  <strong>Processed:</strong>
                </div>
                <div class="col-8">
                  {{ $existingRecovery->processed_at->format('M d, Y H:i') }}
                </div>
              </div>
              @endif

              @if($existingRecovery->admin_notes)
              <div class="row mb-3">
                <div class="col-12">
                  <strong>Admin Notes:</strong>
                  <div class="mt-2 p-3 bg-light rounded">
                    {{ $existingRecovery->admin_notes }}
                  </div>
                </div>
              </div>
              @endif
            </div>

            @if($existingRecovery->status == 'pending')
            <div class="alert alert-info mb-4">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Your request is being reviewed.</strong><br>
              Please wait for admin approval. You will be notified once your request has been processed.
            </div>
            @elseif($existingRecovery->status == 'approved')
            <div class="alert alert-success mb-4">
              <i class="fas fa-check-circle me-2"></i>
              <strong>Your request has been approved!</strong><br>
              Your account has been recovered successfully. You can now log in to your account.
            </div>
            @else
            <div class="alert alert-danger mb-4">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <strong>Your request has been rejected.</strong><br>
              Please check the admin notes above for more information.
            </div>
            @endif

            <div class="d-grid gap-2">
              @if($existingRecovery->status == 'approved')
                <a href="{{ route('login') }}" class="btn btn-success">
                  <i class="fas fa-sign-in-alt me-1"></i>Go to Login
                </a>
              @elseif($existingRecovery->status == 'rejected')
                <form action="{{ route('account-recovery.reset') }}" method="POST" class="mb-2">
                  @csrf
                  <input type="hidden" name="email" value="{{ $existingRecovery->email }}">
                  <button type="submit" class="btn btn-primary w-100"
                          onclick="return confirm('This will allow you to submit a new recovery request. Continue?')">
                    <i class="fas fa-redo me-1"></i>Submit New Request
                  </button>
                </form>
              @endif

              <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                <i class="fas fa-home me-1"></i>Back to Home
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- /Recovery Status -->
  </div>
</div>
@endsection
