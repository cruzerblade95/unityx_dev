<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't allow adding to ENUM directly, so we need to use raw SQL
        DB::statement("ALTER TABLE wallet_profit_transfers MODIFY COLUMN transfer_type ENUM('profit_to_temp', 'temp_to_deposit', 'temp_to_profit', 'profit_to_deposit', 'rollback') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the profit_to_deposit option (revert to original)
        DB::statement("ALTER TABLE wallet_profit_transfers MODIFY COLUMN transfer_type ENUM('profit_to_temp', 'temp_to_deposit', 'temp_to_profit', 'rollback') NOT NULL");
    }
};
