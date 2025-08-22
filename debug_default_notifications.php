<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Notification;
use App\Models\User;

echo "=== EXISTING NOTIFICATIONS DEBUG ===\n";

$notifications = Notification::all();
echo "Total notifications: " . $notifications->count() . "\n\n";

foreach ($notifications as $notification) {
    echo "ID: {$notification->id}\n";
    echo "User ID: {$notification->user_id}\n";
    echo "Type: {$notification->type}\n";
    echo "Title: {$notification->title}\n";
    echo "Message: {$notification->message}\n";
    echo "Created: {$notification->created_at}\n";
    echo "Read: " . ($notification->read_at ? 'Yes' : 'No') . "\n";
    echo "---\n";
}

echo "\n=== USERS ===\n";
$users = User::all();
foreach ($users as $user) {
    echo "User ID: {$user->id} | Email: {$user->email} | Created: {$user->created_at}\n";
}
