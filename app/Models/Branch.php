<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'phone',
        'email',
        'is_active',
        'latitude',
        'longitude',
        'current_subscription_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the subscription-based status for the branch.
     * Returns active if subscription is valid, inactive if expired.
     */
    public function getSubscriptionStatusAttribute(): bool
    {
        // If there's no subscription, use the original is_active field
        if (!$this->currentSubscription) {
            return $this->attributes['is_active'] ?? false;
        }

        // Check if subscription is active and not expired
        $subscription = $this->currentSubscription;

        // Subscription is active only if:
        // 1. Status is 'active'
        // 2. End date is today or in the future (>= today)
        if ($subscription->status === 'active' &&
            $subscription->end_date &&
            $subscription->end_date->gte(now()->startOfDay())) {
            return true;
        }

        // Subscription is expired or inactive
        return false;
    }

    /**
     * Get the subscription status text with date info.
     */
    public function getSubscriptionStatusTextAttribute(): string
    {
        if (!$this->currentSubscription) {
            return $this->is_active ? 'Active' : 'Inactive';
        }

        $subscription = $this->currentSubscription;

        // Check if subscription is expired (before today)
        if ($subscription->end_date && $subscription->end_date->lt(now()->startOfDay())) {
            return 'Expired';
        }

        // Check subscription status
        if ($subscription->status === 'active') {
            return 'Active';
        }

        return 'Inactive';
    }

    /**
     * Get the subscription status color class.
     */
    public function getSubscriptionStatusColorAttribute(): string
    {
        if (!$this->currentSubscription) {
            return $this->is_active ? 'emerald' : 'red';
        }

        $subscription = $this->currentSubscription;

        // Check if subscription is expired (before today)
        if ($subscription->end_date && $subscription->end_date->lt(now()->startOfDay())) {
            return 'red';
        }

        // Check subscription status
        if ($subscription->status === 'active') {
            return 'emerald';
        }

        return 'red';
    }

    /**
     * Get the company that owns the branch.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the users for the branch.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the orders for the branch.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the purchases for the branch.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the stock transactions for the branch.
     */
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    /**
     * Get the current subscription for the branch.
     */
    public function currentSubscription()
    {
        return $this->belongsTo(BranchSubscription::class, 'current_subscription_id');
    }

    /**
     * Get all subscriptions for the branch.
     */
    public function subscriptions()
    {
        return $this->hasMany(BranchSubscription::class);
    }

    /**
     * Scope to get only active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get branches by company.
     */
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get branches with active subscription.
     */
    public function scopeWithActiveSubscription($query)
    {
        return $query->whereHas('currentSubscription', function ($q) {
            $q->where('status', 'active')
              ->where('start_date', '<=', now())
              ->where('end_date', '>=', now());
        });
    }

    /**
     * Check if branch has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->currentSubscription &&
               $this->currentSubscription->status === 'active' &&
               $this->currentSubscription->end_date &&
               $this->currentSubscription->end_date->gte(now()->startOfDay());
    }

    /**
     * Check if branch can operate (has active subscription).
     */
    public function canOperate(): bool
    {
        return $this->hasActiveSubscription();
    }

    /**
     * Get the full branch name with company.
     */
    public function getFullNameAttribute(): string
    {
        return ($this->company?->name ?? 'Unknown Company') . " - {$this->name}";
    }
}
