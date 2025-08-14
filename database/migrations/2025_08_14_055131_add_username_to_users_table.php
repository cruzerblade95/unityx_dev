<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('id');
        });

        // Add unique index after setting initial values
        Schema::table('users', function (Blueprint $table) {
            // Update existing users to have a username based on their email
            DB::table('users')->whereNull('username')->update([
                'username' => DB::raw('SUBSTRING_INDEX(email, "@", 1)')
            ]);
            
            // Now add the unique constraint
            $table->unique('username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
