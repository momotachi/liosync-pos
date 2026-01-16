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
        Schema::create('item_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_item_id')->comment('The finished product item being made');
            $table->foreignId('ingredient_item_id')->comment('The raw material ingredient used');
            $table->decimal('quantity_required', 10, 3)->comment('Amount of ingredient per parent item unit');
            $table->timestamps();

            // Foreign keys with cascade delete
            $table->foreign('parent_item_id')->references('id')->on('items')->cascadeOnDelete();
            $table->foreign('ingredient_item_id')->references('id')->on('items')->cascadeOnDelete();

            // Prevent duplicate recipes
            $table->unique(['parent_item_id', 'ingredient_item_id'], 'unique_recipe_combination');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_recipes');
    }
};
