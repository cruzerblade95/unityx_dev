<?php
// Quick debug test for upline management DataTable
require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== Upline Management DataTable Debug Test ===\n\n";

// Test the optimized query
echo "Testing optimized User query with eager loading...\n";
$start = microtime(true);

$users = User::with(['wallet', 'downlines', 'upline'])
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

$end = microtime(true);
$queryTime = ($end - $start) * 1000;

echo "Query completed in: " . round($queryTime, 2) . "ms\n";
echo "Users retrieved: " . $users->count() . "\n\n";

// Test data formatting
echo "Sample user data:\n";
foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Username: {$user->username}\n";
    echo "Email: {$user->email}\n";
    echo "Upline: " . ($user->upline ? $user->upline->username : 'None') . "\n";
    echo "Downlines count: " . $user->downlines->count() . "\n";

    if ($user->wallet) {
        echo "Wallet - Deposit: $" . number_format($user->wallet->deposit, 2) .
             ", Profit: $" . number_format($user->wallet->profit, 2) .
             ", Total: $" . number_format($user->wallet->deposit + $user->wallet->profit, 2) . "\n";
    } else {
        echo "Wallet: Not available\n";
    }

    echo "---\n";
}

echo "\n=== Database Queries ===\n";
// Enable query logging to see all queries
\Illuminate\Support\Facades\DB::enableQueryLog();

// Run the same query again
$users = User::with(['wallet', 'downlines', 'upline'])
    ->orderBy('id', 'desc')
    ->limit(3)
    ->get();

$queries = \Illuminate\Support\Facades\DB::getQueryLog();
echo "Number of queries executed: " . count($queries) . "\n";

foreach ($queries as $index => $query) {
    echo "Query " . ($index + 1) . ": " . $query['query'] . "\n";
    echo "Time: " . $query['time'] . "ms\n";
    echo "Bindings: " . json_encode($query['bindings']) . "\n\n";
}

echo "=== Test Complete ===\n";
