@extends('layouts.admin')

@section('title', 'Choose Subscription Plan')

@section('content')
    <header class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Choose Your Plan</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Select a subscription plan that fits your needs</p>
    </header>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach($plans as $plan)
            <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden @if($currentSubscription && $currentSubscription->subscription_plan_id == $plan->id) ring-2 ring-primary @endif">
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
            <form action="{{ route('subscription.process') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="subscription_plan_id" id="modalPlanId">

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

    function showPurchaseForm(planId, planName, price) {
        currentPrice = price;
        document.getElementById('modalPlanId').value = planId;
        document.getElementById('modalPlanName').textContent = planName;
        document.getElementById('modalPlanPrice').textContent = 'Rp ' + formatNumber(price);

        // Update price options
        document.getElementById('price1').textContent = formatNumber(price * 1);
        document.getElementById('price3').textContent = formatNumber(price * 3);
        document.getElementById('price6').textContent = formatNumber(price * 6);
        document.getElementById('price12').textContent = formatNumber(price * 12);

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

    // Update total when duration changes
    document.getElementById('modalMonths')?.addEventListener('change', function() {
        const months = parseInt(this.value);
        // Optional: display total amount
    });
</script>
@endpush
