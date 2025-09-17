<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Withdrawal;

class DebugGoldWithdrawals extends Command
{
    protected $signature = 'debug:gold-withdrawals';
    protected $description = 'Debug gold withdrawal data';

    public function handle()
    {
        $this->info('=== DEBUGGING GOLD WITHDRAWALS ===');

        $goldWithdrawals = Withdrawal::where('withdrawal_type', 'gold')
            ->with('user')
            ->limit(3)
            ->get();

        $this->info("Found " . $goldWithdrawals->count() . " gold withdrawals");

        foreach($goldWithdrawals as $withdrawal) {
            $this->info("=== Withdrawal ID: {$withdrawal->id} ===");
            $this->info("Status: {$withdrawal->status}");
            $this->info("Bank Name: " . ($withdrawal->bank_name ?? 'NULL'));
            $this->info("Account Number: " . ($withdrawal->account_number ?? 'NULL'));
            $this->info("Branch Selected: " . ($withdrawal->branch_selected ? 'true' : 'false'));
            $this->info("Branch Name: " . ($withdrawal->branch_name ?? 'NULL'));
            $this->info("Branch Manager: " . ($withdrawal->branch_manager_name ?? 'NULL'));

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

            $this->info("Controller would return:");
            $this->info(json_encode($bankInfo, JSON_PRETTY_PRINT));
            $this->info("");
        }

        return 0;
    }
}
