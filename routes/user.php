<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserLoginController;
use App\Http\Controllers\User\UserRegisterController;
use App\Http\Controllers\User\UserForgotPasswordController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\WithdrawalController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\InvestmentController;
use App\Http\Controllers\User\AiTradeController;
use App\Http\Controllers\User\MarketplaceController;
use App\Http\Controllers\User\ReferralBonusHistoryController;
use App\Http\Controllers\User\RoiMatchingBonusHistoryController;
use App\Http\Controllers\User\PaymentInfoController;
use App\Http\Controllers\User\ReInvestController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\User\AiTradeActivateController;
// Route::middleware(['web'])->group(function () {
//     Route::get('login', [App\Http\Controllers\User\UserLoginController::class, 'showLoginForm'])->name('login'); // Main login route
//     Route::post('login', [App\Http\Controllers\User\UserLoginController::class, 'login'])->name('user.login.submit');
//     Route::post('logout', [App\Http\Controllers\User\UserLoginController::class, 'logout'])->name('user.logout');

//     Route::get('register', [App\Http\Controllers\User\UserRegisterController::class, 'showRegisterForm'])->name('user.register');
//     Route::post('register', [App\Http\Controllers\User\UserRegisterController::class, 'register'])->name('user.register.submit');

//     Route::middleware(['auth', 'web'])->group(function () {
//     Route::get('/dashboard', [App\Http\Controllers\User\UserController::class, 'dashboard'])->name('user.dashboard');
//         // Other user routes
//     });
// });

