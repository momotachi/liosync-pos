@extends('layouts.admin')

@section('title', 'Payment History')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <a href="{{ route('subscription.index') }}" class="text-primary hover:underline mb-2 inline-block">
                &larr; Back to Subscription
            </a>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Payment History</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View all your subscription payments</p>
        </div>
    </header>

    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                    <tr>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4">Plan</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($subscriptions as $subscription)
                        @foreach($subscription->payments as $payment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4">
                                    {{ $subscription->start_date->format('M d, Y') }} -
                                    {{ $subscription->end_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ $subscription->subscriptionPlan->name }}
                                </td>
                                <td class="px-6 py-4 font-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    @if($payment->status === 'pending')
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-600 rounded-lg text-xs font-medium">Pending</span>
                                    @elseif($payment->status === 'confirmed')
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-600 rounded-lg text-xs font-medium">Confirmed</span>
                                    @else
                                        <span class="px-2 py-1 bg-red-100 text-red-600 rounded-lg text-xs font-medium">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($payment->payment_date)
                                        {{ $payment->payment_date->format('M d, Y') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600">receipt</span>
                                <p class="text-gray-500 dark:text-gray-400 mt-4">No payment history found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
