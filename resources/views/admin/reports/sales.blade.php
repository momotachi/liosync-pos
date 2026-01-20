@extends('layouts.admin')

@section('title', 'Sales Transactions')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
    <div class="flex items-center gap-3">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Sales Transactions</h2>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.reports.index') }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <span class="material-symbols-outlined text-[18px] mr-1">arrow_back</span>
            Back to Dashboard
        </a>
    </div>
</div>

<!-- Metrics Summary -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium uppercase">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($metrics['total_revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 rounded-lg">
                <span class="material-symbols-outlined">payments</span>
            </div>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium uppercase">Total Orders</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $metrics['total_orders'] }}</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 text-blue-500 rounded-lg">
                <span class="material-symbols-outlined">shopping_cart</span>
            </div>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium uppercase">Avg Order Value</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($metrics['average_order_value'], 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-purple-50 dark:bg-purple-900/20 text-purple-500 rounded-lg">
                <span class="material-symbols-outlined">analytics</span>
            </div>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium uppercase">Items Sold</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $metrics['total_items_sold'] }}</p>
            </div>
            <div class="p-3 bg-orange-50 dark:bg-orange-900/20 text-orange-500 rounded-lg">
                <span class="material-symbols-outlined">inventory_2</span>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-6">
    <form method="GET" action="{{ route('admin.reports.sales') }}" class="flex flex-wrap items-end gap-4">
        <!-- Period Filter -->
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Period</label>
            <select name="period" id="period" onchange="toggleCustomDate()"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>
        </div>

        <!-- Custom Date Range -->
        <div id="customDateRange" class="hidden flex gap-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From</label>
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To</label>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
            </div>
        </div>

        <!-- Order Type Filter -->
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Order Type</label>
            <select name="order_type"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">All Types</option>
                <option value="direct" {{ $orderType === 'direct' ? 'selected' : '' }}>Direct</option>
                <option value="dine_in" {{ $orderType === 'dine_in' ? 'selected' : '' }}>Dine In</option>
                <option value="takeaway" {{ $orderType === 'takeaway' ? 'selected' : '' }}>Takeaway</option>
            </select>
        </div>

        <!-- Payment Method Filter -->
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
            <select name="payment_method"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">All Methods</option>
                <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="qris" {{ $paymentMethod === 'qris' ? 'selected' : '' }}>QRIS</option>
                <option value="debit" {{ $paymentMethod === 'debit' ? 'selected' : '' }}>Debit Card</option>
                <option value="credit" {{ $paymentMethod === 'credit' ? 'selected' : '' }}>Credit Card</option>
                <option value="transfer" {{ $paymentMethod === 'transfer' ? 'selected' : '' }}>Transfer</option>
            </select>
        </div>

        <!-- Search -->
        <div class="flex-1 min-w-[250px]">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Order ID or Customer Name"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
        </div>

        <!-- Submit Button -->
        <div class="flex gap-2">
            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                <span class="material-symbols-outlined text-[18px] align-middle mr-1">filter_list</span>
                Filter
            </button>
            <a href="{{ route('admin.reports.sales') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <span class="material-symbols-outlined text-[18px] align-middle">refresh</span>
            </a>
        </div>

        <!-- Export PDF Button -->
        <a href="{{ route('admin.reports.export-pdf', array_filter([
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'order_type' => $orderType,
            'payment_method' => $paymentMethod,
            'search' => $search
        ])) }}"
           class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
            <span class="material-symbols-outlined text-[18px] align-middle mr-1">picture_as_pdf</span>
            Export PDF
        </a>
    </form>
</div>

