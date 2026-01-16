<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'max_branches',
        'max_users',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    /**
     * Get the branch subscriptions for the plan.
     */
    public function branchSubscriptions()
    {
        return $this->hasMany(BranchSubscription::class, 'subscription_plan_id');
    }

    /**
     * Scope to get only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get plan by slug.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get max branches attribute (returns null for unlimited).
     */
    public function getMaxBranchesAttribute($value)
    {
        return $value ?? null;
    }

    /**
     * Get features as array.
     */
    public function getFeaturesArrayAttribute(): array
    {
        return $this->features ?? [];
    }
}
