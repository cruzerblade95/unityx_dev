<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TelegramNotificationService;
use App\Models\User;

echo "=== TESTING TELEGRAM SERVICE ===\n";

// Get user with numeric chat_id
$user = User::where('id', 14)->first(); // notitest user
if (!$user) {
    echo "❌ User not found\n";
    exit;
}

echo "👤 User: {$user->username}\n";
echo "📱 Chat ID: {$user->telegram_id}\n";

// Enable logging for this test
config(['logging.channels.single.level' => 'debug']);

echo "\n📤 Testing TelegramNotificationService...\n";

$result = TelegramNotificationService::sendNotificationToTelegram(
    14,
    "Service Test",
    "This is a test using the TelegramNotificationService class.\n\nPlease check if this message arrives!"
);

if ($result) {
    echo "✅ Service returned success!\n";
} else {
    echo "❌ Service returned failure!\n";
}

echo "\n📋 Check the Laravel logs for detailed information.\n";
echo "=== TEST COMPLETE ===\n";
