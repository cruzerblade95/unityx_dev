@extends('user.layouts.layoutMaster')

@section('title', 'Re-Investment')

@section('vendor-style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

<style>
    /* Enhanced re-investment form styling - Crypto green theme like deposit */
    .re-invest-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid var(--bs-success);
        user-select: none;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, var(--bs-secondary-bg) 100%);
        box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.15);
    }

    .re-invest-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
        transition: all 0.3s ease;
    }

    .re-invest-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(var(--bs-success-rgb), 0.3);
        border-color: rgba(var(--bs-success-rgb), 0.8);
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, rgba(var(--bs-success-rgb), 0.1) 100%);
    }

    .card-header-custom {
        background: linear-gradient(135deg, var(--bs-success) 0%, rgba(var(--bs-success-rgb), 0.8) 100%);
        color: white;
        text-align: center;
        font-weight: 700;
        font-size: 1.5rem;
        padding: 2rem 1.5rem;
        border: none;
        position: relative;
        z-index: 1;
    }

    .balance-display {
        background: linear-gradient(135deg, var(--bs-secondary-bg) 0%, var(--bs-tertiary-bg) 100%);
        border: 3px solid var(--bs-success);
        border-radius: 12px;
        padding: 1.5rem;
        margin: 1.5rem 0;
        box-shadow: 0 4px 15px rgba(var(--bs-success-rgb), 0.15);
        transition: all 0.3s ease;
    }

    .info-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 3px solid var(--bs-info);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, var(--bs-secondary-bg) 100%);
        box-shadow: 0 4px 15px rgba(var(--bs-info-rgb), 0.15);
    }

    .info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--bs-info), var(--bs-success));
        transition: all 0.3s ease;
    }

    .info-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(var(--bs-info-rgb), 0.3);
        border-color: rgba(var(--bs-info-rgb), 0.8);
        background: linear-gradient(135deg, var(--bs-info-bg-subtle) 0%, rgba(var(--bs-info-rgb), 0.1) 100%);
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
        border-color: var(--bs-success);
        box-shadow: 0 0 0 0.3rem rgba(var(--bs-success-rgb), 0.25);
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
        background: linear-gradient(135deg, var(--bs-success) 0%, rgba(var(--bs-success-rgb), 0.8) 100%);
        border: none;
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.9) 0%, var(--bs-success) 100%);
        color: white;
    }

    .quick-amount {
        transition: all 0.3s ease;
        background: var(--bs-body-bg);
        border: 2px solid var(--bs-success);
        color: var(--bs-success);
        font-weight: 700;
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
    }

    .quick-amount:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(var(--bs-success-rgb), 0.3);
        background: var(--bs-success);
        color: white;
    }

    .quick-amount.active {
        background: var(--bs-success);
        color: white;
        border-color: var(--bs-success);
    }

    .investment-preview {
        background: linear-gradient(135deg, var(--bs-secondary-bg) 0%, var(--bs-tertiary-bg) 100%);
        border: 3px solid var(--bs-success);
        border-radius: 12px;
        padding: 1.5rem;
        margin: 1.5rem 0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .investment-preview::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--bs-success), var(--bs-info));
        transition: all 0.3s ease;
    }

    .investment-preview.show {
        border-color: var(--bs-success);
        background: linear-gradient(135deg, var(--bs-success-bg-subtle) 0%, rgba(var(--bs-success-rgb), 0.1) 100%);
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

    .alert-warning {
        background: linear-gradient(135deg, var(--bs-warning-bg-subtle) 0%, rgba(var(--bs-warning-rgb), 0.1) 100%);
        border-color: var(--bs-warning);
        color: var(--bs-warning-text-emphasis);
    }

    .alert-warning::before {
        background: linear-gradient(90deg, var(--bs-warning), #fd7e14);
    }

    .input-group-text {
        background: var(--bs-tertiary-bg);
        border: 2px solid var(--bs-border-color);
        color: var(--bs-body-color);
        font-weight: 600;
        font-size: 1.1rem;
    }

    .feature-item {
        transition: transform 0.2s ease;
        padding: 0.5rem 0;
    }

    .feature-item:hover {
        transform: translateX(5px);
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

    /* Better spacing */
    .form-group {
        margin-bottom: 2rem;
    }

    .d-grid {
        margin-top: 2rem;
    }

    /* Dark theme specific adjustments */
    [data-bs-theme="dark"] .re-invest-card {
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, rgba(var(--bs-secondary-rgb), 0.3) 100%);
    }

    [data-bs-theme="dark"] .re-invest-card:hover {
        background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.15) 0%, rgba(var(--bs-success-rgb), 0.05) 100%);
    }

    [data-bs-theme="dark"] .info-card {
        background: linear-gradient(135deg, var(--bs-body-bg) 0%, rgba(var(--bs-secondary-rgb), 0.3) 100%);
    }

    [data-bs-theme="dark"] .info-card:hover {
        background: linear-gradient(135deg, rgba(var(--bs-info-rgb), 0.15) 0%, rgba(var(--bs-info-rgb), 0.05) 100%);
    }

    [data-bs-theme="dark"] .balance-display {
        background: linear-gradient(135deg, rgba(var(--bs-secondary-rgb), 0.3) 0%, rgba(var(--bs-tertiary-rgb), 0.3) 100%);
    }

    [data-bs-theme="dark"] .investment-preview {
        background: linear-gradient(135deg, rgba(var(--bs-secondary-rgb), 0.3) 0%, rgba(var(--bs-tertiary-rgb), 0.3) 100%);
    }

    [data-bs-theme="dark"] .alert-success {
        background: linear-gradient(135deg, rgba(25, 135, 84, 0.15) 0%, rgba(25, 135, 84, 0.05) 100%);
    }

    [data-bs-theme="dark"] .alert-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.15) 0%, rgba(220, 53, 69, 0.05) 100%);
    }

    [data-bs-theme="dark"] .alert-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(255, 193, 7, 0.05) 100%);
    }
