<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_trade_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_trade_id')->constrained('ai_trades')->onDelete('cascade');
            $table->decimal('amount', 18, 8)->default(0.00000000);
            $table->decimal('pnl', 18, 8)->default(0.00000000);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_trade_histories');
    }
};
