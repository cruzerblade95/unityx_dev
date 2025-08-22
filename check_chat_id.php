<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING TELEGRAM_VERIFICATIONS TABLE ===\n";

$verifications = DB::table('telegram_verifications')->get();

if ($verifications->count() === 0) {
    echo "❌ No records found in telegram_verifications table\n";
    echo "💡 This means all verification records were deleted after completion\n";
} else {
    echo "✅ Found {$verifications->count()} records:\n\n";
    foreach ($verifications as $v) {
        echo "📋 Verification Record:\n";
        echo "   User ID: {$v->user_id}\n";
        echo "   Chat ID: {$v->chat_id}\n";
        echo "   Username: {$v->telegram_username}\n";
        echo "   Token: {$v->token}\n";
        echo "   Expires: {$v->expires_at}\n";
        echo "   Created: {$v->created_at}\n";
        echo "   Updated: {$v->updated_at}\n";
        echo "---\n";
    }
}

echo "\n=== CHECKING USERS WITH TELEGRAM_ID ===\n";

$users = \App\Models\User::whereNotNull('telegram_id')->get();

if ($users->count() === 0) {
    echo "❌ No users with telegram_id found\n";
} else {
    echo "✅ Found {$users->count()} users with telegram_id:\n\n";
    foreach ($users as $user) {
        echo "👤 User: {$user->username} (ID: {$user->id})\n";
        echo "   Telegram ID: '{$user->telegram_id}'\n";
        echo "   Is numeric: " . (is_numeric($user->telegram_id) ? "✅ YES" : "❌ NO") . "\n";
        echo "   Length: " . strlen($user->telegram_id) . " chars\n";
        echo "---\n";
    }
}

echo "\n💡 SUMMARY:\n";
echo "- For notifications to work, we need numeric chat_id values\n";
echo "- If telegram_verifications table is empty, users need to reconnect\n";
echo "- If users have non-numeric telegram_id, they need to reconnect\n";
