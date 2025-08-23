<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "Users with telegram_id:\n";
$users = User::whereNotNull('telegram_id')->get();
foreach ($users as $user) {
    echo "User: {$user->username} (ID: {$user->id}) - Telegram ID: {$user->telegram_id}\n";
}

echo "\ntelegram_verifications table:\n";
$verifications = DB::table('telegram_verifications')->get();
if ($verifications->count() === 0) {
    echo "No records found\n";
} else {
    foreach ($verifications as $v) {
        echo "User {$v->user_id}: {$v->telegram_username} -> Chat ID: {$v->chat_id}\n";
    }
}
