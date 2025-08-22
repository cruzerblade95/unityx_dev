<?php

use Illuminate\Support\Facades\Route;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Test route for Telegram notifications
Route::post('/test-telegram-notification', function (Request $request) {
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = Auth::user();

    if (!$user->telegram_id) {
        return response()->json([
            'success' => false,
            'message' => 'No Telegram account connected. Please connect your Telegram first.'
        ]);
    }

    $title = $request->input('title', '🧪 Test Notification');
    $message = $request->input('message', 'This is a test notification to verify Telegram integration is working properly.');

    $result = TelegramNotificationService::sendNotificationToTelegram(
        $user->id,
        $title,
        $message
    );

    return response()->json([
        'success' => $result,
        'message' => $result ? 'Test notification sent successfully!' : 'Failed to send notification',
        'telegram_id' => $user->telegram_id,
        'username' => $user->username
    ]);
});
