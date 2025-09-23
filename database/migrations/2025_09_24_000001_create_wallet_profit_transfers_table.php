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
        Schema::create('wallet_profit_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 15, 2);
            $table->enum('transfer_type', ['profit_to_temp', 'temp_to_deposit', 'temp_to_profit', 'rollback']);
            $table->enum('status', ['pending', 'completed', 'failed', 'rolled_back']);
            $table->string('batch_id')->nullable(); // For grouping transfers together
            $table->unsignedBigInteger('processed_by'); // Admin who processed
            $table->decimal('original_profit_balance', 15, 2)->nullable();
            $table->decimal('original_deposit_balance', 15, 2)->nullable();
            $table->decimal('original_temp_balance', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['batch_id', 'status']);
            $table->index(['user_id', 'transfer_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_profit_transfers');
    }
};
