@extends('layouts.superadmin')

@section('title', 'Subscriptions Management')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Subscriptions</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage all branch subscriptions</p>
        </div>
    </header>

    <!-- Filters -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-6">
        <form method="GET" action="{{ route('superadmin.subscriptions.index') }}" class="flex flex-wrap items-end gap-4">
            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select id="status" name="status"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <option value="">All Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            <!-- Company Filter -->
            <div>
                <label for="company" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company</label>
                <select id="company" name="company"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <option value="">All Companies</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ $company == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search Branch -->
            <div class="flex-1 min-w-[200px]">
                <label for="branch" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Branch</label>
                <input type="text"
                       id="branch"
                       name="branch"
                       value="{{ $branch ?? '' }}"
                       placeholder="Branch name..."
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
            </div>

            <!-- Submit & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                    Filter
                </button>
                <a href="{{ route('superadmin.subscriptions.index') }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Subscriptions Table -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        @if($subscriptions->count() > 0)
            <!-- Bulk Actions -->
            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border-b border-border-light dark:border-border-dark flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="selectAll" class="w-4 h-4 text-primary rounded focus:ring-primary">
                    <label for="selectAll" class="text-sm text-gray-700 dark:text-gray-300">Select All</label>
                </div>
                <button type="button"
                        id="bulkConfirmBtn"
                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <span class="material-symbols-outlined text-sm mr-1">check_circle</span>
                    Confirm Selected
                </button>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                    <tr>
                        <th class="px-6 py-4 w-12"></th>
                        <th class="px-6 py-4">Branch</th>
                        <th class="px-6 py-4">Plan</th>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Payment</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                            <td class="px-6 py-4">
                                @if($subscription->payments->where('status', 'pending')->count() > 0)
                                    <input type="checkbox"
                                           class="payment-checkbox w-4 h-4 text-primary rounded focus:ring-primary"
                                           data-payment-id="{{ $subscription->payments->where('status', 'pending')->first()->id }}">
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $subscription->branch->name }}
                                </div>
                                <div class="text-xs text-gray-400">{{ $subscription->branch->company->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $subscription->subscriptionPlan->name }}</div>
                                <div class="text-xs text-gray-400">Rp {{ number_format($subscription->subscriptionPlan->price, 0, ',', '.') }}/mo</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs">
                                    {{ $subscription->start_date->format('M d, Y') }} -
                                    {{ $subscription->end_date->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'active' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'expired' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                        'suspended' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded-lg text-xs font-medium {{ $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($subscription->payments->count() > 0)
                                    @foreach($subscription->payments->take(1) as $payment)
                                        @if($payment->status === 'pending')
                                            <span class="text-yellow-600 dark:text-yellow-400 font-medium">Pending</span>
                                        @elseif($payment->status === 'confirmed')
                                            <span class="text-emerald-600 dark:text-emerald-400 font-medium">Confirmed</span>
                                        @else
                                            <span class="text-red-600 dark:text-red-400 font-medium">Rejected</span>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="text-gray-400">No payment</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('superadmin.subscriptions.show', $subscription) }}"
                                       class="text-gray-400 hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </a>
                                    @if($subscription->payments->where('status', 'pending')->count() > 0)
                                        <button type="button"
                                                class="text-emerald-500 hover:text-emerald-600 transition-colors confirm-single"
                                                data-payment-id="{{ $subscription->payments->where('status', 'pending')->first()->id }}">
                                            <span class="material-symbols-outlined">check_circle</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600">subscriptions</span>
                                <p class="text-gray-500 dark:text-gray-400 mt-4">No subscriptions found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($subscriptions->hasPages())
            <div class="p-4 border-t border-border-light dark:border-border-dark">
                {{ $subscriptions->appends(['status' => $status, 'company' => $company, 'branch' => $branch])->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    // Select all checkbox
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.payment-checkbox');
    const bulkConfirmBtn = document.getElementById('bulkConfirmBtn');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkButton);
    });

    function updateBulkButton() {
        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        bulkConfirmBtn.disabled = checked.length === 0;
    }

    // Single confirm
    document.querySelectorAll('.confirm-single').forEach(btn => {
        btn.addEventListener('click', function() {
            const paymentId = this.dataset.paymentId;
            if (confirm('Confirm this payment?')) {
                confirmPayment(paymentId);
            }
        });
    });

    // Bulk confirm
    if (bulkConfirmBtn) {
        bulkConfirmBtn.addEventListener('click', function() {
            const paymentIds = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.paymentId);

            if (confirm(`Confirm ${paymentIds.length} payment(s)?`)) {
                confirmPaymentBulk(paymentIds);
            }
        });
    }

    function confirmPayment(paymentId) {
        axios.post(`/superadmin/payments/${paymentId}/confirm`)
            .then(response => {
                alert(response.data.message);
                location.reload();
            })
            .catch(error => {
                alert('Error confirming payment');
            });
    }

    function confirmPaymentBulk(paymentIds) {
        axios.post('/superadmin/payments/bulk-confirm', { payment_ids: paymentIds })
            .then(response => {
                alert(response.data.message);
                location.reload();
            })
            .catch(error => {
                alert('Error confirming payments');
            });
    }
</script>
@endpush
