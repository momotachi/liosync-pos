<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Company;

class AccountingController extends Controller
{
    /**
     * Display accounting overview for all companies.
     */
    public function index(Request $request)
    {
        // Get all companies with their branches
        $companies = Company::with('branches')->get();

        // Get accounting data per company
        $companiesWithData = $companies->map(function ($company) {
            $branchIds = $company->branches->pluck('id');

            $revenue = Order::whereIn('branch_id', $branchIds)->sum('total_amount');
            $purchases = Purchase::whereIn('branch_id', $branchIds)->sum('total_amount');
            $profit = $revenue - $purchases;

            $company->revenue = $revenue;
            $company->purchases = $purchases;
            $company->profit = $profit;

            return $company;
        });

        // Calculate totals
        $totalRevenue = $companiesWithData->sum('revenue');
        $totalPurchases = $companiesWithData->sum('purchases');
        $totalProfit = $companiesWithData->sum('profit');

        return view('superadmin.accounting.index', [
            'companies' => $companiesWithData,
            'totalRevenue' => $totalRevenue,
            'totalPurchases' => $totalPurchases,
            'totalProfit' => $totalProfit
        ]);
    }
}
