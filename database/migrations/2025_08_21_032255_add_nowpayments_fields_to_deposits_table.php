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
        Schema::table('deposits', function (Blueprint $table) {
            $table->string('payment_method')->default('manual')->after('status');
            $table->string('payment_id')->nullable()->after('payment_method');
            $table->text('payment_url')->nullable()->after('payment_id');
            $table->string('crypto_currency')->nullable()->after('payment_url');
            $table->decimal('crypto_amount', 20, 8)->nullable()->after('crypto_currency');
            $table->string('payment_address')->nullable()->after('crypto_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_id',
                'payment_url',
                'crypto_currency',
                'crypto_amount',
                'payment_address'
            ]);
        });
    }
};
