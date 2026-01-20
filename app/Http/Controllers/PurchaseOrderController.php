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
    private function authorizePurchaseAccess()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Cashier cannot access purchase orders
        if (!$user || $user->isCashier()) {
            abort(403, 'Access denied. Cashier cannot access purchase orders.');
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
        $this->authorizePurchaseAccess();

        $branchId = $this->getEffectiveBranchId();

        // Validate branch context
        if (!$branchId) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company and branch to access Purchase Orders.');
            } elseif ($user->isCompanyAdmin()) {
                $companyId = session('company_id') ?? $user->company_id;
                if ($companyId) {
                    return redirect()->route('company.branches.index', $companyId)
                        ->with('info', 'Please select a branch to access Purchase Orders.');
                }
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company first.');
            } else {
                abort(403, 'No branch assigned. Please contact administrator.');
            }
        }

        // Get categories that have purchase items in this branch
        $categories = \App\Models\Category::whereHas('items', function ($query) use ($branchId) {
            $query->where('is_purchase', true)->where('is_active', true)->where('branch_id', $branchId);
        })->get();

        // Get purchase items for this branch only
        $items = Item::where('is_purchase', true)
            ->where('is_active', true)
            ->where('branch_id', $branchId)
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category_id' => $item->category_id,
                    'category_name' => $item->category->name ?? 'Uncategorized',
                    'image' => $item->image,
                    'purchase_price' => (float) ($item->unit_price ?? $item->hpp ?? 0),
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
        $this->authorizePurchaseAccess();

        // Check subscription for non-superadmin users
        $user = auth()->user();
        $branchId = $this->getEffectiveBranchId();

        if (!$user->isSuperAdmin() && $branchId) {
            $branch = \App\Models\Branch::find($branchId);
            if ($branch && !$branch->hasActiveSubscription()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Langganan cabang ini telah habis. Silakan perpanjang untuk melanjutkan pembelian.',
                    'redirect' => route('subscription.index')
                ], 403);
            }
        }

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

            // Create Purchase
            $purchase = Purchase::create([
                'user_id' => Auth::id(),
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'supplier_name' => $request->supplier_name ?? 'General Supplier',
                'supplier_phone' => $request->supplier_phone,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $material = Item::find($item['item_id']);

                // Verify material belongs to the same branch
                if ($material->branch_id != $branchId) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Material {$material->name} does not belong to this branch."
                    ], 403);
                }

                $subtotal = ($item['price'] ?? 0) * $item['quantity'];

                // Create Purchase Item
                $purchase->items()->create([
                    'item_id' => $material->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] ?? $material->unit_price ?? $material->hpp,
                    'subtotal' => $subtotal,
                    'note' => $item['note'] ?? null,
                ]);

                // Add Stock (IN transaction) - Observer will handle stock update automatically
                StockTransaction::create([
                    'item_id' => $material->id,
                    'type' => 'in',
                    'quantity' => $item['quantity'],
                    'description' => "Purchased in PO #{$purchase->id}" .
                        ($request->supplier_name ? " from {$request->supplier_name}" : ''),
                    'reference_id' => $purchase->id,
                    'branch_id' => $branchId,
                ]);
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
        $this->authorizePurchaseAccess();

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

    /**
     * Cancel a purchase (with stock revert)
     */
    public function cancelPurchase(Request $request, $id)
    {
        $this->authorizePurchaseAccess();

        $purchase = Purchase::with('items')->findOrFail($id);

        // Only allow admin/company admin to cancel purchases
        $user = auth()->user();
        if ($user->isCashier()) {
            return response()->json([
                'success' => false,
                'message' => 'Cashier cannot cancel purchases.'
            ], 403);
        }

        if ($purchase->isCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase is already cancelled.'
            ], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $success = $purchase->cancel($request->reason, $user->id);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel purchase.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase cancelled successfully! Stock has been reverted.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cancel failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
