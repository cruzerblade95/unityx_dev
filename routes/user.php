
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserLoginController;
use App\Http\Controllers\User\UserRegisterController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\WithdrawalController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\InvestmentController;
use App\Http\Controllers\User\AiTradeController;
use App\Http\Controllers\User\MarketplaceController;
use App\Http\Controllers\KycController;
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
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('user.login');
        Route::post('/login', [UserLoginController::class, 'login'])->name('user.login.submit');
        Route::get('/register', [UserRegisterController::class, 'showRegisterForm'])->name('user.register');
        Route::post('/register', [UserRegisterController::class, 'register'])->name('user.register.submit');
    });

    // Protected routes
    Route::middleware(['auth:web'])->group(function () {
        Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
        Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
        Route::match(['put', 'post'], '/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
        Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('user.profile.edit');
        Route::post('/profile/upload-photo', [UserController::class, 'uploadPhoto'])->name('user.profile.upload-photo');
        Route::get('downline', [UserController::class, 'downline'])->name('user.downline');
        Route::get('/logout', [UserLoginController::class, 'logout']);
        // Route::get('/debug', [UserController::class, 'debug']);

    // KYC Verification
    Route::get('kyc', [KycController::class, 'index'])->name('user.kyc.index');
    Route::post('kyc', [KycController::class, 'store'])->name('user.kyc.store');

    // Deposit & Withdrawal
    Route::get('deposit', [DepositController::class, 'index'])->name('user.deposit.index');
    Route::post('deposit', [DepositController::class, 'store'])->name('user.deposit.store');
    Route::post('deposit/unique-address', [DepositController::class, 'getUniqueAddress'])->name('user.deposit.unique-address');
    Route::post('deposit/crypto', [DepositController::class, 'createCryptoPayment'])->name('user.deposit.crypto');
    Route::get('deposit/success', [DepositController::class, 'success'])->name('user.deposit.success');
    Route::get('deposit/cancel', [DepositController::class, 'cancel'])->name('user.deposit.cancel');
    Route::get('withdrawal', [WithdrawalController::class, 'index'])->name('user.withdrawal.index');
    Route::post('withdrawal', [WithdrawalController::class, 'store'])->name('user.withdrawal.store');

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
    });
});
