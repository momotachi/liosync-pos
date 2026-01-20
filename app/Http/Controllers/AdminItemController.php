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
    private function authorizeItemAccess()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Cashier cannot access item management
        if (!$user || $user->isCashier()) {
            abort(403, 'Access denied. Cashier cannot access item management.');
        }
    }

    /**
     * Get the effective branch ID.
     */
    private function getEffectiveBranchId()
    {
        // Check for active branch context (Superadmin/Company Admin viewing branch)
        if (session('active_branch_id')) {
            return session('active_branch_id');
        }

        // Use authenticated user's branch
        $user = auth()->user();
        return $user ? $user->branch_id : null;
    }

    /**
     * Generate redirect with preserved filter
     */
    private function redirectWithFilter($message = null, $type = 'success')
    {
        $typeFilter = session('items_filter_type', 'all');
        $redirect = redirect()->route('admin.items.index', ['type' => $typeFilter]);

        if ($message) {
            $redirect = $redirect->with($type, $message);
        }

        return $redirect;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        // Validate branch context - user must have a branch to view items
        if (!$branchId) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company and branch to view items.');
            } elseif ($user->isCompanyAdmin()) {
                $companyId = session('company_id') ?? $user->company_id;
                if ($companyId) {
                    return redirect()->route('company.branches.index', $companyId)
                        ->with('info', 'Please select a branch to view items.');
                }
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company first.');
            } else {
                abort(403, 'No branch assigned. Please contact administrator.');
            }
        }

        // Get filter from request or session (allow override via URL parameter)
        $typeFilter = $request->get('type', session('items_filter_type', 'all'));

        // Store current filter in session for persistence after redirects
        session(['items_filter_type' => $typeFilter]);

        $search = $request->get('search');

        // Load category and itemRecipes for HPP calculation
        $query = Item::with('category', 'itemRecipes.ingredient')
            ->where('branch_id', $branchId)
            ->latest();

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

        // Statistics - filtered by branch
        $totalItems = Item::where('branch_id', $branchId)->count();
        $purchaseItems = Item::where('branch_id', $branchId)->where('is_purchase', true)->where('is_sales', false)->count();
        $salesItems = Item::where('branch_id', $branchId)->where('is_sales', true)->where('is_purchase', false)->count();
        $bothItems = Item::where('branch_id', $branchId)->where('is_purchase', true)->where('is_sales', true)->count();
        $activeItems = Item::where('branch_id', $branchId)->where('is_active', true)->where('is_sales', true)->count();
        $lowStock = Item::where('branch_id', $branchId)->where('is_purchase', true)
            ->whereColumn('current_stock', '<=', 'min_stock_level')
            ->where('current_stock', '>', 0)
            ->count();
        $outOfStock = Item::where('branch_id', $branchId)->where('is_purchase', true)->where('current_stock', '<=', 0)->count();

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
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        // Separate categories by type for filtering
        $materialCategories = Category::where('type', 'material')->get();
        $productCategories = Category::where('type', 'product')->get();
        $allCategories = Category::all();
        $items = Item::where('is_purchase', true)->where('branch_id', $branchId)->get(); // For recipe ingredients

        return view('admin.items.create', compact('materialCategories', 'productCategories', 'allCategories', 'items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeItemAccess();

        $validated = $request->validate([
            // Common fields
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:10240',

            // Type flags (at least one must be true)
            'is_purchase' => 'boolean',
            'is_sales' => 'boolean',

            // Purchase fields (required if is_purchase = true)
            'unit' => 'required_if:is_purchase,true|string|max:50',
            'custom_unit' => 'nullable|string|max:50',
            'unit_price' => 'required_if:is_purchase,true|numeric|min:0',
            'current_stock' => 'required_if:is_purchase,true|numeric|min:0',
            'min_stock_level' => 'required_if:is_purchase,true|numeric|min:0',

            // Sales fields (required if is_sales = true)
            'selling_price' => 'required_if:is_sales,true|numeric|min:0',
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
            $branchId = $this->getEffectiveBranchId();

            // Set company_id from session or user
            $companyId = session('company_id') ?? auth()->user()->company_id;

            // Use custom unit if selected
            if (isset($validated['unit']) && $validated['unit'] === 'custom' && !empty($validated['custom_unit'])) {
                $validated['unit'] = $validated['custom_unit'];
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('items', 'public');
            }

            // Note: Boolean values are already converted by the 'boolean' validation rule

            // Set company_id and branch_id
            $validated['company_id'] = $companyId;
            $validated['branch_id'] = $branchId;

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

        return $this->redirectWithFilter('Item created successfully!');
    }

    /**
     * Store a newly created category.
     */
    public function storeCategory(Request $request)
    {
        $this->authorizeItemAccess();

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
        $this->authorizeItemAccess();

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
        $this->authorizeItemAccess();

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
        $this->authorizeItemAccess();

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
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        $item = Item::with('itemRecipes.ingredient')
            ->where('branch_id', $branchId)
            ->findOrFail($id);
        // Separate categories by type for filtering
        $materialCategories = Category::where('type', 'material')->get();
        $productCategories = Category::where('type', 'product')->get();
        $allCategories = Category::all();
        $items = Item::where('is_purchase', true)->where('branch_id', $branchId)->where('id', '!=', $id)->get(); // For recipe ingredients

        return view('admin.items.edit', compact('item', 'materialCategories', 'productCategories', 'allCategories', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        $item = Item::where('branch_id', $branchId)->findOrFail($id);

        $validated = $request->validate([
            // Common fields
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|max:10240',

            // Type flags
            'is_purchase' => 'boolean',
            'is_sales' => 'boolean',

            // Purchase fields
            'unit' => 'required_if:is_purchase,true|string|max:50',
            'custom_unit' => 'nullable|string|max:50',
            'unit_price' => 'required_if:is_purchase,true|numeric|min:0',
            'current_stock' => 'required_if:is_purchase,true|numeric|min:0',
            'min_stock_level' => 'required_if:is_purchase,true|numeric|min:0',

            // Sales fields
            'selling_price' => 'required_if:is_sales,true|numeric|min:0',
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

            return $this->redirectWithFilter('Item updated successfully!');
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
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        $item = Item::where('branch_id', $branchId)->findOrFail($id);

        // Check if item has orders
        if ($item->orderItems()->exists()) {
            return $this->redirectWithFilter('Cannot delete item with order history.', 'error');
        }

        // Check if item is used in recipes
        if ($item->usedInItems()->exists()) {
            return $this->redirectWithFilter('Cannot delete item that is used in other item recipes.', 'error');
        }

        // Delete image if exists
        if ($item->image && Storage::disk('public')->exists($item->image)) {
            Storage::disk('public')->delete($item->image);
        }

        // Delete recipes first
        $item->itemRecipes()->delete();

        // Soft delete item
        $item->delete();

        return $this->redirectWithFilter('Item deleted successfully!');
    }

    /**
     * Restock an item.
     */
    public function restock(Request $request, $id)
    {
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        $item = Item::where('branch_id', $branchId)->findOrFail($id);

        if (!$item->is_purchase) {
            return $this->redirectWithFilter('Cannot restock a non-purchase item.', 'error');
        }

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->increaseStock(
            $validated['quantity'],
            $validated['notes'] ?? "Restocked: {$validated['quantity']} {$item->unit}"
        );

        return $this->redirectWithFilter("Added {$validated['quantity']} {$item->unit} of {$item->name}");
    }

    /**
     * Adjust stock for an item.
     */
    public function adjustStock(Request $request, $id)
    {
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        $item = Item::where('branch_id', $branchId)->findOrFail($id);

        if (!$item->is_purchase) {
            return $this->redirectWithFilter('Cannot adjust stock for a non-purchase item.', 'error');
        }

        $validated = $request->validate([
            'new_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->adjustStock(
            $validated['new_quantity'],
            $validated['notes'] ?? "Stock adjusted"
        );

        return $this->redirectWithFilter("Stock for {$item->name} adjusted to {$validated['new_quantity']} {$item->unit}");
    }

    /**
     * Export items to Excel format with BOM.
     */
    public function export(Request $request)
    {
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();
        $typeFilter = $request->get('type', 'all');

        $filename = 'items_export_' . date('Y-m-d_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ItemsWithBomExport($branchId, $typeFilter),
            $filename
        );
    }

    /**
     * Download import template (Excel format with BOM).
     */
    public function downloadTemplate()
    {
        $this->authorizeItemAccess();

        $filename = 'items_import_template.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ItemsWithBomTemplateExport(),
            $filename
        );
    }

    /**
     * Import items from Excel file with BOM.
     */
    public function import(Request $request)
    {
        $this->authorizeItemAccess();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB - Excel only (for multiple sheets)
        ]);

        $branchId = $this->getEffectiveBranchId();
        $companyId = session('company_id') ?? auth()->user()->company_id;

        $import = new \App\Imports\ItemsWithBomImport($branchId, $companyId);

        try {
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }

        $imported = $import->getImported();
        $updated = $import->getUpdated();
        $skipped = $import->getSkipped();
        $recipesImported = $import->getRecipesImported();
        $failed = $import->getFailed();
        $errors = $import->getErrors();

        $message = "Import completed:";
        $parts = [];
        if ($imported > 0) {
            $parts[] = "$imported items imported";
        }
        if ($updated > 0) {
            $parts[] = "$updated items updated";
        }
        if ($skipped > 0) {
            $parts[] = "$skipped items skipped (no changes)";
        }
        if ($recipesImported > 0) {
            $parts[] = "$recipesImported recipes imported";
        }
        if ($failed > 0) {
            $parts[] = "$failed rows failed";
        }

        $message .= " " . implode(', ', $parts);

        if (!empty($errors)) {
            // Store errors in session for display
            session()->flash('import_errors', $errors);
        }

        return redirect()->back()->with('success', $message);
    }
}
