<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('login', [App\Http\Controllers\User\UserLoginController::class, 'showLoginForm'])->name('user.login');
    Route::post('login', [App\Http\Controllers\User\UserLoginController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\User\UserLoginController::class, 'logout'])->name('user.logout');

    Route::middleware(['auth', 'user'])->group(function () {
        Route::get('/', [App\Http\Controllers\User\UserController::class, 'index'])->name('user.dashboard');
        // Other user routes
    });
});
