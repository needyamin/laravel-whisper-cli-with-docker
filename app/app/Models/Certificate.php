<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'user_id',
        'test_id',
        'certificate_number',
        'score_achieved',
        'grade',
        'certificate_file_path',
        'issued_at',
        'expires_at',
        'is_valid'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_valid' => 'boolean',
        'score_achieved' => 'integer'
    ];

    /**
     * Get the user that owns the certificate.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the test that this certificate is for.
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(SpeakingTest::class, 'test_id');
    }

    /**
     * Check if certificate is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if certificate is valid.
     */
    public function isValid(): bool
    {
        return $this->is_valid && !$this->isExpired();
    }

    /**
     * Generate unique certificate number.
     */
    public static function generateCertificateNumber(): string
    {
        do {
            $number = 'CERT-' . strtoupper(uniqid());
        } while (self::where('certificate_number', $number)->exists());
        
        return $number;
    }
}
