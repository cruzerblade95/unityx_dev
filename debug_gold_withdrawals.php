<?php

// Simple test to debug what's happening with gold withdrawals
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once 'vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Now we can use Laravel
use App\Models\Withdrawal;

echo "=== DEBUGGING GOLD WITHDRAWALS ===\n\n";

$goldWithdrawals = Withdrawal::where('withdrawal_type', 'gold')
    ->with('user')
    ->limit(3)
    ->get();

echo "Found " . $goldWithdrawals->count() . " gold withdrawals\n\n";

foreach($goldWithdrawals as $withdrawal) {
    echo "=== Withdrawal ID: {$withdrawal->id} ===\n";
    echo "Status: {$withdrawal->status}\n";
    echo "Bank Name: " . ($withdrawal->bank_name ?? 'NULL') . "\n";
    echo "Account Number: " . ($withdrawal->account_number ?? 'NULL') . "\n";
    echo "Branch Selected: " . ($withdrawal->branch_selected ? 'true' : 'false') . "\n";
    echo "Branch Name: " . ($withdrawal->branch_name ?? 'NULL') . "\n";
    echo "Branch Manager: " . ($withdrawal->branch_manager_name ?? 'NULL') . "\n";

    // Simulate what the controller does
    $bankInfo = [
        'type' => 'Gold Bank',
        'bank_name' => $withdrawal->bank_name ?? 'Not provided',
        'account_number' => $withdrawal->account_number ?
            '****' . substr($withdrawal->account_number, -4) :
            'Not provided'
    ];

    // Add branch info if assigned
    if ($withdrawal->branch_selected && $withdrawal->branch_name) {
        $bankInfo['branch'] = $withdrawal->branch_name;
        $bankInfo['manager'] = $withdrawal->branch_manager_name ?? 'Not assigned';
    }

    echo "Controller would return:\n";
    print_r($bankInfo);
    echo "\n";
}
