<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_invest', 18, 8)->default(0.00000000);
            $table->decimal('pnl', 18, 8)->default(0.00000000);
            $table->tinyInteger('trade_status')->default(1)->comment('0=cancel, 1=running, 2=trade end');
            $table->decimal('current_amount', 18, 8)->default(0.00000000);
            $table->decimal('ending_amount', 18, 8)->default(0.00000000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_trades');
    }
};
