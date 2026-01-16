<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Display company admin dashboard.
     */
    public function dashboard(Company $company)
    {
        // Ensure user belongs to this company
        if (auth()->user()->company_id !== $company->id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        $company->load(['branches', 'users']);

        return view('company.dashboard', compact('company'));
    }

    /**
     * Show company profile edit form.
     */
    public function edit(Company $company)
    {
        // Ensure user belongs to this company
        if (auth()->user()->company_id !== $company->id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        return view('company.edit', compact('company'));
    }

    /**
     * Update company profile.
     */
    public function update(Request $request, Company $company)
    {
        // Ensure user belongs to this company
        if (auth()->user()->company_id !== $company->id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:companies,code,' . $company->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tax_id' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $company->update([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'tax_id' => $request->tax_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('company.dashboard', $company)
            ->with('success', 'Company profile updated successfully!');
    }

    /**
     * Display users list for this company.
     */
    public function usersIndex(Request $request, Company $company)
    {
        // Ensure user belongs to this company
        if (auth()->user()->company_id !== $company->id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        $branchFilter = $request->get('branch_id');
        $branches = $company->branches()->get();

        // Build query
        $query = $company->users()->with('branch', 'roles');

        // Filter by branch if selected
        if ($branchFilter) {
            $query->where('branch_id', $branchFilter);
        }

        $users = $query->paginate(15);

        // Role mapping for display
        $roles = [
            'Superadmin' => 'Superadmin',
            'Company Admin' => 'Company Admin',
            'Branch Admin' => 'Branch Admin',
            'Stock Admin' => 'Stock Admin',
            'Cashier' => 'Cashier',
        ];

        return view('company.users', compact('company', 'users', 'branches', 'branchFilter', 'roles'));
    }

    /**
     * Show user create form.
     */
    public function usersCreate(Company $company)
    {
        // Ensure user belongs to this company
        if (auth()->user()->company_id !== $company->id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        // Only allow creating Company Admin and Branch Admin roles
        $roles = \Spatie\Permission\Models\Role::whereIn('name', ['Company Admin', 'Branch Admin'])->get();

        $branches = $company->branches;

        return view('company.users-create', compact('company', 'roles', 'branches'));
    }

    /**
     * Store new user.
     */
    public function usersStore(Request $request, Company $company)
    {
        // Ensure user belongs to this company
        if (auth()->user()->company_id !== $company->id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:Company Admin,Branch Admin',
            'branch_id' => 'nullable|exists:branches,id',
            'password_hint' => 'nullable|string|max:255',
        ]);

        // Validate branch_id is required for Branch Admin
        if ($request->role === 'Branch Admin' && empty($request->branch_id)) {
            return back()
                ->withInput()
                ->withErrors(['branch_id' => 'Branch is required for Branch Admin role.']);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_hint' => $request->password_hint,
            'company_id' => $company->id,
            'branch_id' => $request->role === 'Branch Admin' ? $request->branch_id : null,
            'is_active' => true,
        ]);

        // Assign role
        $user->assignRole($request->role);

        return redirect()->route('company.users.index', $company)
            ->with('success', 'User created successfully!');
    }

    /**
     * Show user edit form.
     */
    public function usersEdit(Company $company, User $user)
    {
        // Ensure user belongs to this company
        if ($user->company_id !== $company->id) {
            abort(403, 'Access denied.');
        }

        // Only allow editing Company Admin and Branch Admin roles
        $roles = \Spatie\Permission\Models\Role::whereIn('name', ['Company Admin', 'Branch Admin'])->get();

        $branches = $company->branches;

        return view('company.users-edit', compact('company', 'user', 'roles', 'branches'));
    }

    /**
     * Update user.
     */
    public function usersUpdate(Request $request, Company $company, User $user)
    {
        // Ensure user belongs to this company
        if ($user->company_id !== $company->id) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:Company Admin,Branch Admin',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Update role
        $user->syncRoles([$request->role]);

        // Update branch assignment
        if ($request->role === 'Branch Admin') {
            $user->update(['branch_id' => $request->branch_id]);
        } else {
            $user->update(['branch_id' => null]);
        }

        return redirect()->route('company.users.index', $company)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Delete user.
     */
    public function usersDestroy(Company $company, User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Ensure user belongs to this company
        if ($user->company_id !== $company->id) {
            abort(403, 'Access denied.');
        }

        $user->delete();

        return redirect()->route('company.users.index', $company)
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Display branches list for this company.
     */
    public function branchesIndex(Company $company)
    {
        // Ensure user belongs to this company
        if (auth()->user()->company_id !== $company->id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        $branches = $company->branches()->with('currentSubscription', 'users')->paginate(15);

        return view('company.branches.index', compact('company', 'branches'));
    }

    /**
     * Show branch create form.
     */
    public function branchesCreate(Company $company)
    {
        // Ensure user belongs to this company
        if (auth()->user()->company_id !== $company->id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        return view('company.branches.create', compact('company'));
    }

    /**
     * Store new branch.
     */
    public function branchesStore(Request $request, Company $company)
    {
        // Ensure user belongs to this company
        if (auth()->user()->company_id !== $company->id && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Generate code from name if not provided
        $code = $request->code ?: strtoupper(Str::substr(Str::slug($request->name), 0, 10)) . rand(100, 999);

        $branch = $company->branches()->create([
            'name' => $request->name,
            'code' => $code,
            'address' => $request->address,
            'phone' => $request->phone,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => true,
        ]);

        return redirect()->route('company.branches.index', $company)
            ->with('success', 'Branch created successfully!');
    }

    /**
     * Show branch edit form.
     */
    public function branchesEdit(Company $company, Branch $branch)
    {
        // Ensure branch belongs to this company
        if ($branch->company_id !== $company->id) {
            abort(403, 'Access denied.');
        }

        return view('company.branches.edit', compact('company', 'branch'));
    }

    /**
     * Update branch.
     */
    public function branchesUpdate(Request $request, Company $company, Branch $branch)
    {
        // Ensure branch belongs to this company
        if ($branch->company_id !== $company->id) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        $branch->update([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('company.branches.index', $company)
            ->with('success', 'Branch updated successfully!');
    }

    /**
     * Delete branch.
     */
    public function branchesDestroy(Company $company, Branch $branch)
    {
        // Ensure branch belongs to this company
        if ($branch->company_id !== $company->id) {
            abort(403, 'Access denied.');
        }

        $branch->delete();

        return redirect()->route('company.branches.index', $company)
            ->with('success', 'Branch deleted successfully!');
    }

    /**
     * Switch to branch context (Company Admin acts as Branch Admin for this branch)
     */
    public function switchToBranch(Company $company, Branch $branch)
    {
        // Ensure user is Company Admin or Superadmin (with company context) and branch belongs to this company
        $user = auth()->user();
        $isAuthorized = ($user->isCompanyAdmin() && $user->company_id === $company->id)
            || ($user->isSuperAdmin() && session('company_id') === $company->id);

        if (!$isAuthorized || $branch->company_id !== $company->id) {
            abort(403, 'Access denied.');
        }

        // Set the active branch in session
        session(['active_branch_id' => $branch->id]);

        return redirect()->route('branch.dashboard')
            ->with('success', "Switched to {$branch->name}");
    }

    /**
     * Switch back to company context
     */
    public function switchToCompany()
    {
        // Clear the active branch from session
        session()->forget('active_branch_id');

        // Redirect back to company dashboard
        $company = auth()->user()->company;
        if ($company) {
            return redirect()->route('company.branches.index', $company)
                ->with('success', 'Switched back to Company Admin');
        }

        return redirect('/')->with('success', 'Switched back to Company Admin');
    }
}
