@extends('layouts.company')

@section('title', 'Branches - ' . $company->name)

@section('content')
<!-- Page Header -->
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Branches</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your company branches</p>
    </div>
    <a href="{{ route('company.branches.create', $company) }}"
       class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
        <span class="material-symbols-outlined mr-2 text-sm">add</span>
        Add Branch
    </a>
</div>

<!-- Branches Table -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
            <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                <tr>
                    <th class="px-6 py-4">Branch</th>
                    <th class="px-6 py-4">Address</th>
                    <th class="px-6 py-4">Phone</th>
                    <th class="px-6 py-4">Users</th>
                    <th class="px-6 py-4">Subscription</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                @forelse($branches as $branch)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                        {{ $branch->name }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $branch->address ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $branch->phone ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center text-gray-900 dark:text-white">
                            <span class="material-symbols-outlined text-sm mr-1">people</span>
                            {{ $branch->users->count() }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($branch->currentSubscription)
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $branch->currentSubscription->subscriptionPlan->name }}</p>
                            <p class="text-xs text-gray-500">
                                @if($branch->currentSubscription->end_date)
                                Expires: {{ $branch->currentSubscription->end_date->format('M d, Y') }}
                                @endif
                            </p>
                        </div>
                        @else
                        <span class="text-gray-400">No subscription</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($branch->subscription_status_text === 'Active')
                        <span class="px-2 py-1 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                            {{ $branch->subscription_status_text }}
                        </span>
                        @elseif($branch->subscription_status_text === 'Expired')
                        <span class="px-2 py-1 rounded-lg text-xs font-medium bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                            {{ $branch->subscription_status_text }}
                        </span>
                        @else
                        <span class="px-2 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                            {{ $branch->subscription_status_text }}
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('company.branches.switch', [$company, $branch]) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-primary hover:bg-primary-dark text-white rounded-lg text-xs font-medium transition-colors"
                               title="Manage Branch Operations">
                                <span class="material-symbols-outlined text-sm mr-1">storefront</span>
                                Manage
                            </a>
                            <a href="{{ route('company.branches.edit', [$company, $branch]) }}"
                               class="text-gray-400 hover:text-primary transition-colors"
                               title="Edit Branch Details">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <form action="{{ route('company.branches.destroy', [$company, $branch]) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this branch?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors"
                                        title="Delete Branch">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600">store</span>
                        <p class="text-gray-500 dark:text-gray-400 mt-4">No branches found</p>
                        <a href="{{ route('company.branches.create', $company) }}" class="inline-flex items-center mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                            <span class="material-symbols-outlined mr-2 text-sm">add</span>
                            Create Your First Branch
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($branches->hasPages())
    <div class="p-4 border-t border-border-light dark:border-border-dark">
        {{ $branches->links() }}
    </div>
    @endif
</div>
@endsection
