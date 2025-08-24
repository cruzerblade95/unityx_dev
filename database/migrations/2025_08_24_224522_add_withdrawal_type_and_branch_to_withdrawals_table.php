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
            $table->enum('withdrawal_type', ['crypto', 'etc_black', 'gold'])->default('crypto')->after('status');
            $table->string('branch_name')->nullable()->after('withdrawal_type');
            $table->boolean('branch_selected')->default(false)->after('branch_name');
            $table->text('payment_details')->nullable()->after('branch_selected'); // JSON field for payment details
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn([
                'withdrawal_type',
                'branch_name', 
                'branch_selected',
                'payment_details'
            ]);
        });
    }
};
