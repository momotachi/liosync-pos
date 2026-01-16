<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            // Common fields
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image')->nullable();

            // Type flags
            $table->boolean('is_purchase')->default(false)->comment('Can be purchased/restocked');
            $table->boolean('is_sales')->default(false)->comment('Can be sold');

            // Purchase-related fields (when is_purchase = true)
            $table->string('unit')->nullable()->comment('Unit of measurement: kg, pcs, liter, etc.');
            $table->decimal('unit_price', 15, 4)->nullable()->comment('Purchase price per unit');
            $table->decimal('current_stock', 10, 3)->default(0)->comment('Current stock quantity');
            $table->decimal('min_stock_level', 10, 3)->default(0)->comment('Minimum stock before alert');

            // Sales-related fields (when is_sales = true)
            $table->decimal('hpp', 15, 4)->nullable()->comment('Harga Pokok Penjualan (COGS)');
            $table->decimal('selling_price', 15, 2)->nullable()->comment('Selling price');
            $table->boolean('is_active')->default(true)->comment('Active/Visible in POS');
            $table->string('sku')->nullable()->comment('Stock Keeping Unit');
            $table->string('barcode')->nullable()->unique()->comment('Product barcode');
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['is_purchase', 'is_sales']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
