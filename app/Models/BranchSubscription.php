<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchSubscription extends Model
{
    protected $fillable = [
        'branch_id',
        'subscription_plan_id',
        'start_date',
        'end_date',
        'status',
        'payment_proof',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
    ];

    /**
     * Get the branch that owns the subscription.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the subscription plan for the subscription.
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Get the payments for the subscription.
     */
    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class, 'branch_subscription_id');
    }

    /**
     * Scope to get active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Scope to get expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
                     ->orWhere('end_date', '<', now());
    }

    /**
     * Scope to get pending subscriptions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get subscriptions for a specific branch.
     */
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Check if subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               $this->start_date->isPast() &&
               $this->end_date->isFuture();
    }

    /**
     * Get remaining days until expiration.
     * Counts from tomorrow (excluding today) until end date.
     */
    public function getRemainingDaysAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }

        // Get the difference in days, not counting today
        $daysRemaining = now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false);

        return max(0, $daysRemaining);
    }

    /**
     * Extend subscription by months.
     */
    public function extend(int $months): void
    {
        $currentEndDate = $this->end_date ?? now();
        $this->end_date = $currentEndDate->addMonths($months);
        $this->status = 'active';
        $this->save();
    }
}
