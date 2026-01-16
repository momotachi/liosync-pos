<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Company;

class PurchaseController extends Controller
{
    /**
     * Display purchase overview for all companies.
     */
    public function index(Request $request)
    {
        // Get all companies with their branches
        $companies = Company::with('branches')->get();

        // Get total purchases across all companies
        $totalPurchases = Purchase::sum('total_amount');
        $totalPurchaseOrders = Purchase::count();

        // Get purchase data per company
        $companiesWithPurchases = $companies->map(function ($company) {
            $branchIds = $company->branches->pluck('id');
            $companyPurchases = Purchase::whereIn('branch_id', $branchIds)->sum('total_amount');
            $companyOrders = Purchase::whereIn('branch_id', $branchIds)->count();

            $company->total_purchases = $companyPurchases;
            $company->total_orders = $companyOrders;

            return $company;
        });

        return view('superadmin.purchase.index', [
            'companies' => $companiesWithPurchases,
            'totalPurchases' => $totalPurchases,
            'totalPurchaseOrders' => $totalPurchaseOrders
        ]);
    }
}
