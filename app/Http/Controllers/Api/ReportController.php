<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);

        $query = Order::where('company_id', $request->user()->company_id)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to);

        $totalRevenue = $query->sum('total_amount');
        $totalOrders = $query->count();
        
        // Group by Date
        $dailySales = Order::select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('sum(total_amount) as revenue'),
                DB::raw('count(*) as orders')
            )
            ->where('company_id', $request->user()->company_id)
             ->where('status', 'completed')
            ->whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to)
            ->groupBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'total_orders' => $totalOrders,
                ],
                'daily_sales' => $dailySales
            ]
        ]);
    }

    public function inventory(Request $request)
    {
        // Snapshot of current stock
        $items = Item::where('company_id', $request->user()->company_id)
            ->select('id', 'name', 'current_stock', 'unit_price')
            ->get();
            
        $totalValue = $items->sum(function($item) {
            return $item->current_stock * $item->unit_price;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_items' => $items->count(),
                    'total_value' => $totalValue
                ],
                // 'items' => $items 
            ]
        ]);
    }

    public function export(Request $request) 
    {
        // Simple stub for export. Real implementation involves Maatwebsite Excel
        return response()->json([
            'success' => true, 
            'message' => 'Export feature coming soon to API'
        ]);
    }
}
