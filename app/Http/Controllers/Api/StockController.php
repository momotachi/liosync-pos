<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockTransaction;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request) 
    {
         $query = StockTransaction::with(['item', 'user'])
            ->whereHas('item', function($q) {
                $q->where('company_id', request()->user()->company_id);
            });

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        $transactions = $query->latest()->paginate($request->input('per_page', 20));

        // Create a simple inline resource or separate class if needed
        return response()->json($transactions);
    }

    public function restock(Request $request) 
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string'
        ]);

        $item = Item::where('id', $request->item_id)
            ->where('company_id', $request->user()->company_id)
            ->firstOrFail();

        DB::transaction(function() use ($item, $request) {
            $item->increment('current_stock', $request->quantity);

            StockTransaction::create([
                'item_id' => $item->id,
                'user_id' => $request->user()->id,
                'type' => 'restock',
                'quantity' => $request->quantity,
                'stock_before' => $item->current_stock - $request->quantity,
                'stock_after' => $item->current_stock,
                'notes' => $request->notes
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Restock successful']);
    }

    public function adjust(Request $request) 
    {
         $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric', // Can be negative
            'notes' => 'required|string',
            'reason' => 'required|string'
        ]);

        $item = Item::where('id', $request->item_id)
            ->where('company_id', $request->user()->company_id)
            ->firstOrFail();

        DB::transaction(function() use ($item, $request) {
            $before = $item->current_stock;
            $item->increment('current_stock', $request->quantity); // Adds negative if adjustment is negative
            $after = $item->current_stock;

            StockTransaction::create([
                'item_id' => $item->id,
                'user_id' => $request->user()->id,
                'type' => 'adjustment',
                'quantity' => $request->quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'notes' => $request->notes . " (Reason: {$request->reason})"
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Stock adjusted successfully']);
    }

    public function lowStock(Request $request) 
    {
        $items = Item::where('company_id', $request->user()->company_id)
            ->whereColumn('current_stock', '<=', 'min_stock_level')
            ->get();
            
        return response()->json(['success' => true, 'data' => $items]);
    }
}
