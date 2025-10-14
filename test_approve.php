<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $deposit = \App\Models\Deposit::find(18);

    echo "Deposit ID: " . $deposit->id . "\n";
    echo "Payment Method: " . $deposit->payment_method . "\n";
    echo "Amount: " . $deposit->amount . "\n";
    echo "Status: " . $deposit->status . "\n";
    echo "\n";

    $user = $deposit->user;
    echo "User: " . $user->name . "\n";
    echo "User ID: " . $user->id . "\n";

    if ($user->wallet) {
        echo "User has wallet: YES\n";
        echo "Current balance: " . $user->wallet->deposit . "\n";
    } else {
        echo "User has wallet: NO - THIS IS THE PROBLEM!\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
}
