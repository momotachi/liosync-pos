<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\ItemRecipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::query()
            ->where('company_id', $user->company_id)
            ->with(['items.item', 'createdBy']);
            
        // Optional: Filter by branch for Branch Admin/Cashier
        // if ($user->branch_id) {
        //     $query->where('branch_id', $user->branch_id);
        // }

        if ($request->has('status')) {
             $query->where('status', $request->status);
        }
        
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereDate('created_at', '>=', $request->date_from)
                  ->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search')) {
             $query->where('order_number', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%");
        }

        $orders = $query->latest()->paginate($request->input('per_page', 20));

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string',
            'order_type' => 'required|in:dine_in,take_away,delivery',
            'table_number' => 'nullable|string',
            'payment_method' => 'required|string',
            'total_amount' => 'required|numeric',
            'paid_amount' => 'nullable|numeric',
            'change_amount' => 'nullable|numeric',
            'status' => 'nullable|in:pending,completed,cancelled',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
            'items.*.subtotal' => 'required|numeric',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $user = $request->user();
            
            // 1. Create Order
            $order = Order::create([
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id ?? null, // Cashier usually has branch_id
                'order_number' => 'ORD-' . time() . '-' . rand(100, 999), // Simple generator
                'customer_name' => $validated['customer_name'] ?? 'Walk-in',
                'order_type' => $validated['order_type'],
                'table_number' => $validated['table_number'] ?? null,
                'payment_method' => $validated['payment_method'],
                'total_amount' => $validated['total_amount'],
                'paid_amount' => $validated['paid_amount'] ?? 0,
                'change_amount' => $validated['change_amount'] ?? 0,
                'status' => $validated['status'] ?? 'completed',
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->id,
            ]);

            // 2. Create Order Items & Deduct Stock
            foreach ($validated['items'] as $itemData) {
                // Create Line Item
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'subtotal' => $itemData['subtotal'],
                ]);

                // Deduct Stock Logic (Simplified)
                // In a real app, you'd check both the Item itself and its Recipes (BOM)
                $this->deductStock($itemData['item_id'], $itemData['quantity']);
            }

            return new OrderResource($order->load('items'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
         $order = Order::with(['items.item', 'createdBy'])
            ->where('id', $id)
            ->where('company_id', request()->user()->company_id)
            ->firstOrFail();

        return new OrderResource($order);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, string $id) 
    {
        $request->validate([
            'status' => 'required|in:completed,cancelled'
        ]);

        $order = Order::where('id', $id)->where('company_id', request()->user()->company_id)->firstOrFail();
        
        // Handle cancellation stock return logic here if needed
        if ($request->status === 'cancelled' && $order->status !== 'cancelled') {
             // $this->returnStock($order);
        }

        $order->update(['status' => $request->status]);

        return new OrderResource($order);
    }

    public function cancel(string $id) {
         $order = Order::where('id', $id)->where('company_id', request()->user()->company_id)->firstOrFail();
         $order->update(['status' => 'cancelled']);
         // Implement stock return logic
         return response()->json(['success' => true, 'message' => 'Order cancelled']);
    }

    public function receipt(string $id) {
        // Return receipt data (HTML or Text)
        $order = Order::with(['items.item', 'createdBy'])->find($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'order' => new OrderResource($order),
                // 'receipt_html' => view('receipts.thermal', compact('order'))->render(),
                'print_url' => route('pos.receipt.print', $id) // Use existing web route if compatible
            ]
        ]);
    }

    private function deductStock($itemId, $qty) {
        $item = Item::find($itemId);
        
        // If item tracks stock directly
        if ($item->current_stock !== null) {
            $item->decrement('current_stock', $qty);
        }

        // Check recipes (BOM)
        $recipes = ItemRecipe::where('product_id', $itemId)->get();
        foreach ($recipes as $recipe) {
            $rawMaterial = Item::find($recipe->raw_material_id);
            if ($rawMaterial) {
                // Calculate required raw material qty
                $needed = $recipe->quantity * $qty;
                $rawMaterial->decrement('current_stock', $needed);
            }
        }
    }
}
