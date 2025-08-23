<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Notification;
use App\Models\Deposit;
use Carbon\Carbon;

echo "=== NOTIFICATION DEBUG ===\n";

// Check recent deposits
echo "\nRecent deposits (last 10):\n";
$deposits = Deposit::orderBy('created_at', 'desc')->limit(10)->get();
foreach ($deposits as $deposit) {
    echo "ID: {$deposit->id} | User: {$deposit->user_id} | Amount: {$deposit->amount} | Status: {$deposit->status} | Created: {$deposit->created_at}\n";
}

// Check all notifications
echo "\nAll notifications in database:\n";
$notifications = Notification::orderBy('created_at', 'desc')->get();
foreach ($notifications as $notification) {
    echo "ID: {$notification->id} | User: {$notification->user_id} | Type: {$notification->type} | Created: {$notification->created_at} | Title: {$notification->title}\n";
}

echo "\nTotal notifications: " . Notification::count() . "\n";
echo "Total deposits: " . Deposit::count() . "\n";

// Check for notifications created today
echo "\nNotifications created today:\n";
$today = Carbon::today();
$todayNotifications = Notification::whereDate('created_at', $today)->get();
foreach ($todayNotifications as $notification) {
    echo "ID: {$notification->id} | User: {$notification->user_id} | Type: {$notification->type} | Created: {$notification->created_at} | Title: {$notification->title}\n";
}

echo "\nNotifications created today: " . $todayNotifications->count() . "\n";
