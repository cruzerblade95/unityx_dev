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
        Schema::create('withdrawal_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // null for global settings
            $table->string('withdrawal_type'); // 'crypto', 'etc_black', 'gold'
            $table->decimal('min_amount', 15, 2)->default(10.00);
            $table->decimal('max_amount', 15, 2)->default(10000.00);
            $table->decimal('processing_fee_percent', 5, 2)->default(4.75);
            $table->decimal('processing_fee_fixed', 15, 2)->default(0.00);
            $table->integer('cooldown_hours')->default(30);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_global_default')->default(false);
            $table->json('additional_settings')->nullable(); // for future extensibility
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'withdrawal_type']);
            $table->index('withdrawal_type');
            $table->index('is_global_default');

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Unique constraint to prevent duplicate settings
            $table->unique(['user_id', 'withdrawal_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_settings');
    }
};
