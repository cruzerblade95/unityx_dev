<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;

echo "=== DIRECT TELEGRAM API TEST ===\n";

// Get user with numeric chat_id
$user = User::where('id', 14)->first(); // notitest user
if (!$user) {
    echo "❌ User not found\n";
    exit;
}

echo "👤 User: {$user->username}\n";
echo "📱 Chat ID: {$user->telegram_id}\n";

// Get bot token
$botToken = Config::get('services.telegram.bot_token');
echo "🔑 Bot Token: " . ($botToken ? "✅ Configured" : "❌ Not found") . "\n";

if (!$botToken) {
    echo "❌ Cannot proceed without bot token\n";
    exit;
}

// Prepare API call
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
$chatId = $user->telegram_id;
$message = "🧪 Direct API Test\n\nThis is a direct test of the Telegram Bot API.\n\n👤 Account: @{$user->username}\n⏰ Time: " . date('Y-m-d H:i:s');

$params = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML',
    'disable_web_page_preview' => true,
];

echo "\n📤 Sending message to Telegram API...\n";
echo "🔗 URL: {$url}\n";
echo "📊 Chat ID: {$chatId}\n";
echo "💬 Message length: " . strlen($message) . " chars\n";

try {
    $client = new Client(['timeout' => 30]);
    $response = $client->post($url, [
        'form_params' => $params,
        'verify' => false // Disable SSL verification for testing
    ]);

    $statusCode = $response->getStatusCode();
    $body = $response->getBody()->getContents();

    echo "\n📨 Response:\n";
    echo "Status Code: {$statusCode}\n";
    echo "Body: {$body}\n";

    $responseData = json_decode($body, true);

    if ($statusCode === 200 && isset($responseData['ok']) && $responseData['ok'] === true) {
        echo "\n✅ SUCCESS! Message sent to Telegram!\n";
        echo "📱 Check your Telegram app!\n";
        echo "📧 Message ID: " . ($responseData['result']['message_id'] ?? 'unknown') . "\n";
    } else {
        echo "\n❌ FAILED! Telegram API returned an error:\n";
        if (isset($responseData['description'])) {
            echo "Error: {$responseData['description']}\n";
        }
        if (isset($responseData['error_code'])) {
            echo "Error Code: {$responseData['error_code']}\n";
        }
    }

} catch (\GuzzleHttp\Exception\RequestException $e) {
    echo "\n❌ REQUEST EXCEPTION:\n";
    echo "Error: " . $e->getMessage() . "\n";

    if ($e->hasResponse()) {
        $response = $e->getResponse();
        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Response Body: " . $response->getBody()->getContents() . "\n";
    }

} catch (\Exception $e) {
    echo "\n❌ GENERAL EXCEPTION:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
