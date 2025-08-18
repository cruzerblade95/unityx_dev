<?php
use App\Http\Controllers\TelegramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);
Route::get('/telegram/webhook', function() { return 'ok'; });
Route::post('/test-post', function() { return 'post works'; });

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
