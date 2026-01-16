<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AdminReportsController extends Controller
{
    /**
     * Display the sales reports page.
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'today'); // today, week, month, custom
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Determine date range
        [$from, $to] = $this->getDateRange($period, $startDate, $endDate);

        // Get sales metrics
        $metrics = $this->getSalesMetrics($from, $to);

        // Get sales over time data for chart
        $salesOverTime = $this->getSalesOverTime($from, $to, $period);

        // Get top selling products
        $topProducts = $this->getTopProducts($from, $to);

        // Get recent orders
        $recentOrders = Order::with(['items.item'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get payment method breakdown
        $paymentMethods = $this->getPaymentMethodBreakdown($from, $to);

        return view('admin.reports.index', compact(
            'metrics',
            'salesOverTime',
            'topProducts',
            'recentOrders',
            'paymentMethods',
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

    /**
     * Get sales metrics for the period.
     */
    private function getSalesMetrics($from, $to)
    {
        $orders = Order::whereBetween('created_at', [$from, $to]);

        return [
            'total_revenue' => $orders->sum('total_amount'),
            'total_orders' => $orders->count(),
            'average_order_value' => $orders->avg('total_amount') ?? 0,
            'total_items_sold' => OrderItem::whereHas('order', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            })->sum('quantity'),
            'total_tax' => 0, // Calculate based on settings if needed
        ];
    }

    /**
     * Get sales over time data for charts.
     */
    private function getSalesOverTime($from, $to, $period)
    {
        $groupBy = match ($period) {
            'today' => 'H:00', // Hourly for today
            'week', 'month' => '%Y-%m-%d', // Daily for week/month
            'custom' => '%Y-%m-%d', // Daily for custom
            default => '%Y-%m-%d',
        };

        $data = Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE_FORMAT(created_at, ?) as period, SUM(total_amount) as revenue, COUNT(*) as orders', [$groupBy])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'labels' => $data->pluck('period')->toArray(),
            'revenue' => $data->pluck('revenue')->toArray(),
            'orders' => $data->pluck('orders')->toArray(),
        ];
    }

    /**
     * Get top selling products.
     */
    private function getTopProducts($from, $to)
    {
        return OrderItem::selectRaw('
                item_id,
                SUM(quantity) as total_quantity,
                SUM(subtotal) as total_revenue,
                COUNT(DISTINCT order_id) as orders_count
            ')
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            })
            ->with('item')
            ->groupBy('item_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get payment method breakdown.
     */
    private function getPaymentMethodBreakdown($from, $to)
    {
        return Order::whereBetween('created_at', [$from, $to])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * Export sales report as PDF/CSV (placeholder for future implementation).
     */
    public function export(Request $request)
    {
        $period = $request->get('period', 'today');
        $format = $request->get('format', 'csv');

        // TODO: Implement export functionality
        return redirect()
            ->route('admin.reports.index')
            ->with('info', 'Export functionality coming soon!');
    }

    /**
     * Display all sales transactions with filtering and pagination.
     */
    public function salesTransactions(Request $request)
    {
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');
        $paymentMethod = $request->get('payment_method');
        $orderType = $request->get('order_type');

        // Determine date range
        [$from, $to] = $this->getDateRange($period, $startDate, $endDate);

        // Build query
        $query = Order::with(['items.item', 'user'])
            ->whereBetween('created_at', [$from, $to]);

        // Filter by order type
        if ($orderType) {
            $query->where('order_type', $orderType);
        }

        // Filter by payment method
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        // Search by order ID or customer name
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Paginate results
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Get summary metrics
        $metrics = $this->getSalesMetrics($from, $to);
        $paymentMethods = $this->getPaymentMethodBreakdown($from, $to);

        return view('admin.reports.sales', compact(
            'orders',
            'metrics',
            'paymentMethods',
            'period',
            'startDate',
            'endDate',
            'search',
            'paymentMethod',
            'orderType'
        ));
    }

    /**
     * Export sales transactions as PDF.
     */
    public function exportPdf(Request $request)
    {
        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $paymentMethod = $request->get('payment_method');
        $search = $request->get('search');
        $orderType = $request->get('order_type');

        // Determine date range
        [$from, $to] = $this->getDateRange($period, $startDate, $endDate);

        // Build query
        $query = Order::with(['items.item', 'user'])
            ->whereBetween('created_at', [$from, $to]);

        // Filter by order type
        if ($orderType) {
            $query->where('order_type', $orderType);
        }

        // Filter by payment method
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        // Search by order ID or customer name
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Get all orders (no pagination for export)
        $orders = $query->orderBy('created_at', 'desc')->get();

        // Get summary metrics
        $metrics = $this->getSalesMetrics($from, $to);

        // Generate PDF
        $pdf = Pdf::loadView('admin.reports.pdf', compact(
            'orders',
            'metrics',
            'period',
            'startDate',
            'endDate',
            'from',
            'to'
        ));

        return $pdf->download('sales-report-' . $period . '-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Get detailed product sales report.
     */
    public function productSales(Request $request)
    {
        $period = $request->get('period', 'today');
        [$from, $to] = $this->getDateRange($period, $request->get('start_date'), $request->get('end_date'));

        $products = Item::where('is_sales', true)
            ->with(['category' => function ($q) {
                $q->select('id', 'name');
            }])
            ->withCount(['orderItems as total_sold' => function ($q) use ($from, $to) {
                $q->whereHas('order', function ($q2) use ($from, $to) {
                    $q2->whereBetween('created_at', [$from, $to]);
                });
            }])
            ->withSum(['orderItems as total_revenue' => function ($q) use ($from, $to) {
                $q->whereHas('order', function ($q2) use ($from, $to) {
                    $q2->whereBetween('created_at', [$from, $to]);
                });
            }], 'subtotal')
            ->get()
            ->sortByDesc('total_sold');

        return view('admin.reports.products', compact('products', 'period'));
    }

    /**
     * Get inventory report.
     */
    public function inventory(Request $request)
    {
        $rawMaterials = Item::where('is_purchase', true)
            ->with(['stockTransactions' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(5);
            }])
            ->get();

        $stockValue = $rawMaterials->sum(function ($item) {
            return $item->current_stock * ($item->hpp ?? 0);
        });

        $totalTransactions = StockTransaction::count();

        return view('admin.reports.inventory', compact('rawMaterials', 'stockValue', 'totalTransactions'));
    }

    /**
     * Get purchase report (pembelian bahan baku).
     */
    public function purchases(Request $request)
    {
        $period = $request->get('period', 'today');
        [$from, $to] = $this->getDateRange($period, $request->get('start_date'), $request->get('end_date'));

        // Get purchase transactions (Purchase Orders)
        $purchases = Purchase::with(['items.item', 'user'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get purchase summary metrics
        $totalPurchases = $purchases->sum(function ($purchase) {
            return $purchase->items->sum('quantity');
        });

        $totalPurchaseValue = $purchases->sum('total_amount');

        $totalTransactions = $purchases->count();

        return view('admin.reports.purchases', compact(
            'purchases',
            'period',
            'totalPurchases',
            'totalPurchaseValue',
            'totalTransactions'
        ));
    }

    /**
     * Get order details via AJAX.
     */
    public function orderDetails($id)
    {
        $order = Order::with(['items.item', 'user'])->findOrFail($id);

        return response()->json([
            'order' => $order,
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                    'note' => $item->note,
                ];
            }),
        ]);
    }
}
