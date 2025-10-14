<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\WalletManagementController;
use App\Http\Controllers\Admin\KycApprovalController;
use App\Http\Controllers\Admin\NOWPaymentsController;
use App\Http\Controllers\Admin\WithdrawalManagementController;
use App\Http\Controllers\Admin\XeusdWithdrawalController;
use App\Http\Controllers\Admin\MarketplaceController;
use App\Http\Controllers\Admin\FixCronValueController;
use App\Http\Controllers\Admin\BannerAdvertisementController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AdminWithdrawalSettingsController;
use App\Http\Controllers\Admin\AdminReinvestmentSettingsController;
use App\Http\Controllers\Admin\UplineManagementController;
use App\Http\Controllers\Admin\AiTradeSettingsController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\WalletProfitCheckerController;
use App\Http\Controllers\Admin\DepositReportController;
use App\Http\Controllers\Admin\GroupSalesController;

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
        Route::post('/users/{user}/ban', [UserManagementController::class, 'ban'])->name('admin.users.ban');
        Route::post('/users/{user}/unban', [UserManagementController::class, 'unban'])->name('admin.users.unban');
        Route::post('/users/{user}/test-telegram', [UserManagementController::class, 'testTelegramConnection'])->name('admin.users.test-telegram');
        Route::post('/users/{user}/process-deposit-promo-bonus', [UserManagementController::class, 'processDepositPromoBonus'])->name('admin.users.process-deposit-promo-bonus');

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
        Route::post('/nowpayments/refresh-all-statuses', [NOWPaymentsController::class, 'refreshAllStatuses'])->name('admin.nowpayments.refresh-all-statuses');
        Route::post('/nowpayments/test-api', [NOWPaymentsController::class, 'testApiConnection'])->name('admin.nowpayments.test-api');
        Route::get('/nowpayments/debug-connectivity', [NOWPaymentsController::class, 'debugConnectivity'])->name('admin.nowpayments.debug-connectivity');
        Route::get('/nowpayments/deposit-details/{depositId}', [NOWPaymentsController::class, 'showDepositDetails'])->name('admin.nowpayments.deposit-details');
        Route::post('/deposits/approve/{depositId}', [NOWPaymentsController::class, 'approveDeposit'])->name('admin.deposits.approve');
        Route::post('/deposits/reject/{depositId}', [NOWPaymentsController::class, 'rejectDeposit'])->name('admin.deposits.reject');

        // Deposit Report Routes
        Route::get('/deposit-report', [DepositReportController::class, 'index'])->name('admin.deposit-report.index');
        Route::get('/deposit-report/list', [DepositReportController::class, 'list'])->name('admin.deposit-report.list');

        // Withdrawal Management Routes
        Route::get('/withdrawals', [WithdrawalManagementController::class, 'index'])->name('admin.withdrawals.index');
        Route::get('/withdrawals/list', [WithdrawalManagementController::class, 'list'])->name('admin.withdrawals.list');
        Route::get('/withdrawals/{id}/debug', function($id) {
            // Debug route to check withdrawal data structure
            try {
                $withdrawal = \App\Models\Withdrawal::with('user')->findOrFail($id);
                return response()->json([
                    'withdrawal_exists' => true,
                    'withdrawal_id' => $withdrawal->id,
                    'user_exists' => !is_null($withdrawal->user),
                    'user_id' => $withdrawal->user_id,
                    'withdrawal_columns' => array_keys($withdrawal->getAttributes()),
                    'user_columns' => $withdrawal->user ? array_keys($withdrawal->user->getAttributes()) : [],
                    'withdrawal_type' => $withdrawal->withdrawal_type,
                    'status' => $withdrawal->status,
                    'error' => null
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        })->name('admin.withdrawals.debug');
        Route::get('/withdrawals/{id}', [WithdrawalManagementController::class, 'show'])->name('admin.withdrawals.show');
        Route::post('/withdrawals/{id}/approve', [WithdrawalManagementController::class, 'approve'])->name('admin.withdrawals.approve');
        Route::post('/withdrawals/{id}/approve-gold', [WithdrawalManagementController::class, 'approveGold'])->name('admin.withdrawals.approve-gold');
        Route::get('/withdrawals/{id}/receipt', [WithdrawalManagementController::class, 'receipt'])->name('admin.withdrawals.receipt');
        Route::post('/withdrawals/{id}/reject', [WithdrawalManagementController::class, 'reject'])->name('admin.withdrawals.reject');

        // XEUSD Withdrawal Management Routes
        Route::get('/xeusd-withdrawals', [XeusdWithdrawalController::class, 'index'])->name('admin.xeusd_withdrawals.index');
        Route::get('/xeusd-withdrawals/list', [XeusdWithdrawalController::class, 'list'])->name('admin.xeusd_withdrawals.list');
        Route::get('/xeusd-withdrawals/{id}', [XeusdWithdrawalController::class, 'show'])->name('admin.xeusd_withdrawals.show');
        Route::post('/xeusd-withdrawals/{id}/approve', [XeusdWithdrawalController::class, 'approve'])->name('admin.xeusd_withdrawals.approve');
        Route::post('/xeusd-withdrawals/{id}/reject', [XeusdWithdrawalController::class, 'reject'])->name('admin.xeusd_withdrawals.reject');
        Route::post('/xeusd-withdrawals/{id}/process', [XeusdWithdrawalController::class, 'markProcessing'])->name('admin.xeusd_withdrawals.process');

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

        // Reinvestment History Management Routes
        Route::get('/reinvest-history', [App\Http\Controllers\Admin\ReInvestHistoryController::class, 'index'])->name('admin.reinvest-history.index');
        Route::get('/reinvest-history/data', [App\Http\Controllers\Admin\ReInvestHistoryController::class, 'getData'])->name('admin.reinvest-history.data');
        Route::get('/reinvest-history/stats', [App\Http\Controllers\Admin\ReInvestHistoryController::class, 'getStats'])->name('admin.reinvest-history.stats');
        Route::get('/reinvest-history/export', [App\Http\Controllers\Admin\ReInvestHistoryController::class, 'export'])->name('admin.reinvest-history.export');
        Route::get('/reinvest-history/{id}/edit', [App\Http\Controllers\Admin\ReInvestHistoryController::class, 'edit'])->name('admin.reinvest-history.edit');
        Route::put('/reinvest-history/{id}', [App\Http\Controllers\Admin\ReInvestHistoryController::class, 'update'])->name('admin.reinvest-history.update');

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
            Route::post('/toggle-maintenance', [AdminWithdrawalSettingsController::class, 'toggleMaintenance'])->name('toggle-maintenance');
            Route::get('/maintenance-status', [AdminWithdrawalSettingsController::class, 'getMaintenanceStatus'])->name('maintenance-status');
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

        // Upline Management Routes
        Route::prefix('upline-management')->name('admin.upline-management.')->group(function () {
            Route::get('/', [UplineManagementController::class, 'index'])->name('index');
            Route::get('/users-data', [UplineManagementController::class, 'getUsersData'])->name('users-data');
            Route::get('/search-users', [UplineManagementController::class, 'searchUsers'])->name('search-users');
            Route::get('/user-tree/{userId}', [UplineManagementController::class, 'getUserTree'])->name('user-tree');
            Route::get('/user-downlines/{userId}', [UplineManagementController::class, 'getAllDownlines'])->name('user-downlines');
            Route::post('/update-upline', [UplineManagementController::class, 'updateUpline'])->name('update-upline');
            Route::post('/bulk-update-upline', [UplineManagementController::class, 'bulkUpdateUpline'])->name('bulk-update-upline');
            Route::post('/refresh-referral-codes', [UplineManagementController::class, 'refreshReferralCodes'])->name('refresh-referral-codes');
        });

        // AI Trade Settings Routes
        Route::prefix('ai-trade-settings')->name('admin.ai-trade-settings.')->group(function () {
            Route::get('/', [AiTradeSettingsController::class, 'index'])->name('index');
            Route::post('/update-settings', [AiTradeSettingsController::class, 'updateSettings'])->name('update');
            Route::get('/users-list', [AiTradeSettingsController::class, 'getUsersList'])->name('users.list');
            Route::get('/user/{userId}/trades', [AiTradeSettingsController::class, 'getUserTrades'])->name('user.trades');
            Route::put('/update-status/{id}', [AiTradeSettingsController::class, 'updateStatus'])->name('update-status');
            Route::put('/update-trade/{id}', [AiTradeSettingsController::class, 'updateTrade'])->name('update-trade');
            Route::delete('/delete-trade/{id}', [AiTradeSettingsController::class, 'deleteTrade'])->name('delete-trade');
        });

        // Admin Settings Routes
        Route::prefix('settings')->name('admin.settings.')->group(function () {
            Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
            Route::post('/profile', [AdminSettingsController::class, 'updateProfile'])->name('profile.update');
            Route::post('/password', [AdminSettingsController::class, 'updatePassword'])->name('password.update');
        });

        // Deposit Promo Bonus Routes
        Route::prefix('deposit-promo')->name('admin.deposit-promo.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\DepositPromoBonusController::class, 'index'])->name('index');
            Route::get('/campaigns-list', [App\Http\Controllers\Admin\DepositPromoBonusController::class, 'campaignsList'])->name('campaigns.list');
            Route::post('/campaigns', [App\Http\Controllers\Admin\DepositPromoBonusController::class, 'store'])->name('campaigns.store');
            Route::get('/campaigns/{id}', [App\Http\Controllers\Admin\DepositPromoBonusController::class, 'show'])->name('campaigns.show');
            Route::put('/campaigns/{id}', [App\Http\Controllers\Admin\DepositPromoBonusController::class, 'update'])->name('campaigns.update');
            Route::delete('/campaigns/{id}', [App\Http\Controllers\Admin\DepositPromoBonusController::class, 'destroy'])->name('campaigns.destroy');
            Route::get('/bonus-history-list', [App\Http\Controllers\Admin\DepositPromoBonusController::class, 'bonusHistoryList'])->name('bonus-history.list');
        });

        // Wallet Profit Checker Routes
        Route::prefix('wallet-profit-checker')->name('admin.wallet-profit-checker.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'index'])->name('index');
            Route::get('/debug-users-data', function() {
                try {
                    $controller = new App\Http\Controllers\Admin\WalletProfitCheckerController();
                    $request = new Illuminate\Http\Request();
                    $result = $controller->getUsersData($request);
                    return ['success' => true, 'result' => 'Method executed successfully'];
                } catch (Exception $e) {
                    return ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()];
                }
            })->name('debug-users-data');
            Route::get('/users-data', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'getUsersData'])->name('users-data');
            Route::get('/transfer-history-data', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'getTransferHistoryData'])->name('transfer-history-data');
            Route::get('/batch-summary-data', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'getBatchSummary'])->name('batch-summary-data');
            Route::post('/transfer-profit-to-temp', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'transferProfitToTemp'])->name('transfer-profit-to-temp');
            Route::post('/transfer-temp-to-deposit', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'transferTempToDeposit'])->name('transfer-temp-to-deposit');
            Route::post('/transfer-all-profit-to-deposit', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'transferAllProfitToDeposit'])->name('transfer-all-profit-to-deposit');
            Route::post('/return-temp-to-profit', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'returnTempToProfit'])->name('return-temp-to-profit');
            Route::post('/rollback-transfer', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'rollbackTransfer'])->name('rollback-transfer');
            Route::post('/rollback', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'rollbackTransfer'])->name('rollback');
            Route::post('/update-threshold', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'updateThreshold'])->name('update-threshold');
            Route::get('/over-threshold', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'overThreshold'])->name('over-threshold');
            Route::get('/over-threshold-data', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'getOverThresholdData'])->name('over-threshold-data');
            Route::post('/adjust-user-profit', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'adjustUserProfit'])->name('adjust-user-profit');
            Route::post('/reject-all-pending-withdrawals', [App\Http\Controllers\Admin\WalletProfitCheckerController::class, 'rejectAllPendingWithdrawals'])->name('reject-all-pending-withdrawals');
        });

        // Group Sales Management Routes
        Route::prefix('group-sales')->name('admin.group-sales.')->group(function () {
            Route::get('/', [GroupSalesController::class, 'index'])->name('index');
            Route::get('/data', [GroupSalesController::class, 'getRewardsData'])->name('data');
            Route::get('/history', [GroupSalesController::class, 'history'])->name('history');
            Route::get('/history/data', [GroupSalesController::class, 'getHistoryData'])->name('history.data');
            Route::post('/', [GroupSalesController::class, 'store'])->name('store');
            Route::get('/{id}', [GroupSalesController::class, 'show'])->name('show');
            Route::put('/{id}', [GroupSalesController::class, 'update'])->name('update');
            Route::delete('/{id}', [GroupSalesController::class, 'destroy'])->name('destroy');
        });
    });
});
