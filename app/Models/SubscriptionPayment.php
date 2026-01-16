<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'branch_subscription_id',
        'amount',
        'payment_method',
        'payment_date',
        'proof_image',
        'status',
        'confirmed_by',
        'confirmed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Get the branch subscription for the payment.
     */
    public function branchSubscription()
    {
        return $this->belongsTo(BranchSubscription::class, 'branch_subscription_id');
    }

    /**
     * Get the user who confirmed the payment.
     */
    public function confirmedBy()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get confirmed payments.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Confirm the payment.
     */
    public function confirm(int $userId): void
    {
        $this->status = 'confirmed';
        $this->confirmed_by = $userId;
        $this->confirmed_at = now();
        $this->save();

        // Activate the subscription
        if ($this->branchSubscription) {
            $this->branchSubscription->status = 'active';
            $this->branchSubscription->payment_date = now();
            $this->branchSubscription->save();

            // Update branch current subscription
            if ($this->branchSubscription->branch) {
                $this->branchSubscription->branch->current_subscription_id = $this->branchSubscription->id;
                $this->branchSubscription->branch->save();
            }
        }
    }

    /**
     * Reject the payment.
     */
    public function reject(string $reason): void
    {
        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->save();
    }
}
