<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function index(Request $request)
    {
        $group = $request->get('group', 'general');
        $groups = Setting::select('group')->distinct()->pluck('group');
        // Only show system-wide settings (branch_id = NULL) for superadmin
        $settings = Setting::byGroup($group)->whereNull('branch_id')->get();

        // Load banks for payment settings
        $banks = Bank::ordered()->get();

        return view('superadmin.settings.index', compact('settings', 'groups', 'group', 'banks'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($validated['settings'] as $setting) {
            // Only update system-wide settings (branch_id = NULL)
            $model = Setting::where('key', $setting['key'])->whereNull('branch_id')->first();
            if ($model) {
                $model->update(['value' => $setting['value'] ?? '']);
            }
        }

        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Store a new bank.
     */
    public function storeBank(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
        ]);

        Bank::create([
            'name' => $validated['name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'is_active' => true,
            'sort_order' => Bank::max('sort_order') + 1,
        ]);

        return back()->with('success', 'Bank added successfully!');
    }

    /**
     * Update a bank.
     */
    public function updateBank(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $bank->update([
            'name' => $validated['name'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Bank updated successfully!');
    }

    /**
     * Delete a bank.
     */
    public function deleteBank(Bank $bank)
    {
        $bank->delete();

        return back()->with('success', 'Bank deleted successfully!');
    }
}
