<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('login', [App\Http\Controllers\User\UserLoginController::class, 'showLoginForm'])->name('login'); // Main login route
    Route::post('login', [App\Http\Controllers\User\UserLoginController::class, 'login'])->name('user.login.submit');
    Route::post('logout', [App\Http\Controllers\User\UserLoginController::class, 'logout'])->name('user.logout');

    Route::get('register', [App\Http\Controllers\User\UserRegisterController::class, 'showRegisterForm'])->name('user.register');
    Route::post('register', [App\Http\Controllers\User\UserRegisterController::class, 'register'])->name('user.register.submit');

    Route::middleware(['auth', 'user'])->group(function () {
        Route::get('/', [App\Http\Controllers\User\UserController::class, 'index'])->name('user.dashboard');
        // Other user routes
    });
});
