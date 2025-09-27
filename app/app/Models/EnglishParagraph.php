<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnglishParagraph extends Model
{
    protected $fillable = [
        'content',
        'difficulty_level',
        'word_count',
        'keywords',
        'is_active'
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
        'word_count' => 'integer'
    ];

    /**
     * Get the speaking tests for this paragraph.
     */
    public function speakingTests(): HasMany
    {
        return $this->hasMany(SpeakingTest::class, 'paragraph_id');
    }

    /**
     * Scope for active paragraphs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for difficulty level.
     */
    public function scopeDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }
}
