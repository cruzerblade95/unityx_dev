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
        Schema::create('trading_cron_test_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('ai_trade_id')->nullable();
            $table->string('test_type', 50); // 'manual_test', 'auto_test'
            $table->string('status', 20); // 'success', 'failed', 'skipped'
            $table->decimal('profit_amount', 20, 8)->nullable();
            $table->decimal('trade_amount', 20, 8)->nullable();
            $table->decimal('current_amount', 20, 8)->nullable();
            $table->decimal('ending_amount', 20, 8)->nullable();
            $table->text('error_message')->nullable();
            $table->text('error_details')->nullable();
            $table->json('data_snapshot')->nullable(); // Store complete data at test time
            $table->boolean('ai_trade_active')->default(false);
            $table->boolean('trade_status_valid')->default(false);
            $table->boolean('wallet_exists')->default(false);
            $table->string('failure_reason', 255)->nullable();
            $table->timestamp('test_executed_at');
            $table->timestamps();

            $table->index('user_id');
            $table->index('ai_trade_id');
            $table->index('status');
            $table->index('test_type');
            $table->index('test_executed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trading_cron_test_logs');
    }
};
