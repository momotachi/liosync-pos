<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\Setting;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function index()
    {
        $categories = \App\Models\Category::whereHas('items', function ($query) {
            $query->where('is_sales', true)->where('is_active', true);
        })->get();
        $items = Item::with('category')->where('is_sales', true)->where('is_active', true)->get();

        // Get company type for POS mode
        $companyType = 'toko'; // Default
        if (Auth::check() && Auth::user()->company) {
            $companyType = Auth::user()->company->type ?? 'toko';
        }

        // Get pending orders for resto type
        $pendingOrders = collect();
        if ($companyType === 'resto') {
            $pendingOrders = Order::with(['items.item', 'user'])
                ->whereIn('status', ['pending', 'ordered'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Get settings for POS
        $settings = [
            'currency' => Setting::get('currency', 'IDR'),
            'currency_symbol' => Setting::get('currency_symbol', 'Rp'),
            'tax_rate' => Setting::get('tax_rate', 0),
            'tax_name' => Setting::get('tax_name', 'Tax'),
            'company_type' => $companyType,
        ];

        return view('pos.index', compact('categories', 'items', 'settings', 'pendingOrders'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.note' => 'nullable|string|max:500',
            'total_amount' => 'required|numeric',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'order_type' => 'nullable|in:dine_in,takeaway,direct',
            'table_number' => 'nullable|string|max:20',
            'skip_payment' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Determine order type and status
            $orderType = $request->order_type ?? 'direct';
            $skipPayment = $request->skip_payment ?? false;
            $orderStatus = ($skipPayment || $orderType !== 'direct') ? 'pending' : 'completed';
            $paymentMethod = $skipPayment ? null : ($request->payment_method ?? 'cash');

            // Create Order
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $request->customer_name ?? Setting::get('default_customer_name', 'Walk-in Customer'),
                'customer_phone' => $request->customer_phone,
                'total_amount' => $request->total_amount,
                'payment_method' => $paymentMethod,
                'status' => $orderStatus,
                'order_type' => $orderType,
                'table_number' => $request->table_number,
            ]);

            foreach ($request->items as $item) {
                $product = Item::with('itemRecipes')->find($item['item_id']);
                $subtotal = $product->selling_price * $item['quantity'];

                // Create Order Item
                $order->items()->create([
                    'item_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->selling_price,
                    'subtotal' => $subtotal,
                    'note' => $item['note'] ?? null,
                ]);

                // Automatic Stock Deduction (BOM) - Only deduct if order is paid/completed
                if (!$skipPayment) {
                    foreach ($product->itemRecipes as $recipe) {
                        $qtyNeeded = $recipe->quantity_required * $item['quantity'];

                        StockTransaction::create([
                            'item_id' => $recipe->ingredient_item_id,
                            'type' => 'out',
                            'quantity' => $qtyNeeded,
                            'description' => "Sold in Order #{$order->id} (Product: {$product->name})",
                            'reference_id' => $order->id,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'status' => $order->status,
                'message' => $skipPayment ? 'Order created! Please complete payment when ready.' : 'Order placed successfully!',
                'receipt_url' => $skipPayment ? null : route('pos.receipt', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Checkout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending orders for resto
     */
    public function pendingOrders()
    {
        $orders = Order::with(['items.item', 'user'])
            ->whereIn('status', ['pending', 'ordered'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    /**
     * Process payment for a pending order
     */
    public function processPayment(Request $request, $id)
    {
        $order = Order::with(['items.item'])->findOrFail($id);

        if ($order->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Order is already paid.'
            ], 400);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,qris,debit,credit,transfer',
            'cash_received' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update order with payment info
            $order->update([
                'payment_method' => $request->payment_method,
                'status' => 'completed',
            ]);

            // Deduct stock now that payment is complete
            foreach ($order->items as $orderItem) {
                $product = $orderItem->item;
                if ($product && $product->itemRecipes) {
                    foreach ($product->itemRecipes as $recipe) {
                        $qtyNeeded = $recipe->quantity_required * $orderItem->quantity;

                        StockTransaction::create([
                            'item_id' => $recipe->ingredient_item_id,
                            'type' => 'out',
                            'quantity' => $qtyNeeded,
                            'description' => "Sold in Order #{$order->id} (Product: {$product->name})",
                            'reference_id' => $order->id,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'message' => 'Payment processed successfully!',
                'receipt_url' => route('pos.receipt', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a pending order
     */
    public function deletePendingOrder($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a completed order.'
            ], 400);
        }

        // Delete order items first
        $order->items()->delete();
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully!'
        ]);
    }

    /**
     * Generate and display receipt for an order.
     */
    public function receipt($id)
    {
        $order = Order::with(['items.item', 'user'])->findOrFail($id);

        // Get settings for receipt
        $settings = [
            'store_name' => Setting::get('store_name', 'JuicePOS Store'),
            'store_address' => Setting::get('store_address', ''),
            'store_phone' => Setting::get('store_phone', ''),
            'store_email' => Setting::get('store_email', ''),
            'currency_symbol' => Setting::get('currency_symbol', '$'),
            'receipt_header' => Setting::get('receipt_header', 'Thank you for your purchase!'),
            'receipt_footer' => Setting::get('receipt_footer', 'Please come again!'),
            'show_customer_phone' => Setting::get('show_customer_phone_on_receipt', true),
            'show_cashier_name' => Setting::get('show_cashier_name_on_receipt', true),
            'tax_rate' => Setting::get('tax_rate', 0),
            'tax_name' => Setting::get('tax_name', 'Tax'),
            'tax_included' => Setting::get('tax_included', false),
        ];

        return view('pos.receipt', compact('order', 'settings'));
    }

    /**
     * Print receipt for an order.
     */
    public function printReceipt($id)
    {
        $order = Order::with(['items.item', 'user'])->findOrFail($id);

        // Get settings for receipt
        $settings = [
            'store_name' => Setting::get('store_name', 'JuicePOS Store'),
            'store_address' => Setting::get('store_address', ''),
            'store_phone' => Setting::get('store_phone', ''),
            'store_email' => Setting::get('store_email', ''),
            'currency_symbol' => Setting::get('currency_symbol', '$'),
            'receipt_header' => Setting::get('receipt_header', 'Thank you for your purchase!'),
            'receipt_footer' => Setting::get('receipt_footer', 'Please come again!'),
            'show_customer_phone' => Setting::get('show_customer_phone_on_receipt', true),
            'show_cashier_name' => Setting::get('show_cashier_name_on_receipt', true),
            'tax_rate' => Setting::get('tax_rate', 0),
            'tax_name' => Setting::get('tax_name', 'Tax'),
            'tax_included' => Setting::get('tax_included', false),
        ];

        return view('pos.receipt-print', compact('order', 'settings'));
    }

    /**
     * Generate kitchen receipt for pending orders.
     */
    public function kitchenReceipt($id)
    {
        $order = Order::with(['items.item', 'user'])->findOrFail($id);

        // Get settings for receipt
        $settings = [
            'store_name' => Setting::get('store_name', 'Kitchen'),
            'currency_symbol' => Setting::get('currency_symbol', 'Rp'),
        ];

        return view('pos.receipt-kitchen', compact('order', 'settings'));
    }

    /**
     * Generate table receipt for customer (for dine-in/takeaway).
     */
    public function tableReceipt($id)
    {
        $order = Order::with(['items.item', 'user'])->findOrFail($id);

        // Get settings for receipt
        $settings = [
            'store_name' => Setting::get('store_name', 'JuicePOS Store'),
            'store_address' => Setting::get('store_address', ''),
            'store_phone' => Setting::get('store_phone', ''),
            'currency_symbol' => Setting::get('currency_symbol', 'Rp'),
            'receipt_footer' => Setting::get('receipt_footer', 'Please come again!'),
        ];

        return view('pos.receipt-table', compact('order', 'settings'));
    }
}
