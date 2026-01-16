<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = [
        'item_id',
        'type',
        'quantity',
        'description',
        'reference_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    /**
     * Get the item that owns the stock transaction.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
