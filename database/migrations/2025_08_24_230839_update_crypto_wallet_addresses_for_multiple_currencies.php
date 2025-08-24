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
        Schema::table('users', function (Blueprint $table) {
            // Add specific wallet address fields for different currencies
            $table->string('usdt_bep20_address')->nullable()->after('crypto_wallet_address');
            $table->string('usdc_bep20_address')->nullable()->after('usdt_bep20_address');
            $table->string('fdusd_bep20_address')->nullable()->after('usdc_bep20_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'usdt_bep20_address',
                'usdc_bep20_address', 
                'fdusd_bep20_address'
            ]);
        });
    }
};
