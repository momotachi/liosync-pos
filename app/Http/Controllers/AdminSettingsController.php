<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminSettingsController extends Controller
{
    private function authorizeSettingsAccess()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cashier cannot access settings
        if (!$user || $user->isCashier()) {
            abort(403, 'Access denied. Cashier cannot access settings.');
        }
    }

    /**
     * Display the settings page.
     */
    public function index()
    {
        $this->authorizeSettingsAccess();

        $settings = Setting::grouped();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $this->authorizeSettingsAccess();

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
        $this->authorizeSettingsAccess();

        $branchId = $this->getEffectiveBranchId();

        DB::transaction(function () use ($branchId) {
            // Reset all settings to their default values (no currency settings)
            $defaults = [
                // General Settings
                'store_name' => 'JuicePOS Store',
                'store_address' => '',
                'store_phone' => '',
                'store_email' => '',

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
                $setting = Setting::where('key', $key)
                    ->where('branch_id', $branchId)
                    ->first();
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
     * Handle password change request.
     */
    public function handlePasswordChange(Request $request)
    {
        $this->authorizeSettingsAccess();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'current_password' => 'The current password is incorrect.',
                ]);
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->setRememberToken(null); // Invalidate remember tokens
        $user->save();

        return redirect()
            ->route('admin.settings.index')
            ->with('password_success', 'Your password has been changed successfully!');
    }
}