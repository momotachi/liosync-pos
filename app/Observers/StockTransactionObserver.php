<?php

namespace App\Observers;

use App\Models\StockTransaction;

class StockTransactionObserver
{
    /**
     * Handle the StockTransaction "created" event.
     */
    public function created(StockTransaction $stockTransaction): void
    {
        $item = $stockTransaction->item;

        if (!$item) {
            return;
        }

        if ($stockTransaction->type === 'in') {
            $item->increment('current_stock', $stockTransaction->quantity);
        } elseif ($stockTransaction->type === 'out') {
            $item->decrement('current_stock', $stockTransaction->quantity);
        }
    }

    /**
     * Handle the StockTransaction "updated" event.
     */
    public function updated(StockTransaction $stockTransaction): void
    {
        //
    }

    /**
     * Handle the StockTransaction "deleted" event.
     */
    public function deleted(StockTransaction $stockTransaction): void
    {
        //
    }

    /**
     * Handle the StockTransaction "restored" event.
     */
    public function restored(StockTransaction $stockTransaction): void
    {
        //
    }

    /**
     * Handle the StockTransaction "force deleted" event.
     */
    public function forceDeleted(StockTransaction $stockTransaction): void
    {
        //
    }
}
