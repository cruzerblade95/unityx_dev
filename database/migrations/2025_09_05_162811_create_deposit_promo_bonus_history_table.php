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
        Schema::create('deposit_promo_bonus_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('campaign_id');
            $table->decimal('deposit_amount', 15, 2); // Original deposit amount
            $table->decimal('user_bonus_amount', 15, 2); // Bonus amount for user
            $table->decimal('upline_bonus_amount', 15, 2); // Bonus amount for upline
            $table->enum('status', ['pending', 'claimed', 'expired'])->default('pending');
            $table->timestamp('expires_at')->nullable(); // When this bonus expires
            $table->timestamp('claimed_at')->nullable(); // When bonus was claimed
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('campaign_id')->references('id')->on('deposit_promo_campaigns')->onDelete('cascade');

            $table->index(['user_id', 'status']);
            $table->index(['campaign_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_promo_bonus_history');
    }
};
