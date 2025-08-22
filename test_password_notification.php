<?php

// Test script to send a password change notification
// Run this by accessing /test-password-notification in your browser

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

Route::get('/test-password-notification', function() {
    // Make sure you're logged in or use a specific user ID
    $userId = Auth::id() ?? 1; // Use logged in user or user ID 1 for testing

    // Send a test password change notification
    $notification = NotificationService::createPasswordChangedNotification(
        $userId,
        request()->ip(),
        request()->userAgent()
    );

    return response()->json([
        'success' => true,
        'message' => 'Password change notification sent!',
        'notification' => $notification,
        'user_id' => $userId,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent()
    ]);
})->name('test.password.notification');
