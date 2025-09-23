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
        Schema::create('wallet_profit_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('admins')->onDelete('set null');
        });

        // Insert default settings
        DB::table('wallet_profit_settings')->insert([
            [
                'setting_key' => 'profit_threshold_percentage',
                'setting_value' => '85',
                'description' => 'Percentage threshold for profit wallet vs deposit wallet',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'auto_check_enabled',
                'setting_value' => 'false',
                'description' => 'Enable automatic checking for profit threshold violations',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_profit_settings');
    }
};
