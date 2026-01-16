<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The item_id column already exists from previous partial migrations
        // Just need to make it not nullable and add foreign key constraint

        // First, delete any order_items with NULL item_id
        DB::statement('DELETE FROM order_items WHERE item_id IS NULL');

        // Make item_id not nullable and add foreign key
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable(false)->change();
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->foreignId('item_id')->nullable()->change();
        });
    }
};
