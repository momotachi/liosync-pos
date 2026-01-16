<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();

        return view('superadmin.subscription-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can create subscription plans.');
        }

        return view('superadmin.subscription-plans.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can create subscription plans.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:subscription_plans,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'max_branches' => 'nullable|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Handle unlimited branches
        if ($request->has('unlimited_branches')) {
            $validated['max_branches'] = null;
        }

        // Handle unlimited users
        if ($request->has('unlimited_users')) {
            $validated['max_users'] = null;
        }

        // Handle features as JSON
        $features = [];
        if ($request->has('features')) {
            foreach ($request->input('features', []) as $feature => $value) {
                $features[$feature] = true;
            }
        }
        $validated['features'] = $features;

        SubscriptionPlan::create($validated);

        return redirect()->route('superadmin.subscription-plans.index')
            ->with('success', 'Subscription plan created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubscriptionPlan $plan)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can edit subscription plans.');
        }

        return view('superadmin.subscription-plans.form', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can update subscription plans.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:subscription_plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'max_branches' => 'nullable|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Handle unlimited branches
        if ($request->has('unlimited_branches')) {
            $validated['max_branches'] = null;
        }

        // Handle unlimited users
        if ($request->has('unlimited_users')) {
            $validated['max_users'] = null;
        }

        // Handle features as JSON
        $features = [];
        if ($request->has('features')) {
            foreach ($request->input('features', []) as $feature => $value) {
                $features[$feature] = true;
            }
        }
        $validated['features'] = $features;

        $plan->update($validated);

        return redirect()->route('superadmin.subscription-plans.index')
            ->with('success', 'Subscription plan updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubscriptionPlan $plan)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can delete subscription plans.');
        }

        // Check if plan has active subscriptions
        if ($plan->branchSubscriptions()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $plan->delete();

        return redirect()->route('superadmin.subscription-plans.index')
            ->with('success', 'Subscription plan deleted successfully!');
    }
}
