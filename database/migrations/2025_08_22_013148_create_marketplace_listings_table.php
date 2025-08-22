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
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id', 20)->unique(); // Unique identifier for listing
            $table->unsignedBigInteger('seller_id');
            $table->decimal('amount', 15, 2); // USDT amount for sale
            $table->decimal('price_per_usdt', 10, 4)->default(1.0000); // Price per USDT (could be different from 1:1)
            $table->decimal('total_price', 15, 2); // Total price (amount * price_per_usdt)
            $table->enum('status', ['active', 'sold', 'cancelled'])->default('active');
            $table->text('description')->nullable();
            $table->timestamp('expires_at')->nullable(); // Optional expiration
            $table->unsignedBigInteger('buyer_id')->nullable(); // Who bought it
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
            
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status', 'created_at']);
            $table->index('seller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_listings');
    }
};
