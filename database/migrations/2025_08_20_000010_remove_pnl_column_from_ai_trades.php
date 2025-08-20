<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_trades', function (Blueprint $table) {
            $table->dropColumn('pnl');
        });
    }

    public function down(): void
    {
        Schema::table('ai_trades', function (Blueprint $table) {
            $table->decimal('pnl', 18, 8)->default(0.00000000);
        });
    }
};
