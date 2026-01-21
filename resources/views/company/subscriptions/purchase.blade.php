@extends('layouts.company')

@section('title', 'Purchase Subscription')

@section('content')
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Purchase Subscription</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Select a branch and subscription plan</p>
    </header>

    <!-- Branch Selection -->
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Branch</label>
        <select id="branchSelect" class="w-full md:w-1/2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
            <option value="">-- Choose a branch --</option>
            <option value="all" data-current-plan="">🌐 All Branches</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" data-current-plan="{{ $branch->currentSubscription?->subscription_plan_id ?? '' }}">
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @if($branches->count() > 1)
        <p class="text-xs text-gray-500 mt-1">Select "All Branches" to purchase subscription for all branches at once</p>
        @endif
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach($plans as $plan)
            <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden plan-card"
                 data-plan-id="{{ $plan->id }}">
                <!-- Plan Header -->
                <div class="p-6 border-b border-border-light dark:border-border-dark">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                    <p class="text-3xl font-bold text-primary mt-2">Rp {{ number_format($plan->price, 0, ',', '.') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">per month</p>
                </div>

                <!-- Features -->
                <div class="p-6 space-y-3">
                    @foreach($plan->features_array as $feature => $enabled)
                        @if($enabled)
                            <div class="flex items-center text-sm">
                                <span class="material-symbols-outlined text-emerald-500 mr-2">check_circle</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $feature)) }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Action -->
                <div class="p-6 bg-gray-50 dark:bg-gray-800/50">
                    <button type="button"
                            onclick="showPurchaseForm({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price }})"
                            class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                        Select Plan
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Purchase Modal -->
    <div id="purchaseModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Complete Purchase</h3>
                <button type="button" onclick="closePurchaseForm()" class="text-gray-400 hover:text-gray-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form action="{{ route('company.subscriptions.process-purchase', $company) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="subscription_plan_id" id="modalPlanId">
                <input type="hidden" name="branch_id" id="modalBranchId">

                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Selected Plan</p>
                    <p class="font-bold text-gray-900 dark:text-white" id="modalPlanName">-</p>
                    <p class="text-2xl font-bold text-primary" id="modalPlanPrice">-</p>
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
                    <button type="button" onclick="closePurchaseForm()" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
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
    let currentPrice = 0;
    const totalBranches = {{ $branches->count() }};

    function showPurchaseForm(planId, planName, price) {
        const branchSelect = document.getElementById('branchSelect');
        if (!branchSelect.value) {
            alert('Please select a branch first');
            branchSelect.focus();
            return;
        }

        const isAllBranches = branchSelect.value === 'all';
        currentPrice = price;
        document.getElementById('modalPlanId').value = planId;
        document.getElementById('modalBranchId').value = branchSelect.value;
        document.getElementById('modalPlanName').textContent = planName;

        // Calculate total based on selection
        const multiplier = isAllBranches ? totalBranches : 1;
        const displayPrice = isAllBranches
            ? `Rp ${formatNumber(price)} × ${totalBranches} branches = Rp ${formatNumber(price * totalBranches)}/month`
            : `Rp ${formatNumber(price)}`;

        document.getElementById('modalPlanPrice').textContent = displayPrice;

        // Update price options
        document.getElementById('price1').textContent = formatNumber(price * multiplier * 1);
        document.getElementById('price3').textContent = formatNumber(price * multiplier * 3);
        document.getElementById('price6').textContent = formatNumber(price * multiplier * 6);
        document.getElementById('price12').textContent = formatNumber(price * multiplier * 12);

        document.getElementById('purchaseModal').classList.remove('hidden');
        document.getElementById('purchaseModal').classList.add('flex');
    }

    function closePurchaseForm() {
        document.getElementById('purchaseModal').classList.add('hidden');
        document.getElementById('purchaseModal').classList.remove('flex');
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
</script>
@endpush