</style>

@section('content')
    <div class="container py-4 position-relative" style="z-index: 1;">
        <div class="deposit-header">
            <h2>
                <i class="fas fa-recycle me-2"></i>
                Re-Investment Center
            </h2>
        </div>
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('warning') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="row">
            <!-- Re-Investment Form -->
            <div class="col-lg-8">
                <div class="card re-invest-card shadow-lg border-0 mb-4">
                    <div class="card-header bg-gradient-primary text-white p-4 text-center">
                        <i class="fas fa-recycle text-white mb-2" style="font-size: 2rem;"></i>
                        <h4 class="mb-0 text-white font-weight-bold">
                            Re-Investment Portal
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Current Balance Display -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="balance-display text-center profit-balance">
                                    <h6 class="mb-2">
                                        <i class="fas fa-chart-line me-2"></i>Profit Balance
                                    </h6>
                                    <h3 class="mb-0 font-weight-bold">
                                        ${{ number_format($profitBalance, 2) }}
                                    </h3>
                                    <small>Available for re-investment</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="balance-display text-center bonus-balance">
                                    <h6 class="mb-2">
                                        <i class="fas fa-gift me-2"></i>Bonus Balance
                                    </h6>
                                    <h3 class="mb-0 font-weight-bold">
                                        ${{ number_format($bonusBalance, 2) }}
                                    </h3>
                                    <small>Available for re-investment</small>
                                </div>
                            </div>
                        </div>

                        <!-- Re-Investment Form -->
                        <form method="POST" action="{{ route('user.re-invest.store') }}" id="reInvestForm">
                            @csrf

                            <div class="row">
                                <!-- Wallet Type Selection -->
                                <div class="col-md-12">
                                    <div class="form-group mb-4">
                                        <label for="wallet_type" class="form-label">
                                            <i class="fas fa-wallet me-1"></i>
                                            Select Wallet Type <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex gap-3">
                                            <div class="flex-fill">
                                                <input type="radio"
                                                    class="btn-check @error('wallet_type') is-invalid @enderror"
                                                    name="wallet_type" id="profit_wallet" value="profit"
                                                    {{ old('wallet_type', 'profit') === 'profit' ? 'checked' : '' }}
                                                    required>
                                                <label class="btn btn-outline-success w-100 py-3" for="profit_wallet">
                                                    <i class="fas fa-chart-line me-2"></i>
                                                    <div>
                                                        <strong>Profit Wallet</strong>
                                                        <div class="small">${{ number_format($profitBalance, 2) }}</div>
                                                    </div>
                                                </label>
                                            </div>
                                            <div class="flex-fill">
                                                <input type="radio"
                                                    class="btn-check @error('wallet_type') is-invalid @enderror"
                                                    name="wallet_type" id="bonus_wallet" value="bonus"
                                                    {{ old('wallet_type') === 'bonus' ? 'checked' : '' }} required>
                                                <label class="btn btn-outline-warning w-100 py-3" for="bonus_wallet">
                                                    <i class="fas fa-gift me-2"></i>
                                                    <div>
                                                        <strong>Bonus Wallet</strong>
                                                        <div class="small">${{ number_format($bonusBalance, 2) }}</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        @error('wallet_type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group mb-4">
                                        <label for="amount" class="form-label">
                                            <i class="fas fa-dollar-sign me-1"></i>
                                            Re-Investment Amount <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                                id="amount" name="amount" step="0.01"
                                                min="{{ min($profitSettings ? $profitSettings->min_amount : 1, $bonusSettings ? $bonusSettings->min_amount : 1) }}"
                                                max="{{ max($profitBalance, $bonusBalance) }}" placeholder="0.00"
                                                value="{{ old('amount') }}" required>
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Amount will be transferred from selected wallet to deposit, then automatically
                                            invested in AI Trading
                                            <br>
                                            <small id="wallet-limits-info" class="text-muted">
                                                <!-- Dynamic limits will be shown here -->
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div class="mb-4">
                                <label class="form-label">Quick Selection:</label>
                                <div class="d-flex flex-wrap gap-2" id="quickAmountButtons">
                                    <!-- Buttons will be dynamically generated based on selected wallet -->
                                </div>
                            </div>

                            <!-- Expected Returns Info -->
                            <div class="investment-preview mb-4 p-3 rounded bg-light" id="investmentPreview"
                                style="display: none;">
                                                                <h6 class="mb-3">
                                    <i class="fas fa-chart-line me-2"></i>Investment Preview
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Investment Amount:</small>
                                        <div class="font-weight-bold" id="previewAmount">$0.00</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Expected Return (5x):</small>
                                        <div class="font-weight-bold text-success" id="previewReturn">$0.00</div>
                                    </div>
                                </div>
                                <div class="row mt-2" id="taxPreview" style="display: none;">
                                    <div class="col-md-6">
                                        <small class="text-muted">Tax Amount:</small>
                                        <div class="font-weight-bold text-warning" id="previewTax">$0.00</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Net Investment:</small>
                                        <div class="font-weight-bold text-info" id="previewNet">$0.00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Confirmation Checkbox -->
                            <div class="form-check mb-4">
                                <input class="form-check-input @error('confirm_transfer') is-invalid @enderror"
                                    type="checkbox" id="confirm_transfer" name="confirm_transfer" value="1"
                                    {{ old('confirm_transfer') ? 'checked' : '' }} required>
                                <label class="form-check-label" for="confirm_transfer">
                                    I confirm that I want to transfer the specified amount from my selected wallet to
                                    deposit balance and automatically invest it in AI Trading with 5x potential return.
                                </label>
                                @error('confirm_transfer')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn bg-gradient-success btn-lg" id="submitBtn"
                                    @if ($profitBalance <= 0 && $bonusBalance <= 0) disabled @endif>
                                    <i class="fas fa-recycle me-2"></i>
                                    Process Re-Investment
                                </button>
                            </div>

                            @if ($profitBalance <= 0 && $bonusBalance <= 0)
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    You need profit or bonus balance to make a re-investment.
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- AI Trading Info Card -->
                <div class="card mx-3 mb-4 info-card shadow border-0">
                    <div class="card-header bg-gradient-info text-white p-3">
                        <h6 class="mb-0 text-white">
                            <i class="fas fa-robot me-2"></i>AI Trading Benefits
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="feature-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-sm bg-gradient-success text-white rounded-circle me-3">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">5x Returns</h6>
                                    <small class="text-muted">Potential 500% profit</small>
                                </div>
                            </div>
                        </div>
                        <div class="feature-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-sm bg-gradient-primary text-white rounded-circle me-3">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Automated</h6>
                                    <small class="text-muted">No manual intervention</small>
                                </div>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-sm bg-gradient-warning text-white rounded-circle me-3">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Secure</h6>
                                    <small class="text-muted">Protected investments</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent AI Trades -->
                @if ($recentAiTrades->count() > 0)
                    <div class="card mx-3 mb-4 shadow border-0">
                        <div class="card-header bg-gradient-dark text-white p-3">
                            <h6 class="mb-0 text-white">
                                <i class="fas fa-history me-2"></i>Recent AI Trades
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            @foreach ($recentAiTrades as $trade)
                                <div class="d-flex align-items-center p-3 border-bottom">
                                    <div class="icon icon-sm bg-gradient-primary text-white rounded-circle me-3">
                                        <i class="fas fa-robot"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">${{ number_format($trade->amount_invest, 2) }}</h6>
                                        <small
                                            class="text-muted">{{ \Carbon\Carbon::parse($trade->created_at)->diffForHumans() }}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-success">5x Return</small>
                                        <div class="font-weight-bold text-success">
                                            ${{ number_format($trade->ending_amount, 2) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div>
    </div>

    <!-- Re-Investment History Section -->
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow border-0">
                    <div class="card-header bg-gradient-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Re-Investment History
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if ($reInvestHistory->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Investment Details</th>
                                            <th>Wallet Type</th>
                                            <th>Expected Return</th>
                                            <th>Current Value</th>
                                            <th>Status</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reInvestHistory as $reinvestment)
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span
                                                            class="fw-medium">{{ \Carbon\Carbon::parse($reinvestment->created_at)->format('M d, Y') }}</span>
                                                        <small
                                                            class="text-muted">{{ \Carbon\Carbon::parse($reinvestment->created_at)->format('H:i A') }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <span class="fw-bold text-primary">${{ number_format($reinvestment->original_amount, 2) }}</span>
                                                        <small class="text-muted d-block">Original Amount</small>
                                                    </div>
                                                    @if($reinvestment->tax_amount > 0)
                                                        <div class="mt-1">
                                                            <span class="fw-bold text-warning">${{ number_format($reinvestment->tax_amount, 2) }}</span>
                                                            <small class="text-muted d-block">Tax Deducted</small>
                                                        </div>
                                                    @endif
                                                    <div class="mt-1">
                                                        <span class="fw-bold text-info">${{ number_format($reinvestment->net_amount, 2) }}</span>
                                                        <small class="text-muted d-block">Net Invested</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $reinvestment->wallet_type === 'profit' ? 'warning' : 'info' }}">
                                                        {{ ucfirst($reinvestment->wallet_type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="fw-bold text-success">${{ number_format($reinvestment->expected_return ?? 0, 2) }}</span>
                                                    <small class="text-muted d-block">5x Return</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $currentValue = $reinvestment->aiTrade
                                                            ? ($reinvestment->aiTrade->current_amount ?? $reinvestment->net_amount)
                                                            : $reinvestment->net_amount;
                                                        $profit = $currentValue - $reinvestment->net_amount;
                                                        $profitClass = $profit >= 0 ? 'text-success' : 'text-danger';
                                                    @endphp
                                                    <div>
                                                        <span
                                                            class="fw-bold">${{ number_format($currentValue, 2) }}</span>
                                                        <div>
                                                            <small class="{{ $profitClass }}">
                                                                {{ $profit >= 0 ? '+' : '' }}${{ number_format($profit, 2) }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusClass = match ($reinvestment->status) {
                                                            'completed' => 'success',
                                                            'pending' => 'warning',
                                                            'failed' => 'danger',
                                                            'cancelled' => 'secondary',
                                                            default => 'primary',
                                                        };
                                                        $statusIcon = match ($reinvestment->status) {
                                                            'completed' => 'fas fa-check',
                                                            'pending' => 'fas fa-clock',
                                                            'failed' => 'fas fa-times',
                                                            'cancelled' => 'fas fa-ban',
                                                            default => 'fas fa-cog',
                                                        };
                                                    @endphp
                                                    <span class="badge bg-{{ $statusClass }}">
                                                        <i class="{{ $statusIcon }} me-1"></i>{{ ucfirst($reinvestment->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $progress = $reinvestment->progress_percentage ?? 0;
                                                        $progressClass =
                                                            $progress >= 75
                                                                ? 'bg-success'
                                                                : ($progress >= 25
                                                                    ? 'bg-warning'
                                                                    : 'bg-info');
                                                    @endphp
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                            <div class="progress-bar {{ $progressClass }}"
                                                                role="progressbar" style="width: {{ $progress }}%"
                                                                aria-valuenow="{{ $progress }}" aria-valuemin="0"
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <small
                                                            class="text-muted">{{ number_format($progress, 1) }}%</small>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-history fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No Re-Investment History</h5>
                                <p class="text-muted">Your re-investment transactions will appear here once you start
                                    investing.</p>
                            </div>
                        @endif
                    </div>
                    @if ($reInvestHistory->count() > 0)
                        <div class="card-footer bg-light">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="d-flex flex-column">
                                        <span
                                            class="h5 mb-0 text-primary">${{ number_format($reInvestHistory->sum('original_amount'), 2) }}</span>
                                        <small class="text-muted">Total Original Amount</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex flex-column">
                                        <span
                                            class="h5 mb-0 text-info">${{ number_format($reInvestHistory->sum('net_amount'), 2) }}</span>
                                        <small class="text-muted">Net Invested</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex flex-column">
                                        <span
                                            class="h5 mb-0 text-success">${{ number_format($reInvestHistory->sum('expected_return'), 2) }}</span>
                                        <small class="text-muted">Expected Returns</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex flex-column">
                                        @php
                                            $totalCurrent = 0;
                                            $totalNetInvested = $reInvestHistory->sum('net_amount');
                                            foreach ($reInvestHistory as $reinvestment) {
                                                $currentValue = $reinvestment->aiTrade
                                                    ? ($reinvestment->aiTrade->current_amount ?? $reinvestment->net_amount)
                                                    : $reinvestment->net_amount;
                                                $totalCurrent += $currentValue;
                                            }
                                            $totalProfit = $totalCurrent - $totalNetInvested;
                                        @endphp
                                        <span class="h5 mb-0 {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $totalProfit >= 0 ? '+' : '' }}${{ number_format($totalProfit, 2) }}
                                        </span>
                                        <small class="text-muted">Total P&L</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <style>
        /* Re-Investment page theme-adaptive styling */
        .re-invest-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .re-invest-card .card-header {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark, #5a67d8) 100%);
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }

        .balance-display {
            background: linear-gradient(135deg, var(--bs-primary-bg-subtle, #e7e9ff) 0%, var(--bs-secondary-bg-subtle, #f1f3f4) 100%);
            border: 1px solid var(--bs-primary-border-subtle, rgba(var(--bs-primary-rgb), 0.2));
            color: var(--bs-emphasis-color);
            border-radius: 12px !important;
            box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.1);
            padding: 1.5rem 1rem;
            transition: all 0.3s ease;
        }

        .balance-display.profit-balance {
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%);
            border: 2px solid rgba(25, 135, 84, 0.3);
        }

        .balance-display.bonus-balance {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
            border: 2px solid rgba(255, 193, 7, 0.3);
        }

        [data-bs-theme="dark"] .balance-display {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.25) 0%, rgba(108, 117, 125, 0.2) 100%);
            border: 2px solid rgba(13, 110, 253, 0.4);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        [data-bs-theme="dark"] .balance-display.profit-balance {
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.3) 0%, rgba(25, 135, 84, 0.15) 100%);
            border: 2px solid rgba(25, 135, 84, 0.5);
        }

        [data-bs-theme="dark"] .balance-display.bonus-balance {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.3) 0%, rgba(255, 193, 7, 0.15) 100%);
            border: 2px solid rgba(255, 193, 7, 0.5);
        }

        /* Wallet selection styling */
        .btn-check:checked+.btn-outline-success {
            background-color: var(--bs-success);
            border-color: var(--bs-success);
            color: var(--bs-white);
        }

        .btn-check:checked+.btn-outline-warning {
            background-color: var(--bs-warning);
            border-color: var(--bs-warning);
            color: var(--bs-dark);
        }

        .btn-outline-success:hover,
        .btn-outline-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .info-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .info-card {
            background: #1a1d29;
            border: 1px solid #373a47;
        }

        .info-card .card-header {
            background: linear-gradient(135deg, var(--bs-info) 0%, var(--bs-info-dark, #0ea5e9) 100%);
            border: none;
        }

        .feature-item {
            transition: transform 0.2s ease;
            padding: 0.5rem 0;
        }

        .feature-item:hover {
            transform: translateX(5px);
        }

        .quick-amount {
            transition: all 0.2s ease;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-primary);
            color: var(--bs-primary);
        }

        [data-bs-theme="dark"] .quick-amount {
            background: #1a1d29;
            border-color: #0d6efd;
            color: #0d6efd;
        }

        .quick-amount:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(var(--bs-primary-rgb), 0.2);
            background: var(--bs-primary);
            color: var(--bs-white);
        }

        .quick-amount.active {
            background: var(--bs-primary);
            color: var(--bs-white);
            border-color: var(--bs-primary);
        }

        .investment-preview {
            background: var(--bs-light-bg-subtle, #f8f9fa);
            border: 2px dashed var(--bs-border-color-translucent);
            color: var(--bs-body-color);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .investment-preview.show {
            border-color: var(--bs-success);
            background: var(--bs-success-bg-subtle);
            color: var(--bs-success-text-emphasis);
        }

        [data-bs-theme="dark"] .investment-preview {
            background: #1a1d29;
            border-color: #373a47;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .investment-preview.show {
            background: rgba(25, 135, 84, 0.2);
            border-color: #198754;
            color: #75b798;
        }

        /* Form controls theme adaptation */
        .form-control {
            background: var(--bs-body-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }

        [data-bs-theme="dark"] .form-control {
            background: #1a1d29;
            border-color: #373a47;
            color: #ffffff;
        }

        .form-control:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
            background: var(--bs-body-bg);
        }

        [data-bs-theme="dark"] .form-control:focus {
            background: #1a1d29;
            border-color: #0d6efd;
            color: #ffffff;
        }

        .input-group-text {
            background: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }

        [data-bs-theme="dark"] .input-group-text {
            background: #2b2f3a;
            border-color: #373a47;
            color: #ffffff;
        }

        /* Alert theme adaptation */
        .alert-success {
            background: var(--bs-success-bg-subtle);
            border-color: var(--bs-success-border-subtle);
            color: var(--bs-success-text-emphasis);
        }

        [data-bs-theme="dark"] .alert-success {
            background: rgba(25, 135, 84, 0.2);
            border-color: rgba(25, 135, 84, 0.4);
            color: #75b798;
        }

        .alert-danger {
            background: var(--bs-danger-bg-subtle);
            border-color: var(--bs-danger-border-subtle);
            color: var(--bs-danger-text-emphasis);
        }

        [data-bs-theme="dark"] .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.4);
            color: #ea868f;
        }

        .alert-warning {
            background: var(--bs-warning-bg-subtle);
            border-color: var(--bs-warning-border-subtle);
            color: var(--bs-warning-text-emphasis);
        }

        [data-bs-theme="dark"] .alert-warning {
            background: rgba(255, 193, 7, 0.2);
            border-color: rgba(255, 193, 7, 0.4);
            color: #ffda6a;
        }

        /* Icon colors */
        .icon {
            background: var(--bs-primary);
            color: var(--bs-white);
        }

        .icon.bg-gradient-success {
            background: linear-gradient(135deg, var(--bs-success) 0%, var(--bs-success-dark, #198754) 100%);
        }

        .icon.bg-gradient-primary {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark, #5a67d8) 100%);
        }

        .icon.bg-gradient-warning {
            background: linear-gradient(135deg, var(--bs-warning) 0%, var(--bs-warning-dark, #e0a800) 100%);
        }

        .icon.bg-gradient-info {
            background: linear-gradient(135deg, var(--bs-info) 0%, var(--bs-info-dark, #0ea5e9) 100%);
        }

        .icon.bg-gradient-dark {
            background: linear-gradient(135deg, var(--bs-dark) 0%, var(--bs-gray-800, #343a40) 100%);
        }

        /* Text colors for dark mode */
        [data-bs-theme="dark"] .text-white-75 {
            color: rgba(255, 255, 255, 0.75) !important;
        }

        [data-bs-theme="dark"] .text-white-50 {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        [data-bs-theme="dark"] .text-muted {
            color: #8a8d93 !important;
        }

        [data-bs-theme="dark"] .form-text {
            color: #8a8d93 !important;
        }

        [data-bs-theme="dark"] .form-label {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .form-check-label {
            color: #ffffff !important;
        }

        /* Card backgrounds in dark mode */
        [data-bs-theme="dark"] .card {
            background: #1a1d29;
            border-color: #373a47;
            color: #ffffff;
        }

        [data-bs-theme="dark"] .card-header {
            border-color: #373a47;
        }

        [data-bs-theme="dark"] .card-body {
            background: #1a1d29;
            color: #ffffff;
        }

        /* Main container dark theme */
        [data-bs-theme="dark"] .container-fluid {
            background: transparent;
        }

        /* Ensure text readability */
        [data-bs-theme="dark"] h1,
        [data-bs-theme="dark"] h2,
        [data-bs-theme="dark"] h3,
        [data-bs-theme="dark"] h4,
        [data-bs-theme="dark"] h5,
        [data-bs-theme="dark"] h6,
        [data-bs-theme="dark"] p,
        [data-bs-theme="dark"] span,
        [data-bs-theme="dark"] div:not(.icon):not(.btn) {
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] small {
            color: #8a8d93 !important;
        }

        /* Button theme adaptation */
        .btn-outline-primary {
            color: var(--bs-primary);
            border-color: var(--bs-primary);
        }

        .btn-outline-primary:hover {
            background: var(--bs-primary);
            color: var(--bs-white);
        }

        .btn-outline-success {
            color: var(--bs-success);
            border-color: var(--bs-success);
        }

        .btn-outline-success:hover {
            background: var(--bs-success);
            color: var(--bs-white);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .quick-amount {
                flex: 1;
                min-width: calc(50% - 0.25rem);
            }

            .re-invest-card,
            .info-card {
                margin: 0 1rem;
            }
        }

        /* Enhanced hover effects */
        .feature-item:hover .icon {
            transform: scale(1.1);
        }

        .card:hover {
            box-shadow: 0 8px 25px rgba(var(--bs-primary-rgb), 0.15);
        }

        [data-bs-theme="dark"] .card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount');
            const quickAmountContainer = document.getElementById('quickAmountButtons');
            const investmentPreview = document.getElementById('investmentPreview');
            const previewAmount = document.getElementById('previewAmount');
            const previewReturn = document.getElementById('previewReturn');
            const confirmCheckbox = document.getElementById('confirm_transfer');
            const submitBtn = document.getElementById('submitBtn');
            const profitWallet = document.getElementById('profit_wallet');
            const bonusWallet = document.getElementById('bonus_wallet');

            // Wallet balances
            const profitBalance = {{ $profitBalance }};
            const bonusBalance = {{ $bonusBalance }};

            // Reinvestment settings
            const profitSettings = @json($profitSettings);
            const bonusSettings = @json($bonusSettings);

            // Generate quick amount buttons based on selected wallet
            function generateQuickAmountButtons() {
                const selectedWallet = document.querySelector('input[name="wallet_type"]:checked');
                const selectedBalance = selectedWallet && selectedWallet.value === 'bonus' ? bonusBalance : profitBalance;
                const settings = selectedWallet && selectedWallet.value === 'bonus' ? bonusSettings : profitSettings;

                // Update limits info
                updateWalletLimitsInfo(selectedWallet ? selectedWallet.value : 'profit');

                // Update amount input limits
                if (settings) {
                    amountInput.min = settings.min_amount;
                    amountInput.max = Math.min(settings.max_amount, selectedBalance);
                } else {
                    amountInput.min = 1;
                    amountInput.max = selectedBalance;
                }

                let buttonsHTML = '';
                const minAmount = settings ? settings.min_amount : 1;
                const maxAmount = settings ? Math.min(settings.max_amount, selectedBalance) : selectedBalance;

                // Generate buttons based on settings and balance
                const buttonAmounts = [10, 25, 50, 100, 250, 500, 1000];

                buttonAmounts.forEach(amount => {
                    if (amount >= minAmount && amount <= maxAmount && selectedBalance >= amount) {
                        buttonsHTML += `<button type="button" class="btn btn-outline-primary btn-sm quick-amount" data-amount="${amount}">$${amount}</button>`;
                    }
                });

                // Add 50% and All buttons if within limits
                const halfBalance = selectedBalance / 2;
                if (halfBalance >= minAmount && halfBalance <= maxAmount) {
                    buttonsHTML += `<button type="button" class="btn btn-outline-info btn-sm quick-amount" data-amount="${halfBalance}">50% ($${halfBalance.toFixed(2)})</button>`;
                }

                if (selectedBalance >= minAmount && selectedBalance <= maxAmount) {
                    buttonsHTML += `<button type="button" class="btn btn-outline-success btn-sm quick-amount" data-amount="${selectedBalance}">All ($${selectedBalance.toFixed(2)})</button>`;
                }

                quickAmountContainer.innerHTML = buttonsHTML;

                // Reattach event listeners to new buttons
                attachQuickAmountListeners();
            }

            // Update wallet limits information
            function updateWalletLimitsInfo(walletType) {
                const limitsInfo = document.getElementById('wallet-limits-info');
                const settings = walletType === 'bonus' ? bonusSettings : profitSettings;

                if (settings) {
                    let infoText = `Min: $${settings.min_amount}, Max: $${settings.max_amount}`;
                    if (settings.tax_percent > 0) {
                        infoText += `, Tax: ${settings.tax_percent}%`;
                    }
                    if (!settings.is_enabled) {
                        infoText = '<span class="text-danger">⚠️ Reinvestment disabled for this wallet</span>';
                    }
                    limitsInfo.innerHTML = infoText;
                } else {
                    limitsInfo.innerHTML = 'No specific limits configured';
                }
            }

            // Attach event listeners to quick amount buttons
            function attachQuickAmountListeners() {
                const quickAmountBtns = document.querySelectorAll('.quick-amount');
                quickAmountBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const amount = this.dataset.amount;
                        amountInput.value = amount;
                        updatePreview();

                        // Remove active class from all buttons
                        quickAmountBtns.forEach(b => b.classList.remove('active'));
                        // Add active class to clicked button
                        this.classList.add('active');
                    });
                });
            }

            // Wallet type change listeners
            profitWallet.addEventListener('change', generateQuickAmountButtons);
            bonusWallet.addEventListener('change', generateQuickAmountButtons);

            // Amount input change
            amountInput.addEventListener('input', function() {
                updatePreview();
                // Remove active class from quick buttons when manually typing
                const quickAmountBtns = document.querySelectorAll('.quick-amount');
                quickAmountBtns.forEach(b => b.classList.remove('active'));
            });

            // Update investment preview
            function updatePreview() {
                const amount = parseFloat(amountInput.value) || 0;
                const selectedWallet = document.querySelector('input[name="wallet_type"]:checked');
                const settings = selectedWallet && selectedWallet.value === 'bonus' ? bonusSettings : profitSettings;

                if (amount > 0) {
                    let taxAmount = 0;
                    let netAmount = amount;

                    // Calculate tax if settings exist
                    if (settings && settings.tax_percent > 0) {
                        taxAmount = (amount * settings.tax_percent) / 100;
                        netAmount = amount - taxAmount;
                    }

                    const returnAmount = netAmount * 5; // Return is based on net amount

                    previewAmount.textContent = '$' + amount.toFixed(2);
                    previewReturn.textContent = '$' + returnAmount.toFixed(2);

                    // Show/hide tax preview
                    const taxPreview = document.getElementById('taxPreview');
                    const previewTax = document.getElementById('previewTax');
                    const previewNet = document.getElementById('previewNet');

                    if (taxAmount > 0) {
                        previewTax.textContent = '$' + taxAmount.toFixed(2);
                        previewNet.textContent = '$' + netAmount.toFixed(2);
                        taxPreview.style.display = 'block';
                    } else {
                        taxPreview.style.display = 'none';
                    }

                    investmentPreview.style.display = 'block';
                    investmentPreview.classList.add('show');
                } else {
                    investmentPreview.style.display = 'none';
                    investmentPreview.classList.remove('show');
                }
            }

            // Form validation
            document.getElementById('reInvestForm').addEventListener('submit', function(e) {
                const amount = parseFloat(amountInput.value) || 0;
                const selectedWallet = document.querySelector('input[name="wallet_type"]:checked');
                const selectedBalance = selectedWallet && selectedWallet.value === 'bonus' ? bonusBalance :
                    profitBalance;

                if (amount <= 0) {
                    e.preventDefault();
                    alert('Please enter a valid amount.');
                    return false;
                }

                if (amount > selectedBalance) {
                    e.preventDefault();
                    alert('Amount exceeds your available ' + (selectedWallet ? selectedWallet.value :
                        'wallet') + ' balance.');
                    return false;
                }

                if (!selectedWallet) {
                    e.preventDefault();
                    alert('Please select a wallet type.');
                    return false;
                }

                if (!confirmCheckbox.checked) {
                    e.preventDefault();
                    alert('Please confirm the transfer by checking the confirmation box.');
                    return false;
                }

                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                submitBtn.disabled = true;
            });

            // Initialize quick amount buttons
            generateQuickAmountButtons();
        });
    </script>
@endsection
