<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('admin')->group(function () {
    Route::get('login', [App\Http\Controllers\Admin\AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [App\Http\Controllers\Admin\AdminLoginController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\Admin\AdminLoginController::class, 'logout'])->name('admin.logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('admin.dashboard');
        // Other admin routes
    });
});
