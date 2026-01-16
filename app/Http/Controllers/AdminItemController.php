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

        $typeFilter = $request->get('type', 'all');
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

        return redirect()->route('admin.items.index')->with('success', 'Item created successfully!');
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
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        $item = Item::where('branch_id', $branchId)->findOrFail($id);

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
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        $item = Item::where('branch_id', $branchId)->findOrFail($id);

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
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        $item = Item::where('branch_id', $branchId)->findOrFail($id);

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

    /**
     * Export items to Excel format including BOM.
     */
    public function export(Request $request)
    {
        $this->authorizeItemAccess();

        $branchId = $this->getEffectiveBranchId();

        $typeFilter = $request->get('type', 'all');

        $query = Item::with('category')->where('branch_id', $branchId);

        // Apply type filter
        if ($typeFilter === 'purchase') {
            $query->purchaseItems();
        } elseif ($typeFilter === 'sales') {
            $query->salesItems();
        } elseif ($typeFilter === 'both') {
            $query->both();
        }

        $items = $query->get();

        // Get items with recipes (BOM)
        $itemIds = $items->pluck('id')->toArray();
        $recipes = \App\Models\ItemRecipe::with(['parentItem', 'ingredient'])
            ->whereIn('parent_item_id', $itemIds)
            ->get();

        $filename = 'items_' . date('Y-m-d_His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $callback = function() use ($items, $recipes) {
            echo "\xEF\xBB\xBF";
            echo "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">\n";
            echo "<head>\n";
            echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
            echo "<style>\n";
            echo "table { border-collapse: collapse; margin-bottom: 20px; }\n";
            echo "td, th { border: 1px solid #ddd; padding: 8px; }\n";
            echo "th { background-color: #4CAF50; color: white; font-weight: bold; }\n";
            echo ".sheet-title { font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; color: #333; }\n";
            echo "</style>\n";
            echo "</head>\n";
            echo "<body>\n";

            // ITEMS TABLE
            echo "<div class='sheet-title'>DAFTAR ITEM</div>\n";
            echo "<table>\n";

            // Header Row
            echo "<tr>\n";
            echo "<th>Nama Item</th>\n";
            echo "<th>Kategori</th>\n";
            echo "<th>SKU</th>\n";
            echo "<th>Barcode</th>\n";
            echo "<th>Tipe</th>\n";
            echo "<th>Harga Beli</th>\n";
            echo "<th>Satuan</th>\n";
            echo "<th>Stok</th>\n";
            echo "<th>Min Stok</th>\n";
            echo "<th>Harga Jual</th>\n";
            echo "<th>HPP</th>\n";
            echo "<th>Status</th>\n";
            echo "<th>Deskripsi</th>\n";
            echo "</tr>\n";

            // Data Rows
            foreach ($items as $item) {
                $type = '';
                if ($item->is_purchase && $item->is_sales) {
                    $type = 'Both';
                } elseif ($item->is_purchase) {
                    $type = 'Purchase';
                } elseif ($item->is_sales) {
                    $type = 'Sales';
                }

                echo "<tr>\n";
                echo "<td>" . htmlspecialchars($item->name) . "</td>\n";
                echo "<td>" . htmlspecialchars($item->category->name ?? '') . "</td>\n";
                echo "<td>" . htmlspecialchars($item->sku ?? '') . "</td>\n";
                echo "<td>" . htmlspecialchars($item->barcode ?? '') . "</td>\n";
                echo "<td>" . htmlspecialchars($type) . "</td>\n";
                echo "<td>" . ($item->is_purchase ? number_format($item->unit_price, 0, ',', '.') : '-') . "</td>\n";
                echo "<td>" . ($item->is_purchase ? htmlspecialchars($item->unit) : '-') . "</td>\n";
                echo "<td>" . ($item->is_purchase ? number_format($item->current_stock, 2, ',', '.') : '-') . "</td>\n";
                echo "<td>" . ($item->is_purchase ? number_format($item->min_stock_level, 2, ',', '.') : '-') . "</td>\n";
                echo "<td>" . ($item->is_sales ? number_format($item->selling_price, 0, ',', '.') : '-') . "</td>\n";
                echo "<td>" . number_format($item->hpp, 0, ',', '.') . "</td>\n";
                echo "<td>" . ($item->is_active ? 'Aktif' : 'Non-Aktif') . "</td>\n";
                echo "<td>" . htmlspecialchars($item->description ?? '') . "</td>\n";
                echo "</tr>\n";
            }

            echo "</table>\n";

            // BOM/RECIPE TABLE
            if ($recipes->count() > 0) {
                echo "<div class='sheet-title'>BILL OF MATERIALS (RESEP)</div>\n";
                echo "<table>\n";

                // BOM Header Row
                echo "<tr>\n";
                echo "<th>Nama Produk</th>\n";
                echo "<th>SKU Produk</th>\n";
                echo "<th>Bahan Baku</th>\n";
                echo "<th>SKU Bahan</th>\n";
                echo "<th>Jumlah</th>\n";
                echo "<th>Satuan</th>\n";
                echo "</tr>\n";

                // BOM Data Rows
                foreach ($recipes as $recipe) {
                    echo "<tr>\n";
                    echo "<td>" . htmlspecialchars($recipe->parentItem->name ?? '') . "</td>\n";
                    echo "<td>" . htmlspecialchars($recipe->parentItem->sku ?? '') . "</td>\n";
                    echo "<td>" . htmlspecialchars($recipe->ingredient->name ?? '') . "</td>\n";
                    echo "<td>" . htmlspecialchars($recipe->ingredient->sku ?? '') . "</td>\n";
                    echo "<td>" . number_format($recipe->quantity_required, 3, ',', '.') . "</td>\n";
                    echo "<td>" . htmlspecialchars($recipe->ingredient->unit ?? '') . "</td>\n";
                    echo "</tr>\n";
                }

                echo "</table>\n";
            }

            echo "</body>\n";
            echo "</html>\n";
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download import template.
     */
    public function downloadTemplate()
    {
        $this->authorizeItemAccess();

        $filename = 'items_import_template.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, "\xEF\xBB\xBF");

            // CSV Header with instructions
            fputcsv($file, [
                'Nama Item *',
                'Kategori',
                'SKU',
                'Barcode',
                'Tipe * (Purchase/Sales/Both)',
                'Harga Beli',
                'Satuan',
                'Stok Awal',
                'Min Stok',
                'Harga Jual',
                'Aktif (Yes/No)',
                'Deskripsi'
            ]);

            // Add sample data rows
            fputcsv($file, [
                'Tepung Terigu',
                'Bahan Baku',
                '',
                '',
                'Purchase',
                '15000',
                'kg',
                '100',
                '20',
                '',
                '',
                'Tepung terigu untuk roti'
            ]);

            fputcsv($file, [
                'Roti Bakar Coklat',
                'Produk',
                'RB001',
                '89910001',
                'Sales',
                '',
                '',
                '',
                '',
                '25000',
                'Yes',
                'Roti bakar dengan topping coklat'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import items from CSV/Excel (including BOM).
     */
    public function import(Request $request)
    {
        $this->authorizeItemAccess();

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // Max 10MB
        ]);

        $branchId = $this->getEffectiveBranchId();
        $companyId = session('company_id') ?? auth()->user()->company_id;

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        // Determine file type and parse accordingly
        $extension = $file->getClientOriginalExtension();

        $itemsData = [];
        $bomData = [];

        if ($extension === 'csv' || $extension === 'txt') {
            // Parse CSV file
            if (($handle = fopen($filePath, 'r')) !== false) {
                // Skip BOM if present
                $bom = "\xEF\xBB\xBF";
                $firstBytes = fread($handle, 3);
                if ($firstBytes !== $bom) {
                    rewind($handle);
                }

                $header = fgetcsv($handle); // Get header row

                while (($data = fgetcsv($handle)) !== false) {
                    if (empty(array_filter($data))) continue; // Skip empty rows
                    $combined = array_combine($header, $data);
                    if ($combined !== false) {
                        $itemsData[] = $combined;
                    }
                }

                fclose($handle);
            }
        } elseif ($extension === 'xls' || $extension === 'xlsx') {
            // Parse XLS file (HTML table format from our export)
            $html = file_get_contents($filePath);

            // Convert to UTF-8 if needed
            if (!mb_check_encoding($html, 'UTF-8')) {
                $html = mb_convert_encoding($html, 'UTF-8', 'ISO-8859-1');
            }

            // Use DOMDocument to parse HTML tables
            // Disable XXE vulnerability protection
            libxml_disable_entity_loader(true);
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            $tables = $dom->getElementsByTagName('table');

            foreach ($tables as $tableIndex => $table) {
                $rows = $table->getElementsByTagName('tr');
                $headers = [];
                $tableData = [];

                // Get headers from first row
                if ($rows->length > 0) {
                    $firstRow = $rows->item(0);
                    $thElements = $firstRow->getElementsByTagName('th');
                    if ($thElements->length > 0) {
                        foreach ($thElements as $th) {
                            $headers[] = trim($th->textContent);
                        }
                    } else {
                        // Try td elements if no th
                        $tdElements = $firstRow->getElementsByTagName('td');
                        foreach ($tdElements as $td) {
                            $headers[] = trim($td->textContent);
                        }
                    }
                }

                // Get data rows
                for ($i = 1; $i < $rows->length; $i++) {
                    $row = $rows->item($i);
                    $tdElements = $row->getElementsByTagName('td');

                    $rowData = [];
                    foreach ($tdElements as $td) {
                        $rowData[] = trim($td->textContent);
                    }

                    if (!empty(array_filter($rowData))) {
                        $combined = array_combine($headers, $rowData);
                        if ($combined !== false) {
                            $tableData[] = $combined;
                        }
                    }
                }

                // First table is Items, second is BOM
                if ($tableIndex === 0) {
                    $itemsData = $tableData;
                } elseif ($tableIndex === 1) {
                    $bomData = $tableData;
                }
            }
        } else {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan CSV atau XLS.');
        }

        $imported = 0;
        $recipesImported = 0;
        $failed = 0;
        $errors = [];

        // Map to store created items by name/SKU for BOM reference
        $itemMap = [];

        DB::transaction(function() use ($itemsData, $bomData, $branchId, $companyId, &$imported, &$recipesImported, &$failed, &$errors, &$itemMap) {
            // Import Items first
            foreach ($itemsData as $index => $row) {
                try {
                    $rowNumber = $index + 2; // +2 because header is row 1 and array is 0-indexed

                    // Clean and validate data
                    $name = trim($row['Nama Item'] ?? $row['nama_item'] ?? $row['name'] ?? '');
                    $categoryName = trim($row['Kategori'] ?? $row['kategori'] ?? $row['category'] ?? '');
                    $sku = trim($row['SKU'] ?? $row['sku'] ?? '');
                    $barcode = trim($row['Barcode'] ?? $row['barcode'] ?? '');
                    $type = trim($row['Tipe'] ?? $row['tipe'] ?? $row['type'] ?? '');
                    $unitPrice = !empty($row['Harga Beli'] ?? $row['harga_beli'] ?? $row['unit_price'] ?? '') ? floatval(str_replace(['.', ','], ['', '.'], $row['Harga Beli'] ?? $row['harga_beli'] ?? $row['unit_price'])) : 0;
                    $unit = trim($row['Satuan'] ?? $row['satuan'] ?? $row['unit'] ?? 'pcs');
                    $currentStock = !empty($row['Stok'] ?? $row['Stok Awal'] ?? $row['stok'] ?? $row['stok_awal'] ?? $row['current_stock'] ?? '') ? floatval(str_replace(['.', ','], ['', '.'], $row['Stok'] ?? $row['Stok Awal'] ?? $row['stok'] ?? $row['stok_awal'] ?? $row['current_stock'])) : 0;
                    $minStock = !empty($row['Min Stok'] ?? $row['min_stok'] ?? '') ? floatval(str_replace(['.', ','], ['', '.'], $row['Min Stok'] ?? $row['min_stok'])) : 0;
                    $sellingPrice = !empty($row['Harga Jual'] ?? $row['harga_jual'] ?? $row['selling_price'] ?? '') ? floatval(str_replace(['.', ','], ['', '.'], $row['Harga Jual'] ?? $row['harga_jual'] ?? $row['selling_price'])) : 0;
                    $hpp = !empty($row['HPP'] ?? $row['hpp'] ?? '') ? floatval(str_replace(['.', ','], ['', '.'], $row['HPP'] ?? $row['hpp'])) : 0;
                    $isActive = strtolower(trim($row['Status'] ?? $row['status'] ?? $row['Aktif'] ?? $row['aktif'] ?? $row['is_active'] ?? 'yes')) === 'aktif' || strtolower(trim($row['Status'] ?? $row['status'] ?? $row['Aktif'] ?? $row['aktif'] ?? 'yes')) === 'yes' || strtolower(trim($row['Status'] ?? $row['status'] ?? $row['Aktif'] ?? $row['aktif'] ?? 'yes')) === '1' || !empty($row['Status'] ?? $row['Aktif'] ?? $row['aktif']);
                    $description = trim($row['Deskripsi'] ?? $row['deskripsi'] ?? $row['description'] ?? '');

                    // Validate required fields
                    if (empty($name)) {
                        $errors[] = "Row $rowNumber: Nama Item wajib diisi";
                        $failed++;
                        continue;
                    }

                    if (empty($type) || !in_array(strtolower($type), ['purchase', 'sales', 'both'])) {
                        $errors[] = "Row $rowNumber: Tipe harus salah satu dari: Purchase, Sales, Both";
                        $failed++;
                        continue;
                    }

                    // Determine item type flags
                    $typeLower = strtolower($type);
                    $isPurchase = $typeLower === 'purchase' || $typeLower === 'both';
                    $isSales = $typeLower === 'sales' || $typeLower === 'both';

                    // Find or create category (scoped to company and branch)
                    $categoryId = null;
                    if (!empty($categoryName)) {
                        $category = Category::firstOrCreate(
                            [
                                'name' => $categoryName,
                                'company_id' => $companyId,
                                'branch_id' => $branchId
                            ],
                            [
                                'type' => $isSales ? 'product' : 'material',
                                'is_active' => true
                            ]
                        );
                        $categoryId = $category->id;
                    }

                    // Check for duplicate barcode
                    if (!empty($barcode)) {
                        $existing = Item::where('barcode', $barcode)
                            ->where('branch_id', $branchId)
                            ->first();
                        if ($existing) {
                            $safeBarcode = e($barcode);
                            $safeExistingName = e($existing->name);
                            $errors[] = "Row $rowNumber: Barcode '$safeBarcode' sudah digunakan oleh item lain (ID: {$existing->id})";
                            $failed++;
                            continue;
                        }
                    }

                    // Check for duplicate name
                    $existingByName = Item::where('name', $name)
                        ->where('branch_id', $branchId)
                        ->first();
                    if ($existingByName) {
                        $safeName = e($name);
                        $errors[] = "Row $rowNumber: Nama item '$safeName' sudah digunakan oleh item lain (ID: {$existingByName->id})";
                        $failed++;
                        continue;
                    }

                    // Check for duplicate SKU
                    if (!empty($sku)) {
                        $existingBySku = Item::where('sku', $sku)
                            ->where('branch_id', $branchId)
                            ->first();
                        if ($existingBySku) {
                            $safeSku = e($sku);
                            $safeExistingName = e($existingBySku->name);
                            $errors[] = "Row $rowNumber: SKU '$safeSku' sudah digunakan oleh item lain (ID: {$existingBySku->id})";
                            $failed++;
                            continue;
                        }
                    }

                    // Prepare item data
                    $itemData = [
                        'name' => $name,
                        'category_id' => $categoryId,
                        'sku' => $sku ?: null,
                        'barcode' => $barcode ?: null,
                        'is_purchase' => $isPurchase,
                        'is_sales' => $isSales,
                        'unit_price' => $isPurchase ? $unitPrice : 0,
                        'unit' => $isPurchase ? $unit : null,
                        'current_stock' => $isPurchase ? $currentStock : 0,
                        'min_stock_level' => $isPurchase ? $minStock : 0,
                        'selling_price' => $isSales ? $sellingPrice : 0,
                        'hpp' => $hpp,
                        'is_active' => $isSales ? $isActive : true,
                        'description' => $description,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ];

                    // Create item
                    $item = Item::create($itemData);
                    $imported++;

                    // Store in map for BOM reference (by name and SKU)
                    $itemMap[$name] = $item;
                    if (!empty($sku)) {
                        $itemMap["SKU:$sku"] = $item;
                    }

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    $failed++;
                }
            }

            // Import BOM/Recipes if available
            if (!empty($bomData)) {
                foreach ($bomData as $index => $row) {
                    try {
                        $rowNumber = $index + 2;

                        $productName = trim($row['Nama Produk'] ?? $row['nama_produk'] ?? '');
                        $productSku = trim($row['SKU Produk'] ?? $row['sku_produk'] ?? '');
                        $ingredientName = trim($row['Bahan Baku'] ?? $row['bahan_baku'] ?? '');
                        $ingredientSku = trim($row['SKU Bahan'] ?? $row['sku_bahan'] ?? '');
                        $quantity = !empty($row['Jumlah'] ?? $row['jumlah'] ?? '') ? floatval(str_replace(['.', ','], ['', '.'], $row['Jumlah'] ?? $row['jumlah'])) : 0;

                        // Find parent item (product)
                        $parentItem = null;
                        if (!empty($productName) && isset($itemMap[$productName])) {
                            $parentItem = $itemMap[$productName];
                        } elseif (!empty($productSku) && isset($itemMap["SKU:$productSku"])) {
                            $parentItem = $itemMap["SKU:$productSku"];
                        }

                        // Find ingredient item
                        $ingredientItem = null;
                        if (!empty($ingredientName) && isset($itemMap[$ingredientName])) {
                            $ingredientItem = $itemMap[$ingredientName];
                        } elseif (!empty($ingredientSku) && isset($itemMap["SKU:$ingredientSku"])) {
                            $ingredientItem = $itemMap["SKU:$ingredientSku"];
                        }

                        // Validate
                        if (!$parentItem) {
                            $safeProductName = e($productName);
                            $safeProductSku = e($productSku);
                            $errors[] = "BOM Row $rowNumber: Produk '$safeProductName' (SKU: $safeProductSku) tidak ditemukan";
                            $failed++;
                            continue;
                        }

                        if (!$ingredientItem) {
                            $safeIngredientName = e($ingredientName);
                            $safeIngredientSku = e($ingredientSku);
                            $errors[] = "BOM Row $rowNumber: Bahan '$safeIngredientName' (SKU: $safeIngredientSku) tidak ditemukan";
                            $failed++;
                            continue;
                        }

                        if ($quantity <= 0) {
                            $errors[] = "BOM Row $rowNumber: Jumlah harus lebih dari 0";
                            $failed++;
                            continue;
                        }

                        // Create recipe
                        \App\Models\ItemRecipe::create([
                            'parent_item_id' => $parentItem->id,
                            'ingredient_item_id' => $ingredientItem->id,
                            'quantity_required' => $quantity,
                        ]);

                        $recipesImported++;

                    } catch (\Exception $e) {
                        $errors[] = "BOM Row " . ($index + 2) . ": " . $e->getMessage();
                        $failed++;
                    }
                }
            }
        });

        $message = "Import completed: $imported items imported successfully";
        if ($recipesImported > 0) {
            $message .= ", $recipesImported recipes imported";
        }
        if ($failed > 0) {
            $message .= ", $failed rows failed";
        }

        if (!empty($errors)) {
            // Store errors in session for display
            session()->flash('import_errors', $errors);
        }

        return redirect()->back()->with('success', $message);
    }
}
