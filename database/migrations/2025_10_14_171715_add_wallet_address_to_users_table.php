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
        Schema::table('users', function (Blueprint $table) {
            $table->string('wallet_address', 42)->nullable()->after('email'); // BSC wallet address
            $table->timestamp('wallet_connected_at')->nullable()->after('wallet_address'); // When wallet was connected
            $table->index('wallet_address'); // For quick lookups
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['wallet_address']);
            $table->dropColumn(['wallet_address', 'wallet_connected_at']);
        });
    }
};
