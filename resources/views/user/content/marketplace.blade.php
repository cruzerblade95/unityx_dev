@extends('user.layouts.layoutMaster')

@section('title', 'Marketplace')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .listing-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            height: 100%;
        }

        .listing-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .listing-card .card-body {
            padding: 1rem;
            position: relative;
        }

        .listing-card h6 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }

        .listing-card .coin-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .listing-card .info-row {
            padding: 0.25rem 0;
            font-size: 0.875rem;
        }

        .listing-card .info-row small {
            font-size: 0.8rem;
        }

        .listing-card hr {
            margin: 0.5rem 0;
        }

        .listing-card .btn {
            font-size: 0.875rem;
            padding: 0.5rem;
        }

        /* Compact mobile view */
        @media (max-width: 768px) {
            .listing-card {
                border-radius: 0.5rem;
            }

            .listing-card .card-body {
                padding: 0.5rem;
            }

            .listing-card h6 {
                font-size: 0.9rem;
                margin-bottom: 0.4rem;
            }

            .listing-card .coin-icon {
                font-size: 1rem;
                margin-bottom: 0.3rem;
            }

            .listing-card .info-row {
                font-size: 0.75rem;
                padding: 0.15rem 0;
            }

            .listing-card .info-row small {
                font-size: 0.7rem;
            }

            .listing-card hr {
                margin: 0.3rem 0;
            }

            .listing-card .btn {
                font-size: 0.75rem;
                padding: 0.35rem 0.5rem;
            }

            .listing-card .status-badge {
                font-size: 0.65rem;
                padding: 0.2rem 0.4rem;
            }

            .listing-card .text-muted {
                font-size: 0.7rem;
            }

            .listing-card .mb-2 {
                margin-bottom: 0.4rem !important;
            }
        }

        @media (max-width: 576px) {
            .listing-card {
                border-radius: 0.4rem;
            }

            .listing-card .card-body {
                padding: 0.4rem;
            }

            .listing-card h6 {
                font-size: 0.85rem;
                margin-bottom: 0.3rem;
            }

            .listing-card .coin-icon {
                font-size: 0.9rem;
                margin-bottom: 0.2rem;
            }

            .listing-card .info-row {
                font-size: 0.7rem;
                padding: 0.1rem 0;
            }

            .listing-card .info-row small {
                font-size: 0.65rem;
            }

            .listing-card hr {
                margin: 0.25rem 0;
            }

            .listing-card .btn {
                font-size: 0.7rem;
                padding: 0.3rem 0.4rem;
            }

            .listing-card .btn i {
                font-size: 0.65rem;
            }

            .listing-card .status-badge {
                font-size: 0.6rem;
                padding: 0.15rem 0.3rem;
            }

            .listing-card .mb-2 {
                margin-bottom: 0.3rem !important;
            }
        }

        .wallet-balance-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .balance-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1rem;
            backdrop-filter: blur(10px);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .balance-item h6 {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .balance-item h4 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .balance-item small {
            font-size: 0.75rem;
            opacity: 0.85;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .balance-item .btn {
            font-size: 0.8rem;
            padding: 0.4rem 0.5rem;
            white-space: nowrap;
        }

        /* Ensure proper sizing on desktop */
        @media (min-width: 992px) {
            .balance-item h4 {
                font-size: 1.3rem;
            }

            .balance-item h6 {
                font-size: 0.85rem;
            }

            .balance-item small {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 768px) {
            .balance-item h4 {
                font-size: 1.2rem;
            }

            .balance-item h6 {
                font-size: 0.9rem;
            }
        }

        .listing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .listing-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 768px) {
            .listing-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .listing-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }

            h2 {
                font-size: 1.5rem;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }

            .d-flex.justify-content-between .btn {
                width: 100%;
            }

            .wallet-balance-card .card-body {
                padding: 1rem;
            }

            .wallet-balance-card .card-title {
                font-size: 1.1rem;
            }

            .balance-item {
                margin-bottom: 1rem;
                padding: 0.75rem;
            }

            .balance-item h6 {
                font-size: 0.9rem;
                margin-bottom: 0.25rem;
            }

            .balance-item h4 {
                font-size: 1.2rem;
            }

            .balance-item small {
                font-size: 0.75rem;
            }

            .balance-item .d-flex {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .modal-dialog {
                margin: 0.5rem;
            }

            .card-header h5 {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            h2 {
                font-size: 1.3rem;
            }

            .wallet-balance-card .card-body {
                padding: 0.75rem;
            }

            .balance-item {
                padding: 0.5rem;
            }

            .balance-item h6 {
                font-size: 0.85rem;
            }

            .balance-item h4 {
                font-size: 1rem;
            }

            .btn {
                font-size: 0.85rem;
                padding: 0.5rem 0.75rem;
            }

            .alert {
                font-size: 0.85rem;
                padding: 0.75rem;
            }
        }

        .status-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        @media (max-width: 768px) {
            .status-badge {
                top: 6px;
                right: 6px;
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }
        }

        .listing-card .card-body {
            position: relative;
        }

        /* Custom pagination styling */
        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            color: #696cff;
            border-color: #d9dee3;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }

        .pagination .page-link:hover {
            color: #5f63f2;
            background-color: #f8f9fa;
            border-color: #d9dee3;
        }

        .pagination .page-item.active .page-link {
            background-color: #696cff;
            border-color: #696cff;
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            color: #a7a9b7;
            background-color: white;
            border-color: #d9dee3;
        }

        /* Activity Tables Responsive */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            min-width: 100%;
            font-size: 0.9rem;
        }

        .table th,
        .table td {
            white-space: nowrap;
            padding: 0.75rem;
            vertical-align: middle;
        }

        .table th {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .table td code {
            font-size: 0.8rem;
            padding: 0.2rem 0.4rem;
            background: #f5f5f5;
            border-radius: 0.25rem;
        }

        .table .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .table .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Mobile table optimization */
        @media (max-width: 768px) {
            .table {
                font-size: 0.75rem;
            }

            .table th,
            .table td {
                padding: 0.5rem 0.4rem;
                font-size: 0.75rem;
            }

            .table th {
                font-size: 0.7rem;
            }

            .table td code {
                font-size: 0.7rem;
                padding: 0.15rem 0.3rem;
                display: inline-block;
                max-width: 80px;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .table .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }

            .table .btn-sm i {
                font-size: 0.65rem;
            }

            .table .badge {
                font-size: 0.65rem;
                padding: 0.2rem 0.4rem;
            }

            /* Hide less important columns on mobile */
            .table th:nth-child(3),
            .table td:nth-child(3),
            .table th:nth-child(4),
            .table td:nth-child(4) {
                display: none;
            }

            /* Compact nav tabs */
            .nav-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
                border-bottom: 1px solid #dee2e6;
                scrollbar-width: thin;
            }

            .nav-tabs::-webkit-scrollbar {
                height: 4px;
            }

            .nav-tabs::-webkit-scrollbar-thumb {
                background: #ccc;
                border-radius: 4px;
            }

            .nav-tabs .nav-item {
                flex-shrink: 0;
            }

            .nav-tabs .nav-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.75rem;
                white-space: nowrap;
            }

            .nav-tabs .badge {
                font-size: 0.65rem;
                padding: 0.15rem 0.35rem;
            }
        }

        @media (max-width: 576px) {
            .table {
                font-size: 0.7rem;
            }

            .table th,
            .table td {
                padding: 0.4rem 0.3rem;
                font-size: 0.7rem;
            }

            .table th {
                font-size: 0.65rem;
            }

            .table td code {
                font-size: 0.65rem;
                max-width: 60px;
            }

            .table .btn-sm {
                padding: 0.15rem 0.3rem;
                font-size: 0.65rem;
            }

            .table .btn-sm i {
                display: none;
                /* Hide icons on very small screens */
            }

            .nav-tabs .nav-link {
                padding: 0.4rem 0.5rem;
                font-size: 0.65rem;
                white-space: nowrap;
            }

            .nav-tabs .badge {
                font-size: 0.6rem;
                padding: 0.1rem 0.3rem;
            }
        }

        /* SweetAlert z-index override for modal */
        .swal-high-zindex {
            z-index: 9999 !important;
        }

        .swal-high-zindex .swal2-container {
            z-index: 9999 !important;
        }
    </style>
@endsection

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-store me-2"></i>
                USD Marketplace
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createListingModal">
                <i class="fas fa-plus me-2"></i>
                Sell USD
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (!$telegramVerification || empty($telegramVerification->telegram_username))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fab fa-telegram-plane me-2"></i>
                <strong>Telegram Connection Required!</strong>
                You need to connect your Telegram account to use marketplace features.
                <a href="{{ route('user.profile') }}" class="alert-link">Connect now in your profile settings</a>.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Wallet Balances -->
        <div class="card wallet-balance-card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fas fa-wallet me-2"></i>
                    Your Wallet Balances
                </h5>
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="balance-item text-center">
                            <h6 class="mb-2">Cash Balance</h6>
                            <h4 class="mb-2">${{ number_format($cashBalance, 2) }}</h4>
                            <small class="opacity-75">For marketplace</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="balance-item text-center">
                            <h6 class="mb-2">Deposit Balance</h6>
                            <h4 class="mb-2">${{ number_format($depositBalance, 2) }}</h4>
                            <small class="opacity-75">AI Trading</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="balance-item text-center">
                            <h6 class="mb-2">Profit Balance</h6>
                            <h4 class="mb-2">${{ number_format($profitBalance, 2) }}</h4>
                            <small class="opacity-75">For top-up</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="balance-item text-center">
                            <h6 class="mb-2">Bonus Balance</h6>
                            <h4 class="mb-2">${{ number_format($bonusBalance, 2) }}</h4>
                            <small class="opacity-75">For top-up</small>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 col-lg-12 col-xl-4">
                        <div class="balance-item">
                            <div class="row g-2">
                                <div class="col-4">
                                    <button class="btn btn-light btn-sm w-100" data-bs-toggle="modal"
                                        data-bs-target="#topUpModal">
                                        <i class="fas fa-arrow-up me-1"></i>
                                        Top Up
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-light btn-sm w-100" data-bs-toggle="modal"
                                        data-bs-target="#transferToDepositModal">
                                        <i class="fas fa-exchange-alt me-1"></i>
                                        Transfer
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-light btn-sm w-100" data-bs-toggle="modal"
                                        data-bs-target="#xeusdWithdrawalModal">
                                        <i class="fas fa-paper-plane me-1"></i>
                                        Withdraw
                                    </button>
                                </div>
                            </div>
                            <small class="text-white d-block mt-2 text-center opacity-75" style="font-size: 0.75rem;">1.5%
                                fee for top-up | Free transfer | 0.5% fee for withdrawal</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Verifications (for buyers) -->
        @if (isset($pendingVerifications) && $pendingVerifications->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Your Pending Verifications
                        <span class="badge bg-dark ms-2">{{ $pendingVerifications->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($pendingVerifications as $verification)
                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ number_format($verification->amount, 2) }} USD
                                            </h6>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-1"></i>
                                                <strong>Listing ID:</strong> <code>{{ $verification->unique_id }}</code>
                                            </small>
                                        </div>

                                        <div class="alert alert-info mb-3">
                                            <h6 class="alert-heading mb-2">
                                                <i class="fab fa-telegram me-2"></i>
                                                Contact Seller via Telegram
                                            </h6>
                                            <p class="mb-2">Contact the seller using their Telegram username below to
                                                verify the transaction. Please mention listing ID
                                                <strong>{{ $verification->unique_id }}</strong> when contacting.
                                            </p>
                                            <div class="row">
                                                <div class="col-12">
                                                    <strong>Telegram Contact:</strong><br>
                                                    @if (
                                                        $verification->seller &&
                                                            $verification->seller->telegramVerification &&
                                                            $verification->seller->telegramVerification->telegram_username)
                                                        <a href="https://t.me/{{ $verification->seller->telegramVerification->telegram_username }}"
                                                            target="_blank" class="btn btn-outline-primary btn-sm">
                                                            <i class="fab fa-telegram me-2"></i>
                                                            {{ '@' . $verification->seller->telegramVerification->telegram_username }}
                                                        </a>
                                                        <small class="text-muted d-block mt-1">(Click to open in
                                                            Telegram)</small>
                                                    @else
                                                        <span class="text-muted">Not provided</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                <i class="fas fa-info-circle me-1"></i>
                                                The seller will approve/reject the transaction here on the marketplace after
                                                verification.
                                            </small>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">You'll receive USD:
                                                {{ number_format($verification->amount * 0.982, 2) }} USD</small>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                Time Remaining: <span class="countdown text-danger"
                                                    data-expires="{{ $verification->verification_expires_at }}"></span>
                                            </small>
                                        </div>

                                        <button class="btn btn-outline-secondary btn-sm w-100"
                                            onclick="cancelVerification('{{ $verification->unique_id }}')">
                                            <i class="fas fa-times me-2"></i>
                                            Cancel Verification
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Verification Requests (for sellers) -->
        @if (isset($verificationRequests) && $verificationRequests->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2"></i>
                        Verification Requests for Your Listings
                        <span class="badge bg-light text-dark ms-2">{{ $verificationRequests->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($verificationRequests as $request)
                            <div class="col-md-6 mb-3">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ number_format($request->amount, 2) }} USD</h6>
                                            <span class="badge bg-info">Verification Request</span>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-1"></i>
                                                <strong>Listing ID:</strong> <code>{{ $request->unique_id }}</code>
                                            </small>
                                        </div>

                                        <div class="alert alert-light mb-3">
                                            <h6 class="alert-heading mb-2">
                                                <i class="fas fa-user me-2"></i>
                                                Buyer Information
                                            </h6>
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Buyer:</strong><br>
                                                    {{ $request->buyer->username ?? 'Unknown' }}
                                                </div>
                                                <div class="col-6">
                                                    <strong>Contact:</strong><br>
                                                    <small>{{ $request->buyer->email ?? 'No email' }}</small>
                                                </div>
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                <i class="fas fa-info-circle me-1"></i>
                                                The buyer should contact you via your Telegram. After verification,
                                                approve/reject below.
                                            </small>
                                        </div>

                                        <div class="mb-2">
                                            <small class="text-muted">USD Amount to give:
                                                {{ number_format($request->amount, 2) }} USD</small>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                Time Remaining: <span class="countdown text-danger"
                                                    data-expires="{{ $request->verification_expires_at }}"></span>
                                            </small>
                                        </div>

                                        <!-- Screenshot Upload Section -->
                                        @if (!$request->hasScreenshot())
                                            <div class="alert alert-warning mb-3">
                                                <h6 class="alert-heading mb-2">
                                                    <i class="fas fa-camera me-2"></i>
                                                    Screenshot Required
                                                </h6>
                                                <p class="mb-2">Please upload a screenshot of the transaction as proof
                                                    before approving.</p>
                                                <form id="screenshot-form-{{ $request->unique_id }}"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="mb-2">
                                                        <input type="file" class="form-control form-control-sm"
                                                            id="screenshot-{{ $request->unique_id }}" name="screenshot"
                                                            accept="image/*" required>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-sm w-100"
                                                        onclick="uploadScreenshot('{{ $request->unique_id }}')">
                                                        <i class="fas fa-upload me-2"></i>
                                                        Upload Screenshot
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <div class="alert alert-success mb-3">
                                                <h6 class="alert-heading mb-2">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    Screenshot Uploaded
                                                </h6>
                                                <p class="mb-2">Screenshot uploaded successfully at
                                                    {{ $request->screenshot_uploaded_at->format('M d, Y H:i') }}</p>
                                                <div class="d-flex gap-2 mb-2">
                                                    <a href="#"
                                                        onclick="previewScreenshot('{{ $request->screenshot_url }}')"
                                                        class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye me-2"></i>
                                                        View Screenshot
                                                    </a>
                                                    <button type="button" class="btn btn-outline-warning btn-sm"
                                                        onclick="showReplaceForm('{{ $request->unique_id }}')">
                                                        <i class="fas fa-sync-alt me-2"></i>
                                                        Replace
                                                    </button>
                                                </div>

                                                <!-- Hidden replace form -->
                                                <div id="replace-form-{{ $request->unique_id }}" class="mt-3"
                                                    style="display: none;">
                                                    <hr>
                                                    <h6 class="text-warning mb-2">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        Replace Screenshot
                                                    </h6>
                                                    <form id="replace-screenshot-form-{{ $request->unique_id }}"
                                                        enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="mb-2">
                                                            <input type="file" class="form-control form-control-sm"
                                                                id="replace-screenshot-{{ $request->unique_id }}"
                                                                name="screenshot" accept="image/*" required>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button type="button"
                                                                class="btn btn-warning btn-sm flex-fill"
                                                                onclick="replaceScreenshot('{{ $request->unique_id }}')">
                                                                <i class="fas fa-upload me-2"></i>
                                                                Upload New Screenshot
                                                            </button>
                                                            <button type="button" class="btn btn-secondary btn-sm"
                                                                onclick="hideReplaceForm('{{ $request->unique_id }}')">
                                                                <i class="fas fa-times me-2"></i>
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="row">
                                            <div class="col-6">
                                                <button
                                                    class="btn btn-success btn-sm w-100 {{ !$request->hasScreenshot() ? 'disabled' : '' }}"
                                                    onclick="approveVerification('{{ $request->unique_id }}')"
                                                    {{ !$request->hasScreenshot() ? 'disabled title="Upload screenshot first"' : '' }}>
                                                    <i class="fas fa-check me-2"></i>
                                                    Approve
                                                </button>
                                            </div>
                                            <div class="col-6">
                                                <button class="btn btn-danger btn-sm w-100"
                                                    onclick="showRejectModal('{{ $request->unique_id }}')">
                                                    <i class="fas fa-times me-2"></i>
                                                    Reject
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Active Listings -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Available USD Listings
                </h5>
            </div>
            <div class="card-body">
                @if ($listings->count() > 0)
                    <div class="listing-grid">
                        @foreach ($listings as $listing)
                            <div class="card listing-card">
                                <div class="card-body">
                                    <span class="badge bg-success status-badge">Available</span>

                                    <div class="text-center mb-2">
                                        <i class="fas fa-coins text-warning coin-icon"></i>
                                    </div>

                                    <h6 class="card-title text-center mb-2">{{ number_format($listing->amount, 2) }} USD
                                    </h6>

                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between info-row">
                                            <small class="text-muted">Multiplier:</small>
                                            <strong>x{{ number_format($listing->price_per_usdt, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between info-row">
                                            <small class="text-muted">Total Price:</small>
                                            <strong>${{ number_format($listing->total_price, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between info-row">
                                            <small class="text-muted">After Fee:</small>
                                            <small class="text-success">{{ number_format($listing->amount * 0.982, 2) }}
                                                USD</small>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between info-row">
                                            <strong style="font-size: 0.85rem;">You Receive:</strong>
                                            <strong class="text-success"
                                                style="font-size: 0.85rem;">{{ number_format($listing->amount * 0.982, 2) }}
                                                USD</strong>
                                        </div>
                                    </div>

                                    @if ($listing->description)
                                        <p class="card-text small text-muted mb-2"
                                            style="font-size: 0.75rem; line-height: 1.3;">
                                            {{ strlen($listing->description) > 60 ? substr($listing->description, 0, 60) . '...' : $listing->description }}
                                        </p>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $listing->seller->username ?? 'Seller' }}
                                        </small>
                                        <small class="text-muted"
                                            style="font-size: 0.7rem;">{{ $listing->unique_id }}</small>
                                    </div>

                                    <button class="btn btn-primary btn-sm w-100"
                                        onclick="purchaseListing('{{ $listing->unique_id }}', {{ $listing->amount }})">
                                        <i class="fas fa-gift me-1"></i>
                                        Start Verification
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if ($listings->hasPages())
                        <div class="mt-4">
                            <!-- Results Info -->
                            <div class="text-center mb-3">
                                <small class="text-muted">
                                    Showing {{ $listings->firstItem() }} to {{ $listings->lastItem() }} of
                                    {{ $listings->total() }} listings
                                </small>
                            </div>
                            <!-- Pagination Links -->
                            <div class="d-flex justify-content-center">
                                {{ $listings->links('pagination::custom-bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-store-slash text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No USD listings available</h5>
                        <p class="text-muted">Be the first to create a listing!</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- My Listings with Tabs -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>
                    My Activity
                </h5>
            </div>
            <div class="card-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="myActivityTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active"
                            type="button" role="tab" aria-controls="active" aria-selected="true">
                            Active Listings
                            @if ($myActiveListings->count() > 0)
                                <span class="badge bg-success ms-2">{{ $myActiveListings->count() }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pending-purchases-tab" data-bs-toggle="tab"
                            data-bs-target="#pending-purchases" type="button" role="tab"
                            aria-controls="pending-purchases" aria-selected="false">
                            Pending Purchases
                            @if ($myPendingPurchases->count() > 0)
                                <span class="badge bg-warning ms-2">{{ $myPendingPurchases->count() }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sold-tab" data-bs-toggle="tab" data-bs-target="#sold"
                            type="button" role="tab" aria-controls="sold" aria-selected="false">
                            Sold
                            @if ($mySoldListings->count() > 0)
                                <span class="badge bg-primary ms-2">{{ $mySoldListings->count() }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completed-purchases-tab" data-bs-toggle="tab"
                            data-bs-target="#completed-purchases" type="button" role="tab"
                            aria-controls="completed-purchases" aria-selected="false">
                            Completed Purchases
                            @if ($myCompletedPurchases->count() > 0)
                                <span class="badge bg-info ms-2">{{ $myCompletedPurchases->count() }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled"
                            type="button" role="tab" aria-controls="cancelled" aria-selected="false">
                            Cancelled
                            @if ($myCancelledListings->count() > 0)
                                <span class="badge bg-secondary ms-2">{{ $myCancelledListings->count() }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected"
                            type="button" role="tab" aria-controls="rejected" aria-selected="false">
                            Rejected
                            @if ($myRejectedListings->count() > 0)
                                <span class="badge bg-danger ms-2">{{ $myRejectedListings->count() }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="expired-tab" data-bs-toggle="tab" data-bs-target="#expired"
                            type="button" role="tab" aria-controls="expired" aria-selected="false">
                            Expired
                            @if ($myExpiredListings->count() > 0)
                                <span class="badge bg-dark ms-2">{{ $myExpiredListings->count() }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="xeusd-withdrawals-tab" data-bs-toggle="tab"
                            data-bs-target="#xeusd-withdrawals" type="button" role="tab"
                            aria-controls="xeusd-withdrawals" aria-selected="false">
                            XEUSD Withdrawals
                            @if ($myXeusdWithdrawals->count() > 0)
                                <span class="badge bg-purple ms-2">{{ $myXeusdWithdrawals->count() }}</span>
                            @endif
                        </button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content mt-3" id="myActivityTabContent">
                    <!-- Active Listings Tab -->
                    <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                        @if ($myActiveListings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Amount</th>
                                            <th>Multiplier</th>
                                            <th>Total</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($myActiveListings as $listing)
                                            <tr>
                                                <td><code>{{ $listing->unique_id }}</code></td>
                                                <td>{{ number_format($listing->amount, 2) }} USD</td>
                                                <td>x{{ number_format($listing->price_per_usdt, 2) }}</td>
                                                <td>${{ number_format($listing->total_price, 2) }}</td>
                                                <td>{{ $listing->created_at->diffForHumans() }}</td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm"
                                                        onclick="cancelListing('{{ $listing->unique_id }}')">
                                                        <i class="fas fa-times me-1"></i>Cancel
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-list fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No active listings</p>
                            </div>
                        @endif
                    </div>

                    <!-- Pending Purchases Tab -->
                    <div class="tab-pane fade" id="pending-purchases" role="tabpanel"
                        aria-labelledby="pending-purchases-tab">
                        @if ($myPendingPurchases->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Seller</th>
                                            <th>Amount</th>
                                            <th>Total</th>
                                            <th>Time Left</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($myPendingPurchases as $purchase)
                                            <tr>
                                                <td><code>{{ $purchase->unique_id }}</code></td>
                                                <td>{{ $purchase->seller->username }}</td>
                                                <td>{{ number_format($purchase->amount, 2) }} USD</td>
                                                <td>${{ number_format($purchase->total_price, 2) }}</td>
                                                <td>
                                                    @if ($purchase->remaining_verification_time > 0)
                                                        <span
                                                            class="badge bg-warning">{{ $purchase->remaining_verification_time }}m
                                                            left</span>
                                                    @else
                                                        <span class="badge bg-danger">Expired</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge bg-info">Pending Verification</span></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm"
                                                        onclick="cancelVerification('{{ $purchase->unique_id }}')">
                                                        <i class="fas fa-times me-1"></i>Cancel
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No pending purchases</p>
                            </div>
                        @endif
                    </div>

                    <!-- Sold Tab -->
                    <div class="tab-pane fade" id="sold" role="tabpanel" aria-labelledby="sold-tab">
                        @if ($mySoldListings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Buyer</th>
                                            <th>Amount</th>
                                            <th>Total</th>
                                            <th>Sold Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($mySoldListings as $listing)
                                            <tr>
                                                <td><code>{{ $listing->unique_id }}</code></td>
                                                <td>{{ $listing->buyer ? $listing->buyer->username : 'N/A' }}</td>
                                                <td>{{ number_format($listing->amount, 2) }} USD</td>
                                                <td>${{ number_format($listing->total_price, 2) }}</td>
                                                <td>{{ $listing->sold_at ? $listing->sold_at->format('M d, Y H:i') : 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No sold listings</p>
                            </div>
                        @endif
                    </div>

                    <!-- Completed Purchases Tab -->
                    <div class="tab-pane fade" id="completed-purchases" role="tabpanel"
                        aria-labelledby="completed-purchases-tab">
                        @if ($myCompletedPurchases->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Seller</th>
                                            <th>Amount</th>
                                            <th>Total Paid</th>
                                            <th>Completed Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($myCompletedPurchases as $purchase)
                                            <tr>
                                                <td><code>{{ $purchase->unique_id }}</code></td>
                                                <td>{{ $purchase->seller->username }}</td>
                                                <td>{{ number_format($purchase->amount, 2) }} USD</td>
                                                <td>${{ number_format($purchase->total_price, 2) }}</td>
                                                <td>{{ $purchase->sold_at ? $purchase->sold_at->format('M d, Y H:i') : 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No completed purchases</p>
                            </div>
                        @endif
                    </div>

                    <!-- Cancelled Tab -->
                    <div class="tab-pane fade" id="cancelled" role="tabpanel" aria-labelledby="cancelled-tab">
                        @if ($myCancelledListings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Amount</th>
                                            <th>Total</th>
                                            <th>Cancelled Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($myCancelledListings as $listing)
                                            <tr>
                                                <td><code>{{ $listing->unique_id }}</code></td>
                                                <td>{{ number_format($listing->amount, 2) }} USD</td>
                                                <td>${{ number_format($listing->total_price, 2) }}</td>
                                                <td>{{ $listing->updated_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-times-circle fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No cancelled listings</p>
                            </div>
                        @endif
                    </div>

                    <!-- Rejected Tab -->
                    <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                        @if ($myRejectedListings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Amount</th>
                                            <th>Total</th>
                                            <th>Rejection Reason</th>
                                            <th>Rejected By</th>
                                            <th>Rejected Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($myRejectedListings as $listing)
                                            <tr>
                                                <td><code>{{ $listing->unique_id }}</code></td>
                                                <td>{{ number_format($listing->amount, 2) }} USDT</td>
                                                <td>${{ number_format($listing->total_price, 2) }}</td>
                                                <td>
                                                    <div class="text-break" style="max-width: 300px;">
                                                        {{ $listing->admin_rejection_reason ?: 'No reason provided' }}
                                                    </div>
                                                </td>
                                                <td>{{ $listing->rejectedBy ? $listing->rejectedBy->username : 'System' }}
                                                </td>
                                                <td>{{ $listing->admin_rejected_at ? $listing->admin_rejected_at->format('M d, Y H:i') : 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-ban fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No rejected listings</p>
                            </div>
                        @endif
                    </div>

                    <!-- Expired Tab -->
                    <div class="tab-pane fade" id="expired" role="tabpanel" aria-labelledby="expired-tab">
                        @if ($myExpiredListings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Amount</th>
                                            <th>Total</th>
                                            <th>Expired Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($myExpiredListings as $listing)
                                            <tr>
                                                <td><code>{{ $listing->unique_id }}</code></td>
                                                <td>{{ number_format($listing->amount, 2) }} USDT</td>
                                                <td>${{ number_format($listing->total_price, 2) }}</td>
                                                <td>{{ $listing->updated_at->format('M d, Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No expired listings</p>
                            </div>
                        @endif
                    </div>

                    <!-- XEUSD Withdrawals Tab -->
                    <div class="tab-pane fade" id="xeusd-withdrawals" role="tabpanel"
                        aria-labelledby="xeusd-withdrawals-tab">
                        @if ($myXeusdWithdrawals->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Reference ID</th>
                                            <th>Amount</th>
                                            <th>Source</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($myXeusdWithdrawals as $withdrawal)
                                            <tr>
                                                <td><strong>{{ $withdrawal->unique_id }}</strong></td>
                                                <td>
                                                    ${{ number_format($withdrawal->amount_usd, 2) }}<br>
                                                    <small
                                                        class="text-muted">{{ number_format($withdrawal->amount_xeusd, 8) }}
                                                        XEUSD</small>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-info">{{ ucfirst($withdrawal->source_wallet) }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $withdrawal->getStatusBadgeClass() }}">
                                                        {{ ucfirst($withdrawal->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                                                <td>
                                                    @if ($withdrawal->status === 'completed' && $withdrawal->transaction_hash)
                                                        <a href="https://bscscan.com/tx/{{ $withdrawal->transaction_hash }}"
                                                            target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-external-link-alt me-1"></i>View TX
                                                        </a>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> XEUSD withdrawals are processed manually by our admin team.
                                Withdrawal fee: 0.5% | Network: BSC (BEP20)
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No XEUSD withdrawal requests yet</p>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#xeusdWithdrawalModal">
                                    <i class="fas fa-paper-plane me-2"></i>Create Withdrawal Request
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Listing Modal -->
    <div class="modal fade" id="createListingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>
                        Create USDT Listing
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('user.marketplace.create') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Minimum:</strong> 10 USDT | <strong>Maximum:</strong> 200 USDT | <strong>Available
                                Cash:</strong>
                            ${{ number_format($cashBalance, 2) }}
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">USDT Amount</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                                    required min="10" max="{{ min(200, $cashBalance) }}">
                                <span class="input-group-text">USDT</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="price_per_usdt" class="form-label">Price Multiplier</label>
                            <div class="input-group">
                                <span class="input-group-text">x</span>
                                <input type="number" step="0.01" class="form-control" id="price_per_usdt"
                                    name="price_per_usdt" required min="0.95" max="1.5" value="1.00"
                                    onchange="calculateFinalPrice()">
                            </div>
                            <div class="form-text">Multiplier range: x0.95 - x1.50</div>
                        </div>

                        <div class="mb-3">
                            <div class="card border-info">
                                <div class="card-body p-3">
                                    <h6 class="card-title mb-2">
                                        <i class="fas fa-calculator me-2"></i>
                                        Price Calculation
                                    </h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">USDT Amount:</small>
                                            <div id="calc-amount">0 USDT</div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Multiplier:</small>
                                            <div id="calc-multiplier">x1.00</div>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <hr class="my-2">
                                            <strong class="text-primary">Final Price: <span
                                                    id="calc-final">$0.00</span></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3" maxlength="500"
                                placeholder="Add a description for your listing..."></textarea>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            The USDT amount will be deducted from your cash wallet immediately. Final price will be
                            calculated as: <strong>Amount × Multiplier</strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Create Listing
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Top Up Cash Modal -->
    <div class="modal fade" id="topUpModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-arrow-up me-2"></i>
                        Top Up Cash Wallet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('user.marketplace.topup') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Available Profit:</strong> ${{ number_format($profitBalance, 2) }} |
                            <strong>Available Bonus:</strong> ${{ number_format($bonusBalance, 2) }} |
                            <strong>Fee:</strong> 1.5%
                        </div>

                        <div class="mb-3">
                            <label for="source_wallet" class="form-label">Source Wallet</label>
                            <select class="form-select" id="source_wallet" name="source_wallet" required
                                onchange="updateMaxAmount()">
                                <option value="">Select wallet to transfer from</option>
                                @if ($profitBalance > 0)
                                    <option value="profit">Profit Wallet (${{ number_format($profitBalance, 2) }}
                                        available)</option>
                                @endif
                                @if ($bonusBalance > 0)
                                    <option value="bonus">Bonus Wallet (${{ number_format($bonusBalance, 2) }}
                                        available)</option>
                                @endif
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="topup_amount" class="form-label">Amount to Transfer</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="topup_amount"
                                    name="amount" required min="1" max="0">
                            </div>
                            <div class="form-text" id="fee-text">Fee (1.5%) will be deducted from your selected wallet
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-calculator me-2"></i>
                            <strong>Example:</strong> Transfer $100 → You pay $101.50 (including 1.5% fee)
                        </div>

                        @if ($profitBalance == 0 && $bonusBalance == 0)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                You don't have sufficient balance in either Profit or Bonus wallet to perform a top-up.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" @if ($profitBalance == 0 && $bonusBalance == 0) disabled @endif>
                            <i class="fas fa-arrow-up me-2"></i>
                            Top Up Cash
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transfer to Deposit Modal -->
    <div class="modal fade" id="transferToDepositModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Transfer Cash to Deposit Wallet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('user.marketplace.transfer_to_deposit') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Available Cash:</strong> ${{ number_format($cashBalance, 2) }} |
                            <strong>No fees</strong> for this transfer
                        </div>

                        <div class="mb-3">
                            <label for="transfer_amount" class="form-label">Amount to Transfer</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="transfer_amount"
                                    name="amount" required min="1" max="{{ $cashBalance }}">
                            </div>
                            <div class="form-text">Transfer from Cash wallet to Deposit wallet for AI Trading</div>
                        </div>

                        <div class="alert alert-success">
                            <i class="fas fa-robot me-2"></i>
                            <strong>After transfer:</strong> Your deposit will be automatically invested in AI Trade for
                            potential 5x returns!
                        </div>

                        @if ($cashBalance == 0)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                You don't have any balance in your Cash wallet to transfer.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" @if ($cashBalance == 0) disabled @endif>
                            <i class="fas fa-exchange-alt me-2"></i>
                            Transfer to Deposit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- XEUSD Withdrawal Modal -->
    <div class="modal fade" id="xeusdWithdrawalModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-paper-plane me-2"></i>
                        Create XEUSD Withdrawal Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('user.marketplace.xeusd.withdrawal') }}"
                    id="xeusdWithdrawalForm">
                    @csrf
                    <div class="modal-body">
                        @if (!auth()->user()->hasConnectedWallet())
                            <div class="alert alert-warning">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Wallet Required:</strong> Please connect your wallet to
                                        proceed.
                                    </div>
                                    <button type="button" class="btn btn-sm btn-warning"
                                        onclick="connectWalletMarketplace()">
                                        <i class="fab fa-ethereum me-1"></i>Connect Wallet
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-wallet me-2"></i>
                                    <strong>Wallet Address:</strong>
                                    {{ auth()->user()->getFormattedWalletAddressAttribute() }}
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="connectWalletMarketplace()" title="Connect a different wallet address">
                                    <i class="fas fa-sync-alt me-1"></i>Reconnect
                                </button>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Available Cash:</strong> ${{ number_format($cashBalance, 2) }} |
                            <strong>Fee:</strong> 0.5%
                        </div>

                        <input type="hidden" name="source_wallet" value="cash">

                        <div class="mb-3">
                            <label for="withdrawal_amount" class="form-label">Withdrawal Amount (USD)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" id="withdrawal_amount"
                                    name="amount" required min="1" max="500"
                                    oninput="updateWithdrawalCalculation()">
                            </div>
                            <div class="form-text">Minimum: $1 | Maximum: $500</div>
                        </div>

                        <div class="alert alert-success">
                            <h6 class="mb-2">
                                <i class="fas fa-calculator me-2"></i>
                                Withdrawal Information
                            </h6>
                            <p class="mb-1"><strong>Withdrawal Amount:</strong> $<span
                                    id="withdrawal-display-amount">0.00</span></p>
                            <p class="mb-1"><strong>Withdrawal Fee (0.5%):</strong> $<span
                                    id="withdrawal-fee-amount">0.00</span>
                            </p>
                            <p class="mb-0"><strong>You will receive:</strong> <span
                                    id="withdrawal-xeusd-amount">0</span> XEUSD
                                tokens</p>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Network:</strong> BSC (BEP20) |
                            <strong>Processing:</strong> Manual by admin team
                        </div>

                        @if ($cashBalance == 0)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                You don't have sufficient balance in your Cash wallet to perform a withdrawal.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" @if (!auth()->user()->hasConnectedWallet() || $cashBalance == 0) disabled @endif>
                            <i class="fas fa-paper-plane me-2"></i>
                            Submit Withdrawal Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Screenshot Preview Modal -->
    <div class="modal fade" id="screenshotModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-image me-2"></i>
                        Verification Screenshot
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="screenshotImage" src="" alt="Verification Screenshot" class="img-fluid rounded">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function calculateFinalPrice() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const multiplier = parseFloat(document.getElementById('price_per_usdt').value) || 1.00;
            const finalPrice = amount * multiplier;

            document.getElementById('calc-amount').textContent = amount.toFixed(2) + ' USDT';
            document.getElementById('calc-multiplier').textContent = 'x' + multiplier.toFixed(2);
            document.getElementById('calc-final').textContent = '$' + finalPrice.toFixed(2);
        }

        // Update calculation when amount changes
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount');
            const multiplierInput = document.getElementById('price_per_usdt');

            if (amountInput) {
                amountInput.addEventListener('input', calculateFinalPrice);
            }
            if (multiplierInput) {
                multiplierInput.addEventListener('input', calculateFinalPrice);
            }

            // Initial calculation
            calculateFinalPrice();
        });

        function purchaseListing(listingId, usdtAmount) {
            const feeDeduction = usdtAmount * 0.018;
            const finalAmount = usdtAmount * 0.982;

            Swal.fire({
                title: 'Start Verification Process?',
                html: `
            <div class="text-start">
                <p><strong>USDT Amount:</strong> ${usdtAmount.toFixed(2)} USDT</p>
                <p><strong>Fee (1.8%):</strong> -${feeDeduction.toFixed(2)} USDT</p>
                <p><strong>You'll Receive:</strong> ${finalAmount.toFixed(2)} USDT</p>
                <div class="alert alert-success">
                    <i class="fas fa-gift me-2"></i>
                    <strong>BUYING USDT!</strong> Payment on Telegram.
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Process:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Contact seller via Telegram for verification</li>
                        <li>Seller will approve/reject on this marketplace</li>
                        <li>You have <strong>45 minutes</strong> to complete</li>
                        <li>USDT will be added to your cash wallet when approved</li>
                    </ol>
                </div>
            </div>
        `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Start Verification'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/user/marketplace/purchase/${listingId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function previewScreenshot(imageUrl) {
            document.getElementById('screenshotImage').src = imageUrl;
            const modal = new bootstrap.Modal(document.getElementById('screenshotModal'));
            modal.show();
        }

        function uploadScreenshot(listingId) {
            const form = document.getElementById(`screenshot-form-${listingId}`);
            const fileInput = document.getElementById(`screenshot-${listingId}`);

            if (!fileInput.files[0]) {
                Swal.fire('Error', 'Please select a screenshot to upload.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('screenshot', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            // Show loading state
            Swal.fire({
                title: 'Uploading Screenshot...',
                text: 'Please wait while we upload your verification screenshot.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/user/marketplace/upload-screenshot/${listingId}`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload page to show updated status
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.error || 'Failed to upload screenshot.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error uploading screenshot:', error);
                    Swal.fire('Error', 'Failed to upload screenshot. Please try again.', 'error');
                });
        }

        function showReplaceForm(listingId) {
            const replaceForm = document.getElementById(`replace-form-${listingId}`);
            replaceForm.style.display = 'block';
        }

        function hideReplaceForm(listingId) {
            const replaceForm = document.getElementById(`replace-form-${listingId}`);
            replaceForm.style.display = 'none';

            // Clear the file input
            const fileInput = document.getElementById(`replace-screenshot-${listingId}`);
            if (fileInput) {
                fileInput.value = '';
            }
        }

        function replaceScreenshot(listingId) {
            const fileInput = document.getElementById(`replace-screenshot-${listingId}`);

            if (!fileInput.files[0]) {
                Swal.fire('Error', 'Please select a new screenshot to upload.', 'error');
                return;
            }

            // Confirm replacement
            Swal.fire({
                title: 'Replace Screenshot?',
                text: 'This will replace your current screenshot with the new one.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Replace It'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('screenshot', fileInput.files[0]);
                    formData.append('_token', '{{ csrf_token() }}');

                    // Show loading state
                    Swal.fire({
                        title: 'Replacing Screenshot...',
                        text: 'Please wait while we replace your verification screenshot.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`/user/marketplace/upload-screenshot/${listingId}`, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Screenshot replaced successfully!',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    // Reload page to show updated status
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.error || 'Failed to replace screenshot.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error replacing screenshot:', error);
                            Swal.fire('Error', 'Failed to replace screenshot. Please try again.', 'error');
                        });
                }
            });
        }

        function approveVerification(listingId) {
            Swal.fire({
                title: 'Approve Transaction?',
                text: 'This will transfer USDT from your escrow to the buyer\'s cash wallet.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Approve Transaction'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/user/marketplace/approve/${listingId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function showRejectModal(listingId) {
            Swal.fire({
                title: 'Reject Transaction',
                input: 'textarea',
                inputLabel: 'Reason for rejection',
                inputPlaceholder: 'Please provide a reason for rejecting this transaction...',
                inputAttributes: {
                    'aria-label': 'Reason for rejection',
                    'maxlength': '255'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Reject Transaction',
                inputValidator: (value) => {
                    if (!value || value.trim().length < 5) {
                        return 'Please provide a reason (at least 5 characters)';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/user/marketplace/reject/${listingId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const reasonInput = document.createElement('input');
                    reasonInput.type = 'hidden';
                    reasonInput.name = 'reason';
                    reasonInput.value = result.value;

                    form.appendChild(csrfToken);
                    form.appendChild(reasonInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function cancelVerification(listingId) {
            Swal.fire({
                title: 'Cancel Verification?',
                text: 'This will cancel the verification process and make the listing available again.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Cancel Verification'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/user/marketplace/cancel-verification/${listingId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Countdown timer for verification expiration
        function updateCountdowns() {
            const countdowns = document.querySelectorAll('.countdown');

            countdowns.forEach(countdown => {
                const expires = new Date(countdown.dataset.expires).getTime();
                const now = new Date().getTime();
                const distance = expires - now;

                if (distance > 0) {
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    countdown.innerHTML = `${minutes}m ${seconds}s`;
                } else {
                    countdown.innerHTML = 'EXPIRED';
                    countdown.classList.add('text-danger');
                }
            });
        }

        // Update countdowns every second
        document.addEventListener('DOMContentLoaded', function() {
            updateCountdowns();
            setInterval(updateCountdowns, 1000);
        });

        function cancelListing(listingId) {
            Swal.fire({
                title: 'Cancel Listing?',
                text: 'The USDT will be returned to your cash wallet',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/user/marketplace/cancel/${listingId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function updateMaxAmount() {
            const sourceWallet = document.getElementById('source_wallet').value;
            const amountInput = document.getElementById('topup_amount');
            const feeText = document.getElementById('fee-text');

            const profitBalance = {{ $profitBalance }};
            const bonusBalance = {{ $bonusBalance }};

            if (sourceWallet === 'profit') {
                // Calculate max amount considering 1.5% fee (amount + fee = total, so max = balance / 1.015)
                const maxAmount = profitBalance / 1.015;
                amountInput.max = maxAmount.toFixed(2);
                feeText.textContent = 'Fee (1.5%) will be deducted from your Profit wallet';
            } else if (sourceWallet === 'bonus') {
                const maxAmount = bonusBalance / 1.015;
                amountInput.max = maxAmount.toFixed(2);
                feeText.textContent = 'Fee (1.5%) will be deducted from your Bonus wallet';
            } else {
                amountInput.max = 0;
                feeText.textContent = 'Fee (1.5%) will be deducted from your selected wallet';
            }

            // Reset amount if it exceeds new max
            if (parseFloat(amountInput.value) > parseFloat(amountInput.max)) {
                amountInput.value = '';
            }
        }

        // XEUSD Withdrawal Calculation with 0.5% fee
        function updateWithdrawalCalculation() {
            const amount = parseFloat(document.getElementById('withdrawal_amount').value) || 0;

            const cashBalance = {{ $cashBalance }};

            // Calculate 0.5% fee
            const feePercentage = 0.005; // 0.5%
            const feeAmount = amount * feePercentage;
            const netAmount = amount - feeAmount; // Amount user will receive in XEUSD

            // Update display
            document.getElementById('withdrawal-display-amount').textContent = amount.toFixed(2);
            document.getElementById('withdrawal-fee-amount').textContent = feeAmount.toFixed(2);
            document.getElementById('withdrawal-xeusd-amount').textContent = netAmount.toFixed(8);

            // Validate amount doesn't exceed available balance
            const withdrawalInput = document.getElementById('withdrawal_amount');
            if (amount > cashBalance) {
                withdrawalInput.setCustomValidity('Insufficient balance in Cash wallet');
            } else if (amount > 500) {
                withdrawalInput.setCustomValidity('Maximum withdrawal amount is $500');
            } else if (amount < 1 && amount > 0) {
                withdrawalInput.setCustomValidity('Minimum withdrawal amount is $1');
            } else {
                withdrawalInput.setCustomValidity('');
            }
        }

        // Connect Wallet for XEUSD Withdrawal
        async function connectWalletMarketplace() {
            // Check if Web3 wallet is installed
            if (typeof window.ethereum === 'undefined') {
                Swal.fire({
                    title: 'Wallet Not Found',
                    html: `
                        <p>No Web3 wallet extension detected in your browser.</p>
                        <p class="mt-3">
                            <a href="https://metamask.io/download/" target="_blank" class="btn btn-primary me-2">
                                <i class="fab fa-ethereum me-2"></i>
                                MetaMask
                            </a>
                            <a href="https://www.binance.com/en/web3wallet" target="_blank" class="btn btn-warning">
                                <i class="fas fa-wallet me-2"></i>
                                Binance Wallet
                            </a>
                        </p>
                        <div class="alert alert-info mt-3">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                After installing a wallet, refresh this page and try again
                            </small>
                        </div>
                    `,
                    icon: 'warning',
                    customClass: {
                        container: 'swal-high-zindex'
                    }
                });
                return;
            }

            try {
                // Request account access
                const accounts = await window.ethereum.request({
                    method: 'eth_requestAccounts'
                });

                if (!accounts || accounts.length === 0) {
                    throw new Error('No accounts found');
                }

                const walletAddress = accounts[0];

                // Switch to BSC network if not already on it
                await switchToBSCMarketplace();

                // Send wallet address to backend
                const response = await fetch('{{ route('user.withdrawal.xeusd.connect_wallet') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        wallet_address: walletAddress
                    })
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        title: 'Success!',
                        html: `
                            <p>${result.message}</p>
                            <p class="mt-3">
                                <strong>Connected Wallet:</strong><br>
                                <small>${walletAddress}</small>
                            </p>
                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    All your XEUSD withdrawals will be sent to this address
                                </small>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: 'Continue',
                        customClass: {
                            container: 'swal-high-zindex'
                        }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(result.message);
                }

            } catch (error) {
                console.error('Error connecting wallet:', error);

                let errorMessage = error.message || 'Failed to connect wallet';
                let helpText = '';

                if (error.code === 4001) {
                    errorMessage = 'Connection request was rejected';
                    helpText =
                        'You cancelled the connection request. Please try again and approve the connection in your wallet.';
                } else if (error.code === -32002) {
                    errorMessage = 'Connection request pending';
                    helpText =
                        'Please check your wallet extension. There might be a pending request waiting for your approval.';
                }

                Swal.fire({
                    title: 'Connection Failed',
                    html: `
                        <p>${errorMessage}</p>
                        ${helpText ? `<p class="text-muted mt-2"><small>${helpText}</small></p>` : ''}
                    `,
                    icon: 'error',
                    customClass: {
                        container: 'swal-high-zindex'
                    }
                });
            }
        }

        // Switch to BSC network for marketplace withdrawal
        async function switchToBSCMarketplace() {
            const bscChainId = '0x38'; // BSC Mainnet

            try {
                await window.ethereum.request({
                    method: 'wallet_switchEthereumChain',
                    params: [{
                        chainId: bscChainId
                    }]
                });
            } catch (switchError) {
                // This error code indicates that the chain has not been added to the wallet
                if (switchError.code === 4902) {
                    try {
                        await window.ethereum.request({
                            method: 'wallet_addEthereumChain',
                            params: [{
                                chainId: bscChainId,
                                chainName: 'BSC Mainnet',
                                nativeCurrency: {
                                    name: 'BNB',
                                    symbol: 'BNB',
                                    decimals: 18
                                },
                                rpcUrls: ['https://bsc-dataseed1.binance.org/'],
                                blockExplorerUrls: ['https://bscscan.com/']
                            }]
                        });
                    } catch (addError) {
                        throw new Error('Failed to add BSC network');
                    }
                } else {
                    throw new Error('Failed to switch to BSC network');
                }
            }
        }
    </script>
@endsection
