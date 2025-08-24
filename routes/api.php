<?php
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\WebhookTestController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\TelegramDebugController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);
Route::get('/telegram/webhook', function() { return 'ok'; });
Route::get('/telegram/setup-webhook', [TelegramController::class, 'setupWebhook']);

// Telegram Debug routes
Route::any('/telegram/debug', [TelegramDebugController::class, 'debug']);
Route::any('/telegram/debug/logs', [TelegramDebugController::class, 'getLastLog']);
Route::any('/telegram/test-message', [TelegramDebugController::class, 'testMessage']);
Route::any('/telegram/config-test', [App\Http\Controllers\TelegramConfigTestController::class, 'testConfig']);

// Debug routes
Route::get('/debug/info', [DebugController::class, 'info']);
Route::get('/debug/routes', [DebugController::class, 'routes']);

// Webhook test routes
Route::any('/webhook/test', [WebhookTestController::class, 'test']);
Route::any('/webhook/test/telegram', [WebhookTestController::class, 'testTelegram']);
Route::any('/webhook/test/nowpayments', [WebhookTestController::class, 'testNowPayments']);
Route::post('/test-post', function() { return 'post works'; });

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
