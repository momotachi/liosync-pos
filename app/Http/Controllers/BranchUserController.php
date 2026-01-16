<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class BranchUserController extends Controller
{
    /**
     * Display all users for the current branch.
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        // Only Branch Admin can access this
        if (!$user->isBranchAdmin()) {
            abort(403, 'Access denied. Only Branch Admin can manage users.');
        }

        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        // Get users for the branch
        if ($branchId) {
            $branch = \App\Models\Branch::find($branchId);
            if ($branch) {
                // Get users through branch relationship
                $branchUsers = $branch->users()->with('roles')->get();

                // ALSO get users with branch_id = null from the same company
                // (These are company-wide users that should be visible to Branch Admin)
                $companyWideUsers = User::where('company_id', $companyId)
                    ->whereNull('branch_id')
                    ->with('roles')
                    ->get();

                // Merge both collections
                $allUsers = $branchUsers->merge($companyWideUsers);
            } else {
                $allUsers = collect();
            }
        } else {
            // Fallback: query by company_id if branch_id is not set
            $allUsers = User::where('company_id', $companyId)
                ->with('roles')
                ->get();
        }

        // Filter out the current user
        $allUsers = $allUsers->filter(function ($u) use ($user) {
            return $u->id !== $user->id;
        });

        // Filter out Branch Admins
        $users = $allUsers->filter(function ($u) {
            return !$u->isBranchAdmin();
        });

        // Convert to paginated collection
        $perPage = 15;
        $currentPage = request()->get('page', 1);
        $currentPageItems = $users->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedUsers = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $users->count(),
            $perPage,
            $currentPage
        );
        $paginatedUsers->withPath($request->url());

        $roles = [
            'Stock Admin' => 'Stock Admin (Kasir Pembelian)',
            'Cashier' => 'Cashier (Kasir Penjualan)',
        ];

        return view('admin.users.index', ['users' => $paginatedUsers, 'roles' => $roles]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        // Only Branch Admin can access this
        if (!$user->isBranchAdmin()) {
            abort(403, 'Access denied. Only Branch Admin can manage users.');
        }

        $roles = [
            'Stock Admin' => 'Stock Admin (Kasir Pembelian)',
            'Cashier' => 'Cashier (Kasir Penjualan)',
        ];

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        // Only Branch Admin can access this
        if (!$authUser->isBranchAdmin()) {
            abort(403, 'Access denied. Only Branch Admin can manage users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:Stock Admin,Cashier',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_hint' => $request->password_hint ?? $request->password,
            'company_id' => $authUser->company_id,
            'branch_id' => $authUser->branch_id,
        ]);

        // Assign role
        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Request $request, User $userData)
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        // Only Branch Admin can access this
        if (!$authUser->isBranchAdmin()) {
            abort(403, 'Access denied. Only Branch Admin can manage users.');
        }

        // Verify user belongs to the same company
        if ($userData->company_id !== $authUser->company_id) {
            abort(403, 'User does not belong to your company.');
        }

        // Only allow editing users from the same branch OR company-wide users (branch_id = null)
        // Branch Admin can only edit their own branch users or company-wide users
        if ($userData->branch_id !== null && $userData->branch_id !== $authUser->branch_id) {
            abort(403, 'User does not belong to your branch.');
        }

        // Don't allow editing other Branch Admins
        if ($userData->isBranchAdmin()) {
            abort(403, 'Cannot edit Branch Admin accounts.');
        }

        $roles = [
            'Stock Admin' => 'Stock Admin (Kasir Pembelian)',
            'Cashier' => 'Cashier (Kasir Penjualan)',
        ];

        return view('admin.users.edit', ['user' => $userData, 'roles' => $roles]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $userData)
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        // Only Branch Admin can access this
        if (!$authUser->isBranchAdmin()) {
            abort(403, 'Access denied. Only Branch Admin can manage users.');
        }

        // Verify user belongs to the same company
        if ($userData->company_id !== $authUser->company_id) {
            abort(403, 'User does not belong to your company.');
        }

        // Only allow editing users from the same branch OR company-wide users (branch_id = null)
        if ($userData->branch_id !== null && $userData->branch_id !== $authUser->branch_id) {
            abort(403, 'User does not belong to your branch.');
        }

        // Prevent editing self
        if ($userData->id === $authUser->id) {
            return redirect()->back()
                ->with('error', 'Cannot edit your own account from this page.');
        }

        // Don't allow editing other Branch Admins
        if ($userData->isBranchAdmin()) {
            return redirect()->back()
                ->with('error', 'Cannot edit Branch Admin accounts.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $userData->id,
            'password' => 'nullable|confirmed|min:8',
            'role' => 'required|in:Stock Admin,Cashier',
        ]);

        $userData->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $userData->update([
                'password' => Hash::make($request->password),
                'password_hint' => $request->password_hint ?? $request->password,
            ]);
        }

        // Update role
        $userData->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $userData)
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        // Only Branch Admin can access this
        if (!$authUser->isBranchAdmin()) {
            abort(403, 'Access denied. Only Branch Admin can manage users.');
        }

        // Verify user belongs to the same branch
        if ($userData->branch_id !== $authUser->branch_id) {
            abort(403, 'User does not belong to your branch.');
        }

        // Prevent deleting self
        if ($userData->id === $authUser->id) {
            return redirect()->back()
                ->with('error', 'Cannot delete your own account.');
        }

        // Prevent deleting other Branch Admins
        if ($userData->isBranchAdmin()) {
            return redirect()->back()
                ->with('error', 'Cannot delete Branch Admin accounts.');
        }

        $userData->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
