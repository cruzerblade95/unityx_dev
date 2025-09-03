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
        Schema::table('withdrawals', function (Blueprint $table) {
            // First update any existing 'both' values to 'profit' as default
            DB::table('withdrawals')->where('wallet_type', 'both')->update(['wallet_type' => 'profit']);
            
            // Drop and recreate the column with new enum values
            $table->dropColumn('wallet_type');
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->enum('wallet_type', ['profit', 'bonus'])->default('profit')->after('withdrawal_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('wallet_type');
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->enum('wallet_type', ['profit', 'bonus', 'both'])->default('both')->after('withdrawal_type');
        });
    }
};
