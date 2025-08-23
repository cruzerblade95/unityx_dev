<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

echo "=== NOTIFICATION API TEST ===\n";

// Simulate user authentication
$user = User::find(1);
if (!$user) {
    echo "User not found!\n";
    exit;
}

echo "Testing notifications API for user: {$user->id}\n";

// Clear cache (same as in the controller)
Cache::forget("user_notifications_{$user->id}");
Cache::forget("notifications_user_{$user->id}");

// Get notifications (same query as controller)
$notifications = Notification::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

$unread_count = Notification::where('user_id', $user->id)
    ->whereNull('read_at')
    ->count();

echo "\nAPI Response:\n";
echo "Total notifications: " . $notifications->count() . "\n";
echo "Unread count: " . $unread_count . "\n";

echo "\nNotification list:\n";
foreach ($notifications as $notification) {
    echo "ID: {$notification->id} | Type: {$notification->type} | Created: {$notification->created_at} | Title: {$notification->title} | Read: " . ($notification->read_at ? 'Yes' : 'No') . "\n";
}

echo "\nJSON Response:\n";
$response = [
    'notifications' => $notifications->toArray(),
    'unread_count' => $unread_count
];

echo json_encode($response, JSON_PRETTY_PRINT);

echo "\n\n=== TEST COMPLETE ===\n";
