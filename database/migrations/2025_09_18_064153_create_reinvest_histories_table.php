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
        Schema::create('reinvest_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ai_trade_id')->nullable()->constrained('ai_trades')->onDelete('set null');
            $table->decimal('original_amount', 15, 2)->comment('Original amount before tax');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('Tax deducted from reinvestment');
            $table->decimal('net_amount', 15, 2)->comment('Net amount after tax deduction');
            $table->enum('wallet_type', ['profit', 'bonus'])->comment('Source wallet type');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');
            $table->decimal('expected_return', 15, 2)->nullable()->comment('Expected return amount');
            $table->text('notes')->nullable()->comment('Admin notes or failure reasons');
            $table->unsignedBigInteger('processed_by')->nullable()->comment('Admin who processed if manually handled');
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['wallet_type', 'status']);
            $table->index('processed_at');
            $table->index('ai_trade_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reinvest_histories');
    }
};
