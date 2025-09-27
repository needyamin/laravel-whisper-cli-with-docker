<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'stripe_customer_id',
        'role',
        'free_tests_used',
        'has_payment_waiver',
        'custom_discount_percentage',
        'waiver_expires_at',
        'waiver_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'waiver_expires_at' => 'datetime',
            'custom_discount_percentage' => 'decimal:2',
            'has_payment_waiver' => 'boolean',
        ];
    }

    /**
     * Get the speaking tests for the user.
     */
    public function speakingTests()
    {
        return $this->hasMany(\App\Models\SpeakingTest::class);
    }

    /**
     * Get the certificates for the user.
     */
    public function certificates()
    {
        return $this->hasMany(\App\Models\Certificate::class);
    }

    /**
     * Get the user's latest test attempt.
     */
    public function latestTest()
    {
        return $this->speakingTests()->latest()->first();
    }

    /**
     * Check if user has any certificates.
     */
    public function hasCertificates(): bool
    {
        return $this->certificates()->where('is_valid', true)->exists();
    }

    /**
     * Get the payments for the user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the subscriptions for the user.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the user's active subscription.
     */
    public function activeSubscription()
    {
        return $this->subscriptions()->active()->latest()->first();
    }

    /**
     * Check if user is admin or superadmin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'superadmin']);
    }

    /**
     * Check if user is superadmin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user has payment waiver.
     */
    public function hasActiveWaiver(): bool
    {
        return $this->has_payment_waiver && 
               (!$this->waiver_expires_at || $this->waiver_expires_at->isFuture());
    }

    /**
     * Check if user can take free test.
     */
    public function canTakeFreeTest(): bool
    {
        return $this->free_tests_used < 1;
    }

    /**
     * Check if user can take test (free or paid).
     */
    public function canTakeTest(): bool
    {
        // Check if user has active waiver
        if ($this->hasActiveWaiver()) {
            return true;
        }

        // Check if user can take free test
        if ($this->canTakeFreeTest()) {
            return true;
        }

        // Check if user has active subscription with remaining tests
        $activeSubscription = $this->activeSubscription();
        if ($activeSubscription && $activeSubscription->hasTestsRemaining()) {
            return true;
        }

        return false;
    }

    /**
     * Get user's effective discount percentage.
     */
    public function getEffectiveDiscountPercentage(): float
    {
        if ($this->hasActiveWaiver()) {
            return 100; // Full waiver
        }

        return $this->custom_discount_percentage ?? 0;
    }
}
