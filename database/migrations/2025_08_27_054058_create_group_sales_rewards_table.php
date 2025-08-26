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
        Schema::create('group_sales_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('reward_level'); // 1-8
            $table->decimal('sales_threshold', 15, 2); // The sales threshold for this reward
            $table->decimal('reward_amount', 15, 2); // The actual amount rewarded
            $table->decimal('tax_percentage', 5, 2); // Tax percentage applied
            $table->decimal('total_sales_at_claim', 15, 2); // Total sales at the time of claim
            $table->string('claimed_month', 7); // Format: YYYY-MM
            $table->timestamp('claimed_at');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Unique constraint to prevent claiming same reward multiple times in same month
            $table->unique(['user_id', 'reward_level', 'claimed_month']);

            // Indexes
            $table->index(['user_id', 'claimed_month']);
            $table->index('reward_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_sales_rewards');
    }
};
