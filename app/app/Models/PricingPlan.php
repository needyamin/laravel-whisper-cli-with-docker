<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'test_limit',
        'validity_days',
        'is_popular',
        'is_active',
        'features',
        'stripe_price_id',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get subscriptions for this pricing plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Scope for active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for popular plans.
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Check if plan is free.
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Get price with discount applied.
     */
    public function getPriceWithDiscount(float $discountPercentage = 0): float
    {
        if ($discountPercentage <= 0) {
            return $this->price;
        }
        
        return $this->price * (1 - ($discountPercentage / 100));
    }
}