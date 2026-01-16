<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\BranchSubscription;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $company = $request->get('company');
        $branch = $request->get('branch');

        $query = BranchSubscription::with(['branch.company', 'subscriptionPlan', 'payments']);

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by company
        if ($company) {
            $query->whereHas('branch', function ($q) use ($company) {
                $q->where('company_id', $company);
            });
        }

        // Filter by branch
        if ($branch) {
            $query->where('branch_id', $branch);
        }

        $subscriptions = $query->latest()->paginate(20);

        // Get companies for filter dropdown
        $companies = \App\Models\Company::active()->get();

        return view('superadmin.subscriptions.index', compact(
            'subscriptions',
            'companies',
            'status',
            'company',
            'branch'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(BranchSubscription $subscription)
    {
        $subscription->load(['branch.company', 'subscriptionPlan', 'payments.confirmedBy']);

        return view('superadmin.subscriptions.show', compact('subscription'));
    }

    /**
     * Confirm a payment.
     */
    public function confirmPayment($paymentId)
    {
        $payment = SubscriptionPayment::findOrFail($paymentId);
        $payment->confirm(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed successfully!'
        ]);
    }

    /**
     * Confirm multiple payments at once.
     */
    public function confirmPaymentBulk(Request $request)
    {
        $validated = $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:subscription_payments,id',
        ]);

        $confirmedCount = 0;
        foreach ($validated['payment_ids'] as $paymentId) {
            $payment = SubscriptionPayment::find($paymentId);
            if ($payment && $payment->status === 'pending') {
                $payment->confirm(auth()->id());
                $confirmedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$confirmedCount} payment(s) confirmed successfully!"
        ]);
    }

    /**
     * Reject a payment.
     */
    public function rejectPayment(Request $request, $paymentId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $payment = SubscriptionPayment::findOrFail($paymentId);
        $payment->reject($validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Payment rejected successfully!'
        ]);
    }

    /**
     * Toggle subscription status (suspend/activate).
     */
    public function toggleStatus(Request $request, BranchSubscription $subscription)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,suspended',
        ]);

        $subscription->status = $validated['status'];
        $subscription->save();

        return redirect()->route('superadmin.subscriptions.show', $subscription)
            ->with('success', 'Subscription status updated successfully!');
    }
}
