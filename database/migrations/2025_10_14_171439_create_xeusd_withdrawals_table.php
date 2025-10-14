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
        Schema::create('xeusd_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('unique_id', 20)->unique(); // Withdrawal reference ID
            $table->decimal('amount_usd', 10, 2); // Amount in USD (1:1 with XEUSD)
            $table->decimal('amount_xeusd', 18, 8); // Amount in XEUSD tokens
            $table->string('source_wallet')->default('profit'); // profit, bonus, cash
            $table->string('recipient_address', 42); // User's MetaMask BSC address
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'rejected'])->default('pending');
            $table->string('transaction_hash', 66)->nullable(); // BSC transaction hash
            $table->text('admin_notes')->nullable(); // Admin rejection/approval notes
            $table->foreignId('processed_by')->nullable()->constrained('admins')->onDelete('set null'); // Admin who processed
            $table->timestamp('processed_at')->nullable(); // When admin processed
            $table->timestamp('blockchain_sent_at')->nullable(); // When sent to blockchain
            $table->timestamp('blockchain_confirmed_at')->nullable(); // When confirmed on chain
            $table->json('blockchain_data')->nullable(); // Store transaction details, gas used, etc.
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('unique_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xeusd_withdrawals');
    }
};
