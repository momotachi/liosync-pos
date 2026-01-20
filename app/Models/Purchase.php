<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'supplier_name',
        'supplier_phone',
        'total_amount',
        'payment_method',
        'status',
        'notes',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Scope to filter only active (non-cancelled) purchases
     */
    public function scopeActive($query)
    {
        return $query->whereNull('cancelled_at');
    }

    /**
     * Scope to filter only cancelled purchases
     */
    public function scopeCancelled($query)
    {
        return $query->whereNotNull('cancelled_at');
    }

    /**
     * Check if purchase is cancelled
     */
    public function isCancelled(): bool
    {
        return !is_null($this->cancelled_at);
    }

    /**
     * Cancel the purchase and revert stock changes
     */
    public function cancel(string $reason, int $cancelledBy): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($reason, $cancelledBy) {
            // Mark purchase as cancelled
            $this->update([
                'cancelled_at' => now(),
                'cancelled_by' => $cancelledBy,
                'cancel_reason' => $reason,
            ]);

            // Revert stock - purchases add stock, so we need to decrease it
            foreach ($this->items as $purchaseItem) {
                $item = $purchaseItem->item;

                if ($item) {
                    // Decrease stock to revert the purchase
                    $item->decrement('current_stock', $purchaseItem->quantity);

                    // Create stock transaction for the revert
                    \App\Models\StockTransaction::create([
                        'item_id' => $item->id,
                        'type' => 'out',
                        'quantity' => $purchaseItem->quantity,
                        'description' => "Purchase #{$this->id} cancelled - reverted stock",
                        'reference_id' => $this->id,
                        'branch_id' => $this->branch_id,
                    ]);
                }
            }

            return true;
        });
    }

    /**
     * Get the user who cancelled the purchase
     */
    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
