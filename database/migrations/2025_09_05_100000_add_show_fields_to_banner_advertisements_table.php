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
        Schema::table('banner_advertisements', function (Blueprint $table) {
            $table->boolean('show_title')->default(true)->after('text_color');
            $table->boolean('show_description')->default(true)->after('show_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banner_advertisements', function (Blueprint $table) {
            $table->dropColumn(['show_title', 'show_description']);
        });
    }
};
