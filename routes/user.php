    
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserLoginController;
use App\Http\Controllers\User\UserRegisterController;
use App\Http\Controllers\User\UserController;
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
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
        Route::get('downline', [UserController::class, 'downline'])->name('user.downline');
        Route::get('/logout', [UserLoginController::class, 'logout']);
        // Route::get('/debug', [UserController::class, 'debug']);

        // User Management Routes
        // Route::get('/users', [UserManagementController::class, 'index'])->name('user.users.index');
        // Route::get('/users/list', [UserManagementController::class, 'list'])->name('user.users.list');
        // Route::post('/users', [UserManagementController::class, 'store'])->name('user.users.store');
        // Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('user.users.update');
        // Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('user.users.destroy');
    });
});
