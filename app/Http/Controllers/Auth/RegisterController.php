<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchSubscription;
use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        // If already logged in, redirect to appropriate page
        if (Auth::check()) {
            return redirect('/pos');
        }

        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            // Company Information
            'company_name' => 'required|string|max:255',
            'company_type' => 'required|in:restaurant,retail,cafe,bar,other',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:500',

            // Branch Information
            'branch_name' => 'required|string|max:255',
            'branch_code' => 'nullable|string|max:20',
            'branch_phone' => 'nullable|string|max:50',

            // Admin Account
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',

            // Subscription
            'subscription_plan' => 'required|exists:subscription_plans,id',
        ]);

        try {
            DB::beginTransaction();

            // Generate company code and slug
            $companyCode = strtoupper(Str::substr(Str::slug($validated['company_name']), 0, 10)) . rand(100, 999);
            $companySlug = Str::slug($validated['company_name']) . '-' . Str::lower($companyCode);

            // Create Company
            $company = Company::create([
                'name' => $validated['company_name'],
                'code' => $companyCode,
                'slug' => $companySlug,
                'type' => $validated['company_type'],
                'address' => $validated['company_address'] ?? null,
                'phone' => $validated['company_phone'] ?? null,
                'email' => $validated['email'],
                'is_active' => true,
                'has_branches' => true,
            ]);

            // Generate branch code if not provided
            $branchCode = $validated['branch_code'] ?: strtoupper(Str::substr(Str::slug($validated['branch_name']), 0, 10)) . rand(100, 999);

            // Create Branch
            $branch = Branch::create([
                'company_id' => $company->id,
                'name' => $validated['branch_name'],
                'code' => $branchCode,
                'address' => $validated['company_address'] ?? null,
                'phone' => $validated['branch_phone'] ?? $validated['company_phone'] ?? null,
                'is_active' => true,
            ]);

            // Get subscription plan
            $plan = SubscriptionPlan::find($validated['subscription_plan']);

            // Calculate trial end date (14 days from now)
            $trialEndDate = now()->addDays(14);

            // Create Branch Subscription
            $subscription = BranchSubscription::create([
                'branch_id' => $branch->id,
                'subscription_plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'plan_price' => $plan->price,
                'plan_duration_months' => $plan->duration_months,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => $trialEndDate,
                'is_trial' => true,
                'auto_renew' => false,
            ]);

            // Create Admin User (Company Admin)
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'role' => 'company_admin',
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]);

            // Assign permissions using Spatie
            $user->assignRole('Company Admin');

            DB::commit();

            // Log the user in
            Auth::login($user);

            // Set session variables
            session([
                'company_id' => $company->id,
                'branch_id' => $branch->id,
            ]);

            Log::info('New company registered', [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'subscription_id' => $subscription->id,
            ]);

            return redirect()
                ->route('company.dashboard', ['company' => $company->id])
                ->with('success', 'Registration successful! Welcome to Liosync POS. Your 14-day trial has started.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'registration' => 'Registration failed: ' . $e->getMessage(),
                ]);
        }
    }
}
