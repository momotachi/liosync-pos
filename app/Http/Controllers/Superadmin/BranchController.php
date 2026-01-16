<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class BranchController extends Controller
{
    /**
     * Show branches for a company.
     */
    public function index(Company $company)
    {
        $branches = $company->branches()->with('users')->paginate(15);

        return view('superadmin.branches.index', compact('company', 'branches'));
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create(Company $company)
    {
        return view('superadmin.branches.create', compact('company'));
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Check if code is unique within the company
        $exists = $company->branches()->where('code', $request->code)->exists();
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Branch code already exists for this company.');
        }

        $branch = $company->branches()->create([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_active' => $request->is_active ?? true,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('company.branches.index', $company)
            ->with('success', 'Branch created successfully.');
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit(Company $company, Branch $branch)
    {
        return view('superadmin.branches.edit', compact('company', 'branch'));
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request, Company $company, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Check if code is unique within the company (excluding this branch)
        $exists = $company->branches()
            ->where('code', $request->code)
            ->where('id', '!=', $branch->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Branch code already exists for this company.');
        }

        $branch->update([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_active' => $request->is_active ?? true,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('company.branches.index', $company)
            ->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Company $company, Branch $branch)
    {
        // Check if branch has users
        if ($branch->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete branch with users. Please reassign or delete users first.');
        }

        $branch->delete();

        return redirect()->route('company.branches.index', $company)
            ->with('success', 'Branch deleted successfully.');
    }

    /**
     * Show users for a branch.
     */
    public function usersIndex(Company $company, Branch $branch)
    {
        $branchId = $branch->id;
        $companyId = $company->id;

        // Get users for the branch
        $branchUsers = $branch->users()->with('roles')->get();

        // ALSO get users with branch_id = null from the same company
        // (These are company-wide users that should be visible to Branch Admin)
        $companyWideUsers = User::where('company_id', $companyId)
            ->whereNull('branch_id')
            ->with('roles')
            ->get();

        // Merge both collections
        $allUsers = $branchUsers->merge($companyWideUsers);

        // Paginate the merged collection
        $page = request()->get('page', 1);
        $perPage = 15;
        $users = new \Illuminate\Pagination\LengthAwarePaginator(
            $allUsers->forPage($page, $perPage),
            $allUsers->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $roles = [
            'Branch Admin' => 'Branch Admin',
            'Stock Admin' => 'Stock Admin',
            'Cashier' => 'Cashier',
        ];

        return view('superadmin.branches.users', compact('company', 'branch', 'users', 'roles'));
    }

    /**
     * Show the form for creating a new branch user.
     */
    public function usersCreate(Company $company, Branch $branch)
    {
        $roles = [
            'Branch Admin' => 'Branch Admin',
            'Stock Admin' => 'Stock Admin',
            'Cashier' => 'Cashier',
        ];

        return view('superadmin.branches.users-create', compact('company', 'branch', 'roles'));
    }

    /**
     * Store a newly created branch user.
     */
    public function usersStore(Request $request, Company $company, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:Branch Admin,Stock Admin,Cashier',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_hint' => $request->password_hint ?? $request->password,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        // Assign role
        $user->assignRole($request->role);

        return redirect()->route('company.branches.users.index', [$company, $branch])
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing a branch user.
     */
    public function usersEdit(Company $company, Branch $branch, User $user)
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

        // Verify user belongs to this branch
        if ($user->branch_id !== $branch->id) {
            abort(404, 'User not found in this branch.');
        }

        $roles = [
            'Branch Admin' => 'Branch Admin',
            'Stock Admin' => 'Stock Admin',
            'Cashier' => 'Cashier',
        ];

        return view('superadmin.branches.users-edit', compact('company', 'branch', 'user', 'roles'));
    }

    /**
     * Update a branch user.
     */
    public function usersUpdate(Request $request, Company $company, Branch $branch, User $user)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Authorization: Superadmin and Company Admin can update
        if (!$currentUser->isSuperAdmin() && !$currentUser->isCompanyAdmin()) {
            abort(403, 'Access denied');
        }

        // Company Admin can only update users from their own company
        if (!$currentUser->isSuperAdmin() && $currentUser->company_id !== $company->id) {
            abort(403, 'Access denied');
        }

        // Verify user belongs to this branch
        if ($user->branch_id !== $branch->id) {
            abort(403, 'User does not belong to this branch.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:8',
            'role' => 'required|in:Branch Admin,Stock Admin,Cashier',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
                'password_hint' => $request->password_hint ?? $request->password,
            ]);
        }

        // Update role
        $user->syncRoles([$request->role]);

        return redirect()->route('company.branches.users.index', [$company, $branch])
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from branch.
     */
    public function usersDestroy(Company $company, Branch $branch, User $user)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Authorization: Superadmin and Company Admin can delete
        if (!$currentUser->isSuperAdmin() && !$currentUser->isCompanyAdmin()) {
            abort(403, 'Access denied');
        }

        // Company Admin can only delete users from their own company
        if (!$currentUser->isSuperAdmin() && $currentUser->company_id !== $company->id) {
            abort(403, 'Access denied');
        }

        // Verify user belongs to this branch
        if ($user->branch_id !== $branch->id) {
            abort(403, 'User does not belong to this branch.');
        }

        $user->delete();

        return redirect()->route('company.branches.users.index', [$company, $branch])
            ->with('success', 'User deleted successfully.');
    }
}
