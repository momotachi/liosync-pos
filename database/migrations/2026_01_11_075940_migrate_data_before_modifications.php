<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate raw_materials to items (is_purchase = true, is_sales = false)
        DB::statement('
            INSERT INTO items (
                name, category_id, image, is_purchase, is_sales,
                unit, unit_price, current_stock, min_stock_level,
                created_at, updated_at
            )
            SELECT
                name,
                NULL as category_id,
                image,
                1 as is_purchase,
                0 as is_sales,
                unit,
                unit_price,
                current_stock,
                min_stock_level,
                created_at,
                updated_at
            FROM raw_materials
        ');

        // Migrate products to items (is_purchase = false, is_sales = true)
        DB::statement('
            INSERT INTO items (
                name, category_id, image, is_purchase, is_sales,
                hpp, selling_price, is_active, sku, barcode, description,
                created_at, updated_at
            )
            SELECT
                name,
                category_id,
                image,
                0 as is_purchase,
                1 as is_sales,
                hpp,
                selling_price,
                is_active,
                sku,
                barcode,
                NULL as description,
                created_at,
                updated_at
            FROM products
        ');

        // Migrate product_recipes to item_recipes
        DB::statement('
            INSERT INTO item_recipes (parent_item_id, ingredient_item_id, quantity_required, created_at, updated_at)
            SELECT
                (SELECT id FROM items WHERE items.name = products.name AND items.is_sales = 1 LIMIT 1) as parent_item_id,
                (SELECT id FROM items WHERE items.name = raw_materials.name AND items.is_purchase = 1 LIMIT 1) as ingredient_item_id,
                pr.quantity_required,
                pr.created_at,
                pr.updated_at
            FROM product_recipes pr
            INNER JOIN products ON pr.product_id = products.id
            INNER JOIN raw_materials ON pr.raw_material_id = raw_materials.id
            WHERE EXISTS (
                SELECT 1 FROM items WHERE items.name = products.name AND items.is_sales = 1
            ) AND EXISTS (
                SELECT 1 FROM items WHERE items.name = raw_materials.name AND items.is_purchase = 1
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all migrated items
        DB::table('items')->delete();
        DB::table('item_recipes')->delete();
    }
};
