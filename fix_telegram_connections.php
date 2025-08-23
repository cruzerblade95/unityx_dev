<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TELEGRAM RECONNECTION HELPER ===\n";

$users = User::whereNotNull('telegram_id')->get();

if ($users->count() === 0) {
    echo "❌ No users with telegram_id found\n";
    exit;
}

echo "📋 Users with Telegram connections:\n";
foreach ($users as $user) {
    echo "\n👤 User: {$user->username} (ID: {$user->id})\n";
    echo "📱 Current telegram_id: {$user->telegram_id}\n";
    echo "🔢 Is numeric (valid chat_id): " . (is_numeric($user->telegram_id) ? "✅ YES" : "❌ NO") . "\n";

    if (!is_numeric($user->telegram_id)) {
        echo "❌ This user needs to reconnect Telegram\n";
        echo "💡 Current value looks like a username, but we need a numeric chat_id\n";

        // Clear the invalid telegram_id
        $user->telegram_id = null;
        $user->save();
        echo "✅ Cleared invalid telegram_id - user needs to reconnect\n";
    } else {
        echo "✅ This user has a valid chat_id\n";
    }
}

echo "\n📝 INSTRUCTIONS FOR USERS TO RECONNECT:\n";
echo "1. Visit: " . env('APP_URL') . "/connect-telegram\n";
echo "2. Follow the instructions to connect with the Telegram bot\n";
echo "3. Make sure to complete the verification process\n";
echo "4. The system will now store the numeric chat_id instead of username\n";

echo "\n💡 WHAT CHANGED:\n";
echo "- The verification process now prioritizes storing chat_id over username\n";
echo "- Verification records are kept instead of being deleted\n";
echo "- This ensures notifications can be sent properly\n";
