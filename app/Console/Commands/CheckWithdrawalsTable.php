<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckWithdrawalsTable extends Command
{
    protected $signature = 'check:withdrawals-columns';
    protected $description = 'Check withdrawals table columns';

    public function handle()
    {
        $this->info('=== Withdrawals Table Columns ===');

        $columns = Schema::getColumnListing('withdrawals');

        foreach($columns as $column) {
            $this->info("- $column");
        }

        // Check specifically for branch-related columns
        $branchColumns = [
            'withdrawal_type',
            'branch_name',
            'branch_selected',
            'branch_manager_name',
            'branch_manager_phone',
            'branch_assigned_at',
            'assigned_by_admin_id'
        ];

        $this->info('');
        $this->info('=== Branch-related columns ===');
        foreach($branchColumns as $column) {
            $exists = in_array($column, $columns);
            $status = $exists ? '✓ EXISTS' : '✗ MISSING';
            $this->info("$column: $status");
        }

        return 0;
    }
}
