@extends('layouts.admin')

@section('title', 'Subscription Plans')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription Plans</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Choose the perfect plan for your branch</p>
        </div>
    </header>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
                <!-- Plan Header -->
                <div class="p-6 border-b border-border-light dark:border-border-dark">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                        <span class="px-2 py-1 bg-emerald-100 text-emerald-600 rounded-lg text-xs font-medium">Active</span>
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
                    @if(auth()->user()?->isSuperAdmin())
                        <span class="block w-full text-center px-4 py-2 text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg">
                            Superadmin cannot purchase
                        </span>
                    @elseif(auth()->user()?->isCompanyAdmin())
                        <a href="{{ route('company.subscriptions.purchase', auth()->user()->company_id) }}"
                           class="block w-full text-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                            Subscribe Now
                        </a>
                    @else
                        <a href="{{ route('subscription.purchase') }}"
                           class="block w-full text-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                            Subscribe Now
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($plans->isEmpty())
        <div class="text-center py-12">
            <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600">subscriptions</span>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-4">No subscription plans available</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Please contact superadmin to set up subscription plans</p>
        </div>
    @endif
@endsection
