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
               $this->currentSubscription->end_date->isFuture();
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
        return "{$this->company->name} - {$this->name}";
    }
}
