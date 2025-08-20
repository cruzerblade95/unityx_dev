<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('deposit', 18, 8)->default(0.00000000)->change();
            $table->decimal('profit', 18, 8)->default(0.00000000)->change();
            $table->decimal('bonus', 18, 8)->default(0.00000000)->change();
            $table->decimal('cash', 18, 8)->default(0.00000000)->change();
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->decimal('deposit', 8, 2)->default(0.00)->change();
            $table->decimal('profit', 8, 2)->default(0.00)->change();
            $table->decimal('bonus', 8, 2)->default(0.00)->change();
            $table->decimal('cash', 8, 2)->default(0.00)->change();
        });
    }
};
