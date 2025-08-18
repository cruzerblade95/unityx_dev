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
        Schema::table('telegram_verifications', function (Blueprint $table) {
            $table->string('telegram_username')->nullable()->after('token');
            $table->string('chat_id')->nullable()->after('telegram_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegram_verifications', function (Blueprint $table) {
            $table->dropColumn(['telegram_username', 'chat_id']);
        });
    }
};
