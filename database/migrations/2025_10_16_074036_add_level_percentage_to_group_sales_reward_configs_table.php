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
        Schema::table('group_sales_reward_configs', function (Blueprint $table) {
            $table->decimal('level_percentage', 5, 2)->default(100.00)->after('tax_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_sales_reward_configs', function (Blueprint $table) {
            $table->dropColumn('level_percentage');
        });
    }
};
