<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\RawMaterial;
use App\Models\ProductRecipe;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BomStockDeductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_is_deducted_when_checkout_completes()
    {
        $this->withoutExceptionHandling();
        // 1. Setup Data
        $category = Category::create(['name' => 'Juices', 'slug' => 'juices']);

        $mango = RawMaterial::create([
            'name' => 'Fresh Mango',
            'unit' => 'kg',
            'current_stock' => 10.0, // Initial Stock
            'min_stock_level' => 2.0
        ]);

        $sugar = RawMaterial::create([
            'name' => 'Sugar',
            'unit' => 'kg',
            'current_stock' => 5.0, // Initial Stock
            'min_stock_level' => 1.0
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Mango Delight',
            'slug' => 'mango-delight',
            'price' => 5.00,
            'is_active' => true
        ]);

        // Define Recipe: 0.5kg Mango + 0.1kg Sugar per unit
        ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $mango->id,
            'quantity_required' => 0.5
        ]);

        ProductRecipe::create([
            'product_id' => $product->id,
            'raw_material_id' => $sugar->id,
            'quantity_required' => 0.1
        ]);

        // 2. Simulate Checkout Request (Sell 2 Units)
        $response = $this->postJson(route('pos.checkout'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2 // Sell 2
                ]
            ],
            'total_amount' => 10.00,
            'payment_method' => 'cash'
        ]);

        // 3. Assertions
        $response->dump();
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify Order Created
        $this->assertDatabaseHas('orders', ['total_amount' => 10.00]);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2]);

        // Verify Stock Deduction
        // Mango: 10.0 - (0.5 * 2) = 9.0
        $this->assertDatabaseHas('raw_materials', [
            'id' => $mango->id,
            'current_stock' => 9.0
        ]);

        // Sugar: 5.0 - (0.1 * 2) = 4.8
        $this->assertDatabaseHas('raw_materials', [
            'id' => $sugar->id,
            'current_stock' => 4.8
        ]);

        // Verify Stock Transactions Created
        $this->assertDatabaseHas('stock_transactions', [
            'raw_material_id' => $mango->id,
            'type' => 'out',
            'quantity' => 1.0 // 0.5 * 2
        ]);

        $this->assertDatabaseHas('stock_transactions', [
            'raw_material_id' => $sugar->id,
            'type' => 'out',
            'quantity' => 0.2 // 0.1 * 2
        ]);
    }
}
