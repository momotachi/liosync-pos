<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'branch_id',
        'customer_name',
        'customer_phone',
        'total_amount',
        'payment_method',
        'status',
        'order_type',
        'table_number',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
    ];

    /**
     * Scope to filter only active (non-cancelled) orders
     */
    public function scopeActive($query)
    {
        return $query->whereNull('cancelled_at');
    }

    /**
     * Scope to filter only cancelled orders
     */
    public function scopeCancelled($query)
    {
        return $query->whereNotNull('cancelled_at');
    }

    /**
     * Check if order is cancelled
     */
    public function isCancelled(): bool
    {
        return !is_null($this->cancelled_at);
    }

    /**
     * Cancel the order and revert stock changes
     */
    public function cancel(string $reason, int $cancelledBy): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($reason, $cancelledBy) {
            // Mark order as cancelled
            $this->update([
                'cancelled_at' => now(),
                'cancelled_by' => $cancelledBy,
                'cancel_reason' => $reason,
            ]);

            // Revert stock for sales items with recipes
            foreach ($this->items as $orderItem) {
                $item = $orderItem->item;

                // Only revert for sales items that have recipes (consume materials)
                if ($item && $item->is_sales) {
                    foreach ($item->itemRecipes as $recipe) {
                        $ingredient = $recipe->ingredient;

                        // Calculate quantity to revert
                        $quantityToRevert = $recipe->quantity_required * $orderItem->quantity;

                        // Decrease stock (revert the automatic increase that happens during order)
                        if ($ingredient && $ingredient->is_purchase) {
                            $ingredient->decrement('current_stock', $quantityToRevert);

                            // Create stock transaction for the revert
                            \App\Models\StockTransaction::create([
                                'item_id' => $ingredient->id,
                                'type' => 'out',
                                'quantity' => $quantityToRevert,
                                'description' => "Order #{$this->id} cancelled - reverted stock",
                                'reference_id' => $this->id,
                                'branch_id' => $this->branch_id,
                            ]);
                        }
                    }
                }
            }

            return true;
        });
    }

    /**
     * Get the user who cancelled the order
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
        return $this->hasMany(OrderItem::class);
    }
}
