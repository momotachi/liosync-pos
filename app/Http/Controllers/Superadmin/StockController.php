<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\Company;

class StockController extends Controller
{
    /**
     * Display stock overview for all companies.
     */
    public function index(Request $request)
    {
        // Get all companies with their branches
        $companies = Company::with('branches')->get();

        // Get stock data per company
        $companiesWithData = $companies->map(function ($company) {
            $totalItems = Item::where('company_id', $company->id)->count();

            $stockValue = Item::where('company_id', $company->id)
                ->get()
                ->sum(function ($item) {
                    return $item->current_stock * $item->selling_price;
                });

            $company->total_items = $totalItems;
            $company->stock_value = $stockValue;

            return $company;
        });

        // Get low stock items across all companies (using min_stock_level)
        $lowStockItems = Item::whereColumn('current_stock', '<=', 'min_stock_level')
            ->where('is_purchase', true)
            ->get();

        return view('superadmin.stock.index', [
            'companies' => $companiesWithData,
            'lowStockItems' => $lowStockItems
        ]);
    }
}
