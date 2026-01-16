@extends('layouts.superadmin')

@section('title', 'Subscription Plans')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription Plans</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage subscription tiers and pricing</p>
        </div>
        @if(auth()->check() && auth()->user()->isSuperAdmin())
        <div class="flex items-center gap-3">
            <a href="{{ route('superadmin.subscription-plans.create') }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                <span class="material-symbols-outlined mr-2">add</span>
                Create Plan
            </a>
        </div>
        @endif
    </header>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
                <!-- Plan Header -->
                <div class="p-6 border-b border-border-light dark:border-border-dark">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                        @if($plan->is_active)
                            <span class="px-2 py-1 bg-emerald-100 text-emerald-600 rounded-lg text-xs font-medium">Active</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-medium">Inactive</span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-primary">Rp {{ number_format($plan->price, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">per month per branch</p>
                </div>

                <!-- Plan Details -->
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Max Branches</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            @if($plan->max_branches)
                                {{ $plan->max_branches }}
                            @else
                                Unlimited
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Max Users</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            @if($plan->max_users)
                                {{ $plan->max_users }}
                            @else
                                Unlimited
                            @endif
                        </span>
                    </div>

                    <!-- Features -->
                    <div class="pt-4 border-t border-border-light dark:border-border-dark">
                        <p class="text-sm font-medium text-gray-900 dark:text-white mb-3">Features</p>
                        <div class="space-y-2">
                            @foreach($plan->features_array as $feature => $enabled)
                                @if($enabled)
                                    <div class="flex items-center text-sm">
                                        <span class="material-symbols-outlined text-emerald-500 mr-2">check_circle</span>
                                        <span class="text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="p-6 bg-gray-50 dark:bg-gray-800/50 border-t border-border-light dark:border-border-dark">
                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('superadmin.subscription-plans.edit', $plan) }}"
                           class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <span class="material-symbols-outlined mr-2 text-sm">edit</span>
                            Edit
                        </a>
                        <form action="{{ route('superadmin.subscription-plans.destroy', $plan) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Are you sure you want to delete this plan?')"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 transition-colors">
                                <span class="material-symbols-outlined mr-2 text-sm">delete</span>
                                Delete
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                        Contact superadmin to modify plans
                    </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($plans->isEmpty())
        <div class="text-center py-12">
            <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600">subscriptions</span>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-4">No subscription plans found</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Create your first subscription plan to get started</p>
        </div>
    @endif
@endsection
