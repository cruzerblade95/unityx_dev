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
use App\Http\Controllers\Admin\BannerAdvertisementController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AdminWithdrawalSettingsController;
use App\Http\Controllers\Admin\AdminReinvestmentSettingsController;

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
        Route::get('/wallets/list', [WalletManagementController::class, 'list'])->name('admin.wallets.list');
        Route::post('/wallets/create', [WalletManagementController::class, 'createWallet'])->name('admin.wallets.create');
        Route::get('/wallets/{id}', [WalletManagementController::class, 'getWallet'])->name('admin.wallets.show');
        Route::post('/wallets/adjust', [WalletManagementController::class, 'adjustBalance'])->name('admin.wallets.adjust');
        Route::get('/wallets/{id}/history', [WalletManagementController::class, 'getTransactionHistory'])->name('admin.wallets.history');

        // KYC Approval Routes
        Route::get('/kyc', [KycApprovalController::class, 'index'])->name('admin.kyc.index');
        Route::get('/kyc/data', [KycApprovalController::class, 'getKycData'])->name('admin.kyc.data');
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
        Route::get('/recovery-accounts/data', [App\Http\Controllers\Admin\RecoveryAccountController::class, 'getRecoveryData'])->name('admin.recovery_accounts.data');
        Route::get('/recovery-accounts/{id}', [App\Http\Controllers\Admin\RecoveryAccountController::class, 'show'])->name('admin.recovery_accounts.show');
        Route::post('/recovery-accounts/{id}/approve', [App\Http\Controllers\Admin\RecoveryAccountController::class, 'approve'])->name('admin.recovery_accounts.approve');
        Route::post('/recovery-accounts/{id}/reject', [App\Http\Controllers\Admin\RecoveryAccountController::class, 'reject'])->name('admin.recovery_accounts.reject');

        // Banner Advertisement Management Routes
        Route::resource('banner-advertisements', BannerAdvertisementController::class)->names([
            'index' => 'admin.banner-advertisements.index',
            'create' => 'admin.banner-advertisements.create',
            'store' => 'admin.banner-advertisements.store',
            'show' => 'admin.banner-advertisements.show',
            'edit' => 'admin.banner-advertisements.edit',
            'update' => 'admin.banner-advertisements.update',
            'destroy' => 'admin.banner-advertisements.destroy',
        ]);
        Route::post('/banner-advertisements/{bannerAdvertisement}/toggle-status', [BannerAdvertisementController::class, 'toggleStatus'])->name('admin.banner-advertisements.toggle-status');
        Route::post('/banner-advertisements/update-order', [BannerAdvertisementController::class, 'updateOrder'])->name('admin.banner-advertisements.update-order');

        // Announcement Management Routes
        Route::resource('announcements', AnnouncementController::class)->names([
            'index' => 'admin.announcements.index',
            'create' => 'admin.announcements.create',
            'store' => 'admin.announcements.store',
            'show' => 'admin.announcements.show',
            'edit' => 'admin.announcements.edit',
            'update' => 'admin.announcements.update',
            'destroy' => 'admin.announcements.destroy',
        ]);
        Route::post('/announcements/{announcement}/toggle-status', [AnnouncementController::class, 'toggleStatus'])->name('admin.announcements.toggle-status');

        // Withdrawal Settings Management Routes
        Route::prefix('withdrawal-settings')->name('admin.withdrawal-settings.')->group(function () {
            Route::get('/', [AdminWithdrawalSettingsController::class, 'index'])->name('index');
            Route::get('/users-data', [AdminWithdrawalSettingsController::class, 'getUsersData'])->name('users-data');
            Route::get('/user/{userId}/settings', [AdminWithdrawalSettingsController::class, 'getUserSettings'])->name('user.settings');
            Route::post('/user/{userId}/update', [AdminWithdrawalSettingsController::class, 'updateUserSettings'])->name('user.update');
            Route::post('/bulk-update', [AdminWithdrawalSettingsController::class, 'bulkUpdate'])->name('bulk-update');
            Route::post('/reset-defaults', [AdminWithdrawalSettingsController::class, 'resetToDefaults'])->name('reset-defaults');
            Route::post('/disable-withdrawals', [AdminWithdrawalSettingsController::class, 'disableWithdrawals'])->name('disable-withdrawals');
            Route::post('/enable-withdrawals', [AdminWithdrawalSettingsController::class, 'enableWithdrawals'])->name('enable-withdrawals');
            Route::get('/global-defaults', [AdminWithdrawalSettingsController::class, 'globalDefaults'])->name('global-defaults');
            Route::post('/global-defaults/update', [AdminWithdrawalSettingsController::class, 'updateGlobalDefaults'])->name('global-defaults.update');
        });

        // Reinvestment Settings Management Routes
        Route::prefix('reinvestment-settings')->name('admin.reinvestment-settings.')->group(function () {
            Route::get('/', [AdminReinvestmentSettingsController::class, 'index'])->name('index');
            Route::get('/users-data', [AdminReinvestmentSettingsController::class, 'getUsersData'])->name('users-data');
            Route::get('/user/{userId}/settings', [AdminReinvestmentSettingsController::class, 'getUserSettings'])->name('user.settings');
            Route::post('/user/{userId}/update', [AdminReinvestmentSettingsController::class, 'updateUserSettings'])->name('user.update');
            Route::post('/bulk-update', [AdminReinvestmentSettingsController::class, 'bulkUpdate'])->name('bulk-update');
            Route::get('/settings-preview', [AdminReinvestmentSettingsController::class, 'getSettingsPreview'])->name('settings-preview');
            Route::post('/global-defaults/update', [AdminReinvestmentSettingsController::class, 'updateGlobalDefaults'])->name('global-defaults.update');
            Route::delete('/user/{userId}/settings', [AdminReinvestmentSettingsController::class, 'deleteUserSettings'])->name('user.delete');
            Route::post('/user/{userId}/follow-defaults', [AdminReinvestmentSettingsController::class, 'setUserToFollowDefaults'])->name('user.follow-defaults');
        });
    });
});
