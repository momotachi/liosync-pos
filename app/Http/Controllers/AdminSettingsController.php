<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminSettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = Setting::grouped();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $allSettings = Setting::pluck('type', 'key')->toArray();

        $validationRules = [];
        foreach ($allSettings as $key => $type) {
            if ($type === 'boolean') {
                $validationRules[$key] = 'boolean';
            } elseif ($type === 'number') {
                $validationRules[$key] = 'numeric';
            } else {
                $validationRules[$key] = 'nullable|string';
            }
        }

        $validated = $request->validate($validationRules);

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                Setting::set($key, $value);
            }
        });

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Reset settings to default values.
     */
    public function reset()
    {
        DB::transaction(function () {
            // Reset all settings to their default values
            $defaults = [
                // General Settings
                'store_name' => 'JuicePOS Store',
                'store_address' => '',
                'store_phone' => '',
                'store_email' => '',
                'currency' => 'USD',
                'currency_symbol' => '$',

                // POS Settings
                'auto_logout_time' => '30',
                'enable_barcode_scanner' => '1',
                'default_customer_name' => 'Walk-in Customer',

                // Receipt Settings
                'receipt_header' => 'Thank you for your purchase!',
                'receipt_footer' => 'Please come again!',
                'receipt_width' => '80',
                'show_customer_phone_on_receipt' => '1',
                'show_cashier_name_on_receipt' => '1',

                // Tax Settings
                'tax_rate' => '0',
                'tax_name' => 'Tax',
                'tax_included' => '0',

                // Inventory Settings
                'low_stock_alert' => '1',
                'auto_deduct_stock' => '1',
            ];

            foreach ($defaults as $key => $value) {
                $setting = Setting::where('key', $key)->first();
                if ($setting) {
                    $setting->value = $value;
                    $setting->save();
                }
            }
        });

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings reset to default values!');
    }
}
