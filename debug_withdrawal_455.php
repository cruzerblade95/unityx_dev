<?php

// Debug script to check withdrawal and user data
// Run this on the server to identify the issue

require_once __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Withdrawal;
use App\Models\User;

try {
    echo "=== Withdrawal Debug Script ===\n";

    // Check if withdrawal 455 exists
    $withdrawal = Withdrawal::find(455);
    if (!$withdrawal) {
        echo "❌ Withdrawal ID 455 not found\n";
        exit(1);
    }

    echo "✅ Withdrawal ID 455 found\n";
    echo "   Status: {$withdrawal->status}\n";
    echo "   Type: {$withdrawal->withdrawal_type}\n";
    echo "   Amount: {$withdrawal->amount}\n";
    echo "   User ID: {$withdrawal->user_id}\n";

    // Check withdrawal columns
    $withdrawalAttributes = $withdrawal->getAttributes();
    echo "\n=== Withdrawal Columns ===\n";
    foreach ($withdrawalAttributes as $key => $value) {
        echo "   {$key}: " . (is_null($value) ? 'NULL' : $value) . "\n";
    }

    // Check if user exists
    $user = User::find($withdrawal->user_id);
    if (!$user) {
        echo "❌ User ID {$withdrawal->user_id} not found - This is likely the issue!\n";
        exit(1);
    }

    echo "\n✅ User found\n";
    echo "   Username: {$user->username}\n";
    echo "   Email: {$user->email}\n";

    // Check user columns that the code accesses
    $userColumns = [
        'firstname', 'lastname', 'usdt_bep20_address', 'usdc_bep20_address',
        'fdusd_bep20_address', 'etc_black_card_number', 'cardholder_name',
        'account_number', 'card_wallet_address'
    ];

    echo "\n=== User Columns Check ===\n";
    foreach ($userColumns as $column) {
        if ($user->offsetExists($column)) {
            $value = $user->{$column};
            echo "   {$column}: " . (is_null($value) ? 'NULL' : $value) . "\n";
        } else {
            echo "   ❌ {$column}: COLUMN MISSING\n";
        }
    }

    // Test the relationship
    echo "\n=== Relationship Test ===\n";
    $withdrawalWithUser = Withdrawal::with('user')->find(455);
    if ($withdrawalWithUser->user) {
        echo "✅ User relationship working\n";
    } else {
        echo "❌ User relationship broken\n";
    }

    echo "\n✅ Debug complete - no fatal errors found\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
