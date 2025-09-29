{{-- @include('components.coming-soon-overlay') --}}

@extends('user.layouts.layoutMaster')

@section('title', 'Withdrawal Options')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/notyf/notyf.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js', 'resources/assets/vendor/libs/notyf/notyf.js'])
@endsection

@section('page-script')
    {{-- forms-pickers.js removed as it's not needed and causes flatpickr errors --}}
@endsection

@section('content')
    @include('user.content.withdrawal.partials.styles')

    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Title -->
        <div class="page-header-mobile mb-4">
            <div>
                <h4 class="py-3 mb-0">Withdrawal Options</h4>
                <p class="text-muted mb-0">Choose your preferred withdrawal method</p>
            </div>
        </div>

        <!-- Wallet Balance -->
        @include('user.content.withdrawal.partials.wallet-balance')

        <!-- Pending Withdrawal Alert -->
        @if ($pendingWithdrawal)
            <div class="alert alert-warning alert-dismissible" role="alert">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Pending Withdrawal</h5>
                <p class="mb-2">You have a pending withdrawal request (#{{ $pendingWithdrawal->id }}) for
                    ${{ number_format($pendingWithdrawal->amount, 2) }}.</p>
                <p class="mb-0">Please wait for it to be processed before submitting a new request.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- KYC Verification Status -->
        @if (!$isKycApproved)
            <div class="alert alert-info alert-dismissible" role="alert">
                <h5 class="alert-heading"><i class="fas fa-shield-alt me-2"></i>KYC Verification Required</h5>
                <p class="mb-2">To ensure security and compliance, KYC verification is required for withdrawal requests.
                </p>
                <p class="mb-0">
                    <a href="{{ route('user.kyc.index') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-upload me-1"></i>Complete KYC Verification
                    </a>
                </p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Withdrawal Options -->
        <div class="row g-4 mb-5">
            <!-- Crypto Withdrawal -->
            <div class="col-lg-4 col-md-6">
                <div class="card withdrawal-type-card crypto-card h-100">
                    <div class="card-body text-center">
                        <div class="card-icon">
                            <i class="fab fa-bitcoin"></i>
                        </div>
                        <h5 class="card-title">Cryptocurrency</h5>
                        <p class="card-text">Withdraw to your crypto wallet (USDT, USDC, FDUSD) via BEP20 network</p>
                        <div class="fee-badge">4.75% Fee</div>
                        <div class="mt-3">
                            <a href="{{ route('user.withdrawal.crypto') }}" class="btn btn-success">
                                <i class="fab fa-bitcoin me-1"></i>Withdraw Crypto
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Crypto Bank Card Withdrawal -->
            <div class="col-lg-4 col-md-6">
                <div class="card withdrawal-type-card etc-card h-100">
                    <div class="card-body text-center">
                        <div class="card-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h5 class="card-title">Crypto Bank Card</h5>
                        <p class="card-text">Withdraw directly to your Crypto Bank Card account</p>
                        <div class="fee-badge">5.6% Fee</div>
                        <div class="mt-3">
                            <a href="{{ route('user.withdrawal.crypto-bank-card') }}" class="btn"
                                style="background-color: rgba(111, 66, 193, 1); color: white;">
                                <i class="fas fa-credit-card me-1"></i>Withdraw to Card
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gold Redemption -->
            <div class="col-lg-4 col-md-6">
                <div class="card withdrawal-type-card gold-card h-100">
                    <div class="card-body text-center">
                        <div class="card-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                        <h5 class="card-title">Gold Redemption</h5>
                        <p class="card-text">Redeem your profits for physical gold at Musleem Gold branches</p>
                        <div class="fee-badge">7.0% Fee</div>
                        <div class="mt-3">
                            <a href="{{ route('user.withdrawal.gold') }}" class="btn btn-warning">
                                <i class="fas fa-coins me-1"></i>Redeem Gold
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdrawal Information -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Important Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Withdrawal Rules</h6>
                                <ul class="list-unstyled small">
                                    <li><i class="fas fa-check text-success me-2"></i>Profit & bonus balances can be
                                        withdrawn
                                    </li>
                                    <li><i class="fas fa-check text-success me-2"></i>One pending request at a time</li>
                                    <li><i class="fas fa-check text-success me-2"></i>KYC verification required</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Processing time: 1-3 business days
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Fee Structure</h6>
                                <ul class="list-unstyled small">
                                    <li><i class="fab fa-bitcoin text-success me-2"></i>Crypto: 4.75% processing fee</li>
                                    <li><i class="fas fa-credit-card me-2" style="color: rgba(111, 66, 193, 1);"></i>Crypto
                                        Bank Card: 5.6% processing fee</li>
                                    <li><i class="fas fa-coins text-warning me-2"></i>Gold: 7.0% processing fee</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-headset me-2"></i>Need Help?</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Contact our support team for assistance with withdrawals</p>
                        <div class="d-grid gap-2">
                            <a href="mailto:support@unityx.pro" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-envelope me-1"></i>Email Support
                            </a>
                            <a href="#" class="btn btn-outline-success btn-sm">
                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdrawal History -->
        @include('user.content.withdrawal.partials.withdrawal-history')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects and interactions
            const cards = document.querySelectorAll('.withdrawal-type-card');

            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(-4px)';
                });
            });
        });
    </script>
@endsection
