@extends('layouts.admin')

@section('title', 'Subscription Details')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <a href="{{ route('company.subscriptions.index', $subscription->branch->company) }}" class="text-primary hover:underline mb-2 inline-block">
                &larr; Back to Subscriptions
            </a>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription Details</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $subscription->branch->name }}</p>
        </div>
    </header>

    <!-- Subscription Info -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-6">
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
                <p class="font-semibold text-gray-900 dark:text-white">{{ $subscription->start_date->format('M d, Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">End Date</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $subscription->end_date->format('M d, Y') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Price</p>
                <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($subscription->subscriptionPlan->price, 0, ',', '.') }}/mo</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Days Remaining</p>
                <p class="font-semibold @if($subscription->remaining_days > 7) text-emerald-600 @elseif($subscription->remaining_days > 0) text-yellow-600 @else text-red-600 @endif">
                    {{ $subscription->remaining_days }} days
                </p>
            </div>
        </div>

        <!-- Extend Form -->
        <div class="flex items-center gap-3 mt-6 pt-6 border-t border-border-light dark:border-border-dark">
            <select id="extendMonths" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800">
                <option value="1">1 Month</option>
                <option value="3">3 Months</option>
                <option value="6">6 Months</option>
                <option value="12">12 Months</option>
            </select>
            <button type="button"
                    onclick="extendSubscription({{ $subscription->id }})"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                <span class="material-symbols-outlined text-sm mr-1">extend</span>
                Extend Subscription
            </button>
        </div>
    </div>

    <!-- Payment History (Read-only for company admin) -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="p-6 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Payment History</h3>
            <p class="text-sm text-gray-500 mt-1">Contact superadmin for payment confirmation</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold border-b border-border-light dark:border-border-dark">
                    <tr>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Confirmed By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($subscription->payments as $payment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                @if($payment->payment_date)
                                    {{ $payment->payment_date->format('M d, Y') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
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
                                @if($payment->confirmedBy)
                                    {{ $payment->confirmedBy->name }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400">No payment history</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function extendSubscription(subscriptionId) {
        const months = document.getElementById('extendMonths').value;
        if (confirm(`Extend subscription by ${months} month(s)?`)) {
            axios.post(`/company/{{ $subscription->branch->company }}/subscriptions/${subscriptionId}/extend`, {
                months: months
            })
            .then(response => {
                alert(response.data.message);
                location.reload();
            })
            .catch(error => {
                alert('Error extending subscription');
            });
        }
    }
</script>
@endpush
