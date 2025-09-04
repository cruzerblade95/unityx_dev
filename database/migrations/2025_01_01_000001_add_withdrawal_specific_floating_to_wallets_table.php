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
        Schema::table('wallets', function (Blueprint $table) {
            // Add separate floating fields for different withdrawal types
            $table->decimal('crypto_floating', 15, 2)->default(0)->after('floating')->comment('Floating amount for crypto withdrawals');
            $table->decimal('bank_floating', 15, 2)->default(0)->after('crypto_floating')->comment('Floating amount for bank/crypto bank card withdrawals');
            $table->decimal('gold_floating', 15, 2)->default(0)->after('bank_floating')->comment('Floating amount for gold withdrawals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['crypto_floating', 'bank_floating', 'gold_floating']);
        });
    }
};
