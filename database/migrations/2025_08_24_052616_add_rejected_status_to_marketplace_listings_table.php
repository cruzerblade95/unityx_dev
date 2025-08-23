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
        Schema::table('marketplace_listings', function (Blueprint $table) {
            // First drop the existing status column
            $table->dropColumn('status');
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            // Add the status column back with 'rejected' included
            $table->enum('status', ['active', 'pending_verification', 'sold', 'cancelled', 'expired', 'rejected'])->default('active')->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            // Drop the status column
            $table->dropColumn('status');
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            // Restore the previous enum without 'rejected'
            $table->enum('status', ['active', 'pending_verification', 'sold', 'cancelled', 'expired'])->default('active')->after('total_price');
        });
    }
};
