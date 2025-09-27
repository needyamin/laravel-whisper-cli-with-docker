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
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('usd');
            $table->integer('test_limit')->default(1); // Number of tests included
            $table->integer('validity_days')->default(30); // Validity in days
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable(); // JSON array of features
            $table->string('stripe_price_id')->nullable(); // Stripe Price ID
            $table->timestamps();
            
            $table->index(['is_active', 'is_popular']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};