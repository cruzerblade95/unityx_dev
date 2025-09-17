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
        Schema::table('deposit_promo_bonus_history', function (Blueprint $table) {
            $table->string('deposit_reference')->nullable()->after('upline_bonus_amount')->comment('Reference to original deposit ID to prevent duplicate processing');
            $table->index('deposit_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposit_promo_bonus_history', function (Blueprint $table) {
            $table->dropIndex(['deposit_reference']);
            $table->dropColumn('deposit_reference');
        });
    }
};
