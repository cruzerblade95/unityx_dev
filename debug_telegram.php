<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\TelegramNotificationService;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

echo "=== DEBUGGING TELEGRAM NOTIFICATION ===\n";

// Check configuration
$botToken = Config::get('services.telegram.bot_token');
echo "🔑 Bot Token configured: " . ($botToken ? "✅ YES" : "❌ NO") . "\n";
if ($botToken) {
    echo "📝 Token (first 10 chars): " . substr($botToken, 0, 10) . "...\n";
}

// Find user with telegram_id
$user = User::whereNotNull('telegram_id')->first();
if (!$user) {
    echo "❌ No users with telegram_id found\n";
    exit;
}

echo "👤 User: {$user->username} (ID: {$user->id})\n";
echo "📱 Telegram ID: {$user->telegram_id}\n";

// Check if telegram_id is numeric (chat_id) or username
$isNumeric = is_numeric($user->telegram_id);
echo "🔢 Is numeric chat_id: " . ($isNumeric ? "✅ YES" : "❌ NO") . "\n";

// If not numeric, check telegram_verifications table
if (!$isNumeric) {
    echo "🔍 Looking for chat_id in telegram_verifications table...\n";

    $verification = DB::table('telegram_verifications')
        ->where('telegram_username', $user->telegram_id)
        ->where('user_id', $user->id)
        ->first();

    if ($verification) {
        echo "✅ Found verification record\n";
        echo "📱 Chat ID from verification: {$verification->chat_id}\n";
        echo "👤 Username from verification: {$verification->telegram_username}\n";

        $chatId = $verification->chat_id;
    } else {
        echo "❌ No verification record found\n";
        echo "💡 User might need to reconnect Telegram\n";

        // Show all telegram_verifications for debugging
        $allVerifications = DB::table('telegram_verifications')->get();
        echo "📋 All telegram_verifications in database:\n";
        foreach ($allVerifications as $v) {
            echo "   - User {$v->user_id}: {$v->telegram_username} -> Chat ID: {$v->chat_id}\n";
        }
        exit;
    }
} else {
    $chatId = $user->telegram_id;
}

echo "🎯 Final Chat ID to use: {$chatId}\n";

// Test Telegram API directly
echo "\n🧪 Testing Telegram API directly...\n";

if (!$botToken) {
    echo "❌ Cannot test - no bot token\n";
    exit;
}

$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
$params = [
    'chat_id' => $chatId,
    'text' => "🧪 Direct API Test\n\nThis is a direct test of the Telegram API from the debug script.\n\n👤 Account: @{$user->username}",
    'parse_mode' => 'HTML',
    'disable_web_page_preview' => true,
];

try {
    $client = new \GuzzleHttp\Client(['timeout' => 10]);
    echo "📤 Sending to Telegram API...\n";
    echo "🔗 URL: {$url}\n";
    echo "📊 Chat ID: {$chatId}\n";

    $response = $client->post($url, ['form_params' => $params]);
    $statusCode = $response->getStatusCode();
    $body = $response->getBody()->getContents();

    echo "📨 Response Status: {$statusCode}\n";
    echo "📄 Response Body: {$body}\n";

    if ($statusCode === 200) {
        echo "✅ Telegram API call successful!\n";
        echo "🎉 Check your Telegram for the test message!\n";
    } else {
        echo "❌ Telegram API call failed with status {$statusCode}\n";
    }

} catch (\Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
    echo "📄 Full error: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
