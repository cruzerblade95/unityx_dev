<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "=== TESTING NEW USER REGISTRATION ===\n";

// Count notifications before
$notificationsBefore = Notification::count();
echo "Notifications before user creation: {$notificationsBefore}\n";

// Generate unique 6-character referral code
do {
    $newReferralCode = strtoupper(Str::random(6));
} while (User::where('referral_code', $newReferralCode)->exists());

// Get an existing user's referral code for the test
$existingUser = User::first();
$referralCode = $existingUser->referral_code;

echo "Creating test user with referral code: {$referralCode}\n";

// Create a test user
$user = User::create([
    'firstname' => 'TestNew',
    'lastname' => 'User',
    'username' => 'testnewuser' . time(),
    'email' => 'testnew' . time() . '@example.com',
    'password' => Hash::make('password'),
    'referral_code' => $newReferralCode,
    'upline_id' => $existingUser->id,
]);

echo "New user created with ID: {$user->id}\n";

// Count notifications after
$notificationsAfter = Notification::count();
echo "Notifications after user creation: {$notificationsAfter}\n";

if ($notificationsAfter > $notificationsBefore) {
    echo "\n=== NEW NOTIFICATIONS CREATED ===\n";
    $newNotifications = Notification::where('id', '>', Notification::orderBy('id', 'desc')->skip($notificationsBefore)->first()->id ?? 0)->get();

    foreach ($newNotifications as $notification) {
        echo "ID: {$notification->id}\n";
        echo "User ID: {$notification->user_id}\n";
        echo "Type: {$notification->type}\n";
        echo "Title: {$notification->title}\n";
        echo "Message: {$notification->message}\n";
        echo "Created: {$notification->created_at}\n";
        echo "---\n";
    }
} else {
    echo "No new notifications were created during user registration.\n";
}

// Check notifications for the new user specifically
$userNotifications = Notification::where('user_id', $user->id)->get();
echo "\nNotifications for new user (ID: {$user->id}): " . $userNotifications->count() . "\n";

foreach ($userNotifications as $notification) {
    echo "- {$notification->type}: {$notification->title}\n";
}
