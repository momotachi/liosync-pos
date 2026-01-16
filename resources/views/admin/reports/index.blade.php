@extends('layouts.admin')

@section('title', 'Sales Reports')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
    <div class="flex items-center gap-3">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Sales Reports</h2>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.reports.sales') }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
            <span class="material-symbols-outlined text-[18px]">list_alt</span>
            <span>Sales Transactions</span>
        </a>
        <form method="GET" action="{{ request()->url() }}" class="flex items-center gap-2">
            <select name="period" id="period" onchange="this.form.submit()"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>

            @if($period === 'custom')
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}"
                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                    Apply
                </button>
            @endif
        </form>
    </div>
</div>

<!-- Metrics Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Sales Chart -->
    <div class="lg:col-span-2 bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sales Over Time</h3>
        <div class="h-64 flex items-center justify-center">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Methods</h3>
        <div class="space-y-3">
            @foreach($paymentMethods as $method)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="capitalize text-gray-700 dark:text-gray-300">{{ $method->payment_method }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($method->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ ($method->total / $metrics['total_revenue']) * 100 }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ $method->count }} orders</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Top Products -->
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <div class="p-6 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Selling Products</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($topProducts as $index => $product)
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center font-bold text-sm">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $product->item->name }}</p>
                            <p class="text-sm text-gray-500">{{ $product->orders_count }} orders</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</p>
                            <p class="text-sm text-gray-500">{{ $product->total_quantity }} sold</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No sales data for this period.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div x-data="{
        showModal: false,
        loading: false,
        order: null,
        items: [],
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
        }
    }" class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <div class="p-6 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-border-light dark:border-border-dark">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-left">Order #</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-left">Customer</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($recentOrders as $order)
                        <tr @click="openModal({{ $order->id }})" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $order->customer_name ?? 'Walk-in' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white text-right">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    <!-- Order Detail Modal -->
    <div x-show="showModal" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
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
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity">
            </div>

            <!-- Modal panel -->
            <div x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 rounded-xl shadow-2xl">

                <!-- Loading state -->
                <div x-show="loading" class="flex flex-col items-center justify-center py-12">
                    <svg class="animate-spin h-10 w-10 text-emerald-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Loading order details...</p>
                </div>

                <!-- Content -->
                <template x-if="!loading && order">
                    <div>
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
                                <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" x-text="'Rp ' + Number(order.total_amount).toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a :href="'/pos/receipt/' + order.id" target="_blank"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">print</span>
                                <span>Print Receipt</span>
                            </a>
                            <button @click="closeModal()" type="button"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Close
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');

    // Create gradients
    const revenueGradient = ctx.createLinearGradient(0, 0, 0, 300);
    revenueGradient.addColorStop(0, 'rgba(16, 185, 129, 0.8)');
    revenueGradient.addColorStop(1, 'rgba(16, 185, 129, 0.2)');

    const ordersGradient = ctx.createLinearGradient(0, 0, 0, 300);
    ordersGradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)');
    ordersGradient.addColorStop(1, 'rgba(59, 130, 246, 0.2)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($salesOverTime['labels']),
            datasets: [{
                label: 'Revenue',
                data: @json($salesOverTime['revenue']),
                backgroundColor: revenueGradient,
                borderColor: '#10b981',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }, {
                label: 'Orders',
                data: @json($salesOverTime['orders']),
                backgroundColor: ordersGradient,
                borderColor: '#3b82f6',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280',
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB',
                        drawBorder: false
                    },
                    ticks: {
                        color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280',
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                        }
                    },
                    title: {
                        display: true,
                        text: 'Revenue',
                        color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280',
                        font: {
                            size: 11
                        },
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Orders',
                        color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#374151',
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: document.documentElement.classList.contains('dark') ? '#1F2937' : '#FFFFFF',
                    titleColor: document.documentElement.classList.contains('dark') ? '#F9FAFB' : '#111827',
                    bodyColor: document.documentElement.classList.contains('dark') ? '#D1D5DB' : '#4B5563',
                    borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.label === 'Revenue') {
                                label += 'Rp ' + Number(context.raw).toLocaleString('id-ID');
                            } else {
                                label += context.raw;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Update chart on dark mode toggle
    const observer = new MutationObserver(function() {
        const chart = Chart.getChart('salesChart');
        if (chart) {
            const isDark = document.documentElement.classList.contains('dark');

            chart.options.scales.x.ticks.color = isDark ? '#9CA3AF' : '#6B7280';
            chart.options.scales.y.grid.color = isDark ? '#374151' : '#E5E7EB';
            chart.options.scales.y.ticks.color = isDark ? '#9CA3AF' : '#6B7280';
            chart.options.scales.y.title.color = isDark ? '#D1D5DB' : '#374151';
            chart.options.scales.y1.ticks.color = isDark ? '#9CA3AF' : '#6B7280';
            chart.options.scales.y1.title.color = isDark ? '#D1D5DB' : '#374151';
            chart.options.plugins.legend.labels.color = isDark ? '#D1D5DB' : '#374151';
            chart.options.plugins.tooltip.backgroundColor = isDark ? '#1F2937' : '#FFFFFF';
            chart.options.plugins.tooltip.titleColor = isDark ? '#F9FAFB' : '#111827';
            chart.options.plugins.tooltip.bodyColor = isDark ? '#D1D5DB' : '#4B5563';
            chart.options.plugins.tooltip.borderColor = isDark ? '#374151' : '#E5E7EB';

            chart.update();
        }
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
</script>
@endpush
