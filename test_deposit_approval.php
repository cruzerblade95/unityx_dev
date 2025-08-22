<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Notification;
use App\Models\Deposit;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

echo "=== MANUAL DEPOSIT APPROVAL TEST ===\n";

// Find the first pending deposit
$pendingDeposit = Deposit::where('status', 'pending')->first();

if (!$pendingDeposit) {
    echo "No pending deposits found!\n";
    exit;
}

echo "Found pending deposit:\n";
echo "ID: {$pendingDeposit->id} | User: {$pendingDeposit->user_id} | Amount: {$pendingDeposit->amount} | Status: {$pendingDeposit->status}\n";

// Count notifications before
$notificationsBefore = Notification::count();
echo "\nNotifications before approval: {$notificationsBefore}\n";

// Approve the deposit manually (simulate the controller logic)
echo "\nApproving deposit...\n";

try {
    // Update deposit status
    $pendingDeposit->status = 'completed';
    $pendingDeposit->save();
    echo "✓ Deposit status updated to completed\n";

    // Update user wallet balance
    if ($pendingDeposit->user && $pendingDeposit->user->wallet) {
        $pendingDeposit->user->wallet->deposit += $pendingDeposit->net_amount;
        $pendingDeposit->user->wallet->save();
        echo "✓ User wallet updated (+{$pendingDeposit->net_amount})\n";
    }

    // Create notification
    echo "Creating notification...\n";
    NotificationService::createDepositSuccessNotification(
        $pendingDeposit->user_id,
        $pendingDeposit->net_amount,
        $pendingDeposit->id
    );
    echo "✓ Notification service called\n";

    // Count notifications after
    $notificationsAfter = Notification::count();
    echo "\nNotifications after approval: {$notificationsAfter}\n";

    if ($notificationsAfter > $notificationsBefore) {
        echo "✅ SUCCESS: New notification created!\n";

        // Get the latest notification
        $latestNotification = Notification::latest()->first();
        echo "Latest notification:\n";
        echo "ID: {$latestNotification->id} | User: {$latestNotification->user_id} | Type: {$latestNotification->type} | Created: {$latestNotification->created_at} | Title: {$latestNotification->title}\n";
    } else {
        echo "❌ ERROR: No new notification was created!\n";
    }

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
