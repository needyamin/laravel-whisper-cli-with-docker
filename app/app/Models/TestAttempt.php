<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAttempt extends Model
{
    protected $fillable = [
        'test_id',
        'original_text',
        'spoken_text',
        'accuracy_score',
        'fluency_score',
        'pronunciation_score',
        'overall_score',
        'audio_file_path',
        'feedback',
        'word_scores',
        'speaking_duration'
    ];

    protected $casts = [
        'word_scores' => 'array',
        'accuracy_score' => 'integer',
        'fluency_score' => 'integer',
        'pronunciation_score' => 'integer',
        'overall_score' => 'integer',
        'speaking_duration' => 'integer'
    ];

    /**
     * Get the test that owns this attempt.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(SpeakingTest::class, 'test_id');
    }

    /**
     * Get the grade based on overall score.
     */
    public function getGradeAttribute(): string
    {
        $score = $this->overall_score;
        
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }

    /**
     * Check if attempt passed.
     */
    public function isPassed(): bool
    {
        return $this->overall_score >= $this->test->passing_score;
    }
}
