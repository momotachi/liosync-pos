<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'category_id' => $this->category_id,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'image' => $this->image, // Ensure full URL logic is handled in Model accessor if needed
            'is_sales' => (bool) $this->is_sales,
            'is_purchase' => (bool) $this->is_purchase,
            'unit_price' => $this->unit_price,
            'hpp' => $this->hpp,
            'selling_price' => $this->selling_price,
            'current_stock' => $this->current_stock,
            'min_stock_level' => $this->min_stock_level,
            'unit' => $this->unit,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
