<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SpeakingTest extends Model
{
    protected $fillable = [
        'user_id',
        'paragraph_id',
        'time_limit',
        'passing_score',
        'status',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'time_limit' => 'integer',
        'passing_score' => 'integer'
    ];

    /**
     * Get the user that owns the test.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the paragraph for this test.
     */
    public function paragraph(): BelongsTo
    {
        return $this->belongsTo(EnglishParagraph::class, 'paragraph_id');
    }

    /**
     * Get the test attempts for this test.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'test_id');
    }

    /**
     * Get the certificate for this test.
     */
    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class, 'test_id');
    }

    /**
     * Check if test is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if test is passed.
     */
    public function isPassed(): bool
    {
        return $this->isCompleted() && $this->attempts()->latest()->first()?->overall_score >= $this->passing_score;
    }
}
