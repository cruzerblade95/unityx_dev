<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test the method
try {
    $user = new \App\Models\User();
    
    echo "Method exists: " . (method_exists($user, 'getWithdrawableBalance') ? 'YES' : 'NO') . PHP_EOL;
    
    // Try to get a user with wallet
    $userWithWallet = \App\Models\User::with('wallet')->first();
    if ($userWithWallet) {
        echo "User found, testing method..." . PHP_EOL;
        $balance = $userWithWallet->getWithdrawableBalance();
        echo "Balance: " . $balance . PHP_EOL;
    } else {
        echo "No users found in database" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
