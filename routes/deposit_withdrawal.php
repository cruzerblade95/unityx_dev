<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\WithdrawalController;

Route::middleware(['auth'])->prefix('user')->group(function () {
    Route::get('deposit', [DepositController::class, 'index'])->name('user.deposit.index');
    Route::post('deposit', [DepositController::class, 'store'])->name('user.deposit.store');
    Route::get('withdrawal', [WithdrawalController::class, 'index'])->name('user.withdrawal.index');
    Route::post('withdrawal', [WithdrawalController::class, 'store'])->name('user.withdrawal.store');
});
