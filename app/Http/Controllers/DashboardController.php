<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function authorizeDashboardAccess()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cashier cannot access dashboard
        if (!$user || $user->isCashier()) {
            abort(403, 'Access denied. Cashier cannot access dashboard.');
        }
    }

    /**
     * Get the effective branch ID for the current user.
     * For Superadmin with active branch session, use session branch_id.
     * For Company Admin with active branch session, use session branch_id.
     * Otherwise use the user's own branch_id.
     */
    private function getEffectiveBranchId($user)
    {
        // Check if user has an active branch context (Superadmin or Company Admin switching to branch)
        if (session('active_branch_id')) {
            return session('active_branch_id');
        }
        // Otherwise use the user's own branch
        return $user->branch_id;
    }

    public function index(Request $request)
    {
        $this->authorizeDashboardAccess();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get the effective branch ID for filtering
        $branchId = $this->getEffectiveBranchId($user);

        // Validate branch context - user must have a branch to view dashboard
        if (!$branchId) {
            if ($user->isSuperAdmin()) {
                // Superadmin without branch context - redirect to companies
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company and branch to view dashboard.');
            } elseif ($user->isCompanyAdmin()) {
                // Company Admin without branch context - redirect to branches
                $companyId = session('company_id') ?? $user->company_id;
                if ($companyId) {
                    return redirect()->route('company.branches.index', $companyId)
                        ->with('info', 'Please select a branch to view dashboard.');
                }
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company first.');
            } else {
                // Branch Admin without branch - should not happen
                abort(403, 'No branch assigned. Please contact administrator.');
            }
        }

        // Determine date range
        [$from, $to] = $this->getDateRange($period, $startDate, $endDate);

        // 1. Revenue
        $revenueQuery = Order::whereBetween('created_at', [$from, $to]);
        if ($branchId) {
            $revenueQuery->where('branch_id', $branchId);
        }
        $revenue = $revenueQuery->sum('total_amount');

        // 2. Orders Count (Transactions)
        $ordersQuery = Order::whereBetween('created_at', [$from, $to]);
        if ($branchId) {
            $ordersQuery->where('branch_id', $branchId);
        }
        $ordersCount = $ordersQuery->count();

        // 3. Items Sold Count
        $itemsSoldQuery = OrderItem::whereHas('order', function ($q) use ($from, $to, $branchId) {
            $q->whereBetween('created_at', [$from, $to]);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        });
        $itemsSold = $itemsSoldQuery->sum('quantity');

        // 4. Top Selling Items - only include items that exist
        $topSellingQuery = OrderItem::whereHas('order', function ($q) use ($from, $to, $branchId) {
            $q->whereBetween('created_at', [$from, $to]);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
            ->whereHas('item')
            ->select('item_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(subtotal) as total_revenue'))
            ->groupBy('item_id')
            ->with('item')
            ->orderByDesc('total_qty')
            ->take(5);
        $topSelling = $topSellingQuery->get();

        // 5. Total Purchases (from Purchase Orders)
        $totalPurchasesQuery = Purchase::whereBetween('created_at', [$from, $to]);
        if ($branchId) {
            $totalPurchasesQuery->where('branch_id', $branchId);
        }
        $totalPurchases = $totalPurchasesQuery->sum('total_amount');

        // 6. Low Stock Items (raw materials with is_purchase = true)
        // Filter by branch to show only items from current branch
        $lowStockItems = Item::where('is_purchase', true)
            ->where('branch_id', $branchId)
            ->whereColumn('current_stock', '<=', 'min_stock_level')
            ->orderBy('current_stock', 'asc')
            ->take(5)
            ->get();

        // 7. Top Purchase Items - based on Purchase Orders
        $topPurchaseItemsQuery = PurchaseItem::whereHas('purchase', function ($q) use ($from, $to, $branchId) {
            $q->whereBetween('purchases.created_at', [$from, $to]);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })
            ->select('item_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(subtotal) as total_cost'))
            ->groupBy('item_id')
            ->with('item')
            ->orderByDesc('total_qty')
            ->take(5);
        $topPurchaseItems = $topPurchaseItemsQuery->get();

        // 8. Top Profit Ratio - items with highest profit margin
        // Profit ratio = (selling_price - hpp) / selling_price
        // Filter by branch to show only items from current branch
        $topProfitRatio = Item::where('is_sales', true)
            ->where('branch_id', $branchId)
            ->where('selling_price', '>', 0)
            ->where('hpp', '>', 0)
            ->select('*', DB::raw('((selling_price - hpp) / selling_price) as profit_ratio'))
            ->orderByDesc('profit_ratio')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'revenue',
            'ordersCount',
            'itemsSold',
            'topSelling',
            'totalPurchases',
            'lowStockItems',
            'topPurchaseItems',
            'topProfitRatio',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get date range based on period.
     */
    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        $now = now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'custom' => [
                $startDate ? \Carbon\Carbon::parse($startDate)->startOfDay() : $now->copy()->startOfMonth(),
                $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : $now->copy()->endOfDay()
            ],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }
}
