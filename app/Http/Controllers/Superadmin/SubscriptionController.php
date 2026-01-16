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
            // Special handling for 'pending' - filter subscriptions with pending payments
            if ($status === 'pending') {
                $query->whereHas('payments', function ($q) {
                    $q->where('status', 'pending');
                });
            } else {
                // For other statuses, filter by subscription status
                $query->where('status', $status);
            }
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

    /**
     * Adjust subscription period (add/remove months or days).
     */
    public function adjustPeriod(Request $request, BranchSubscription $subscription)
    {
        $validated = $request->validate([
            'months' => 'nullable|integer|min:-12|max:12',
            'days' => 'nullable|integer|min:-30|max:30',
        ]);

        $months = (int) ($validated['months'] ?? 0);
        $days = (int) ($validated['days'] ?? 0);

        // Adjust the end date - clone to avoid modifying original
        $currentEndDate = $subscription->end_date ? \Carbon\Carbon::parse($subscription->end_date) : now();
        $subscription->end_date = $currentEndDate->copy()->addMonths($months)->addDays($days);
        $subscription->save();

        // Auto-activate if was expired and we're adding time
        if (($months > 0 || $days > 0) && $subscription->status === 'expired') {
            $subscription->status = 'active';
            $subscription->save();
        }

        // Build success message
        $messageParts = [];
        if ($months !== 0) {
            $messageParts[] = $months > 0
                ? "{$months} month(s) added"
                : abs($months) . " month(s) reduced";
        }
        if ($days !== 0) {
            $messageParts[] = $days > 0
                ? "{$days} day(s) added"
                : abs($days) . " day(s) reduced";
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully ' . implode(' and ', $messageParts) . ' to subscription.'
        ]);
    }
}
