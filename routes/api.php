<?php
use App\Http\Controllers\TelegramController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/telegram/webhook', [TelegramController::class, 'webhook']);
Route::get('/telegram/webhook', function() { return 'ok'; });
Route::get('/telegram/setup-webhook', [TelegramController::class, 'setupWebhook']);

// Very simple test webhook that logs everything
Route::post('/telegram/simple-test', function(Request $request) {
    file_put_contents(
        storage_path('logs/simple_test.log'),
        "[" . date('Y-m-d H:i:s') . "] Simple test called - " . $request->getContent() . PHP_EOL,
        FILE_APPEND
    );
    return response('OK', 200);
});

// Test route for debugging
Route::get('/test', function() {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
