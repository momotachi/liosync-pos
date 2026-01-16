<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
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
     * Same flow as Superadmin creating a company.
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
            'company_code' => 'nullable|string|max:50|unique:companies,code',
            'company_type' => 'required|in:resto,toko',
            'company_structure' => 'required|in:single,multi',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:100',

            // Admin Account
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|string|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
            'admin_phone' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // Determine if multi-branch or single
            $hasBranches = $validated['company_structure'] === 'multi';
            $isSingleBranch = $validated['company_structure'] === 'single';

            // Generate company code if not provided
            $companyCode = $validated['company_code'] ?? strtoupper(Str::substr(Str::slug($validated['company_name']), 0, 10)) . rand(100, 999);

            // Create Company (same as superadmin)
            $company = Company::create([
                'name' => $validated['company_name'],
                'code' => $companyCode,
                'slug' => Str::slug($validated['company_name']) . '-' . time(),
                'type' => $validated['company_type'],
                'address' => $validated['company_address'] ?? null,
                'phone' => $validated['company_phone'] ?? null,
                'email' => $validated['company_email'] ?? $validated['admin_email'],
                'tax_id' => $validated['tax_id'] ?? null,
                'is_active' => true,
                'has_branches' => $hasBranches,
            ]);

            // Create Admin User (same as superadmin - role='admin')
            $adminData = [
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'password_hint' => $validated['admin_password'], // Store original password
                'phone' => $validated['admin_phone'] ?? null,
                'role' => 'admin', // Use 'admin' like superadmin, not 'company_admin'
                'is_active' => true,
                'company_id' => $company->id,
                'branch_id' => null, // Will be set below if single branch
            ];

            // For single-branch, create default branch and assign admin to it
            if ($isSingleBranch) {
                $branch = Branch::create([
                    'company_id' => $company->id,
                    'name' => 'Main Branch',
                    'code' => $companyCode . '_MAIN',
                    'address' => $validated['company_address'] ?? null,
                    'phone' => $validated['company_phone'] ?? null,
                    'email' => $validated['company_email'] ?? null,
                    'is_active' => true,
                ]);

                // Assign admin to this branch
                $adminData['branch_id'] = $branch->id;
            }

            $admin = User::create($adminData);

            // Assign Company Admin role using Spatie
            $admin->assignRole('Company Admin');

            DB::commit();

            // Log the user in
            Auth::login($admin);

            // Set session variables
            $sessionData = ['company_id' => $company->id];
            if ($isSingleBranch && isset($branch)) {
                $sessionData['branch_id'] = $branch->id;
            }
            session($sessionData);

            Log::info('New company registered via signup', [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'company_structure' => $validated['company_structure'],
                'user_id' => $admin->id,
                'user_email' => $admin->email,
                'branch_id' => $admin->branch_id,
            ]);

            $successMessage = $isSingleBranch
                ? 'Registration successful! Welcome to Liosync POS. Your account is ready to use.'
                : 'Registration successful! Welcome to Liosync POS. Please create your first branch to get started.';

            return redirect()
                ->route('company.dashboard', ['company' => $company->id])
                ->with('success', $successMessage);

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
