@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Register')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
    <style>
        .captcha-container {
            display: flex;
            justify-content: center;
            margin: 1rem 0;
        }

        .g-recaptcha {
            transform: scale(0.9);
            transform-origin: 0 0;
        }

        @media (max-width: 576px) {
            .g-recaptcha {
                transform: scale(0.8);
            }
        }
    </style>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection

@section('page-script')
    @vite(['resources/assets/js/pages-auth.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add CAPTCHA callback function
            window.recaptchaCallback = function() {
                // Remove any previous CAPTCHA error styling
                const captchaContainer = document.querySelector('.captcha-container');
                const errorDiv = captchaContainer?.parentElement?.querySelector('.invalid-feedback');
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                }

                // Add visual feedback that CAPTCHA is completed
                if (captchaContainer) {
                    captchaContainer.style.border = '2px solid #28a745';
                    captchaContainer.style.borderRadius = '4px';
                    captchaContainer.style.padding = '2px';
                }
            };

            window.recaptchaExpiredCallback = function() {
                // Remove success styling when CAPTCHA expires
                const captchaContainer = document.querySelector('.captcha-container');
                if (captchaContainer) {
                    captchaContainer.style.border = '';
                    captchaContainer.style.borderRadius = '';
                    captchaContainer.style.padding = '';
                }
            };

            // Username validation - prevent spaces
            const usernameInput = document.getElementById('username');
            if (usernameInput) {
                usernameInput.addEventListener('input', function(e) {
                    // Remove spaces from the input
                    const value = e.target.value.replace(/\s/g, '');
                    if (value !== e.target.value) {
                        e.target.value = value;

                        // Show warning message
                        let errorDiv = e.target.parentElement.querySelector('.invalid-feedback');
                        if (!errorDiv) {
                            errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback';
                            e.target.parentElement.appendChild(errorDiv);
                        }
                        errorDiv.textContent = 'Username cannot contain spaces.';
                        errorDiv.style.display = 'block';
                        e.target.classList.add('is-invalid');

                        // Hide the error after 2 seconds
                        setTimeout(() => {
                            errorDiv.style.display = 'none';
                            e.target.classList.remove('is-invalid');
                        }, 2000);
                    }
                });

                // Prevent space key from being entered
                usernameInput.addEventListener('keydown', function(e) {
                    if (e.code === 'Space') {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
@endsection

@section('content')
    <div class="authentication-wrapper authentication-cover">
        <!-- Logo -->
        <a href="{{ url('/') }}" class="app-brand auth-cover-brand">
            <span class="app-brand-logo demo"><img src="{{ asset('assets/img/icons/logo-new-unityx.png') }}" alt="UnityX Logo"
                    width="200"></span>
            {{-- <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span> --}}
        </a>
        <!-- /Logo -->
        <div class="authentication-inner row m-0">
            <!-- /Left Text -->
            <div class="d-none d-xl-flex col-xl-8 p-0">
                <div class="auth-cover-bg d-flex justify-content-center align-items-center">
                    <img src="{{ asset('assets/img/illustrations/auth-register-illustration-' . $configData['theme'] . '.png') }}"
                        alt="auth-register-cover" class="my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-register-illustration-light.png"
                        data-app-dark-img="illustrations/auth-register-illustration-dark.png" />
                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['theme'] . '.png') }}"
                        alt="auth-register-cover" class="platform-bg"
                        data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png" />
                </div>
            </div>
            <!-- /Left Text -->

            <!-- Register -->
            <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
                <div class="w-px-400 mx-auto mt-12 pt-5">
                    <h4 class="mb-1">Adventure starts here 🚀</h4>
                    <p class="mb-6">Make your app management easy and fun!</p>

                    <form id="formAuthentication" class="mb-6" action="{{ route('user.register.submit') }}"
                        method="POST">
                        @csrf

                        {{-- Display account recovery email not found alert --}}
                        @if (session('email_not_found'))
                            <div class="alert alert-warning alert-dismissible" role="alert">
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                                <strong>Email not found!</strong> Please register first before continuing for account
                                recovery.
                            </div>
                        @endif

                        {{-- Display general errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-6 mb-6 form-control-validation">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" class="form-control @error('firstname') is-invalid @enderror"
                                    id="firstname" name="firstname" value="{{ old('firstname') }}" required autofocus />
                                @error('firstname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-6 form-control-validation">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" class="form-control @error('lastname') is-invalid @enderror"
                                    id="lastname" name="lastname" value="{{ old('lastname') }}" required />
                                @error('lastname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-6 form-control-validation">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror"
                                    id="username" name="username" value="{{ old('username') }}"
                                    placeholder="Enter username (no spaces allowed)" required />
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-6 form-control-validation">
                                <label for="referral_code" class="form-label">Referral Code</label>
                                <input type="text" class="form-control @error('referral_code') is-invalid @enderror"
                                    id="referral_code" name="referral_code" value="{{ old('referral_code') }}"
                                    placeholder="Eg. ASDWFCS" required />
                                @error('referral_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mb-6 form-control-validation">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email') }}"
                                    placeholder="Enter your email" />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-6 form-password-toggle form-control-validation">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                        aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer"><i
                                            class="icon-base ti tabler-eye-off"></i></span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-6 form-control-validation">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    id="password_confirmation" name="password_confirmation" required />
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- reCAPTCHA --}}
                        <div class="mb-6 form-control-validation">
                            <label class="form-label">Security Verification</label>
                            <div class="captcha-container">
                                <div class="g-recaptcha" data-sitekey="{{ config('captcha.sitekey') }}"
                                    data-callback="recaptchaCallback" data-expired-callback="recaptchaExpiredCallback">
                                </div>
                            </div>
                            @error('g-recaptcha-response')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-6 mt-8">
                            <div class="form-check mb-8 ms-2 form-control-validation">
                                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox"
                                    id="terms-conditions" name="terms" />
                                <label class="form-check-label" for="terms-conditions">
                                    I agree to
                                    <a href="javascript:void(0);">privacy policy & terms</a>
                                </label>
                                @error('terms')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <button class="btn btn-primary d-grid w-100">Sign up</button>
                    </form>

                    <p class="text-center">
                        <span>Already have an account?</span>
                        <a href="{{ url('user/login') }}">
                            <span>Sign in instead</span>
                        </a>
                    </p>

                    {{-- <div class="divider my-6">
          <div class="divider-text">or</div>
        </div> --}}

                    {{-- <div class="d-flex justify-content-center">
          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-facebook me-1_5">
            <i class="icon-base ti tabler-brand-facebook-filled icon-20px"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-twitter me-1_5">
            <i class="icon-base ti tabler-brand-twitter-filled icon-20px"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-github me-1_5">
            <i class="icon-base ti tabler-brand-github-filled icon-20px"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-google-plus">
            <i class="icon-base ti tabler-brand-google-filled icon-20px"></i>
          </a>
        </div> --}}
                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>
@endsection
