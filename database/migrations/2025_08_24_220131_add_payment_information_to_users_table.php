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
            $table->string('national_id')->nullable()->after('currency');
            $table->string('crypto_wallet_address')->nullable()->after('national_id');
            $table->string('etc_black_card_number')->nullable()->after('crypto_wallet_address');
            $table->string('cardholder_name')->nullable()->after('etc_black_card_number');
            $table->boolean('payment_info_completed')->default(false)->after('cardholder_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'national_id',
                'crypto_wallet_address',
                'etc_black_card_number',
                'cardholder_name',
                'payment_info_completed'
            ]);
        });
    }
};
