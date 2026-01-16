<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\BranchSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions for company's branches.
     */
    public function index(Request $request, $company)
    {
        $companyId = is_numeric($company) ? $company : $company->id;
        $status = $request->get('status');
        $branch = $request->get('branch');

        $query = BranchSubscription::whereHas('branch', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->with(['branch', 'subscriptionPlan', 'payments']);

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by branch
        if ($branch) {
            $query->where('branch_id', $branch);
        }

        $subscriptions = $query->latest()->paginate(20);

        // Get company branches for filter
        $branches = \App\Models\Branch::where('company_id', $companyId)->active()->get();

        return view('company.subscriptions.index', compact('subscriptions', 'branches', 'company', 'status', 'branch'));
    }

    /**
     * Display the specified subscription.
     */
    public function show($company, BranchSubscription $subscription)
    {
        $companyId = is_numeric($company) ? $company : $company->id;

        // Check if subscription belongs to company
        if ($subscription->branch->company_id != $companyId) {
            abort(403);
        }

        $subscription->load(['branch', 'subscriptionPlan', 'payments.confirmedBy']);

        return view('company.subscriptions.show', compact('subscription'));
    }

    /**
     * Extend a subscription.
     */
    public function extendSubscription(Request $request, $company, BranchSubscription $subscription)
    {
        $companyId = is_numeric($company) ? $company : $company->id;

        // Check if subscription belongs to company
        if ($subscription->branch->company_id != $companyId) {
            abort(403);
        }

        $validated = $request->validate([
            'months' => 'required|integer|min:1|max:36',
        ]);

        $subscription->extend($validated['months']);

        return response()->json([
            'success' => true,
            'message' => "Subscription extended by {$validated['months']} month(s)!"
        ]);
    }

    /**
     * Extend multiple subscriptions at once.
     */
    public function bulkExtend(Request $request, $company)
    {
        $validated = $request->validate([
            'subscription_ids' => 'required|array',
            'subscription_ids.*' => 'exists:branch_subscriptions,id',
            'months' => 'required|integer|min:1|max:36',
        ]);

        $companyId = is_numeric($company) ? $company : $company->id;
        $extendedCount = 0;

        foreach ($validated['subscription_ids'] as $subscriptionId) {
            $subscription = BranchSubscription::find($subscriptionId);
            if ($subscription && $subscription->branch->company_id == $companyId) {
                $subscription->extend($validated['months']);
                $extendedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$extendedCount} subscription(s) extended by {$validated['months']} month(s)!"
        ]);
    }

    /**
     * Show purchase form for company branches.
     */
    public function purchase($company)
    {
        $companyId = is_numeric($company) ? $company : $company->id;
        $plans = \App\Models\SubscriptionPlan::active()->orderBy('sort_order')->get();
        $branches = \App\Models\Branch::where('company_id', $companyId)->active()->get();

        return view('company.subscriptions.purchase', compact('plans', 'branches', 'company'));
    }

    /**
     * Process purchase/upgrade for a branch.
     */
    public function processPurchase(Request $request, $company)
    {
        $companyId = is_numeric($company) ? $company : $company->id;

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'months' => 'required|integer|min:1|max:36',
            'payment_proof' => 'required|image|max:2048',
        ]);

        // Verify branch belongs to company
        $branch = \App\Models\Branch::where('id', $validated['branch_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        $plan = \App\Models\SubscriptionPlan::find($validated['subscription_plan_id']);

        DB::transaction(function () use ($request, $validated, $branch, $plan) {
            // Upload payment proof
            $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');

            // Create subscription
            $subscription = BranchSubscription::create([
                'branch_id' => $branch->id,
                'subscription_plan_id' => $plan->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths($validated['months'])->toDateString(),
                'status' => 'pending',
                'payment_proof' => $proofPath,
            ]);

            // Create payment record
            $subscription->payments()->create([
                'amount' => $plan->price * $validated['months'],
                'payment_method' => 'bank_transfer',
                'proof_image' => $proofPath,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('company.subscriptions.index', $companyId)
            ->with('success', 'Payment submitted! Waiting for admin confirmation.');
    }
}
