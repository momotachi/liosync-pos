<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Determine date range
        [$from, $to] = $this->getDateRange($period, $startDate, $endDate);

        // 1. Revenue
        $revenue = Order::whereBetween('created_at', [$from, $to])->sum('total_amount');

        // 2. Orders Count (Transactions)
        $ordersCount = Order::whereBetween('created_at', [$from, $to])->count();

        // 3. Items Sold Count
        $itemsSold = OrderItem::whereHas('order', function ($q) use ($from, $to) {
            $q->whereBetween('created_at', [$from, $to]);
        })->sum('quantity');

        // 4. Top Selling Items - only include items that exist
        $topSelling = OrderItem::whereHas('order', function ($q) use ($from, $to) {
            $q->whereBetween('created_at', [$from, $to]);
        })
            ->whereHas('item')
            ->select('item_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(subtotal) as total_revenue'))
            ->groupBy('item_id')
            ->with('item')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 5. Total Purchases (from Purchase Orders)
        $totalPurchases = Purchase::whereBetween('created_at', [$from, $to])
            ->sum('total_amount');

        // 6. Low Stock Items (raw materials with is_purchase = true)
        $lowStockItems = Item::where('is_purchase', true)
            ->whereColumn('current_stock', '<=', 'min_stock_level')
            ->orderBy('current_stock', 'asc')
            ->take(5)
            ->get();

        // 7. Top Purchase Items - based on Purchase Orders
        $topPurchaseItems = PurchaseItem::whereHas('purchase', function ($q) use ($from, $to) {
            $q->whereBetween('purchases.created_at', [$from, $to]);
        })
            ->select('item_id', DB::raw('sum(quantity) as total_qty'), DB::raw('sum(subtotal) as total_cost'))
            ->groupBy('item_id')
            ->with('item')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // 8. Top Profit Ratio - items with highest profit margin
        // Profit ratio = (selling_price - hpp) / selling_price
        $topProfitRatio = Item::where('is_sales', true)
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
