@extends('layouts.company')

@section('title', 'Dashboard - ' . $company->name)

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Welcome back, {{ auth()->user()->name }}!</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Branches Count -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Branches</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $company->branches()->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-primary text-2xl">store</span>
            </div>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Active Subscriptions</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $company->branches()->whereHas('subscriptions', function($q) { $q->where('status', 'active'); })->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-2xl">card_membership</span>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Users</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $company->users()->count() }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-2xl">people</span>
            </div>
        </div>
    </div>

    <!-- Pending Payments -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Pending Payments</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    @php $pendingCount = \App\Models\SubscriptionPayment::whereHas('branchSubscription.branch', function($q) use ($company) { return $q->where('company_id', $company->id); })->where('status', 'pending')->count(); @endphp
                    {{ $pendingCount }}
                </p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400 text-2xl">pending_actions</span>
            </div>
        </div>
        @if($pendingCount > 0)
        <a href="{{ route('company.subscriptions.index', $company) }}" class="mt-3 inline-flex items-center text-sm text-primary hover:text-primary-dark">
            <span>View Payments</span>
            <span class="material-symbols-outlined text-sm ml-1">arrow_forward</span>
        </a>
        @endif
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('company.branches.index', $company) }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                <span class="material-symbols-outlined text-primary">add_business</span>
            </div>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Manage Branches</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">View and manage your branches</p>
            </div>
        </a>

        <a href="{{ route('company.users.index', $company) }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">person_add</span>
            </div>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Add Users</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Create user accounts</p>
            </div>
        </a>

        <a href="{{ route('company.subscriptions.purchase', $company) }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center mr-3">
                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">shopping_cart</span>
            </div>
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Purchase Subscription</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Buy or renew subscriptions</p>
            </div>
        </a>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Branches List -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Your Branches</h2>
            <a href="{{ route('company.branches.index', $company) }}" class="text-sm text-primary hover:text-primary-dark">View All</a>
        </div>
        @if($company->branches->count() > 0)
        <div class="space-y-3">
            @foreach($company->branches->take(5) as $branch)
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <div class="flex items-center">
                    <span class="material-symbols-outlined text-gray-400 mr-3">storefront</span>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $branch->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $branch->address ?? 'No address' }}</p>
                    </div>
                </div>
                @if($branch->currentSubscription)
                <span class="px-2 py-1 text-xs font-medium rounded {{ $branch->currentSubscription->status === 'active' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' }}">
                    {{ ucfirst($branch->currentSubscription->status) }}
                </span>
                @else
                <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                    No Subscription
                </span>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <p class="text-center text-gray-500 dark:text-gray-400 py-8">No branches yet. <a href="{{ route('company.branches.create', $company) }}" class="text-primary hover:text-primary-dark">Create your first branch</a></p>
        @endif
    </div>

    <!-- Recent Users -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Users</h2>
            <a href="{{ route('company.users.index', $company) }}" class="text-sm text-primary hover:text-primary-dark">View All</a>
        </div>
        @if($company->users->count() > 0)
        <div class="space-y-3">
            @foreach($company->users->take(5) as $user)
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <div class="flex items-center">
                    <img src="https://ui-avatars.com/api/?name={{ $user->name }}&background=random" class="w-8 h-8 rounded-full mr-3">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                    {{ $user->roles->first()->name ?? 'User' }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-center text-gray-500 dark:text-gray-400 py-8">No users yet. <a href="{{ route('company.users.index', $company) }}" class="text-primary hover:text-primary-dark">Add your first user</a></p>
        @endif
    </div>
</div>
@endsection
