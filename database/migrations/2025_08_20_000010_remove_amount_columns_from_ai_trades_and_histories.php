<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove amount columns from ai_trades table
        Schema::table('ai_trades', function (Blueprint $table) {
            if (Schema::hasColumn('ai_trades', 'amount_invest')) {
                $table->dropColumn('amount_invest');
            }
            if (Schema::hasColumn('ai_trades', 'current_amount')) {
                $table->dropColumn('current_amount');
            }
            if (Schema::hasColumn('ai_trades', 'ending_amount')) {
                $table->dropColumn('ending_amount');
            }
        });

        // Remove amount columns from histories table if it exists
        if (Schema::hasTable('histories')) {
            Schema::table('histories', function (Blueprint $table) {
                if (Schema::hasColumn('histories', 'amount')) {
                    $table->dropColumn('amount');
                }
            });
        }
    }

    public function down(): void
    {
        // Restore amount columns to ai_trades table
        Schema::table('ai_trades', function (Blueprint $table) {
            $table->decimal('amount_invest', 18, 8)->default(0.00000000);
            $table->decimal('current_amount', 18, 8)->default(0.00000000);
            $table->decimal('ending_amount', 18, 8)->default(0.00000000);
        });

        // Restore amount column to histories table if it exists
        if (Schema::hasTable('histories')) {
            Schema::table('histories', function (Blueprint $table) {
                $table->decimal('amount', 18, 8)->default(0.00000000);
            });
        }
    }
};
