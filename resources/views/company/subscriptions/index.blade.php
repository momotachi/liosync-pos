@extends('layouts.company')

@section('title', 'Branch Subscriptions')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Branch Subscriptions</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage subscriptions for your branches</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('subscription-plans.view') }}"
               class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <span class="material-symbols-outlined mr-2 text-sm">visibility</span>
                View Plans
            </a>
            <a href="{{ route('company.subscriptions.purchase', $company) }}"
               class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                <span class="material-symbols-outlined mr-2 text-sm">add</span>
                Purchase Subscription
            </a>
        </div>
    </header>

    <!-- Filters -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-6">
        <form method="GET" action="{{ route('company.subscriptions.index', $company) }}" class="flex flex-wrap items-end gap-4">
            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select id="status" name="status"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <option value="">All Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>

            <!-- Branch Filter -->
            <div>
                <label for="branch" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Branch</label>
                <select id="branch" name="branch"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branch == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                    Filter
                </button>
                <a href="{{ route('company.subscriptions.index', $company) }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions -->
    @if($subscriptions->count() > 0)
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-4 mb-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <input type="checkbox" id="selectAll" class="w-4 h-4 text-primary rounded focus:ring-primary">
                <label for="selectAll" class="text-sm text-gray-700 dark:text-gray-300">Select All</label>
            </div>
            <button type="button"
                    id="bulkExtendBtn"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
                    disabled>
                <span class="material-symbols-outlined text-sm">shopping_cart</span>
                <span>Renew Selected (0)</span>
            </button>
        </div>
    @endif

    <!-- Subscriptions Table -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                    <tr>
                        <th class="px-6 py-4 w-12"></th>
                        <th class="px-6 py-4">Branch</th>
                        <th class="px-6 py-4">Plan</th>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Days Left</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <input type="checkbox"
                                       class="subscription-checkbox w-4 h-4 text-primary rounded focus:ring-primary"
                                       data-subscription-id="{{ $subscription->id }}">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $subscription->branch->name }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ $subscription->subscriptionPlan->name }}</div>
                                <div class="text-xs text-gray-400">Rp {{ number_format($subscription->subscriptionPlan->price, 0, ',', '.') }}/mo</div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                {{ $subscription->start_date->format('M d, Y') }} -
                                {{ $subscription->end_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'active' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'expired' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded-lg text-xs font-medium {{ $statusColors[$subscription->status] }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="@if($subscription->remaining_days > 7) text-emerald-600 @elseif($subscription->remaining_days > 0) text-yellow-600 @else text-red-600 @endif font-medium">
                                    {{ $subscription->remaining_days }} days
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('company.subscriptions.show', [$subscription->branch->company, $subscription]) }}"
                                       class="text-gray-400 hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </a>
                                    <button type="button"
                                            class="px-3 py-1.5 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors text-sm font-medium flex items-center gap-1"
                                            onclick="showExtendModal({{ $subscription->id }}, '{{ $subscription->branch->name }}', '{{ $subscription->subscriptionPlan->name }}', {{ $subscription->subscriptionPlan->price }}, {{ $subscription->branch->id }})">
                                        <span class="material-symbols-outlined text-sm">add</span>
                                        Renew
                                    </button>
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
                {{ $subscriptions->appends(['status' => $status, 'branch' => $branch])->links() }}
            </div>
        @endif
    </div>

    <!-- Extend/Renew Modal -->
    <div id="extendModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Renew Subscription</h3>
                <button type="button" onclick="closeExtendModal()" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="extendForm" action="{{ route('company.subscriptions.renew', $company) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="subscription_id" id="modalSubscriptionId">
                <input type="hidden" name="branch_id" id="modalBranchId">

                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Branch</p>
                    <p class="font-bold text-gray-900 dark:text-white" id="modalBranchName">-</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Current Plan</p>
                    <p class="font-medium text-gray-900 dark:text-white" id="modalPlanName">-</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Duration</label>
                    <select name="months" id="modalMonths" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800">
                        <option value="1">1 Month - Rp <span id="price1">0</span></option>
                        <option value="3">3 Months - Rp <span id="price3">0</span></option>
                        <option value="6">6 Months - Rp <span id="price6">0</span></option>
                        <option value="12">12 Months - Rp <span id="price12">0</span></option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Proof</label>
                    <input type="file"
                           name="payment_proof"
                           accept="image/*"
                           required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800">
                    <p class="text-xs text-gray-500 mt-1">Upload transfer receipt (max 5MB)</p>
                </div>

                <!-- Bank Details -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-400 mb-2">Transfer to:</p>
                    @php $bank = \App\Models\Bank::active()->ordered()->first(); @endphp
                    <p class="text-sm text-gray-700 dark:text-gray-300">Bank: {{ $bank?->name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Account: {{ $bank?->account_number ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Name: {{ $bank?->account_name ?? 'N/A' }}</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <button type="button" onclick="closeExtendModal()" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                        Submit Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Renew Modal -->
    <div id="bulkRenewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Bulk Renew Subscriptions</h3>
                <button type="button" onclick="closeBulkRenewModal()" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="bulkRenewForm" action="{{ route('company.subscriptions.bulk-renew', $company) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="subscription_ids" id="bulkModalSubscriptionIds">

                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Selected Subscriptions</p>
                    <p class="font-bold text-gray-900 dark:text-white" id="bulkModalCount">0 subscriptions</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Duration</label>
                    <select name="months" id="bulkModalMonths" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800">
                        <option value="1">1 Month</option>
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12">12 Months</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Proof</label>
                    <input type="file"
                           name="payment_proof"
                           accept="image/*"
                           required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800">
                    <p class="text-xs text-gray-500 mt-1">Upload transfer receipt (max 5MB)</p>
                </div>

                <!-- Bank Details -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <p class="text-sm font-medium text-blue-900 dark:text-blue-400 mb-2">Transfer to:</p>
                    @php $bulkBank = \App\Models\Bank::active()->ordered()->first(); @endphp
                    <p class="text-sm text-gray-700 dark:text-gray-300">Bank: {{ $bulkBank?->name ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Account: {{ $bulkBank?->account_number ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">Name: {{ $bulkBank?->account_name ?? 'N/A' }}</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <button type="button" onclick="closeBulkRenewModal()" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                        Submit Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.subscription-checkbox');
    const bulkExtendBtn = document.getElementById('bulkExtendBtn');

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
        bulkExtendBtn.disabled = checked.length === 0;

        // Update button text to show count
        const buttonText = bulkExtendBtn.querySelector('span:not(.material-symbols-outlined)');
        if (buttonText) {
            buttonText.textContent = `Renew Selected (${checked.length})`;
        }
    }

    // Bulk renew modal
    if (bulkExtendBtn) {
        bulkExtendBtn.addEventListener('click', function() {
            const subscriptionIds = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.dataset.subscriptionId);

            if (subscriptionIds.length > 0) {
                showBulkRenewModal(subscriptionIds);
            }
        });
    }

    function showBulkRenewModal(subscriptionIds) {
        document.getElementById('bulkModalSubscriptionIds').value = JSON.stringify(subscriptionIds);
        document.getElementById('bulkModalCount').textContent = `${subscriptionIds.length} subscription(s)`;
        document.getElementById('bulkRenewModal').classList.remove('hidden');
        document.getElementById('bulkRenewModal').classList.add('flex');
    }

    function closeBulkRenewModal() {
        document.getElementById('bulkRenewModal').classList.add('hidden');
        document.getElementById('bulkRenewModal').classList.remove('flex');
    }

    // Single renew modal
    function showExtendModal(subscriptionId, branchName, planName, planPrice, branchId) {
        document.getElementById('modalSubscriptionId').value = subscriptionId;
        document.getElementById('modalBranchId').value = branchId;
        document.getElementById('modalBranchName').textContent = branchName;
        document.getElementById('modalPlanName').textContent = planName;

        // Update price options
        document.getElementById('price1').textContent = formatNumber(planPrice * 1);
        document.getElementById('price3').textContent = formatNumber(planPrice * 3);
        document.getElementById('price6').textContent = formatNumber(planPrice * 6);
        document.getElementById('price12').textContent = formatNumber(planPrice * 12);

        document.getElementById('extendModal').classList.remove('hidden');
        document.getElementById('extendModal').classList.add('flex');
    }

    function closeExtendModal() {
        document.getElementById('extendModal').classList.add('hidden');
        document.getElementById('extendModal').classList.remove('flex');
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
</script>
@endpush
