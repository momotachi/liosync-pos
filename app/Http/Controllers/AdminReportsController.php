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
    private function authorizeReportAccess()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Cashier cannot access reports
        if (!$user || $user->isCashier()) {
            abort(403, 'Access denied. Cashier cannot access reports.');
        }
    }

    /**
     * Get the effective branch ID.
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

    /**
     * Display the sales reports page.
     */
    public function index(Request $request)
    {
        $this->authorizeReportAccess();

        $branchId = $this->getEffectiveBranchId();

        // Validate branch context - user must have a branch to view reports
        if (!$branchId) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company and branch to view reports.');
            } elseif ($user->isCompanyAdmin()) {
                $companyId = session('company_id') ?? $user->company_id;
                if ($companyId) {
                    return redirect()->route('company.branches.index', $companyId)
                        ->with('info', 'Please select a branch to view reports.');
                }
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company first.');
            } else {
                abort(403, 'No branch assigned. Please contact administrator.');
            }
        }

        $period = $request->get('period', 'today'); // today, week, month, custom
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Determine date range
        [$from, $to] = $this->getDateRange($period, $startDate, $endDate);

        // Get sales metrics
        $metrics = $this->getSalesMetrics($from, $to, $branchId);

        // Get sales over time data for chart
        $salesOverTime = $this->getSalesOverTime($from, $to, $period, $branchId);

        // Get top selling products
        $topProducts = $this->getTopProducts($from, $to, $branchId);

        // Get recent orders
        $recentOrders = Order::with(['items.item'])
            ->whereBetween('created_at', [$from, $to])
            ->where('branch_id', $branchId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get payment method breakdown
        $paymentMethods = $this->getPaymentMethodBreakdown($from, $to, $branchId);

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
    private function getSalesMetrics($from, $to, $branchId = null)
    {
        $query = Order::whereBetween('created_at', [$from, $to]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $orders = $query;

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
    private function getSalesOverTime($from, $to, $period, $branchId = null)
    {
        $groupBy = match ($period) {
            'today' => 'H:00', // Hourly for today
            'week', 'month' => '%Y-%m-%d', // Daily for week/month
            'custom' => '%Y-%m-%d', // Daily for custom
            default => '%Y-%m-%d',
        };

        $query = Order::whereBetween('created_at', [$from, $to]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $data = $query
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
    private function getTopProducts($from, $to, $branchId = null)
    {
        $query = OrderItem::selectRaw('
                item_id,
                SUM(quantity) as total_quantity,
                SUM(subtotal) as total_revenue,
                COUNT(DISTINCT order_id) as orders_count
            ')
            ->whereHas('order', function ($q) use ($from, $to, $branchId) {
                $q->whereBetween('created_at', [$from, $to]);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->with('item')
            ->groupBy('item_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        return $query;
    }

    /**
     * Get payment method breakdown.
     */
    private function getPaymentMethodBreakdown($from, $to, $branchId = null)
    {
        $query = Order::whereBetween('created_at', [$from, $to]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * Export sales report as PDF/CSV (placeholder for future implementation).
     */
    public function export(Request $request)
    {
        $this->authorizeReportAccess();

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
        $this->authorizeReportAccess();

        $branchId = $this->getEffectiveBranchId();

        // Validate branch context
        if (!$branchId) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company and branch to view sales.');
            } elseif ($user->isCompanyAdmin()) {
                $companyId = session('company_id') ?? $user->company_id;
                if ($companyId) {
                    return redirect()->route('company.branches.index', $companyId)
                        ->with('info', 'Please select a branch to view sales.');
                }
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company first.');
            } else {
                abort(403, 'No branch assigned. Please contact administrator.');
            }
        }

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
            ->whereBetween('created_at', [$from, $to])
            ->where('branch_id', $branchId);

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
        $metrics = $this->getSalesMetrics($from, $to, $branchId);
        $paymentMethods = $this->getPaymentMethodBreakdown($from, $to, $branchId);

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
        $this->authorizeReportAccess();

        $branchId = $this->getEffectiveBranchId();

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
            ->whereBetween('created_at', [$from, $to])
            ->where('branch_id', $branchId);

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
        $metrics = $this->getSalesMetrics($from, $to, $branchId);

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
        $this->authorizeReportAccess();

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
        $this->authorizeReportAccess();

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
        $this->authorizeReportAccess();

        $branchId = $this->getEffectiveBranchId();

        // Validate branch context
        if (!$branchId) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company and branch to view purchase reports.');
            } elseif ($user->isCompanyAdmin()) {
                $companyId = session('company_id') ?? $user->company_id;
                if ($companyId) {
                    return redirect()->route('company.branches.index', $companyId)
                        ->with('info', 'Please select a branch to view purchase reports.');
                }
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company first.');
            } else {
                abort(403, 'No branch assigned. Please contact administrator.');
            }
        }

        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');

        // Determine date range
        [$from, $to] = $this->getDateRange($period, $startDate, $endDate);

        // Build query with branch filter
        $query = Purchase::with(['items.item', 'user'])
            ->whereBetween('created_at', [$from, $to])
            ->where('branch_id', $branchId);

        // Search by supplier or purchase ID
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        // Get paginated purchases
        $purchases = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Get purchase metrics
        $metrics = $this->getPurchaseMetrics($from, $to, $branchId);

        // Get purchases over time for chart
        $purchasesOverTime = $this->getPurchasesOverTime($from, $to, $period, $branchId);

        // Get top purchased items
        $topItems = $this->getTopPurchasedItems($from, $to, $branchId);

        return view('admin.reports.purchases', compact(
            'purchases',
            'metrics',
            'purchasesOverTime',
            'topItems',
            'period',
            'startDate',
            'endDate',
            'search'
        ));
    }

    /**
     * Get purchase metrics for the period.
     */
    private function getPurchaseMetrics($from, $to, $branchId = null)
    {
        $query = Purchase::whereBetween('created_at', [$from, $to]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $purchases = $query;

        $totalItems = \App\Models\PurchaseItem::whereHas('purchase', function ($q) use ($from, $to, $branchId) {
            $q->whereBetween('created_at', [$from, $to]);
            if ($branchId) {
                $q->where('branch_id', $branchId);
            }
        })->sum('quantity');

        return [
            'total_purchase_value' => $purchases->sum('total_amount'),
            'total_purchases' => $purchases->count(),
            'average_purchase_value' => $purchases->avg('total_amount') ?? 0,
            'total_items' => $totalItems,
        ];
    }

    /**
     * Get purchases over time data for charts.
     */
    private function getPurchasesOverTime($from, $to, $period, $branchId = null)
    {
        $groupBy = match ($period) {
            'today' => 'H:00', // Hourly for today
            'week', 'month' => '%Y-%m-%d', // Daily for week/month
            'custom' => '%Y-%m-%d', // Daily for custom
            default => '%Y-%m-%d',
        };

        $query = Purchase::whereBetween('created_at', [$from, $to]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $data = $query
            ->selectRaw('DATE_FORMAT(created_at, ?) as period, SUM(total_amount) as amount, COUNT(*) as purchases', [$groupBy])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'labels' => $data->pluck('period')->toArray(),
            'amount' => $data->pluck('amount')->toArray(),
            'purchases' => $data->pluck('purchases')->toArray(),
        ];
    }

    /**
     * Get top purchased items.
     */
    private function getTopPurchasedItems($from, $to, $branchId = null)
    {
        $query = \App\Models\PurchaseItem::selectRaw('
                item_id,
                SUM(quantity) as total_quantity,
                SUM(subtotal) as total_cost,
                COUNT(DISTINCT purchase_id) as purchase_count
            ')
            ->whereHas('purchase', function ($q) use ($from, $to, $branchId) {
                $q->whereBetween('created_at', [$from, $to]);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->with('item')
            ->groupBy('item_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        return $query;
    }

    /**
     * Get profit report with period filtering.
     */
    public function profit(Request $request)
    {
        $this->authorizeReportAccess();

        $branchId = $this->getEffectiveBranchId();

        // Validate branch context
        if (!$branchId) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company and branch to view profit reports.');
            } elseif ($user->isCompanyAdmin()) {
                $companyId = session('company_id') ?? $user->company_id;
                if ($companyId) {
                    return redirect()->route('company.branches.index', $companyId)
                        ->with('info', 'Please select a branch to view profit reports.');
                }
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company first.');
            } else {
                abort(403, 'No branch assigned. Please contact administrator.');
            }
        }

        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Determine date range
        [$from, $to] = $this->getDateRange($period, $startDate, $endDate);

        // Get profit metrics
        $metrics = $this->getProfitMetrics($from, $to, $branchId);

        // Get profit over time for chart
        $profitOverTime = $this->getProfitOverTime($from, $to, $period, $branchId);

        // Get top profitable products
        $topProducts = $this->getTopProfitableProducts($from, $to, $branchId);

        // Get detailed profit by order
        $orders = Order::with(['items.item'])
            ->whereBetween('created_at', [$from, $to])
            ->where('branch_id', $branchId)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->all());

        return view('admin.reports.profit', compact(
            'metrics',
            'profitOverTime',
            'topProducts',
            'orders',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get profit metrics for the period.
     */
    private function getProfitMetrics($from, $to, $branchId = null)
    {
        // Get sales data
        $salesQuery = Order::whereBetween('created_at', [$from, $to]);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        $orders = $salesQuery->get();

        $totalRevenue = $orders->sum('total_amount');
        $totalOrders = $orders->count();

        // Calculate HPP (cost of goods sold)
        $totalHpp = 0;
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $product = $item->item;
                if ($product && $product->hpp) {
                    $totalHpp += $product->hpp * $item->quantity;
                }
            }
        }

        // Get purchase cost (raw materials purchased)
        $purchaseQuery = Purchase::whereBetween('created_at', [$from, $to]);
        if ($branchId) {
            $purchaseQuery->where('branch_id', $branchId);
        }
        $totalPurchases = $purchaseQuery->sum('total_amount');

        $grossProfit = $totalRevenue - $totalHpp;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_hpp' => $totalHpp,
            'total_purchases' => $totalPurchases,
            'gross_profit' => $grossProfit,
            'profit_margin' => $profitMargin,
            'total_orders' => $totalOrders,
        ];
    }

    /**
     * Get profit over time data for charts.
     */
    private function getProfitOverTime($from, $to, $period, $branchId = null)
    {
        $groupBy = match ($period) {
            'today' => 'H:00',
            'week', 'month' => '%Y-%m-%d',
            'custom' => '%Y-%m-%d',
            default => '%Y-%m-%d',
        };

        // Get revenue over time
        $revenueQuery = Order::whereBetween('created_at', [$from, $to]);
        if ($branchId) {
            $revenueQuery->where('branch_id', $branchId);
        }
        $revenueData = $revenueQuery
            ->selectRaw('DATE_FORMAT(created_at, ?) as period, SUM(total_amount) as revenue', [$groupBy])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Get HPP over time
        $orderIds = Order::whereBetween('created_at', [$from, $to]);
        if ($branchId) {
            $orderIds->where('branch_id', $branchId);
        }
        $orderIds = $orderIds->pluck('id');

        $hppData = \App\Models\OrderItem::whereIn('order_id', $orderIds)
            ->selectRaw('DATE_FORMAT(created_at, ?) as period, SUM(subtotal) as hpp', [$groupBy])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Merge data
        $labels = $revenueData->pluck('period')->toArray();
        $revenue = $revenueData->pluck('revenue')->toArray();
        $hpp = $hppData->pluck('hpp')->toArray();
        $profit = [];
        foreach ($labels as $index => $label) {
            $rev = $revenue[$index] ?? 0;
            $cost = $hpp[$index] ?? 0;
            $profit[] = $rev - $cost;
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'hpp' => $hpp,
            'profit' => $profit,
        ];
    }

    /**
     * Get top profitable products.
     */
    private function getTopProfitableProducts($from, $to, $branchId = null)
    {
        $query = OrderItem::selectRaw('
                item_id,
                SUM(quantity) as total_quantity,
                SUM(subtotal) as total_revenue
            ')
            ->whereHas('order', function ($q) use ($from, $to, $branchId) {
                $q->whereBetween('created_at', [$from, $to]);
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->with('item')
            ->groupBy('item_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Calculate profit for each product
        $query->each(function ($item) {
            $product = $item->item;
            if ($product && $product->hpp) {
                $item->total_hpp = $product->hpp * $item->total_quantity;
                $item->total_profit = $item->total_revenue - $item->total_hpp;
                $item->profit_margin = $item->total_revenue > 0 ? ($item->total_profit / $item->total_revenue) * 100 : 0;
            } else {
                $item->total_hpp = 0;
                $item->total_profit = $item->total_revenue;
                $item->profit_margin = 100;
            }
        });

        return $query;
    }

    /**
     * Get cashflow report with period filtering.
     */
    public function cashflow(Request $request)
    {
        $this->authorizeReportAccess();

        $branchId = $this->getEffectiveBranchId();

        // Validate branch context
        if (!$branchId) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company and branch to view cashflow reports.');
            } elseif ($user->isCompanyAdmin()) {
                $companyId = session('company_id') ?? $user->company_id;
                if ($companyId) {
                    return redirect()->route('company.branches.index', $companyId)
                        ->with('info', 'Please select a branch to view cashflow reports.');
                }
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company first.');
            } else {
                abort(403, 'No branch assigned. Please contact administrator.');
            }
        }

        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Determine date range
        [$from, $to] = $this->getDateRange($period, $startDate, $endDate);

        // Get cashflow metrics
        $metrics = $this->getCashflowMetrics($from, $to, $branchId);

        // Get cashflow transactions
        $transactions = $this->getCashflowTransactions($from, $to, $branchId);

        // Get initial balances (before the period)
        $initialBalances = $this->getInitialBalances($from, $branchId);

        // Get recent balance adjustments
        $recentCashAdjustments = \App\Models\BalanceAdjustment::ofType('cash')
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->orderBy('adjustment_date', 'desc')
            ->limit(5)
            ->get();

        $recentBankAdjustments = \App\Models\BalanceAdjustment::ofType('bank')
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->orderBy('adjustment_date', 'desc')
            ->limit(5)
            ->get();

        return view('admin.reports.cashflow', compact(
            'metrics',
            'transactions',
            'initialBalances',
            'recentCashAdjustments',
            'recentBankAdjustments',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get cashflow metrics for the period.
     */
    private function getCashflowMetrics($from, $to, $branchId = null)
    {
        // Cash In - from sales
        $cashInQuery = Order::whereBetween('created_at', [$from, $to])
            ->where('status', 'completed');
        if ($branchId) {
            $cashInQuery->where('branch_id', $branchId);
        }

        // Group by payment method
        $cashInByMethod = $cashInQuery->clone()
            ->selectRaw('payment_method, SUM(total_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $totalCashIn = $cashInQuery->sum('total_amount');

        // Cash Out - from purchases
        $cashOutQuery = Purchase::whereBetween('created_at', [$from, $to]);
        if ($branchId) {
            $cashOutQuery->where('branch_id', $branchId);
        }
        $totalCashOut = $cashOutQuery->sum('total_amount');

        // Net cashflow
        $netCashflow = $totalCashIn - $totalCashOut;

        return [
            'cash_in' => $totalCashIn,
            'cash_out' => $totalCashOut,
            'net_cashflow' => $netCashflow,
            'cash_in_by_method' => $cashInByMethod,
        ];
    }

    /**
     * Get cashflow transactions (ledger style).
     */
    private function getCashflowTransactions($from, $to, $branchId = null)
    {
        $transactions = collect();

        // Sales transactions (Cash In)
        $salesQuery = Order::whereBetween('created_at', [$from, $to])
            ->where('status', 'completed');
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }

        $sales = $salesQuery->orderBy('created_at', 'asc')->get();
        foreach ($sales as $sale) {
            $transactions->push([
                'date' => $sale->created_at,
                'type' => 'in',
                'category' => 'Penjualan',
                'description' => 'Order #' . $sale->id . ' - ' . ($sale->customer_name ?? 'Walk-in'),
                'reference' => $sale->id,
                'reference_type' => 'order',
                'payment_method' => $sale->payment_method,
                'amount' => $sale->total_amount,
                'cash_amount' => $sale->payment_method === 'cash' ? $sale->total_amount : 0,
                'bank_amount' => $sale->payment_method !== 'cash' ? $sale->total_amount : 0,
            ]);
        }

        // Purchase transactions (Cash Out)
        $purchaseQuery = Purchase::whereBetween('created_at', [$from, $to]);
        if ($branchId) {
            $purchaseQuery->where('branch_id', $branchId);
        }

        $purchases = $purchaseQuery->orderBy('created_at', 'asc')->get();
        foreach ($purchases as $purchase) {
            $transactions->push([
                'date' => $purchase->created_at,
                'type' => 'out',
                'category' => 'Pembelian Bahan',
                'description' => 'PO #' . $purchase->id . ' - ' . ($purchase->supplier_name ?? 'Supplier'),
                'reference' => $purchase->id,
                'reference_type' => 'purchase',
                'payment_method' => $purchase->payment_method ?? 'cash',
                'amount' => $purchase->total_amount,
                'cash_amount' => $purchase->total_amount, // Assume purchases are cash for now
                'bank_amount' => 0,
            ]);
        }

        // Balance adjustments (within the period)
        $adjustmentQuery = \App\Models\BalanceAdjustment::whereBetween('adjustment_date', [$from, $to]);
        if ($branchId) {
            $adjustmentQuery->where('branch_id', $branchId);
        }

        $adjustments = $adjustmentQuery->orderBy('adjustment_date', 'asc')->get();
        foreach ($adjustments as $adjustment) {
            $isPositive = $adjustment->amount >= 0;
            $transactions->push([
                'date' => $adjustment->adjustment_date,
                'type' => $isPositive ? 'in' : 'out',
                'category' => 'Penyesuaian Saldo',
                'description' => 'Penyesuaian ' . ucfirst($adjustment->type) . ($adjustment->note ? ': ' . $adjustment->note : ''),
                'reference' => $adjustment->id,
                'reference_type' => 'adjustment',
                'payment_method' => $adjustment->type,
                'amount' => abs($adjustment->amount),
                'cash_amount' => $adjustment->type === 'cash' ? $adjustment->amount : 0,
                'bank_amount' => $adjustment->type === 'bank' ? $adjustment->amount : 0,
            ]);
        }

        // Sort by date
        return $transactions->sortBy('date');
    }

    /**
     * Get initial balances before the period.
     */
    private function getInitialBalances($from, $branchId = null)
    {
        // Get all sales before this period
        $salesQuery = Order::where('created_at', '<', $from)
            ->where('status', 'completed');
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }

        $sales = $salesQuery->get();
        $initialCash = 0;
        $initialBank = 0;

        foreach ($sales as $sale) {
            if ($sale->payment_method === 'cash') {
                $initialCash += $sale->total_amount;
            } else {
                $initialBank += $sale->total_amount;
            }
        }

        // Subtract purchases before this period
        $purchaseQuery = Purchase::where('created_at', '<', $from);
        if ($branchId) {
            $purchaseQuery->where('branch_id', $branchId);
        }
        $initialCash -= $purchaseQuery->sum('total_amount');

        // Get manual balance adjustments before this period from database
        $initialCash += \App\Models\BalanceAdjustment::getTotalBeforeDate('cash', $from, $branchId);
        $initialBank += \App\Models\BalanceAdjustment::getTotalBeforeDate('bank', $from, $branchId);

        return [
            'cash' => $initialCash,
            'bank' => $initialBank,
        ];
    }

    /**
     * Update cash/bank balance adjustment.
     */
    public function updateBalance(Request $request)
    {
        $this->authorizeReportAccess();

        $request->validate([
            'type' => 'required|in:cash,bank',
            'amount' => 'required|numeric',
            'note' => 'nullable|string|max:500',
            'adjustment_date' => 'required|date',
        ]);

        $branchId = $this->getEffectiveBranchId();
        $type = $request->type;
        $amount = $request->amount;
        $note = $request->note;
        $adjustmentDate = \Carbon\Carbon::parse($request->adjustment_date);

        // Create a new balance adjustment record with the specified datetime
        $adjustment = \App\Models\BalanceAdjustment::create([
            'branch_id' => $branchId,
            'type' => $type,
            'amount' => $amount,
            'note' => $note,
            'adjustment_date' => $adjustmentDate,
        ]);

        // Calculate new total balance
        $from = now();
        $newCashBalance = $this->getInitialBalances($from, $branchId)['cash'];
        $newBankBalance = $this->getInitialBalances($from, $branchId)['bank'];

        return response()->json([
            'success' => true,
            'message' => 'Saldo berhasil diperbarui',
            'adjustment' => $adjustment,
            'new_cash_balance' => $newCashBalance,
            'new_bank_balance' => $newBankBalance,
        ]);
    }

    /**
     * Transfer balance between cash and bank.
     */
    public function transferBalance(Request $request)
    {
        $this->authorizeReportAccess();

        $request->validate([
            'from' => 'required|in:cash,bank',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:500',
            'transfer_date' => 'required|date',
        ]);

        $branchId = $this->getEffectiveBranchId();
        $from = $request->from;
        $to = $from === 'cash' ? 'bank' : 'cash';
        $amount = $request->amount;
        $note = $request->note;
        $transferDate = \Carbon\Carbon::parse($request->transfer_date);

        // Create two balance adjustment records: one deduction, one addition
        $deductionAdjustment = \App\Models\BalanceAdjustment::create([
            'branch_id' => $branchId,
            'type' => $from,
            'amount' => -$amount,
            'note' => 'Transfer ke ' . strtoupper($to) . ($note ? ': ' . $note : ''),
            'adjustment_date' => $transferDate,
        ]);

        $additionAdjustment = \App\Models\BalanceAdjustment::create([
            'branch_id' => $branchId,
            'type' => $to,
            'amount' => $amount,
            'note' => 'Transfer dari ' . strtoupper($from) . ($note ? ': ' . $note : ''),
            'adjustment_date' => $transferDate,
        ]);

        // Calculate new total balance
        $fromNow = now();
        $newCashBalance = $this->getInitialBalances($fromNow, $branchId)['cash'];
        $newBankBalance = $this->getInitialBalances($fromNow, $branchId)['bank'];

        return response()->json([
            'success' => true,
            'message' => 'Transfer saldo berhasil',
            'deduction' => $deductionAdjustment,
            'addition' => $additionAdjustment,
            'new_cash_balance' => $newCashBalance,
            'new_bank_balance' => $newBankBalance,
        ]);
    }

    /**
     * Delete a transaction (only balance adjustments).
     */
    public function deleteTransaction(Request $request)
    {
        $this->authorizeReportAccess();

        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|in:adjustment',
        ]);

        $id = $request->id;
        $type = $request->type;

        if ($type === 'adjustment') {
            $adjustment = \App\Models\BalanceAdjustment::find($id);

            if (!$adjustment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan',
                ], 404);
            }

            // Check branch access
            $branchId = $this->getEffectiveBranchId();
            if ($adjustment->branch_id != $branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus transaksi ini',
                ], 403);
            }

            $adjustment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tipe transaksi tidak valid',
        ], 400);
    }

    /**
     * Get order details via AJAX.
     */
    public function orderDetails($id)
    {
        $this->authorizeReportAccess();

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
