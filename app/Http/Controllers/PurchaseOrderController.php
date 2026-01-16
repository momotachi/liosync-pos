<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $categories = \App\Models\Category::all();
        $items = Item::where('is_purchase', true)->where('is_active', true)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'category_id' => $item->category_id,
                'category_name' => $item->category->name ?? 'Uncategorized',
                'image' => $item->image,
                'purchase_price' => (float) ($item->hpp ?? 0),
                'unit' => $item->unit ?? 'unit',
                'current_stock' => (float) ($item->current_stock ?? 0),
            ];
        })->values();

        // Get settings for Purchase Order
        $settings = [
            'currency' => Setting::get('currency', 'IDR'),
            'currency_symbol' => Setting::get('currency_symbol', 'Rp'),
            'tax_rate' => Setting::get('tax_rate', 0),
            'tax_name' => Setting::get('tax_name', 'Tax'),
        ];

        // Encode items as JSON to avoid JavaScript issues
        $itemsJson = json_encode($items, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        return view('purchase.index', compact('categories', 'settings'))->with('itemsJson', $itemsJson)->with('items', $items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,transfer,credit',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Create Purchase
            $purchase = Purchase::create([
                'user_id' => Auth::id(),
                'supplier_name' => $request->supplier_name ?? 'General Supplier',
                'supplier_phone' => $request->supplier_phone,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $material = Item::find($item['item_id']);
                $subtotal = ($item['price'] ?? 0) * $item['quantity'];

                // Create Purchase Item
                $purchase->items()->create([
                    'item_id' => $material->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] ?? $material->hpp,
                    'subtotal' => $subtotal,
                    'note' => $item['note'] ?? null,
                ]);

                // Add Stock (IN transaction)
                StockTransaction::create([
                    'item_id' => $material->id,
                    'type' => 'in',
                    'quantity' => $item['quantity'],
                    'description' => "Purchased in PO #{$purchase->id}" .
                        ($request->supplier_name ? " from {$request->supplier_name}" : ''),
                    'reference_id' => $purchase->id,
                ]);

                // Update current stock
                $material->increment('current_stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'purchase_id' => $purchase->id,
                'message' => 'Purchase order created successfully!',
                'receipt_url' => route('purchase.receipt', $purchase->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Purchase failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and display receipt for a purchase.
     */
    public function receipt($id)
    {
        $purchase = Purchase::with(['items.item', 'user'])->findOrFail($id);

        // Get settings for receipt
        $settings = [
            'store_name' => Setting::get('store_name', 'JuicePOS Store'),
            'store_address' => Setting::get('store_address', ''),
            'store_phone' => Setting::get('store_phone', ''),
            'store_email' => Setting::get('store_email', ''),
            'currency_symbol' => Setting::get('currency_symbol', '$'),
            'receipt_header' => Setting::get('receipt_header', 'Thank you for your purchase!'),
            'receipt_footer' => Setting::get('receipt_footer', 'Please come again!'),
            'show_supplier_phone' => true,
            'show_cashier_name' => true,
            'tax_rate' => Setting::get('tax_rate', 0),
            'tax_name' => Setting::get('tax_name', 'Tax'),
            'tax_included' => Setting::get('tax_included', false),
        ];

        return view('purchase.receipt', compact('purchase', 'settings'));
    }
}
