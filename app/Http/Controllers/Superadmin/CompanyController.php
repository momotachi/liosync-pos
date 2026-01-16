<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index()
    {
        $companies = Company::with('branches', 'users')->paginate(15);

        return view('superadmin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        return view('superadmin.companies.create');
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'type' => 'required|in:resto,toko',
            'tax_id' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'has_branches' => 'required|boolean',
            // Company Admin fields
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email|max:255',
            'admin_password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            // Create company
            $company = Company::create([
                'name' => $request->name,
                'code' => $request->code,
                'slug' => Str::slug($request->name) . '-' . time(),
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'type' => $request->type ?? 'toko',
                'tax_id' => $request->tax_id,
                'is_active' => $request->is_active ?? true,
                'has_branches' => $request->has_branches ?? false,
            ]);

            // Create company admin user
            $admin = User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => bcrypt($request->admin_password),
                'password_hint' => $request->admin_password, // Store original password for reference
                'role' => 'admin',
                'is_active' => true,
                'company_id' => $company->id,
                'branch_id' => null,
            ]);

            // Assign Company Admin role
            $admin->assignRole('Company Admin');

            // If single company (no branches), create a default branch
            if (!$request->has_branches) {
                $defaultBranch = Branch::create([
                    'company_id' => $company->id,
                    'name' => 'Main Branch',
                    'code' => $request->code . '_MAIN',
                    'address' => $request->address,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'is_active' => true,
                ]);

                // Update admin to have this branch
                $admin->update(['branch_id' => $defaultBranch->id]);
            }

            DB::commit();
            return redirect()->route('superadmin.companies.index')
                ->with('success', 'Company and admin account created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating company: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company)
    {
        $company->load(['branches', 'users', 'items']);
        $users = $company->users()->with('branch')->get();

        return view('superadmin.companies.show', compact('company', 'users'));
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company)
    {
        // Get all admin users for this company (Company Admin role)
        $adminUsers = $company->users()
            ->role('Company Admin')
            ->with('branch')
            ->get();

        return view('superadmin.companies.edit', compact('company', 'adminUsers'));
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code,' . $company->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'type' => 'required|in:resto,toko',
            'tax_id' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'has_branches' => 'boolean',
        ]);

        $company->update([
            'name' => $request->name,
            'code' => $request->code,
            'slug' => Str::slug($request->name) . '-' . time(),
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'type' => $request->type,
            'tax_id' => $request->tax_id,
            'is_active' => $request->is_active ?? true,
            'has_branches' => $request->has_branches ?? false,
        ]);

        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Company $company)
    {
        // Check if company has users
        if ($company->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete company with users. Please reassign or delete users first.');
        }

        $company->delete();

        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    /**
     * Update admin password.
     */
    public function updateAdminPassword(Request $request, Company $company, User $admin)
    {
        // Verify admin belongs to this company
        if ($admin->company_id !== $company->id) {
            abort(403, 'This admin does not belong to this company.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin->update([
            'password' => bcrypt($request->password),
            'password_hint' => $request->password, // Store original password for reference
        ]);

        return redirect()->back()
            ->with('success', 'Admin password updated successfully.');
    }

    /**
     * Show the form for editing the company (for Company Admin).
     */
    public function editCompany(Company $company)
    {
        /** @var User $user */
        $user = auth()->user();

        // Authorization: Company Admin can only edit their own company
        if (!$user->isSuperAdmin() && $user->company_id !== $company->id) {
            abort(403, 'Access denied');
        }

        return view('company.edit', compact('company'));
    }

    /**
     * Update the company (for Company Admin).
     */
    public function updateCompany(Request $request, Company $company)
    {
        /** @var User $user */
        $user = auth()->user();

        // Authorization: Company Admin can only update their own company
        if (!$user->isSuperAdmin() && $user->company_id !== $company->id) {
            abort(403, 'Access denied');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code,' . $company->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tax_id' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $company->update([
            'name' => $request->name,
            'code' => $request->code,
            'slug' => Str::slug($request->name) . '-' . time(),
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'tax_id' => $request->tax_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('company.edit', $company)
            ->with('success', 'Company updated successfully.');
    }

    /**
     * Show all users for a company (across all branches).
     * Supports branch filtering via ?branch_id=X parameter.
     */
    public function usersIndex(Company $company, Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        $branchFilter = $request->get('branch_id');

        // Get all users for this company (including Company Admins with null branch_id)
        $query = User::where('company_id', $company->id)
            ->with(['roles', 'branch']);

        // Apply branch filter if specified
        if ($branchFilter) {
            $query->where('branch_id', $branchFilter);
        }

        // If not superadmin, exclude other Company Admins from view
        if (!$currentUser->isSuperAdmin()) {
            $query->where(function ($q) use ($currentUser) {
                $q->whereNotNull('branch_id')
                  ->orWhere('id', $currentUser->id);
            });
        }

        $users = $query->paginate(20)->appends(['branch_id' => $branchFilter]);

        // Get all branches for filter dropdown
        $branches = $company->branches()->orderBy('name')->get();

        $roles = [
            'Company Admin' => 'Company Admin',
            'Branch Admin' => 'Branch Admin',
            'Stock Admin' => 'Stock Admin',
            'Cashier' => 'Cashier',
        ];

        return view('company.users', compact('company', 'users', 'roles', 'currentUser', 'branches', 'branchFilter'));
    }

    /**
     * Show the form for editing any user (Company Admin or Branch user).
     * Redirects to appropriate edit page based on user type.
     */
    public function usersEdit(Company $company, User $user)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Authorization: Superadmin and Company Admin can edit
        if (!$currentUser->isSuperAdmin() && !$currentUser->isCompanyAdmin()) {
            abort(403, 'Access denied');
        }

        // Company Admin can only edit users from their own company
        if (!$currentUser->isSuperAdmin() && $currentUser->company_id !== $company->id) {
            abort(403, 'Access denied');
        }

        // Verify user belongs to this company
        if ($user->company_id !== $company->id) {
            abort(404, 'User not found in this company');
        }

        // If user has a branch, redirect to branch users edit page
        if ($user->branch_id) {
            return redirect()->route('company.branches.users.edit', [$company, $user->branch, $user]);
        }

        // Company Admin user (no branch) - show company edit page
        $roles = [
            'Company Admin' => 'Company Admin',
        ];

        return view('company.users-edit', compact('company', 'user', 'roles'));
    }

    /**
     * Update a company admin user.
     */
    public function usersUpdate(Request $request, Company $company, User $user)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Authorization: Superadmin can update
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied');
        }

        // Verify user belongs to this company and has no branch
        if ($user->company_id !== $company->id || $user->branch_id !== null) {
            abort(404, 'User not found or can only edit Company Admins here.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:8',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $user->update([
                'password' => bcrypt($request->password),
                'password_hint' => $request->password_hint ?? $request->password,
            ]);
        }

        return redirect()->route('company.users.index', $company)
            ->with('success', 'Company Admin updated successfully.');
    }

    /**
     * Delete a user from company (only Company Admins).
     */
    public function usersDestroy(Company $company, User $user)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Authorization: Only superadmin can delete, or company admin can delete other company admins (but not themselves)
        if (!$currentUser->isSuperAdmin()) {
            // Company Admin can only delete from their own company
            if ($currentUser->company_id !== $company->id) {
                abort(403, 'Access denied');
            }
            // Cannot delete yourself
            if ($user->id === $currentUser->id) {
                return redirect()->back()
                    ->with('error', 'Cannot delete your own account.');
            }
        }

        // Verify user belongs to this company
        if ($user->company_id !== $company->id) {
            abort(404, 'User not found in this company');
        }

        // Only allow deletion of Company Admins (users with null branch_id)
        if ($user->branch_id !== null) {
            return redirect()->back()
                ->with('error', 'Please delete branch users from the branch users page.');
        }

        $user->delete();

        return redirect()->route('company.users.index', $company)
            ->with('success', 'Company Admin deleted successfully.');
    }
}
