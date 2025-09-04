<?php
use App\Http\Controllers\TelegramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);
Route::get('/telegram/webhook', function() { return 'ok'; });
Route::get('/telegram/setup-webhook', [TelegramController::class, 'setupWebhook']);

// Simple test webhook that just returns OK
Route::post('/telegram/test-webhook', function() {
    return response('ok', 200);
});

// Test route for debugging
Route::get('/test', function() {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
