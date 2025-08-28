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
        Schema::create('transaction_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('admin_id')->nullable(); // Admin who made the change
            $table->enum('transaction_type', ['add', 'deduct']); // Type of transaction
            $table->enum('balance_type', ['deposit', 'profit', 'bonus', 'cash']); // Which balance was affected
            $table->decimal('amount', 15, 2); // Transaction amount
            $table->decimal('balance_before', 15, 2); // Balance before transaction
            $table->decimal('balance_after', 15, 2); // Balance after transaction
            $table->string('reason')->nullable(); // Reason for the transaction
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');

            // Index for faster queries
            $table->index(['user_id', 'created_at']);
            $table->index(['wallet_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_histories');
    }
};
