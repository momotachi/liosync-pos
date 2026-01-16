<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Company;

class SalesController extends Controller
{
    /**
     * Display sales overview for all companies.
     */
    public function index(Request $request)
    {
        // Get all companies with their branches
        $companies = Company::with('branches')->get();

        // Get total sales across all companies
        $totalSales = Order::sum('total_amount');
        $totalOrders = Order::count();

        // Get sales data per company
        $companiesWithSales = $companies->map(function ($company) {
            $branchIds = $company->branches->pluck('id');
            $companySales = Order::whereIn('branch_id', $branchIds)->sum('total_amount');
            $companyOrders = Order::whereIn('branch_id', $branchIds)->count();

            $company->total_sales = $companySales;
            $company->total_orders = $companyOrders;

            return $company;
        });

        return view('superadmin.sales.index', [
            'companies' => $companiesWithSales,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders
        ]);
    }
}
