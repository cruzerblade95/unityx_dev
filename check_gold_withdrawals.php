<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

echo "Checking gold withdrawals...\n";

$withdrawals = \App\Models\Withdrawal::where('withdrawal_type', 'gold')
    ->limit(5)
    ->get([
        'id',
        'bank_name',
        'account_number',
        'branch_selected',
        'branch_name',
        'branch_manager_name',
        'status',
        'withdrawal_type'
    ]);

if ($withdrawals->count() === 0) {
    echo "No gold withdrawals found in database.\n";
} else {
    foreach($withdrawals as $w) {
        echo "ID: {$w->id}\n";
        echo "  Type: {$w->withdrawal_type}\n";
        echo "  Bank: " . ($w->bank_name ?? 'NULL') . "\n";
        echo "  Account: " . ($w->account_number ?? 'NULL') . "\n";
        echo "  Branch Selected: " . ($w->branch_selected ? 'true' : 'false') . "\n";
        echo "  Branch Name: " . ($w->branch_name ?? 'NULL') . "\n";
        echo "  Branch Manager: " . ($w->branch_manager_name ?? 'NULL') . "\n";
        echo "  Status: {$w->status}\n";
        echo "  ---\n";
    }
}
