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
    private function authorizePosAccess()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // All authenticated users can access POS
        if (!$user) {
            abort(401, 'Authentication required.');
        }
    }

    /**
     * Get the effective branch ID for the current user.
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

    public function index()
    {
        $this->authorizePosAccess();

        $branchId = $this->getEffectiveBranchId();

        // Validate branch context
        if (!$branchId) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company and branch to access POS.');
            } elseif ($user->isCompanyAdmin()) {
                $companyId = session('company_id') ?? $user->company_id;
                if ($companyId) {
                    return redirect()->route('company.branches.index', $companyId)
                        ->with('info', 'Please select a branch to access POS.');
                }
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company first.');
            } else {
                abort(403, 'No branch assigned. Please contact administrator.');
            }
        }

        // Get categories that have items in this branch
        $categories = \App\Models\Category::whereHas('items', function ($query) use ($branchId) {
            $query->where('is_sales', true)->where('is_active', true)->where('branch_id', $branchId);
        })->get();

        // Get items for this branch only
        $items = Item::with('category')
            ->where('is_sales', true)
            ->where('is_active', true)
            ->where('branch_id', $branchId)
            ->get();

        // Get company type for POS mode
        $companyType = 'toko'; // Default
        if (Auth::check() && Auth::user()->company) {
            $companyType = Auth::user()->company->type ?? 'toko';
        }

        // Get pending orders for resto type (this branch only)
        $pendingOrders = collect();
        if ($companyType === 'resto') {
            $pendingOrders = Order::with(['items.item', 'user'])
                ->where('branch_id', $branchId)
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
        $this->authorizePosAccess();

        // Check subscription for non-superadmin users
        $user = auth()->user();
        $branchId = $this->getEffectiveBranchId();

        if (!$user->isSuperAdmin() && $branchId) {
            $branch = \App\Models\Branch::find($branchId);
            if ($branch && !$branch->hasActiveSubscription()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Langganan cabang ini telah habis. Silakan perpanjang untuk melanjutkan transaksi.',
                    'redirect' => route('subscription.index')
                ], 403);
            }
        }

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
            'print_receipt' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Get branch and company context
            $branchId = $this->getEffectiveBranchId();
            $companyId = session('company_id') ?? Auth::user()->company_id;

            // Validate branch context
            if (!$branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No branch context. Please select a branch first.'
                ], 403);
            }

            // Determine order type and status
            $orderType = $request->order_type ?? 'direct';
            $skipPayment = $request->skip_payment ?? false;
            $orderStatus = ($skipPayment || $orderType !== 'direct') ? 'pending' : 'completed';
            $paymentMethod = $skipPayment ? null : ($request->payment_method ?? 'cash');

            // Create Order
            $order = Order::create([
                'user_id' => Auth::id(),
                'company_id' => $companyId,
                'branch_id' => $branchId,
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

                // Verify product belongs to the same branch
                if ($product->branch_id != $branchId) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Product {$product->name} does not belong to this branch."
                    ], 403);
                }

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
                    // For items with both is_purchase AND is_sales = true, deduct the item's own stock
                    if ($product->is_purchase && $product->is_sales) {
                        StockTransaction::create([
                            'item_id' => $product->id,
                            'type' => 'out',
                            'quantity' => $item['quantity'],
                            'description' => "Sold in Order #{$order->id} ({$product->name})",
                            'reference_id' => $order->id,
                            'branch_id' => $branchId,
                        ]);
                    }

                    // Deduct BOM ingredients if the product has recipes
                    foreach ($product->itemRecipes as $recipe) {
                        $qtyNeeded = $recipe->quantity_required * $item['quantity'];

                        StockTransaction::create([
                            'item_id' => $recipe->ingredient_item_id,
                            'type' => 'out',
                            'quantity' => $qtyNeeded,
                            'description' => "Sold in Order #{$order->id} (Product: {$product->name})",
                            'reference_id' => $order->id,
                            'branch_id' => $branchId,
                        ]);
                    }
                }
            }

            DB::commit();

            $responseData = [
                'success' => true,
                'order_id' => $order->id,
                'status' => $order->status,
                'message' => $skipPayment ? 'Order created! Please complete payment when ready.' : 'Order placed successfully!',
            ];

            // Only include receipt_url if user wants to print and order is completed (not skipped)
            if (!$skipPayment && ($request->print_receipt ?? true)) {
                $responseData['receipt_url'] = route('pos.receipt', $order->id);
            }

            return response()->json($responseData);

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
        $this->authorizePosAccess();

        $branchId = $this->getEffectiveBranchId();

        $orders = Order::with(['items.item', 'user'])
            ->where('branch_id', $branchId)
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
        $this->authorizePosAccess();

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
            'print_receipt' => 'nullable|boolean',
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
                            'branch_id' => $order->branch_id,
                        ]);
                    }
                }
            }

            DB::commit();

            $responseData = [
                'success' => true,
                'order_id' => $order->id,
                'message' => 'Payment processed successfully!',
            ];

            // Only include receipt_url if user wants to print
            if ($request->print_receipt ?? true) {
                $responseData['receipt_url'] = route('pos.receipt', $order->id);
            }

            return response()->json($responseData);

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
        $this->authorizePosAccess();

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
     * Cancel a completed order (with stock revert)
     */
    public function cancelOrder(Request $request, $id)
    {
        $this->authorizePosAccess();

        $order = Order::with('items')->findOrFail($id);

        // Only allow admin/company admin to cancel orders
        $user = auth()->user();
        if ($user->isCashier()) {
            return response()->json([
                'success' => false,
                'message' => 'Cashier cannot cancel orders.'
            ], 403);
        }

        if ($order->isCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Order is already cancelled.'
            ], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $success = $order->cancel($request->reason, $user->id);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel order.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully! Stock has been reverted.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cancel failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and display receipt for an order.
     */
    public function receipt($id)
    {
        $this->authorizePosAccess();

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
        $this->authorizePosAccess();

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
        $this->authorizePosAccess();

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
        $this->authorizePosAccess();

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
