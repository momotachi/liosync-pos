@extends('layouts.admin')

@section('title', 'Subscription Status')

@section('content')
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription Status</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your branch subscription</p>
    </header>

    @if($subscription)
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $subscription->subscriptionPlan->name }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $subscription->subscriptionPlan->description }}</p>
                </div>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
                        'active' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'expired' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                    ];
                @endphp
                <span class="px-3 py-1 rounded-lg text-sm font-medium {{ $statusColors[$subscription->status] }}">
                    {{ ucfirst($subscription->status) }}
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Start Date</p>
                    <p class="font-semibold">{{ $subscription->start_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">End Date</p>
                    <p class="font-semibold">{{ $subscription->end_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Monthly Price</p>
                    <p class="font-semibold">Rp {{ number_format($subscription->subscriptionPlan->price, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Days Remaining</p>
                    <p class="font-semibold @if($subscription->remaining_days > 7) text-emerald-600 @elseif($subscription->remaining_days > 0) text-yellow-600 @else text-red-600 @endif">
                        {{ $subscription->remaining_days }} days
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-border-light dark:border-border-dark">
                <a href="{{ route('subscription.history') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    View History
                </a>
                <a href="{{ route('subscription.purchase') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                    Renew/Upgrade
                </a>
            </div>
        </div>
    @else
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-12 text-center">
            <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600">subscriptions</span>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-4">No Active Subscription</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-2 mb-6">Subscribe to access all features</p>
            <a href="{{ route('subscription.purchase') }}" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                <span class="material-symbols-outlined mr-2">shopping_cart</span>
                Subscribe Now
            </a>
        </div>
    @endif
@endsection
