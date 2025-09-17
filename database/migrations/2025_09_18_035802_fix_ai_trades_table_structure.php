<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_trades', function (Blueprint $table) {
            // Add missing columns that should exist based on the original migration
            $table->decimal('amount_invest', 18, 8)->default(0.00000000)->after('user_id');
            $table->decimal('current_amount', 18, 8)->default(0.00000000)->after('trade_status');
            $table->decimal('ending_amount', 18, 8)->default(0.00000000)->after('current_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_trades', function (Blueprint $table) {
            $table->dropColumn(['amount_invest', 'current_amount', 'ending_amount']);
        });
    }
};
