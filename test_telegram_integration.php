<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TelegramNotificationService;
use App\Services\NotificationService;
use App\Models\User;

echo "=== TESTING TELEGRAM NOTIFICATION INTEGRATION ===\n";

// Find a user with telegram_id
$userWithTelegram = User::whereNotNull('telegram_id')->first();

if (!$userWithTelegram) {
    echo "❌ No users found with telegram_id. Please connect at least one user to Telegram first.\n";
    echo "Visit: " . env('APP_URL') . "/connect-telegram\n";
    exit;
}

echo "✅ Found user with Telegram ID: {$userWithTelegram->username} (ID: {$userWithTelegram->id})\n";
echo "📱 Telegram ID: {$userWithTelegram->telegram_id}\n\n";

// Test 1: Direct Telegram notification
echo "🧪 Test 1: Direct Telegram notification\n";
$result1 = TelegramNotificationService::sendNotificationToTelegram(
    $userWithTelegram->id,
    '🧪 Direct Test Notification',
    'This is a direct test of the Telegram notification service. If you see this, the basic functionality is working!'
);

if ($result1) {
    echo "✅ Direct Telegram notification sent successfully!\n";
} else {
    echo "❌ Failed to send direct Telegram notification\n";
}

echo "\n";

// Test 2: Web notification that should also trigger Telegram
echo "🧪 Test 2: Creating web notification (should also send to Telegram)\n";
$notification = NotificationService::createDepositSuccessNotification(
    $userWithTelegram->id,
    99.99,
    'telegram_test_' . time()
);

if ($notification) {
    echo "✅ Web notification created successfully!\n";
    echo "📧 Notification ID: {$notification->id}\n";
    echo "💬 Should have also sent to Telegram automatically\n";
} else {
    echo "❌ Failed to create web notification\n";
}

echo "\n";

// Test 3: Test connection
echo "🧪 Test 3: Test Telegram connection\n";
$result3 = TelegramNotificationService::testTelegramConnection($userWithTelegram->id);

if ($result3) {
    echo "✅ Telegram connection test successful!\n";
} else {
    echo "❌ Telegram connection test failed\n";
}

echo "\n=== TESTING COMPLETE ===\n";
echo "Check your Telegram app for the test notifications!\n";
echo "If you received notifications, the integration is working correctly. 🎉\n";