<!-- Sales Transactions Table -->
<div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden"
     x-data="{
        showModal: false,
        loading: false,
        order: null,
        items: [],
        canCancelOrder: window.canCancelOrder || false,
        openModal(orderId) {
            this.showModal = true;
            this.loading = true;
            this.order = null;
            this.items = [];
            axios.get('/branch/reports/order/' + orderId)
                .then(response => {
                    this.order = response.data.order;
                    this.items = response.data.items;
                })
                .catch(error => {
                    console.error('Error fetching order details:', error);
                    this.showModal = false;
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        closeModal() {
            this.showModal = false;
            this.order = null;
            this.items = [];
        },
        cancelOrder() {
            if (!this.order) return;

            const reason = prompt('Masukkan alasan pembatalan:');
            if (!reason || reason.trim() === '') {
                alert('Alasan pembatalan harus diisi');
                return;
            }

            fetch('/pos/orders/' + this.order.id + '/cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Gagal membatalkan transaksi');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat membatalkan transaksi');
            });
        }
    }">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800 border-b border-border-light dark:border-border-dark">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-left">Order ID</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-left">Date & Time</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-left">Customer</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-center">Order Type</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-center">Payment</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-center">Status</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-left">Cashier</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-right">Total</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                @forelse($orders as $order)
                    <tr @click="openModal({{ $order->id }})" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition-colors">
                        <td class="px-6 py-4 text-sm">
                            <span class="font-mono font-medium text-gray-900 dark:text-white">
                                #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $order->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $order->customer_name ?? 'Walk-in' }}
                            </div>
                            @if($order->customer_phone)
                                <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                            @endif
                        </td>
                        <!-- Order Type -->
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($order->order_type === 'dine_in') bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400
                                @elseif($order->order_type === 'takeaway') bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400
                                @elseif($order->order_type === 'direct') bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400
                                @else bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-400
                                @endif">
                                @if($order->order_type === 'dine_in') Dine In
                                @elseif($order->order_type === 'takeaway') Takeaway
                                @elseif($order->order_type === 'direct') Direct
                                @else - @endif
                            </span>
                            @if($order->table_number)
                                <div class="text-xs text-gray-500 mt-1">Table {{ $order->table_number }}</div>
                            @endif
                        </td>
                        <!-- Payment Method -->
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($order->payment_method === 'cash') bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400
                                @elseif($order->payment_method === 'qris') bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400
                                @elseif($order->payment_method === 'debit') bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400
                                @elseif($order->payment_method === 'credit') bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400
                                @elseif($order->payment_method === 'transfer') bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400
                                @else bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-400
                                @endif">
                                @if($order->payment_method) {{ ucfirst($order->payment_method) }}
                                @else - @endif
                            </span>
                        </td>
                        <!-- Payment Status -->
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($order->status === 'completed') bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400
                                @elseif($order->status === 'pending') bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400
                                @else bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-400
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <!-- Cashier -->
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $order->user->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white text-right">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button @click.stop="openModal({{ $order->id }})"
                                    class="inline-flex items-center px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-lg text-xs font-medium transition-colors">
                                <span class="material-symbols-outlined text-sm mr-1">visibility</span>
                                View Details
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-4xl text-gray-400 mb-2">receipt_long</span>
                                <p class="text-gray-500">No sales transactions found for the selected period.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-border-light dark:border-border-dark flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} results
            </p>
            <div class="flex gap-2">
                @if($orders->onFirstPage())
                    <span class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-400 cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $orders->previousPageUrl() }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Previous</a>
                @endif

                @if($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Next</a>
                @else
                    <span class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-400 cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
    @endif

<!-- Order Detail Modal -->
<div x-show="showModal" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title" role="dialog" aria-modal="true"
    style="display: none;"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div x-show="showModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="closeModal()"
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"
            aria-hidden="true">
        </div>

        <!-- Modal panel -->
        <div x-show="showModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 rounded-xl shadow-2xl"
            @click.stop>

            <!-- Loading state -->
            <div x-show="loading" class="flex flex-col items-center justify-center py-12">
                <svg class="animate-spin h-10 w-10 text-emerald-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">Loading order details...</p>
            </div>

            <!-- Content -->
            <div x-show="!loading && order" style="display: none;">
                <!-- Header -->
                <div class="flex items-start justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modal-title">
                                Order #<span x-text="String(order.id).padStart(6, '0')"></span>
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="new Date(order.created_at).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' })"></p>
                        </div>
                        <button @click="closeModal()" type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <!-- Cancelled Status Badge -->
                    <div x-show="order.cancelled_at" class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4" style="display: none;">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400">cancel</span>
                            <div>
                                <p class="text-sm font-semibold text-red-800 dark:text-red-300">Transaksi Dibatalkan</p>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1" x-show="order.cancel_reason" x-text="'Alasan: ' + order.cancel_reason"></p>
                                <p class="text-xs text-red-600 dark:text-red-400" x-show="order.cancelled_by_user" x-text="'Oleh: ' + (order.cancelled_by_user?.name || '-')"></p>
                                <p class="text-xs text-red-500 dark:text-red-500 mt-1" x-text="new Date(order.cancelled_at).toLocaleString('id-ID')"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Info -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Customer</p>
                            <p class="font-medium text-gray-900 dark:text-white" x-text="order.customer_name || 'Walk-in Customer'"></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400" x-show="order.customer_phone" x-text="order.customer_phone"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Payment</p>
                            <p class="font-medium text-gray-900 dark:text-white capitalize" x-text="order.payment_method"></p>
                            <p class="text-sm text-emerald-600 dark:text-emerald-400" x-text="order.status"></p>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="mb-6">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">Order Items</h4>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Item</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Qty</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Price</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="item in items" :key="item.name">
                                        <tr>
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-gray-900 dark:text-white" x-text="item.name"></p>
                                                <p class="text-xs text-amber-600 dark:text-amber-400" x-show="item.note" x-text="'Note: ' + item.note"></p>
                                            </td>
                                            <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400" x-text="item.quantity"></td>
                                            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400" x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')"></td>
                                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white" x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-gray-700 dark:text-gray-300">Total Amount</span>
                            <span class="text-2xl font-bold" :class="order.cancelled_at ? 'text-gray-400 line-through' : 'text-emerald-600 dark:text-emerald-400'" x-text="'Rp ' + Number(order.total_amount).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a :href="'/pos/receipt/' + order.id" target="_blank"
                           class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                            <span class="material-symbols-outlined text-[18px]">print</span>
                            <span>Print Receipt</span>
                        </a>
                        <button x-show="!order.cancelled_at && canCancelOrder" @click="cancelOrder" type="button"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors" style="display: none;">
                            Batalkan Transaksi
                        </button>
                        <button @click="closeModal()" type="button"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Order Cancel Data (Hidden) -->
<script>
    window.canCancelOrder = {{ auth()->check() && !auth()->user()->isCashier() ? 'true' : 'false' }};
    window.csrfToken = '{{ csrf_token() }}';
</script>
@endsection

@push('scripts')
<script>
    function toggleCustomDate() {
        const period = document.getElementById('period').value;
        const customDateRange = document.getElementById('customDateRange');

        if (period === 'custom') {
            customDateRange.classList.remove('hidden');
        } else {
            customDateRange.classList.add('hidden');
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomDate();
    });
</script>
@endpush
