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
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained('speaking_tests')->onDelete('cascade');
            $table->text('original_text'); // The paragraph text
            $table->text('spoken_text'); // What user actually spoke
            $table->integer('accuracy_score'); // Percentage accuracy
            $table->integer('fluency_score'); // Fluency score
            $table->integer('pronunciation_score'); // Pronunciation score
            $table->integer('overall_score'); // Overall score
            $table->text('audio_file_path')->nullable(); // Path to recorded audio
            $table->text('feedback')->nullable(); // Detailed feedback
            $table->json('word_scores')->nullable(); // Individual word scores
            $table->integer('speaking_duration')->nullable(); // Duration in seconds
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_attempts');
    }
};
