<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\WalletManagementController;
use App\Http\Controllers\Admin\KycApprovalController;
use App\Http\Controllers\Admin\NOWPaymentsController;
use App\Http\Controllers\Admin\WithdrawalManagementController;
use App\Http\Controllers\Admin\MarketplaceController;
use App\Http\Controllers\Admin\FixCronValueController;

Route::prefix('admin')->middleware('web')->group(function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminLoginController::class, 'login']);
    });

    // Protected routes
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::post('/logout', [AdminLoginController::class, 'logout']);
        Route::get('/debug', [AdminController::class, 'debug']);

        // User Management Routes
        Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::get('/users/list', [UserManagementController::class, 'list'])->name('admin.users.list');
        Route::get('/users/{id}', [UserManagementController::class, 'show'])->name('admin.users.show');
        Route::get('/users/{id}/profile', [UserManagementController::class, 'profile'])->name('admin.users.profile');
        Route::post('/users', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');

        // Wallet Management Routes
        Route::get('/wallets', [WalletManagementController::class, 'index'])->name('admin.wallets.index');
        Route::post('/wallets/create', [WalletManagementController::class, 'createWallet'])->name('admin.wallets.create');

        // KYC Approval Routes
        Route::get('/kyc', [KycApprovalController::class, 'index'])->name('admin.kyc.index');
        Route::get('/kyc/{id}', [KycApprovalController::class, 'show'])->name('admin.kyc.show');
        Route::post('/kyc/{id}/approve', [KycApprovalController::class, 'approve'])->name('admin.kyc.approve');
        Route::post('/kyc/{id}/reject', [KycApprovalController::class, 'reject'])->name('admin.kyc.reject');

        // NOWPayments Payment Details
        Route::get('/nowpayments', [NOWPaymentsController::class, 'index'])->name('admin.nowpayments.index');
        Route::get('/nowpayments/list', [NOWPaymentsController::class, 'list'])->name('admin.nowpayments.list');
        Route::post('/nowpayments/refresh-status/{paymentId}', [NOWPaymentsController::class, 'refreshStatus'])->name('admin.nowpayments.refresh-status');
        Route::post('/nowpayments/test-api', [NOWPaymentsController::class, 'testApiConnection'])->name('admin.nowpayments.test-api');
        Route::get('/nowpayments/deposit-details/{depositId}', [NOWPaymentsController::class, 'showDepositDetails'])->name('admin.nowpayments.deposit-details');
        Route::post('/deposits/approve/{depositId}', [NOWPaymentsController::class, 'approveDeposit'])->name('admin.deposits.approve');
        Route::post('/deposits/reject/{depositId}', [NOWPaymentsController::class, 'rejectDeposit'])->name('admin.deposits.reject');

        // Withdrawal Management Routes
        Route::get('/withdrawals', [WithdrawalManagementController::class, 'index'])->name('admin.withdrawals.index');
        Route::get('/withdrawals/list', [WithdrawalManagementController::class, 'list'])->name('admin.withdrawals.list');
        Route::get('/withdrawals/{id}', [WithdrawalManagementController::class, 'show'])->name('admin.withdrawals.show');
        Route::post('/withdrawals/{id}/approve', [WithdrawalManagementController::class, 'approve'])->name('admin.withdrawals.approve');
        Route::post('/withdrawals/{id}/reject', [WithdrawalManagementController::class, 'reject'])->name('admin.withdrawals.reject');

        // Marketplace Management Routes
        Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('admin.marketplace.index');
        Route::get('/marketplace/{id}', [MarketplaceController::class, 'show'])->name('admin.marketplace.show');
        Route::post('/marketplace/{id}/reject', [MarketplaceController::class, 'reject'])->name('admin.marketplace.reject');

        // Admin: Fix Cron Value
        Route::get('fix-cron-value', [FixCronValueController::class, 'index'])->name('admin.fix_cron_value.index');
        Route::post('fix-cron-value', [FixCronValueController::class, 'process'])->name('admin.fix_cron_value.process');

        // Recovery Accounts Management
        Route::get('/recovery-accounts', [App\Http\Controllers\Admin\RecoveryAccountController::class, 'index'])->name('admin.recovery_accounts.index');
        Route::get('/recovery-accounts/{id}', [App\Http\Controllers\Admin\RecoveryAccountController::class, 'show'])->name('admin.recovery_accounts.show');
        Route::post('/recovery-accounts/{id}/approve', [App\Http\Controllers\Admin\RecoveryAccountController::class, 'approve'])->name('admin.recovery_accounts.approve');
        Route::post('/recovery-accounts/{id}/reject', [App\Http\Controllers\Admin\RecoveryAccountController::class, 'reject'])->name('admin.recovery_accounts.reject');
    });
});
