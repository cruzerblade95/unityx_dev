<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TelegramNotificationService;
use App\Models\User;

echo "=== TESTING WITH USER WHO HAS NUMERIC CHAT_ID ===\n";

// Find user with numeric telegram_id
$user = User::whereNotNull('telegram_id')->get()->filter(function($u) {
    return is_numeric($u->telegram_id);
})->first();

if (!$user) {
    echo "❌ No user with numeric telegram_id found\n";
    exit;
}

echo "✅ Found user with numeric telegram_id:\n";
echo "👤 User: {$user->username} (ID: {$user->id})\n";
echo "📱 Telegram ID (Chat ID): {$user->telegram_id}\n";

echo "\n🧪 Testing Telegram notification...\n";

$result = TelegramNotificationService::sendNotificationToTelegram(
    $user->id,
    '🧪 Test Notification from PHP Script',
    'Hello! This is a test notification to verify that the Telegram integration is working with the correct numeric chat_id. If you receive this message, everything is working properly! 🎉'
);

if ($result) {
    echo "✅ Telegram notification sent successfully!\n";
    echo "📱 Check your Telegram app for the message!\n";
} else {
    echo "❌ Failed to send Telegram notification\n";
    echo "💡 Check the logs for more details\n";
}

echo "\n=== TEST COMPLETE ===\n";
