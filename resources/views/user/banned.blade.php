@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Account Banned')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
    <style>
        .banned-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .banned-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
            max-width: 600px;
            width: 100%;
            margin: 2rem;
            overflow: hidden;
        }

        .banned-header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .banned-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .banned-content {
            padding: 2rem;
        }

        .reason-box {
            background: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 1rem;
            margin: 1.5rem 0;
            border-radius: 4px;
        }

        .reason-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .reason-text {
            color: #6c757d;
            line-height: 1.6;
            margin: 0;
        }

        .ban-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .ban-detail-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .ban-detail-label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .ban-detail-value {
            font-weight: 600;
            color: #495057;
        }

        .action-buttons {
            text-align: center;
            margin-top: 2rem;
        }

        .btn-back {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        @media (max-width: 576px) {
            .banned-card {
                margin: 1rem;
            }

            .banned-header {
                padding: 1.5rem;
            }

            .banned-content {
                padding: 1.5rem;
            }

            .ban-details {
                grid-template-columns: 1fr;
            }

            .banned-icon {
                font-size: 3rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="banned-container">
        <div class="banned-card">
            <div class="banned-header">
                <div class="banned-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <h1 class="mb-2">Account Suspended</h1>
                <p class="mb-0">Your account has been temporarily suspended</p>
            </div>

            <div class="banned-content">
                <div class="text-center mb-4">
                    <h5 class="text-muted">We're sorry, but your account access has been restricted.</h5>
                </div>

                @if($banReason)
                    <div class="reason-box">
                        <div class="reason-label">Reason for Suspension:</div>
                        <p class="reason-text">{{ $banReason }}</p>
                    </div>
                @endif

                <div class="ban-details">
                    <div class="ban-detail-item">
                        <div class="ban-detail-label">Suspended On</div>
                        <div class="ban-detail-value">
                            {{ $bannedAt ? $bannedAt->format('M d, Y') : 'N/A' }}
                        </div>
                    </div>
                    <div class="ban-detail-item">
                        <div class="ban-detail-label">Time</div>
                        <div class="ban-detail-value">
                            {{ $bannedAt ? $bannedAt->format('g:i A') : 'N/A' }}
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>What does this mean?</strong><br>
                    Your account has been temporarily suspended due to a violation of our terms of service.
                    During this time, you will not be able to access your account or use our services.
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Need help?</strong><br>
                    If you believe this suspension was made in error or if you have questions about your account status,
                    please contact our support team for assistance.
                </div>

                <div class="action-buttons">
                    <a href="{{ route('user.login') }}" class="btn-back">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
