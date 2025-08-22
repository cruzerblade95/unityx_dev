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
            $table->string('unique_address')->nullable()->after('payment_address');
            $table->string('address_memo')->nullable()->after('unique_address');
            $table->timestamp('address_expires_at')->nullable()->after('address_memo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn([
                'unique_address',
                'address_memo',
                'address_expires_at'
            ]);
        });
    }
};
