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
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('branch_manager_name')->nullable()->after('branch_selected');
            $table->string('branch_manager_phone')->nullable()->after('branch_manager_name');
            $table->timestamp('branch_assigned_at')->nullable()->after('branch_manager_phone');
            $table->unsignedBigInteger('assigned_by_admin_id')->nullable()->after('branch_assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn([
                'branch_manager_name',
                'branch_manager_phone',
                'branch_assigned_at',
                'assigned_by_admin_id'
            ]);
        });
    }
};
