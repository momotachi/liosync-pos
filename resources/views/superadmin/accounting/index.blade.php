@extends('layouts.superadmin')

@section('title', 'Accounting - Superadmin')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Accounting Overview</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Financial summary across all companies</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
            </div>
            <div class="h-12 w-12 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400">payments</span>
            </div>
        </div>
    </div>

    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Purchases</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    {{ number_format($totalPurchases, 0, ',', '.') }}
                </p>
            </div>
            <div class="h-12 w-12 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">shopping_cart</span>
            </div>
        </div>
    </div>

    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Companies</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $companies->count() }}</p>
            </div>
            <div class="h-12 w-12 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">business</span>
            </div>
        </div>
    </div>

    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Net Profit</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    {{ number_format($totalProfit, 0, ',', '.') }}
                </p>
            </div>
            <div class="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">trending_up</span>
            </div>
        </div>
    </div>
</div>

<!-- Companies Table -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-border-light dark:divide-border-dark">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Company</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Branches</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revenue</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Purchases</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Profit</th>
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
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $company->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $company->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                {{ $company->branches->count() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900 dark:text-white">
                            {{ number_format($company->revenue ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm text-gray-600 dark:text-gray-400">
                            {{ number_format($company->purchases ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <span class="{{ ($company->profit ?? 0) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ number_format($company->profit ?? 0, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('superadmin.companies.branches.index', $company) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-medium transition-colors">
                                <span class="material-symbols-outlined text-sm mr-1">visibility</span>
                                View Details
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
