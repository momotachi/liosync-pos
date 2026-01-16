<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanViewController extends Controller
{
    /**
     * Display a listing of subscription plans (read-only for non-superadmin).
     */
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('sort_order')
            ->where('is_active', true)
            ->get();

        return view('subscription-plans.view', compact('plans'));
    }
}
