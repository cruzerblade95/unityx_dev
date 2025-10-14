@extends('user.layouts.layoutMaster')

@section('title', 'XEUSD Deposit')

<style>
    /* Enhanced deposit-direct form styling - Crypto green theme like withdrawal */
    .card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid #28a745;
        user-select: none;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, var(--bs-secondary-bg) 100%);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.12);
    }

    .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #28a745, #20c997);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2);
        border-color: #20c997;
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, rgba(32, 201, 151, 0.03) 100%);
    }

    .card-header {
        background: linear-gradient(135deg, #6f42c1 20%, #0d6efd 100%) !important;
        color: white !important;
        text-align: center !important;
        font-weight: 700 !important;
        font-size: 1.1rem !important;
        padding: 0.5rem 1rem !important;
        border: none !important;
        position: relative;
        z-index: 1;
    }

    .card-header h5 {
        margin: 0;
        font-size: 1.1rem;
    }

    .card-body {
        padding: 2rem 1.5rem;
        position: relative;
        z-index: 1;
    }

    .form-control {
        font-size: 1.1rem;
        padding: 1rem 1.25rem;
        border: 2px solid var(--bs-border-color);
        border-radius: 10px;
        transition: all 0.3s ease;
        background-color: var(--bs-body-bg);
        color: var(--bs-body-color);
    }

    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.3rem rgba(40, 167, 69, 0.15);
        transform: scale(1.02);
        background-color: var(--bs-body-bg);
    }

    .form-label {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--bs-emphasis-color);
        margin-bottom: 0.75rem;
    }

    .btn {
        font-size: 1.1rem;
        padding: 1rem 2rem;
        border-radius: 10px;
        font-weight: 700;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-primary {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, var(--bs-success) 0%, rgba(var(--bs-success-rgb), 0.8) 100%);
        border: none;
    }

    .btn-success:hover {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.9) 0%, var(--bs-success) 100%);
    }

    .btn-outline-secondary {
        border: 2px solid var(--bs-secondary);
        color: var(--bs-secondary);
        font-weight: 600;
        background: var(--bs-body-bg);
    }

    .btn-outline-secondary:hover {
        background: var(--bs-secondary);
        color: var(--bs-white);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(var(--bs-secondary-rgb), 0.3);
    }

    .alert {
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        border: 2px solid;
        position: relative;
        overflow: hidden;
    }

    .alert::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        transition: all 0.3s ease;
    }

    .alert-info {
        background: linear-gradient(135deg, var(--bs-info-bg-subtle) 0%, rgba(var(--bs-info-rgb), 0.1) 100%);
        border-color: var(--bs-info);
        color: var(--bs-info-text-emphasis);
    }

    .alert-info::before {
        background: linear-gradient(90deg, var(--bs-info), #20c997);
    }

    .alert-warning {
        background: linear-gradient(135deg, var(--bs-warning-bg-subtle) 0%, rgba(var(--bs-warning-rgb), 0.1) 100%);
        border-color: var(--bs-warning);
        color: var(--bs-warning-text-emphasis);
    }

    .alert-warning::before {
        background: linear-gradient(90deg, var(--bs-warning), #fd7e14);
    }

    .alert-success {
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, rgba(var(--bs-success-rgb), 0.1) 100%);
        border-color: var(--bs-success);
        color: var(--bs-success-text-emphasis);
    }

    .alert-success::before {
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
    }

    .alert-danger {
        background: linear-gradient(135deg, var(--bs-danger-bg-subtle) 0%, rgba(var(--bs-danger-rgb), 0.1) 100%);
        border-color: var(--bs-danger);
        color: var(--bs-danger-text-emphasis);
    }

    .alert-danger::before {
        background: linear-gradient(90deg, var(--bs-danger), #dc3545);
    }

    .alert-light {
        background: linear-gradient(135deg, var(--bs-light-bg-subtle, var(--bs-secondary-bg)) 0%, rgba(var(--bs-light-rgb, var(--bs-secondary-rgb)), 0.1) 100%);
        border-color: var(--bs-light, var(--bs-secondary));
        color: var(--bs-emphasis-color);
        position: relative;
        overflow: hidden;
        border: 3px solid var(--bs-secondary);
        box-shadow: 0 4px 15px rgba(var(--bs-secondary-rgb), 0.15);
    }

    .alert-light::before {
        background: linear-gradient(90deg, var(--bs-secondary), #20c997);
    }

    .input-group-text {
        background: var(--bs-tertiary-bg);
        border: 2px solid var(--bs-border-color);
        color: var(--bs-body-color);
        font-weight: 600;
        font-size: 1.1rem;
    }

    .deposit-section {
        background: var(--bs-body-bg);
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .payment-info {
        background: linear-gradient(135deg, var(--bs-secondary-bg) 0%, var(--bs-tertiary-bg) 100%);
        border: 3px solid #28a745;
        border-radius: 12px;
        padding: 1.5rem;
        margin: 1.5rem 0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.12);
    }

    .payment-info::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #28a745, #20c997);
        transition: all 0.3s ease;
    }

    .payment-info:hover {
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.18);
        transform: translateY(-2px);
    }

    .payment-info h6 {
        color: #28a745;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .fee-display {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--bs-success);
        text-align: center;
        padding: 1rem;
        background: rgba(var(--bs-success-rgb), 0.1);
        border-radius: 8px;
        margin-bottom: 1rem;
        border: 1px solid rgba(var(--bs-success-rgb), 0.2);
        position: relative;
        overflow: hidden;
    }

    .fee-display::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
    }

    /* Enhanced visibility for required fields */
    .text-danger {
        font-weight: 700;
        font-size: 1.2rem;
    }

    /* Better error styling */
    .is-invalid {
        border-color: var(--bs-danger);
        box-shadow: 0 0 0 0.3rem rgba(var(--bs-danger-rgb), 0.25);
    }

    .invalid-feedback {
        font-size: 1rem;
        font-weight: 700;
    }

    /* Better spacing for older users */
    .form-group {
        margin-bottom: 2rem;
    }

    .mb-3 {
        margin-bottom: 2rem !important;
    }

    .d-grid {
        margin-top: 2rem;
    }

    /* Code and address styling */
    code {
        background: var(--bs-secondary-bg);
        color: #28a745;
        padding: 0.5rem;
        border-radius: 6px;
        font-size: 0.9rem;
        border: 1px solid var(--bs-border-color);
        position: relative;
        overflow: hidden;
    }

    code::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #28a745, #20c997);
    }

    /* Table styling for history */
    .table {
        margin-bottom: 0;
        table-layout: fixed;
        width: 100%;
    }

    .table th {
        background-color: var(--bs-secondary-bg);
        border-bottom: 2px solid var(--bs-border-color);
        font-weight: 600;
        color: var(--bs-emphasis-color);
        white-space: nowrap;
    }

    .table td {
        vertical-align: top;
        border-bottom: 1px solid var(--bs-border-color-translucent);
        color: var(--bs-body-color);
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* Specific column width constraints */
    .table th:nth-child(1),
    .table td:nth-child(1) {
        width: 60px;
    }

    /* ID */
    .table th:nth-child(2),
    .table td:nth-child(2) {
        width: 100px;
    }

    /* Amount */
    .table th:nth-child(3),
    .table td:nth-child(3) {
        width: 70px;
    }

    /* Tax % */
    .table th:nth-child(4),
    .table td:nth-child(4) {
        width: 100px;
    }

    /* Tax Amount */
    .table th:nth-child(5),
    .table td:nth-child(5) {
        width: 100px;
    }

    /* Net Amount */
    .table th:nth-child(6),
    .table td:nth-child(6) {
        width: 90px;
    }

    /* Status */
    .table th:nth-child(7),
    .table td:nth-child(7) {
        width: 140px;
        min-height: 50px;
    }

    /* Payment Address */
    .table th:nth-child(8),
    .table td:nth-child(8) {
        width: 120px;
    }

    /* Payment ID */
    .table th:nth-child(9),
    .table td:nth-child(9) {
        width: 100px;
    }

    /* Reference */
    .table th:nth-child(10),
    .table td:nth-child(10) {
        width: 100px;
    }

    /* Note */
    .table th:nth-child(11),
    .table td:nth-child(11) {
        width: 130px;
    }

    /* Created At */

    /* Payment address column specific styling */
    .table td:nth-child(7) {
        line-height: 1.3;
        padding: 0.75rem 0.5rem;
    }

    .table td:nth-child(7) small {
        display: block;
        font-size: 0.8rem;
        line-height: 1.3;
    }

    .table td:nth-child(7) code {
        display: block;
        margin-top: 3px;
        padding: 2px 4px;
        font-size: 0.7rem;
        background: rgba(var(--bs-info-rgb), 0.1);
        border: 1px solid rgba(var(--bs-info-rgb), 0.2);
        border-radius: 3px;
        word-break: break-all;
    }

    .table-striped>tbody>tr:nth-of-type(odd)>td {
        background-color: var(--bs-tertiary-bg);
    }

    .table tbody tr:hover {
        background-color: var(--bs-secondary-bg);
        transition: background-color 0.2s ease;
    }

    /* Responsive table adjustments */
    @media (max-width: 992px) {

        .table th:nth-child(7),
        .table td:nth-child(7) {
            width: 120px;
        }

        .table td:nth-child(7) small {
            font-size: 0.75rem;
        }

        .table td:nth-child(7) code {
            font-size: 0.65rem;
        }
    }

    @media (max-width: 768px) {
        .table {
            font-size: 0.85rem;
        }

        .table th:nth-child(7),
        .table td:nth-child(7) {
            width: 100px;
        }

        .table td:nth-child(7) small {
            font-size: 0.7rem;
        }

        .table td:nth-child(7) code {
            font-size: 0.6rem;
            padding: 1px 2px;
        }
    }

    /* Copy button styling */
    .btn-sm {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }

    .btn-sm:hover {
        transform: translateY(-1px);
    }

    /* Dark theme specific adjustments */
    [data-bs-theme="dark"] .card {
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, rgba(var(--bs-secondary-rgb), 0.3) 100%);
    }

    [data-bs-theme="dark"] .card:hover {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.15) 0%, rgba(var(--bs-primary-rgb), 0.05) 100%);
    }

    [data-bs-theme="dark"] .payment-info {
        background: linear-gradient(135deg, rgba(var(--bs-secondary-rgb), 0.3) 0%, rgba(var(--bs-tertiary-rgb), 0.3) 100%);
    }

    [data-bs-theme="dark"] .fee-display {
        background: rgba(var(--bs-success-rgb), 0.15);
    }

    [data-bs-theme="dark"] .alert-info {
        background: linear-gradient(135deg, rgba(13, 202, 240, 0.15) 0%, rgba(13, 202, 240, 0.05) 100%);
    }

    [data-bs-theme="dark"] .alert-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(255, 193, 7, 0.05) 100%);
    }

    [data-bs-theme="dark"] .alert-success {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.15) 0%, rgba(25, 135, 84, 0.05) 100%);
    }

    [data-bs-theme="dark"] .alert-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.15) 0%, rgba(220, 53, 69, 0.05) 100%);
    }

    [data-bs-theme="dark"] .alert-light {
        background: linear-gradient(135deg, rgba(108, 117, 125, 0.15) 0%, rgba(108, 117, 125, 0.05) 100%);
    }

    [data-bs-theme="dark"] code {
        background: rgba(var(--bs-secondary-rgb), 0.3);
    }

    /* Enhanced Wallet Address Styling */
    .wallet-address-container {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-primary);
        border-radius: 6px;
        padding: 0.75rem;
        position: relative;
        overflow: hidden;
    }

    .wallet-address-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #28a745, #20c997);
    }

    .wallet-address-header h6 {
        font-size: 1.1rem;
        color: #28a745 !important;
    }

    .wallet-address-box {
        position: relative;
        background: #ffffff;
        border: 2px solid #28a745;
        border-radius: 12px;
        padding: 0;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.1);
    }

    .wallet-address-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.2);
        border-color: #20c997;
    }

    .wallet-address-content {
        padding: 0.75rem;
        background: var(--bs-body-bg);
        position: relative;
        z-index: 2;
    }

    .wallet-address-text {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--bs-emphasis-color) !important;
        background: none !important;
        border: none;
        word-break: break-all;
        line-height: 1.4;
        display: block;
        letter-spacing: 0.3px;
        font-family: 'Courier New', monospace;
    }

    .wallet-address-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.95) 0%, rgba(32, 201, 151, 0.95) 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: white;
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 3;
        backdrop-filter: blur(2px);
    }

    .wallet-address-box:hover .wallet-address-overlay {
        opacity: 1;
    }

    .wallet-address-overlay i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .wallet-address-overlay span {
        font-weight: 600;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .wallet-network-info {
        background: rgba(255, 193, 7, 0.1);
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 0.75rem;
        text-align: center;
    }

    /* Dark theme adjustments */
    [data-bs-theme="dark"] .wallet-address-container {
        background: var(--bs-body-bg);
        border-color: var(--bs-primary);
    }

    [data-bs-theme="dark"] .wallet-address-content {
        background: var(--bs-body-bg);
    }

    [data-bs-theme="dark"] .wallet-address-text {
        color: var(--bs-body-color) !important;
    }

    [data-bs-theme="dark"] .wallet-network-info {
        background: rgba(255, 193, 7, 0.15);
        border-color: #ffc107;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .wallet-address-text {
            font-size: 0.9rem;
            letter-spacing: 0.2px;
        }

        .wallet-address-overlay i {
            font-size: 1.5rem;
        }

        .wallet-address-overlay span {
            font-size: 0.9rem;
        }
    }
