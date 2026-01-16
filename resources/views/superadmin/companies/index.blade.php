@extends('layouts.superadmin')

@section('title', 'Companies Management')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Companies</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage all companies in the system</p>
    </div>
    <a href="{{ route('superadmin.companies.create') }}"
       class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
        <span class="material-symbols-outlined text-sm mr-1">add</span>
        Create Company
    </a>
</div>

<!-- Companies Table -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
    @if($companies->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-light dark:divide-border-dark">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Branches</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-card-light dark:bg-card-dark divide-y divide-border-light dark:divide-border-dark">
                    @foreach($companies as $company)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center mr-3">
                                        <span class="material-symbols-outlined text-primary">business</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $company->name }}</div>
                                            @if($company->has_branches ?? true)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary/10 text-secondary" title="Multi-Branch">
                                                    <span class="material-symbols-outlined text-xs mr-0.5">account_tree</span>
                                                    Multi
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary" title="Single Company">
                                                    <span class="material-symbols-outlined text-xs mr-0.5">store</span>
                                                    Single
                                                </span>
                                            @endif
                                        </div>
                                        @if($company->address)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($company->address, 40) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                    {{ $company->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($company->email || $company->phone)
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        @if($company->email)
                                            <div class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm">email</span>
                                                {{ $company->email }}
                                            </div>
                                        @endif
                                        @if($company->phone)
                                            <div class="flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm">phone</span>
                                                {{ $company->phone }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                    <span class="material-symbols-outlined text-xs mr-1">store</span>
                                    {{ $company->branches_count ?? $company->branches->count() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                    <span class="material-symbols-outlined text-xs mr-1">group</span>
                                    {{ $company->users_count ?? $company->users->count() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($company->is_active)
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
                                    <a href="{{ route('superadmin.switch.company.enter', $company) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-secondary hover:bg-green-600 text-white rounded-lg text-xs font-medium transition-colors"
                                       title="Enter Company Admin">
                                        <span class="material-symbols-outlined text-sm mr-1">login</span>
                                        Enter
                                    </a>
                                    <a href="{{ route('superadmin.companies.edit', $company) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium transition-colors">
                                        <span class="material-symbols-outlined text-sm mr-1">edit</span>
                                        Edit
                                    </a>
                                    <form action="{{ route('superadmin.companies.destroy', $company) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this company? All branches and data will be deleted.');">
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
        @if($companies->hasPages())
            <div class="px-6 py-4 border-t border-border-light dark:border-border-dark">
                {{ $companies->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                <span class="material-symbols-outlined text-3xl text-gray-400 dark:text-gray-600">business</span>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No companies yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Get started by creating your first company</p>
            <a href="{{ route('superadmin.companies.create') }}"
               class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
                <span class="material-symbols-outlined text-sm mr-1">add</span>
                Create Company
            </a>
        </div>
    @endif
</div>
@endsection
