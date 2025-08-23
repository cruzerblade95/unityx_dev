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
            $table->text('admin_rejection_reason')->nullable()->after('seller_approved_at');
            $table->timestamp('admin_rejected_at')->nullable()->after('admin_rejection_reason');
            $table->unsignedBigInteger('admin_rejected_by')->nullable()->after('admin_rejected_at');

            $table->foreign('admin_rejected_by')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropForeign(['admin_rejected_by']);
            $table->dropColumn(['admin_rejection_reason', 'admin_rejected_at', 'admin_rejected_by']);
        });
    }
};
