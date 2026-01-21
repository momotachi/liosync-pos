<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ItemResource;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Item::query();
        
        // Scope by company if user has one (or is not superadmin)
        if ($user->company_id) {
            $query->where('company_id', $user->company_id);
        } else if (!$user->isSuperAdmin()) {
             // If not superadmin and no company, technically shouldn't see anything or handle error
             // For safety, return empty
             $query->where('id', -1); 
        }
            
        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->boolean('is_sales')) {
            $query->where('is_sales', true);
        }
        if ($request->boolean('is_purchase')) {
            $query->where('is_purchase', true);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $items = $query->paginate($request->input('per_page', 20));

        return ItemResource::collection($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Permission check (Branch Admin+)
        if (!$request->user()->can('manage items')) { // Adjust permission name as needed
             // return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'is_sales' => 'boolean',
            'is_purchase' => 'boolean',
            'unit_price' => 'numeric|min:0',
            'selling_price' => 'numeric|min:0',
            'current_stock' => 'numeric',
            'min_stock_level' => 'numeric',
            'unit' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $item = Item::create(array_merge($validated, [
            'company_id' => $request->user()->company_id,
            // 'branch_id' => $request->user()->branch_id, // If items are per branch
        ]));

        return new ItemResource($item);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Item::where('id', $id)->where('company_id', request()->user()->company_id)->firstOrFail();
        return new ItemResource($item);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         $item = Item::where('id', $id)->where('company_id', request()->user()->company_id)->firstOrFail();

         $validated = $request->validate([
            'name' => 'string|max:255',
            'category_id' => 'exists:categories,id',
            'unit_price' => 'numeric',
            'selling_price' => 'numeric',
            'current_stock' => 'numeric',
        ]);

        $item->update($validated);

        return new ItemResource($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Item::where('id', $id)->where('company_id', request()->user()->company_id)->firstOrFail();
        $item->delete(); // Soft delete if enabled

        return response()->json(['success' => true, 'message' => 'Item deleted']);
    }

    public function categories(Request $request) {
        $user = $request->user();
        $query = Category::query();
        
        if ($user->company_id) {
            $query->where('company_id', $user->company_id);
        }

        $categories = $query->get();
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
