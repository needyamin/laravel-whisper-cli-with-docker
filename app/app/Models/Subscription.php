<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pricing_plan_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'status',
        'amount',
        'currency',
        'test_limit',
        'tests_used',
        'starts_at',
        'ends_at',
        'canceled_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pricing plan for this subscription.
     */
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->ends_at && 
               $this->ends_at->isFuture() &&
               $this->tests_used < $this->test_limit;
    }

    /**
     * Check if subscription has tests remaining.
     */
    public function hasTestsRemaining(): bool
    {
        return $this->tests_used < $this->test_limit;
    }

    /**
     * Get remaining tests count.
     */
    public function getRemainingTestsAttribute(): int
    {
        return max(0, $this->test_limit - $this->tests_used);
    }

    /**
     * Increment tests used.
     */
    public function incrementTestsUsed(): void
    {
        $this->increment('tests_used');
    }

    /**
     * Scope for active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('ends_at', '>', now())
                    ->whereRaw('tests_used < test_limit');
    }

    /**
     * Scope for expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', '!=', 'active')
              ->orWhere('ends_at', '<=', now())
              ->orWhereRaw('tests_used >= test_limit');
        });
    }
}