Route::prefix('user')->middleware('web')->group(function () {
    // Password reset routes (accessible without auth)
    Route::get('/password/reset/telegram/{token}', [UserForgotPasswordController::class, 'showTelegramResetForm'])
        ->name('user.password.reset.telegram');

    Route::get('/password/reset/{token}', [UserForgotPasswordController::class, 'showEmailResetForm'])
        ->name('user.password.reset.email');

    Route::post('/password/reset', [UserForgotPasswordController::class, 'resetPassword'])
        ->name('user.password.reset.submit');

    Route::post('/password/update', [UserForgotPasswordController::class, 'resetPassword'])
        ->name('user.password.update');

    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('user.login');
        Route::post('/login', [UserLoginController::class, 'login'])->name('user.login.submit');
        Route::get('/register', [UserRegisterController::class, 'showRegisterForm'])->name('user.register');
        Route::post('/register', [UserRegisterController::class, 'register'])
            ->middleware('throttle:5,1') // 5 attempts per minute
            ->name('user.register.submit');
        Route::get('/forgot-password', [UserForgotPasswordController::class, 'index'])->name('user.forgot-password');
        Route::post('/forgot-password', [UserForgotPasswordController::class, 'sendResetLink'])->name('user.password.forgot.send');
    });

    // Protected routes
    Route::middleware(['auth:web'])->group(function () {
        Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
        Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
        Route::match(['put', 'post'], '/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
        Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('user.profile.edit');
        Route::post('/profile/upload-photo', [UserController::class, 'uploadPhoto'])->name('user.profile.upload-photo');
        // AI Trade Activation
        Route::post('ai-trade-activate/toggle', [AiTradeActivateController::class, 'toggle'])->name('user.ai_trade_activate.toggle');
        Route::get('downline', [UserController::class, 'downline'])->name('user.downline');
        Route::get('/logout', [UserLoginController::class, 'logout']);
        // Route::get('/debug', [UserController::class, 'debug']);

    // ROI Matching Bonus History
    Route::get('roi-matching-bonus-histories', [RoiMatchingBonusHistoryController::class, 'index'])->name('user.roi_matching_bonus_histories.index');

    // Referral Bonus Histories
    Route::get('referral-bonus-histories', [ReferralBonusHistoryController::class, 'index'])->name('user.referral_bonus_histories.index');

    // KYC Verification
    Route::get('kyc', [KycController::class, 'index'])->name('user.kyc.index');
    Route::post('kyc', [KycController::class, 'store'])->name('user.kyc.store');

    // Payment Information
    Route::get('payment-info', [PaymentInfoController::class, 'index'])->name('user.payment-info.index');
    Route::post('payment-info', [PaymentInfoController::class, 'store'])->name('user.payment-info.store');

    // Re-Investment
    Route::get('re-invest', [ReInvestController::class, 'index'])->name('user.re-invest.index')->middleware('require.payment.info');
    Route::post('re-invest', [ReInvestController::class, 'store'])->name('user.re-invest.store')->middleware('require.payment.info');

    // Deposit & Withdrawal
    Route::get('deposit', [DepositController::class, 'index'])->name('user.deposit.index');
    Route::get('deposit/direct', [DepositController::class, 'direct'])->name('user.deposit.direct');
    Route::post('deposit', [DepositController::class, 'store'])->name('user.deposit.store');
    Route::post('deposit/unique-address', [DepositController::class, 'getUniqueAddress'])->name('user.deposit.unique-address');
    Route::post('deposit/crypto', [DepositController::class, 'createCryptoPayment'])->name('user.deposit.crypto');
    Route::get('deposit/success', [DepositController::class, 'success'])->name('user.deposit.success');
    Route::get('deposit/cancel', [DepositController::class, 'cancel'])->name('user.deposit.cancel');
    Route::get('withdrawal', [WithdrawalController::class, 'index'])->name('user.withdrawal.index')->middleware('require.payment.info');
    Route::get('withdrawal/crypto', [WithdrawalController::class, 'crypto'])->name('user.withdrawal.crypto')->middleware('require.payment.info');
    Route::get('withdrawal/etc-black', [WithdrawalController::class, 'etcBlack'])->name('user.withdrawal.etc-black')->middleware('require.payment.info');
    Route::get('withdrawal/gold', [WithdrawalController::class, 'gold'])->name('user.withdrawal.gold')->middleware('require.payment.info');
    Route::post('withdrawal', [WithdrawalController::class, 'store'])->name('user.withdrawal.store')->middleware('require.payment.info');
    Route::post('withdrawal/{withdrawalId}/select-branch', [WithdrawalController::class, 'selectBranch'])->name('user.withdrawal.select-branch')->middleware('require.payment.info');

    // Investment
    Route::get('investment', [InvestmentController::class, 'index'])->name('user.investment.index');

    // AI Trades
    Route::get('ai-trades', [AiTradeController::class, 'index'])->name('user.ai_trades.index');
    Route::get('ai-trades/{id}', [AiTradeController::class, 'show'])->name('user.ai_trades.show');
    Route::post('ai-trades/{id}/cancel', [AiTradeController::class, 'cancel'])->name('user.ai_trades.cancel');

    // Marketplace
    Route::get('marketplace', [MarketplaceController::class, 'index'])->name('user.marketplace.index');
    Route::post('marketplace/create', [MarketplaceController::class, 'createListing'])->name('user.marketplace.create');
    Route::post('marketplace/purchase/{listingId}', [MarketplaceController::class, 'purchaseListing'])->name('user.marketplace.purchase');
    Route::post('marketplace/cancel/{listingId}', [MarketplaceController::class, 'cancelListing'])->name('user.marketplace.cancel');
    Route::post('marketplace/topup', [MarketplaceController::class, 'topUpCash'])->name('user.marketplace.topup');

    // Marketplace Verification
    Route::post('marketplace/approve/{listingId}', [MarketplaceController::class, 'approveVerification'])->name('user.marketplace.approve');
    Route::post('marketplace/reject/{listingId}', [MarketplaceController::class, 'rejectVerification'])->name('user.marketplace.reject');
    Route::post('marketplace/cancel-verification/{listingId}', [MarketplaceController::class, 'cancelVerification'])->name('user.marketplace.cancel_verification');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('user.notifications.index');
    Route::get('notifications/api', [NotificationController::class, 'getNotifications'])->name('user.notifications.api');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('user.notifications.read_all');
    Route::delete('notifications/clear-all', [NotificationController::class, 'clearAll'])->name('user.notifications.clear_all');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('user.notifications.read');
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('user.notifications.destroy');
    Route::post('notifications/test', [NotificationController::class, 'testNotification'])->name('user.notifications.test');
    Route::get('notifications/test', [NotificationController::class, 'testNotification'])->name('user.notifications.test.get');
    });
});
