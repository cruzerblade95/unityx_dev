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
        Schema::create('group_sales_reward_configs', function (Blueprint $table) {
            $table->id();
            $table->string('reward_title');
            $table->integer('reward_level'); // 1-8
            $table->decimal('sales_required', 15, 2); // Sales threshold required
            $table->decimal('base_percentage', 5, 2)->default(15.00); // Base percentage (15%)
            $table->decimal('tax_percentage', 5, 2); // Tax percentage
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint on reward level
            $table->unique('reward_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_sales_reward_configs');
    }
};
