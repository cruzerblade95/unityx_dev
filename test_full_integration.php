<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\NotificationService;

echo "=== TESTING FULL NOTIFICATION INTEGRATION ===\n";

echo "📤 Testing NotificationService with dual web + Telegram delivery...\n";

$result = NotificationService::createDepositSuccessNotification(
    14, // notitest user
    99.99, // test amount
    'telegram_test_123' // test deposit ID
);

if ($result) {
    echo "✅ NotificationService returned success!\n";
    echo "📱 Check your Telegram app for the notification!\n";
    echo "🌐 Check the web dashboard for the web notification!\n";
} else {
    echo "❌ NotificationService failed!\n";
}

echo "\n=== TEST COMPLETE ===\n";
