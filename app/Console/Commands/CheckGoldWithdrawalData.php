<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Withdrawal;

class CheckGoldWithdrawalData extends Command
{
    protected $signature = 'check:gold-data {id}';
    protected $description = 'Check specific gold withdrawal data';

    public function handle()
    {
        $id = $this->argument('id');

        $withdrawal = Withdrawal::find($id);
        if (!$withdrawal) {
            $this->error("Withdrawal $id not found");
            return 1;
        }

        $this->info("=== Withdrawal ID: $id ===");
        $this->info("Branch Selected: " . ($withdrawal->branch_selected ? 'true' : 'false'));
        $this->info("Branch Name: " . ($withdrawal->branch_name ?? 'NULL'));
        $this->info("Branch Manager Name: " . ($withdrawal->branch_manager_name ?? 'NULL'));
        $this->info("Branch Manager Phone: " . ($withdrawal->branch_manager_phone ?? 'NULL'));

        return 0;
    }
}
