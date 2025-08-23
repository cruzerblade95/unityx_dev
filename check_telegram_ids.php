<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== CHECKING TELEGRAM_ID VALUES ===\n";

$users = User::whereNotNull('telegram_id')->get();

if ($users->count() === 0) {
    echo "❌ No users with telegram_id found\n";
    exit;
}

foreach ($users as $user) {
    echo "👤 User: {$user->username} (ID: {$user->id})\n";
    echo "📱 Telegram ID: '{$user->telegram_id}'\n";
    echo "🔢 Is numeric: " . (is_numeric($user->telegram_id) ? "✅ YES" : "❌ NO") . "\n";
    echo "📏 Length: " . strlen($user->telegram_id) . " characters\n";

    if (is_numeric($user->telegram_id)) {
        echo "✅ This looks like a valid chat ID\n";
    } else {
        echo "❌ This looks like a username, not a chat ID\n";
        echo "💡 Telegram usernames can't be used directly as chat IDs\n";
        echo "💡 You need the numeric chat ID from when the user first contacts the bot\n";
    }
    echo "---\n";
}

echo "\n💡 EXPLANATION:\n";
echo "- Telegram chat IDs are numeric (e.g., 123456789)\n";
echo "- Usernames (e.g., @username) cannot be used directly as chat IDs\n";
echo "- To get the chat ID, the user must first send a message to your bot\n";
echo "- The bot receives the chat ID in the webhook payload\n";
echo "- Store this numeric chat ID in the telegram_id field\n";
