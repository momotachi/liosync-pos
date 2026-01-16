@extends('layouts.superadmin')

@section('title', 'Branch Users - ' . $branch->name)

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('company.branches.index', $company) }}" class="hover:text-primary transition-colors">Branches</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span>{{ $branch->name }}</span>
    </div>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $branch->name }} - Users</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage users for this branch</p>
        </div>
        <a href="{{ route('company.branches.users.create', [$company, $branch]) }}"
           class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
            <span class="material-symbols-outlined text-sm mr-1">person_add</span>
            Add User
        </a>
    </div>
</div>

<!-- Users Table -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
    @if($users->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-light dark:divide-border-dark">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-card-light dark:bg-card-dark divide-y divide-border-light dark:divide-border-dark">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                                        <span class="material-symbols-outlined text-primary">person</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->created_at->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">email</span>
                                        {{ $user->email }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->roles->count() > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                        {{ $roles[$user->roles->first()->name] ?? $user->roles->first()->name }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400 dark:text-gray-600">No role</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->branch_id)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                        <span class="material-symbols-outlined text-xs mr-1">store</span>
                                        {{ $user->branch->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                        <span class="material-symbols-outlined text-xs mr-1">business</span>
                                        All Branches
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                        <span class="material-symbols-outlined text-xs mr-1">check_circle</span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                        <span class="material-symbols-outlined text-xs mr-1">cancel</span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Button -->
                                    <a href="{{ route('company.branches.users.edit', [$company, $branch, $user]) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-medium transition-colors">
                                        <span class="material-symbols-outlined text-sm mr-1">edit</span>
                                        Edit
                                    </a>
                                    <!-- Delete Button -->
                                    <form action="{{ route('company.branches.users.destroy', [$company, $branch, $user]) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg text-xs font-medium transition-colors">
                                            <span class="material-symbols-outlined text-sm mr-1">delete</span>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-border-light dark:border-border-dark">
                {{ $users->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                <span class="material-symbols-outlined text-3xl text-gray-400 dark:text-gray-600">people</span>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No users yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Add users to this branch to get started</p>
            <a href="{{ route('company.branches.users.create', [$company, $branch]) }}"
               class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
                <span class="material-symbols-outlined text-sm mr-1">person_add</span>
                Add User
            </a>
        </div>
    @endif
</div>
@endsection
