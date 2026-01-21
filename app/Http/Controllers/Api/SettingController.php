<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user->company;
        $branch = $user->branch;
        
        // Fetch custom settings if any
        // $settings = Setting::where('company_id', $company->id)->pluck('value', 'key');

        return response()->json([
            'success' => true,
            'data' => [
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    // 'logo' => $company->logo_url,
                ],
                'branch' => $branch ? [
                    'id' => $branch->id,
                    'name' => $branch->name,
                ] : null,
                'app_config' => [
                    'currency' => 'IDR',
                    'currency_symbol' => 'Rp',
                    'tax_rate' => 11, // Example
                ],
                'payment_methods' => ['cash', 'qris', 'debit'],
                'order_types' => ['dine_in', 'take_away']
            ]
        ]);
    }
}
