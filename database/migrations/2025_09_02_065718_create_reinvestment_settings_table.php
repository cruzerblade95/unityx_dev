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
        Schema::create('reinvestment_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // null for global defaults
            $table->enum('wallet_type', ['profit', 'bonus']);
            $table->decimal('min_amount', 10, 2)->default(1.00);
            $table->decimal('max_amount', 10, 2)->default(10000.00);
            $table->decimal('tax_percent', 5, 2)->default(0.00); // Tax percentage (0-100%)
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_global_default')->default(false);
            $table->json('additional_settings')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['user_id', 'wallet_type']);
            $table->index(['wallet_type', 'is_global_default']);

            // Unique constraints
            $table->unique(['user_id', 'wallet_type'], 'unique_user_wallet_settings');
            $table->unique(['wallet_type', 'is_global_default'], 'unique_global_wallet_defaults');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reinvestment_settings');
    }
};
