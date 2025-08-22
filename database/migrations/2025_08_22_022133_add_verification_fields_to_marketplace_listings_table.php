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
            // Change status enum to include new statuses
            $table->dropColumn('status');
        });
        
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->enum('status', ['active', 'pending_verification', 'sold', 'cancelled', 'expired'])->default('active')->after('total_price');
            $table->timestamp('verification_started_at')->nullable()->after('sold_at');
            $table->timestamp('verification_expires_at')->nullable()->after('verification_started_at');
            $table->text('seller_approval_reason')->nullable()->after('verification_expires_at');
            $table->boolean('seller_approved')->default(false)->after('seller_approval_reason');
            $table->timestamp('seller_approved_at')->nullable()->after('seller_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropColumn([
                'verification_started_at',
                'verification_expires_at', 
                'seller_approval_reason',
                'seller_approved',
                'seller_approved_at'
            ]);
            $table->dropColumn('status');
        });
        
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->enum('status', ['active', 'sold', 'cancelled'])->default('active');
        });
    }
};
