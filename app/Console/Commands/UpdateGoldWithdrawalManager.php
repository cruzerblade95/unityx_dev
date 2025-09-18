<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Withdrawal;

class UpdateGoldWithdrawalManager extends Command
{
    protected $signature = 'update:gold-manager {id}';
    protected $description = 'Update gold withdrawal with manager info';

    public function handle()
    {
        $id = $this->argument('id');

        $withdrawal = Withdrawal::find($id);
        if (!$withdrawal) {
            $this->error("Withdrawal $id not found");
            return 1;
        }

        if ($withdrawal->withdrawal_type !== 'gold') {
            $this->error("Withdrawal $id is not a gold withdrawal");
            return 1;
        }

        // Set proper manager based on branch
        $branchConfig = config('gold_branches.branches');
        $branchKey = null;

        foreach ($branchConfig as $key => $branch) {
            if ($branch['name'] === $withdrawal->branch_name) {
                $branchKey = $key;
                break;
            }
        }

        if ($branchKey && isset($branchConfig[$branchKey])) {
            $branchData = $branchConfig[$branchKey];
            $withdrawal->update([
                'branch_manager_name' => $branchData['manager_name'],
                'branch_manager_phone' => $branchData['manager_phone'],
            ]);

            $this->info("Updated withdrawal $id:");
            $this->info("Branch: {$withdrawal->branch_name}");
            $this->info("Manager: {$branchData['manager_name']}");
            $this->info("Phone: {$branchData['manager_phone']}");
        } else {
            $this->error("No config found for branch: {$withdrawal->branch_name}");
            return 1;
        }

        return 0;
    }
}
