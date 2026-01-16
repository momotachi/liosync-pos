@extends('layouts.superadmin')

@section('title', 'Subscription Details')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <a href="{{ route('superadmin.subscriptions.index') }}" class="text-primary hover:underline mb-2 inline-block">
                &larr; Back to Subscriptions
            </a>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription Details</h2>
            @if($subscription->branch && $subscription->branch->company)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $subscription->branch->company->name }} - {{ $subscription->branch->name }}
                </p>
            @else
                <p class="text-sm text-red-500 mt-1">
                    ⚠️ Branch not found for this subscription
                </p>
            @endif
        </div>
    </header>

    <!-- Subscription Status Card -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                @if($subscription->subscriptionPlan)
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $subscription->subscriptionPlan->name }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $subscription->subscriptionPlan->description }}</p>
                @else
                    <h3 class="text-xl font-bold text-red-600 dark:text-red-400">⚠️ Subscription Plan Not Found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">This subscription is missing its associated plan</p>
                @endif
            </div>
            @php
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
                    'active' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                    'expired' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                    'suspended' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                ];
            @endphp
            <span class="px-3 py-1 rounded-lg text-sm font-medium {{ $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ ucfirst($subscription->status) }}
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-6">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Start Date</p>
                @if($subscription->start_date)
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $subscription->start_date->format('M d, Y') }}</p>
                @else
                    <p class="font-semibold text-gray-400">-</p>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">End Date</p>
                @if($subscription->end_date)
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $subscription->end_date->format('M d, Y') }}</p>
                @else
                    <p class="font-semibold text-gray-400">-</p>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Price</p>
                @if($subscription->subscriptionPlan)
                    <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($subscription->subscriptionPlan->price, 0, ',', '.') }}/mo</p>
                @else
                    <p class="font-semibold text-gray-400">-</p>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Days Remaining</p>
                <p class="font-semibold @if($subscription->remaining_days > 7) text-emerald-600 @elseif($subscription->remaining_days > 0) text-yellow-600 @else text-red-600 @endif">
                    {{ $subscription->remaining_days }} days
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap items-center gap-3 mt-6 pt-6 border-t border-border-light dark:border-border-dark">
            <!-- Adjust Period -->
            <div class="flex items-center gap-2 flex-wrap p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-full">Adjust Period:</span>
                <!-- Days -->
                <div class="flex items-center gap-1">
                    <button type="button"
                            onclick="adjustPeriod({{ $subscription->id }}, 0, -15)"
                            class="px-2 py-1 bg-red-500 text-white text-xs font-medium rounded-lg hover:bg-red-600 transition-colors">
                        -15 Days
                    </button>
                    <button type="button"
                            onclick="adjustPeriod({{ $subscription->id }}, 0, -7)"
                            class="px-2 py-1 bg-orange-500 text-white text-xs font-medium rounded-lg hover:bg-orange-600 transition-colors">
                        -7 Days
                    </button>
                    <button type="button"
                            onclick="adjustPeriod({{ $subscription->id }}, 0, -1)"
                            class="px-2 py-1 bg-orange-500 text-white text-xs font-medium rounded-lg hover:bg-orange-600 transition-colors">
                        -1 Day
                    </button>
                    <button type="button"
                            onclick="adjustPeriod({{ $subscription->id }}, 0, 1)"
                            class="px-2 py-1 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition-colors">
                        +1 Day
                    </button>
                    <button type="button"
                            onclick="adjustPeriod({{ $subscription->id }}, 0, 7)"
                            class="px-2 py-1 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition-colors">
                        +7 Days
                    </button>
                    <button type="button"
                            onclick="adjustPeriod({{ $subscription->id }}, 0, 15)"
                            class="px-2 py-1 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition-colors">
                        +15 Days
                    </button>
                </div>
                <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>
                <!-- Months -->
                <div class="flex items-center gap-1">
                    <button type="button"
                            onclick="adjustPeriod({{ $subscription->id }}, -1, 0)"
                            class="px-2 py-1 bg-red-500 text-white text-xs font-medium rounded-lg hover:bg-red-600 transition-colors">
                        -1 Month
                    </button>
                    <button type="button"
                            onclick="adjustPeriod({{ $subscription->id }}, 1, 0)"
                            class="px-2 py-1 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition-colors">
                        +1 Month
                    </button>
                    <button type="button"
                            onclick="adjustPeriod({{ $subscription->id }}, 3, 0)"
                            class="px-2 py-1 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition-colors">
                        +3 Months
                    </button>
                    <button type="button"
                            onclick="adjustPeriod({{ $subscription->id }}, 6, 0)"
                            class="px-2 py-1 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition-colors">
                        +6 Months
                    </button>
                </div>
            </div>

            @if($subscription->status === 'active')
                <form action="{{ route('superadmin.subscriptions.toggle', $subscription) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="status" value="suspended">
                    <button type="submit"
                            onclick="return confirm('Suspend this subscription?')"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                        Suspend
                    </button>
                </form>
            @elseif($subscription->status === 'suspended')
                <form action="{{ route('superadmin.subscriptions.toggle', $subscription) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="status" value="active">
                    <button type="submit"
                            onclick="return confirm('Activate this subscription?')"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        Activate
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Payment History -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden mb-6">
        <div class="p-6 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Payment History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                    <tr>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Amount & Duration</th>
                        <th class="px-6 py-4">Payment Proof</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Confirmed By</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($subscription->payments as $payment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                @if($payment->payment_date)
                                    {{ $payment->payment_date->format('M d, Y') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
                                @if($payment->months)
                                    <div class="text-xs text-gray-500">{{ $payment->months }} month{{ $payment->months > 1 ? 's' : '' }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($payment->proof_image)
                                    <button type="button"
                                            onclick="viewProof('{{ asset('storage/' . $payment->proof_image) }}')"
                                            class="flex items-center gap-1 text-blue-500 hover:text-blue-700">
                                        <span class="material-symbols-outlined text-sm">image</span>
                                        View Proof
                                    </button>
                                @else
                                    <span class="text-gray-400">No proof</span>
                                @endif
                            </td>
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
                            <td class="px-6 py-4 text-right">
                                @if($payment->status === 'pending')
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                onclick="confirmPayment({{ $payment->id }})"
                                                class="px-3 py-1 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                            Confirm
                                        </button>
                                        <button type="button"
                                                onclick="rejectPayment({{ $payment->id }})"
                                                class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                            Reject
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">No payment history</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function adjustPeriod(subscriptionId, months, days) {
        let message = '';
        const hasMonths = months !== 0;
        const hasDays = days !== 0;

        if (hasMonths && hasDays) {
            const monthAction = months > 0 ? 'Add' : 'Reduce';
            const dayAction = days > 0 ? 'add' : 'reduce';
            message = `${monthAction} ${Math.abs(months)} month(s) and ${dayAction} ${Math.abs(days)} day(s) to this subscription?`;
        } else if (hasMonths) {
            const action = months > 0 ? 'Add' : 'Reduce';
            message = `${action} ${Math.abs(months)} month(s) to this subscription?`;
        } else if (hasDays) {
            const action = days > 0 ? 'Add' : 'Reduce';
            message = `${action} ${Math.abs(days)} day(s) to this subscription?`;
        }

        if (!confirm(message)) {
            return;
        }

        const body = {};
        if (hasMonths) body.months = months;
        if (hasDays) body.days = days;

        fetch(`/superadmin/subscriptions/${subscriptionId}/adjust-period`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(body)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adjusting period');
        });
    }

    function confirmPayment(paymentId) {
        if (confirm('Confirm this payment?')) {
            fetch(`/superadmin/payments/${paymentId}/confirm`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error confirming payment');
            });
        }
    }

    function rejectPayment(paymentId) {
        const reason = prompt('Enter rejection reason:');
        if (reason) {
            fetch(`/superadmin/payments/${paymentId}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error rejecting payment');
            });
        }
    }

    function viewProof(imageUrl) {
        window.open(imageUrl, '_blank');
    }
</script>
@endpush
