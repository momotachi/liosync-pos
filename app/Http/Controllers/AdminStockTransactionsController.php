<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminStockTransactionsController extends Controller
{
    private function authorizeStockAccess()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cashier cannot access stock transactions
        if (!$user || $user->isCashier()) {
            abort(403, 'Access denied. Cashier cannot access stock transactions.');
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
        $user = Auth::user();
        return $user ? $user->branch_id : null;
    }

    /**
     * Display the stock transactions page.
     */
    public function index(Request $request)
    {
        $this->authorizeStockAccess();

        $branchId = $this->getEffectiveBranchId();

        // Validate branch context - user must have a branch to view stock transactions
        if (!$branchId) {
            $user = Auth::user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company and branch to view stock transactions.');
            } elseif ($user->isCompanyAdmin()) {
                $companyId = session('company_id') ?? $user->company_id;
                if ($companyId) {
                    return redirect()->route('company.branches.index', $companyId)
                        ->with('info', 'Please select a branch to view stock transactions.');
                }
                return redirect()->route('superadmin.companies.index')
                    ->with('info', 'Please select a company first.');
            } else {
                abort(403, 'No branch assigned. Please contact administrator.');
            }
        }

        $query = StockTransaction::with(['item', 'order'])
            ->where('branch_id', $branchId);

        // Filter by raw material
        if ($request->has('material_id') && $request->material_id) {
            $query->where('raw_material_id', $request->material_id);
        }

        // Filter by type (in/out)
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        // Search by description
        if ($request->has('search') && $request->search) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Get all raw materials for filter dropdown
        $rawMaterials = RawMaterial::orderBy('name')->get();

        // Get summary statistics
        $stats = $this->getStats($request);

        return view('admin.stock-transactions.index', compact(
            'transactions',
            'rawMaterials',
            'stats'
        ));
    }

    /**
     * Get summary statistics for stock transactions.
     */
    private function getStats(Request $request)
    {
        $branchId = $this->getEffectiveBranchId();

        $query = StockTransaction::where('branch_id', $branchId);

        // Apply same filters as main query
        if ($request->has('material_id') && $request->material_id) {
            $query->where('raw_material_id', $request->material_id);
        }
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        if ($request->has('start_date') && $request->start_date) {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $transactions = $query->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_in' => $transactions->where('type', 'in')->sum('quantity'),
            'total_out' => $transactions->where('type', 'out')->sum('quantity'),
            'total_cost' => $transactions->where('type', 'in')->sum('total_cost'),
        ];
    }

    /**
     * Show a specific stock transaction.
     */
    public function show($id)
    {
        $this->authorizeStockAccess();

        $transaction = StockTransaction::with(['item', 'order'])
            ->findOrFail($id);

        return view('admin.stock-transactions.show', compact('transaction'));
    }

    /**
     * Export stock transactions to CSV.
     */
    public function export(Request $request)
    {
        $this->authorizeStockAccess();

        $branchId = $this->getEffectiveBranchId();

        $query = StockTransaction::with(['item', 'order'])
            ->where('branch_id', $branchId);

        // Apply filters
        if ($request->has('material_id') && $request->material_id) {
            $query->where('raw_material_id', $request->material_id);
        }
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        if ($request->has('start_date') && $request->start_date) {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="stock-transactions-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Material', 'Type', 'Quantity', 'Unit', 'Supplier', 'Unit Cost', 'Total Cost', 'Description', 'Reference']);

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->created_at->format('Y-m-d H:i:s'),
                    $t->rawMaterial->name ?? 'N/A',
                    $t->type === 'in' ? 'IN' : 'OUT',
                    $t->quantity,
                    $t->rawMaterial->unit ?? '',
                    $t->supplier ?? 'N/A',
                    $t->unit_cost ?? 0,
                    $t->total_cost ?? 0,
                    $t->description ?? '',
                    $t->reference_id ? 'Order #' . $t->reference_id : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
