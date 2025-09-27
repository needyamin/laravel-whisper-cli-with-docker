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
        Schema::create('english_paragraphs', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('difficulty_level')->default('medium'); // easy, medium, hard
            $table->integer('word_count');
            $table->text('keywords')->nullable(); // JSON array of important words
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('english_paragraphs');
    }
};
