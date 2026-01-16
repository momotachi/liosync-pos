<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Models\ItemRecipe;
use App\Models\Category;
use App\Models\StockTransaction;

class AdminItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $typeFilter = $request->get('type', 'all');
        $search = $request->get('search');

        // Load category and itemRecipes for HPP calculation
        $query = Item::with('category', 'itemRecipes.ingredient')->latest();

        // Apply type filter
        if ($typeFilter === 'purchase') {
            $query->purchaseItems();
        } elseif ($typeFilter === 'sales') {
            $query->salesItems();
        } elseif ($typeFilter === 'both') {
            $query->both();
        }

        // Apply search
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->paginate(12);

        // Statistics
        $totalItems = Item::count();
        $purchaseItems = Item::where('is_purchase', true)->where('is_sales', false)->count();
        $salesItems = Item::where('is_sales', true)->where('is_purchase', false)->count();
        $bothItems = Item::where('is_purchase', true)->where('is_sales', true)->count();
        $activeItems = Item::where('is_active', true)->where('is_sales', true)->count();
        $lowStock = Item::where('is_purchase', true)
            ->whereColumn('current_stock', '<=', 'min_stock_level')
            ->where('current_stock', '>', 0)
            ->count();
        $outOfStock = Item::where('is_purchase', true)->where('current_stock', '<=', 0)->count();

        return view('admin.items.index', compact(
            'items',
            'totalItems',
            'purchaseItems',
            'salesItems',
            'bothItems',
            'activeItems',
            'lowStock',
            'outOfStock',
            'typeFilter',
            'search'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $items = Item::where('is_purchase', true)->get(); // For recipe ingredients

        return view('admin.items.create', compact('categories', 'items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Common fields
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:2048',

            // Type flags (at least one must be true)
            'is_purchase' => 'boolean',
            'is_sales' => 'boolean',

            // Purchase fields (required if is_purchase = true)
            'unit' => 'required_if:is_purchase,true|nullable|string|max:50',
            'custom_unit' => 'nullable|string|max:50',
            'unit_price' => 'required_if:is_purchase,true|nullable|numeric|min:0',
            'current_stock' => 'required_if:is_purchase,true|nullable|numeric|min:0',
            'min_stock_level' => 'required_if:is_purchase,true|nullable|numeric|min:0',

            // Sales fields (required if is_sales = true)
            'selling_price' => 'required_if:is_sales,true|nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100|unique:items,barcode',
            'description' => 'nullable|string',

            // Recipes (BOM) - only for sales items
            'recipes' => 'array',
        ]);

        // Validate at least one type is selected (check the boolean values after validation)
        if (empty($validated['is_purchase']) && empty($validated['is_sales'])) {
            return back()->withErrors(['type' => 'Please select at least one type: Purchase or Sales.'])->withInput();
        }

        DB::transaction(function () use ($request, $validated) {
            // Use custom unit if selected
            if (isset($validated['unit']) && $validated['unit'] === 'custom' && !empty($validated['custom_unit'])) {
                $validated['unit'] = $validated['custom_unit'];
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('items', 'public');
            }

            // Note: Boolean values are already converted by the 'boolean' validation rule

            // Filter and validate recipes
            $validRecipes = [];
            if (!empty($validated['recipes']) && !empty($validated['is_sales'])) {
                foreach ($validated['recipes'] as $recipe) {
                    $itemId = isset($recipe['item_id']) ? trim($recipe['item_id']) : '';
                    $quantity = isset($recipe['quantity_required']) ? floatval($recipe['quantity_required']) : 0;

                    if (!empty($itemId) && $quantity > 0) {
                        $ingredient = Item::find($itemId);
                        if ($ingredient && $ingredient->is_purchase) {
                            $validRecipes[] = [
                                'item_id' => $itemId,
                                'quantity_required' => $quantity,
                                'ingredient' => $ingredient,
                            ];
                        }
                    }
                }
            }

            // Calculate HPP from valid recipes
            $hpp = 0;
            foreach ($validRecipes as $recipe) {
                $hpp += $recipe['quantity_required'] * $recipe['ingredient']->unit_price;
            }

            $validated['hpp'] = $hpp;

            $item = Item::create($validated);

            // Create recipes
            foreach ($validRecipes as $recipe) {
                $item->itemRecipes()->create([
                    'ingredient_item_id' => $recipe['item_id'],
                    'quantity_required' => $recipe['quantity_required'],
                ]);
            }
        });

        return redirect()->route('admin.items.index')->with('success', 'Item created successfully!');
    }

    /**
     * Store a newly created category.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:product,material',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'category' => $category,
            'message' => 'Category created successfully!'
        ]);
    }

    /**
     * Display a listing of categories.
     */
    public function categoriesIndex()
    {
        $categories = Category::orderBy('name')->get();

        $categoryCounts = [];
        foreach ($categories as $category) {
            $categoryCounts[$category->id] = \App\Models\Item::where('category_id', $category->id)->count();
        }

        return view('admin.categories.index', compact('categories', 'categoryCounts'));
    }

    /**
     * Update a category.
     */
    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'type' => 'required|in:product,material',
        ]);

        $category->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => 'Category updated successfully!'
            ]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    /**
     * Delete a category.
     */
    public function destroyCategory($id)
    {
        $category = Category::findOrFail($id);

        // Check if category is being used
        $itemCount = \App\Models\Item::where('category_id', $id)->count();
        if ($itemCount > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete category. It is being used by {$itemCount} item(s)."
                ], 422);
            }
            return redirect()->back()->with('error', "Cannot delete category. It is being used by {$itemCount} item(s).");
        }

        $category->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!'
            ]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = Item::with('itemRecipes.ingredient')->findOrFail($id);
        $categories = Category::all();
        $items = Item::where('is_purchase', true)->where('id', '!=', $id)->get(); // For recipe ingredients

        return view('admin.items.edit', compact('item', 'categories', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            // Common fields
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:2048',

            // Type flags
            'is_purchase' => 'boolean',
            'is_sales' => 'boolean',

            // Purchase fields
            'unit' => 'required_if:is_purchase,true|nullable|string|max:50',
            'custom_unit' => 'nullable|string|max:50',
            'unit_price' => 'required_if:is_purchase,true|nullable|numeric|min:0',
            'current_stock' => 'required_if:is_purchase,true|nullable|numeric|min:0',
            'min_stock_level' => 'required_if:is_purchase,true|nullable|numeric|min:0',

            // Sales fields
            'selling_price' => 'required_if:is_sales,true|nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100|unique:items,barcode,' . $id,
            'description' => 'nullable|string',

            // Recipes
            'recipes' => 'array',
        ]);

        // Validate at least one type is selected (check the boolean values after validation)
        if (empty($validated['is_purchase']) && empty($validated['is_sales'])) {
            return back()->withErrors(['type' => 'Please select at least one type: Purchase or Sales.'])->withInput();
        }

        try {
            DB::transaction(function () use ($request, $validated, $item) {
                // Use custom unit if selected
                if (isset($validated['unit']) && $validated['unit'] === 'custom' && !empty($validated['custom_unit'])) {
                    $validated['unit'] = $validated['custom_unit'];
                }

                // Handle image upload
                if ($request->hasFile('image')) {
                    if ($item->image && Storage::disk('public')->exists($item->image)) {
                        Storage::disk('public')->delete($item->image);
                    }
                    $validated['image'] = $request->file('image')->store('items', 'public');
                }

                // Note: Boolean values are already converted by the 'boolean' validation rule
                // No need to use $request->has() since the form now always sends these values

                // Filter and validate recipes
                $validRecipes = [];
                if (!empty($validated['recipes']) && !empty($validated['is_sales'])) {
                    foreach ($validated['recipes'] as $recipe) {
                        $itemId = isset($recipe['item_id']) ? trim($recipe['item_id']) : '';
                        $quantity = isset($recipe['quantity_required']) ? floatval($recipe['quantity_required']) : 0;

                        if (!empty($itemId) && $quantity > 0) {
                            $ingredient = Item::find($itemId);
                            if ($ingredient && $ingredient->is_purchase) {
                                $validRecipes[] = [
                                    'item_id' => $itemId,
                                    'quantity_required' => $quantity,
                                    'ingredient' => $ingredient,
                                ];
                            }
                        }
                    }
                }

                // Calculate HPP from valid recipes
                $hpp = 0;
                foreach ($validRecipes as $recipe) {
                    $hpp += $recipe['quantity_required'] * $recipe['ingredient']->unit_price;
                }

                $validated['hpp'] = $hpp;

                $item->update($validated);

                // Delete old recipes and create new ones
                $item->itemRecipes()->delete();

                foreach ($validRecipes as $recipe) {
                    $item->itemRecipes()->create([
                        'ingredient_item_id' => $recipe['item_id'],
                        'quantity_required' => $recipe['quantity_required'],
                    ]);
                }
            });

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item berhasil diperbarui!',
                    'item' => $item->load('itemRecipes'),
                    'hpp' => $item->hpp
                ]);
            }

            return redirect()->route('admin.items.index')->with('success', 'Item updated successfully!');
        } catch (\Exception $e) {
            // Return JSON error response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui item: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()->with('error', 'Failed to update item: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        // Check if item has orders
        if ($item->orderItems()->exists()) {
            return redirect()->route('admin.items.index')
                ->with('error', 'Cannot delete item with order history.');
        }

        // Check if item is used in recipes
        if ($item->usedInItems()->exists()) {
            return redirect()->route('admin.items.index')
                ->with('error', 'Cannot delete item that is used in other item recipes.');
        }

        // Delete image if exists
        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        // Delete recipes first
        $item->itemRecipes()->delete();

        // Soft delete item
        $item->delete();

        return redirect()->route('admin.items.index')->with('success', 'Item deleted successfully!');
    }

    /**
     * Restock an item.
     */
    public function restock(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        if (!$item->is_purchase) {
            return redirect()->route('admin.items.index')
                ->with('error', 'Cannot restock a non-purchase item.');
        }

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->increaseStock(
            $validated['quantity'],
            $validated['notes'] ?? "Restocked: {$validated['quantity']} {$item->unit}"
        );

        return redirect()->route('admin.items.index')->with('success', "Added {$validated['quantity']} {$item->unit} of {$item->name}");
    }

    /**
     * Adjust stock for an item.
     */
    public function adjustStock(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        if (!$item->is_purchase) {
            return redirect()->route('admin.items.index')
                ->with('error', 'Cannot adjust stock for a non-purchase item.');
        }

        $validated = $request->validate([
            'new_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->adjustStock(
            $validated['new_quantity'],
            $validated['notes'] ?? "Stock adjusted"
        );

        return redirect()->route('admin.items.index')->with('success', "Stock for {$item->name} adjusted to {$validated['new_quantity']} {$item->unit}");
    }
}
