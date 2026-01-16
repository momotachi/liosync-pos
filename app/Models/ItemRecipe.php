<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemRecipe extends Model
{
    protected $fillable = [
        'parent_item_id',
        'ingredient_item_id',
        'quantity_required',
    ];

    protected $casts = [
        'quantity_required' => 'decimal:3',
    ];

    /**
     * Get the parent item (the finished product).
     */
    public function parentItem()
    {
        return $this->belongsTo(Item::class, 'parent_item_id');
    }

    /**
     * Get the ingredient item (the raw material used).
     */
    public function ingredient()
    {
        return $this->belongsTo(Item::class, 'ingredient_item_id');
    }

    /**
     * Calculate the cost of this recipe ingredient.
     */
    public function getCostAttribute(): float
    {
        if (!$this->ingredient) {
            return 0;
        }
        return $this->quantity_required * $this->ingredient->unit_price;
    }
}
