<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'category_id', 'image',
        'is_purchase', 'is_sales',
        'unit', 'unit_price', 'current_stock', 'min_stock_level',
        'hpp', 'selling_price', 'is_active', 'sku', 'barcode', 'description',
        'company_id',
    ];

    protected $casts = [
        'is_purchase' => 'boolean',
        'is_sales' => 'boolean',
        'is_active' => 'boolean',
        'unit_price' => 'decimal:4',
        'current_stock' => 'decimal:3',
        'min_stock_level' => 'decimal:3',
        'hpp' => 'decimal:4',
        'selling_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function itemRecipes()
    {
        return $this->hasMany(ItemRecipe::class, 'parent_item_id');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Item::class, 'item_recipes', 'parent_item_id', 'ingredient_item_id')
            ->withPivot('quantity_required')->withTimestamps();
    }

    public function usedInItems()
    {
        return $this->belongsToMany(Item::class, 'item_recipes', 'ingredient_item_id', 'parent_item_id')
            ->withPivot('quantity_required')->withTimestamps();
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePurchaseItems($query)
    {
        return $query->where('is_purchase', true)->where('is_sales', false);
    }

    public function scopeSalesItems($query)
    {
        return $query->where('is_sales', true)->where('is_purchase', false);
    }

    public function scopeBoth($query)
    {
        return $query->where('is_purchase', true)->where('is_sales', true);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isRawMaterial(): bool
    {
        return $this->is_purchase && !$this->is_sales;
    }

    public function isProduct(): bool
    {
        return $this->is_sales && !$this->is_purchase;
    }

    public function isBoth(): bool
    {
        return $this->is_purchase && $this->is_sales;
    }

    public function getStockStatusAttribute(): string
    {
        if (!$this->is_purchase) return 'N/A';
        if ($this->current_stock <= 0) return 'Out of Stock';
        if ($this->current_stock <= $this->min_stock_level) return 'Low Stock';
        return 'In Stock';
    }

    public function calculateHpp(): float
    {
        // Calculate HPP from BOM for sales items
        if (!$this->is_sales) return $this->hpp ?? 0;

        $total = 0;
        // Load recipes if not already loaded
        if (!$this->relationLoaded('itemRecipes')) {
            $this->load('itemRecipes.ingredient');
        }

        foreach ($this->itemRecipes as $recipe) {
            if ($recipe->ingredient) {
                $total += $recipe->quantity_required * ($recipe->ingredient->unit_price ?? 0);
            }
        }
        return $total > 0 ? $total : ($this->hpp ?? 0);
    }

    public function getCalculatedHppAttribute(): float
    {
        return $this->calculateHpp();
    }

    public function getProfitAttribute(): float
    {
        if (!$this->selling_price) return 0;
        return $this->selling_price - $this->calculated_hpp;
    }

    public function getProfitMarginAttribute(): float
    {
        if (!$this->selling_price || $this->selling_price <= 0) return 0;
        return ($this->profit / $this->selling_price) * 100;
    }

    public function decreaseStock(float $quantity, string $description = null, int $referenceId = null): void
    {
        if (!$this->is_purchase) throw new \Exception('Cannot decrease stock for non-purchase items.');
        $this->decrement('current_stock', $quantity);
        $this->stockTransactions()->create(['type' => 'out', 'quantity' => $quantity, 'description' => $description, 'reference_id' => $referenceId]);
    }

    public function increaseStock(float $quantity, string $description = null, int $referenceId = null): void
    {
        if (!$this->is_purchase) throw new \Exception('Cannot increase stock for non-purchase items.');
        $this->increment('current_stock', $quantity);
        $this->stockTransactions()->create(['type' => 'in', 'quantity' => $quantity, 'description' => $description, 'reference_id' => $referenceId]);
    }

    public function adjustStock(float $newQuantity, string $description = null): void
    {
        if (!$this->is_purchase) throw new \Exception('Cannot adjust stock for non-purchase items.');
        $difference = $newQuantity - $this->current_stock;
        $this->current_stock = $newQuantity;
        $this->save();
        $this->stockTransactions()->create(['type' => 'adjustment', 'quantity' => $difference, 'description' => $description]);
    }
}
