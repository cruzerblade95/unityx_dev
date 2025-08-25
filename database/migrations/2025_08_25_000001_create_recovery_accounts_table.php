<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('recovery_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->decimal('deposit', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->string('referral_username')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['email', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('recovery_accounts');
    }
};
