<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address')->nullable()->after('upline_id');
            $table->string('state')->nullable()->after('address');
            $table->string('zipCode')->nullable()->after('state');
            $table->string('country')->nullable()->after('zipCode');
            $table->string('phone')->nullable()->after('country');
            $table->string('organization')->nullable()->after('phone');
            $table->string('language')->nullable()->after('organization');
            $table->string('timezone')->nullable()->after('language');
            $table->string('currency')->nullable()->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'state',
                'zipCode',
                'country',
                'phone',
                'organization',
                'language',
                'timezone',
                'currency',
            ]);
        });
    }
};
