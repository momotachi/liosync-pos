<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\BranchSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubscriptionController extends Controller
{
    /**
     * Display branch subscription status.
     */
    public function index()
    {
        $branch = auth()->user()->branch;
        $subscription = $branch?->currentSubscription;

        return view('branch.subscription.index', compact('subscription'));
    }

    /**
     * Show purchase form.
     */
    public function purchase()
    {
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        $branch = auth()->user()->branch;
        $currentSubscription = $branch?->currentSubscription;

        return view('branch.subscription.purchase', compact('plans', 'currentSubscription'));
    }

    /**
     * Process purchase/upgrade.
     */
    public function processPurchase(Request $request)
    {
        $validated = $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'months' => 'required|integer|min:1|max:36',
            'payment_proof' => 'required|image|max:2048',
        ]);

        $branch = auth()->user()->branch;
        $plan = SubscriptionPlan::find($validated['subscription_plan_id']);

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

        return redirect()->route('subscription.index')
            ->with('success', 'Payment submitted! Waiting for admin confirmation.');
    }

    /**
     * Show payment history.
     */
    public function history()
    {
        $branch = auth()->user()->branch;
        $subscriptions = $branch->subscriptions()
            ->with('subscriptionPlan', 'payments.confirmedBy')
            ->latest()
            ->get();

        return view('branch.subscription.history', compact('subscriptions'));
    }
}
