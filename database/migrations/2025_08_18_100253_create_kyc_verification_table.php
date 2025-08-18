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
        Schema::create('kyc_verification', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('kyc_doc1')->nullable();
            $table->string('kyc_doc2')->nullable();
            $table->string('kyc_doc3')->nullable();
            $table->tinyInteger('kyc_status')->default(0)->comment('0: pending, 1: approved, 2: rejected');
            $table->text('kyc_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_verification');
    }
};