</style>

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/jquery/jquery.js'])
@endsection

@section('content')
    {{-- @include('components.coming-soon-overlay') --}}
    <div class="container py-4 position-relative" style="z-index: 1;">
        <h2><i class="fas fa-coins me-2"></i>XEUSD Deposit (0% Fee)</h2>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="row">
            <!-- XEUSD Deposit Form -->
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-wallet me-2"></i>
                            XEUSD Token Deposit (0% Fee)
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Info Alert -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>How to Deposit XEUSD</h6>
                            <ol class="mb-0 mt-2">
                                <li>Send XEUSD tokens from your wallet to our deposit address below</li>
                                <li>Wait for blockchain confirmation (usually 1-2 minutes)</li>
                                <li>Copy your transaction hash and paste it in the form below</li>
                                <li>Submit and wait for admin approval</li>
                            </ol>
                        </div>

                        <!-- XEUSD Deposit Wallet Info -->
                        <div class="payment-info">
                            <h6><i class="fas fa-wallet me-2"></i>Send XEUSD to this Address:</h6>

                            <!-- Wallet Address Display with Click to Copy -->
                            <div class="wallet-address-box mb-3">
                                <div class="wallet-address-content">
                                    <code class="wallet-address-text" id="walletAddress">{{ $xeusdWalletAddress }}</code>
                                </div>
                                <div class="wallet-address-overlay">
                                    <i class="fas fa-copy"></i>
                                    <span>Click to Copy</span>
                                </div>
                            </div>

                            <!-- Network Information -->
                            <div class="wallet-network-info">
                                <small>
                                    <strong><i class="fas fa-network-wired me-1"></i>Network:</strong>
                                    {{ $xeusdNetworkInfo['network'] ?? 'BSC Mainnet' }}<br>
                                    <strong><i class="fas fa-link me-1"></i>Chain ID:</strong>
                                    {{ $xeusdNetworkInfo['chain_id'] ?? '56' }}<br>
                                    <strong><i class="fas fa-file-contract me-1"></i>Token Contract:</strong>
                                    <a href="https://bscscan.com/token/{{ $xeusdNetworkInfo['contract_address'] ?? '' }}"
                                        target="_blank" class="text-decoration-none">
                                        {{ substr($xeusdNetworkInfo['contract_address'] ?? '', 0, 10) }}...{{ substr($xeusdNetworkInfo['contract_address'] ?? '', -8) }}
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </small>
                            </div>
                        </div>

                        <!-- Deposit Form -->
                        <form method="POST" action="{{ route('user.deposit.direct.submit') }}" id="xeusdDepositForm">
                            @csrf

                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (XEUSD) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                                        required min="1" placeholder="Enter amount you sent">
                                    <span class="input-group-text">XEUSD</span>
                                </div>
                                <div class="form-text">Minimum deposit: 1 XEUSD | <strong class="text-success">0%
                                        Fee</strong></div>

                                <!-- Fee Display -->
                                <div class="fee-display mt-2">
                                    <i class="fas fa-gift me-2"></i>
                                    <strong>Zero Fees!</strong> You receive exactly what you deposit.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="transaction_hash" class="form-label">Transaction Hash <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="transaction_hash" name="transaction_hash"
                                    required placeholder="0x..." minlength="66" maxlength="66">
                                <div class="form-text">
                                    Enter the transaction hash (TX hash) from your wallet after sending XEUSD.
                                    <a href="https://bscscan.com/" target="_blank">Check on BSCScan <i
                                            class="fas fa-external-link-alt ms-1"></i></a>
                                </div>
                            </div>

                            <!-- Warning about verification -->
                            <div class="alert alert-warning">
                                <small><strong><i class="fas fa-exclamation-triangle me-2"></i>Important:</strong></small>
                                <ul class="mb-0 mt-1 small">
                                    <li>Transaction will be automatically verified on the blockchain</li>
                                    <li>Amount must match what you actually sent</li>
                                    <li>Make sure transaction is confirmed before submitting</li>
                                    <li>Each transaction hash can only be used once</li>
                                </ul>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Submit Deposit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Additional Info Card -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="fas fa-question-circle me-2"></i>Why Use XEUSD Deposit?</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="small">
                                    <li><strong class="text-success">0% Fees</strong> - No deposit fees</li>
                                    <li><strong>Fast</strong> - Blockchain confirmed in minutes</li>
                                    <li><strong>Secure</strong> - Automatic blockchain verification</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="small">
                                    <li><strong>Transparent</strong> - Verify on BSCScan</li>
                                    <li><strong>Simple</strong> - Just send & submit TX hash</li>
                                    <li><strong>Direct</strong> - No intermediaries</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    XEUSD Deposit History
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount (XEUSD)</th>
                                <th>Status</th>
                                <th>Transaction Hash</th>
                                <th>From Address</th>
                                <th>Reference</th>
                                <th>Note</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deposits as $deposit)
                                <tr>
                                    <td>{{ $deposit->id }}</td>
                                    <td>
                                        <strong>${{ number_format($deposit->amount, 2) }}</strong>
                                        <br>
                                        <small class="text-success">
                                            <i class="fas fa-gift"></i> 0% Fee
                                        </small>
                                    </td>
                                    <td>
                                        @if ($deposit->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($deposit->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($deposit->status === 'processing')
                                            <span class="badge bg-info">Processing</span>
                                        @elseif($deposit->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($deposit->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($deposit->payment_id)
                                            <small>
                                                <a href="https://bscscan.com/tx/{{ $deposit->payment_id }}"
                                                    target="_blank" class="text-decoration-none"
                                                    title="{{ $deposit->payment_id }}">
                                                    {{ substr($deposit->payment_id, 0, 10) }}...{{ substr($deposit->payment_id, -8) }}
                                                    <i class="fas fa-external-link-alt ms-1"></i>
                                                </a>
                                            </small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($deposit->payment_address)
                                            <small>
                                                <a href="https://bscscan.com/address/{{ $deposit->payment_address }}"
                                                    target="_blank" class="text-decoration-none"
                                                    title="{{ $deposit->payment_address }}">
                                                    {{ substr($deposit->payment_address, 0, 6) }}...{{ substr($deposit->payment_address, -6) }}
                                                    <i class="fas fa-external-link-alt ms-1"></i>
                                                </a>
                                            </small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $deposit->reference }}</td>
                                    <td>{{ $deposit->note }}</td>
                                    <td>{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No XEUSD deposits found. Make your first deposit above!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('xeusdDepositForm');
            const submitBtn = document.getElementById('submitBtn');
            const amountInput = document.getElementById('amount');
            const txHashInput = document.getElementById('transaction_hash');

            // Form validation before submit
            form.addEventListener('submit', function(e) {
                const amount = parseFloat(amountInput.value);
                const txHash = txHashInput.value.trim();

                // Validate amount
                if (!amount || amount < 1) {
                    e.preventDefault();
                    alert('Please enter a valid amount (minimum 1 XEUSD).');
                    amountInput.focus();
                    return;
                }

                // Validate transaction hash
                if (!txHash) {
                    e.preventDefault();
                    alert('Please enter your transaction hash.');
                    txHashInput.focus();
                    return;
                }

                // Check if transaction hash looks valid (0x followed by 64 hex characters)
                if (!txHash.match(/^0x[a-fA-F0-9]{64}$/)) {
                    e.preventDefault();
                    alert(
                        'Please enter a valid BSC transaction hash (should start with 0x and be 66 characters long).'
                        );
                    txHashInput.focus();
                    return;
                }

                // Disable submit button to prevent double submission
                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<i class="fas fa-spinner fa-spin me-2"></i>Verifying on blockchain...';
            });

            // Auto-format transaction hash (add 0x if missing)
            txHashInput.addEventListener('blur', function() {
                let value = this.value.trim();
                if (value && !value.startsWith('0x')) {
                    this.value = '0x' + value;
                }
            });
        });

        // Function to copy text to clipboard
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.textContent;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    // Show success message
                    alert('Copied to clipboard: ' + text);
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Copied to clipboard: ' + text);
            }
        }

        // Make wallet address box clickable for copying
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(event) {
                if (event.target.closest('.wallet-address-box')) {
                    copyToClipboard('walletAddress');

                    // Visual feedback
                    const walletBox = event.target.closest('.wallet-address-box');
                    const overlay = walletBox.querySelector('.wallet-address-overlay');

                    if (overlay) {
                        overlay.innerHTML = '<i class="fas fa-check-circle"></i><span>Copied!</span>';
                        overlay.style.opacity = '1';

                        setTimeout(function() {
                            overlay.innerHTML =
                                '<i class="fas fa-copy"></i><span>Click to Copy</span>';
                            overlay.style.opacity = '0';
                        }, 1500);
                    }
                }
            });
        });
    </script>

@endsection
