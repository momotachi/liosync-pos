@extends('layouts.admin')

@section('title', 'Laporan Profit')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Profit</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Analisis profit dan margin keuntungan untuk periode yang dipilih.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('admin.reports.profit') }}" class="flex items-center gap-2">
                <select name="period" id="period" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm font-medium rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-primary">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Minggu Ini</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Rentang Kustom</option>
                </select>

                @if($period === 'custom')
                    <input type="date" name="start_date" value="{{ $startDate ?? '' }}"
                        class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <input type="date" name="end_date" value="{{ $endDate ?? '' }}"
                        class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <button type="submit" class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark">
                        Terapkan
                    </button>
                @endif
            </form>
        </div>
    </header>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Revenue -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Omset</p>
                    <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-500 mt-2">Rp {{ number_format($metrics['total_revenue'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">payments</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">{{ $metrics['total_orders'] }} transaksi</p>
        </div>

        <!-- Total HPP -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total HPP</p>
                    <h3 class="text-2xl font-bold text-red-600 dark:text-red-500 mt-2">Rp {{ number_format($metrics['total_hpp'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">trending_down</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">Modal barang terjual</p>
        </div>

        <!-- Gross Profit -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Profit Kotor</p>
                    <h3 class="text-2xl font-bold {{ $metrics['gross_profit'] >= 0 ? 'text-blue-600 dark:text-blue-500' : 'text-red-600 dark:text-red-500' }} mt-2">Rp {{ number_format($metrics['gross_profit'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 {{ $metrics['gross_profit'] >= 0 ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-lg">
                    <span class="material-symbols-outlined {{ $metrics['gross_profit'] >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">account_balance_wallet</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">Margin: {{ number_format($metrics['profit_margin'], 1, ',', '.') }}%</p>
        </div>
    </div>

    <!-- Chart and Top Products -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Profit Over Time Chart -->
        <div class="lg:col-span-2 bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
            <div class="p-6 border-b border-border-light dark:border-border-dark">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tren Profit</h3>
            </div>
            <div class="p-6">
                <canvas id="profitChart" class="w-full" style="height: 300px;"></canvas>
            </div>
        </div>

        <!-- Top Profitable Products -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
            <div class="p-6 border-b border-border-light dark:border-border-dark">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Produk Terprofit</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($topProducts as $product)
                        @if($product->item)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $product->item->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($product->total_quantity) }} terjual</p>
                                </div>
                                <div class="text-right ml-3">
                                    <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($product->total_profit, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($product->profit_margin, 1, ',', '.') }}%</p>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada data</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Orders Table -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <div class="p-6 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Rincian Profit per Transaksi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                    <tr>
                        <th class="px-6 py-4" scope="col">No. Order</th>
                        <th class="px-6 py-4" scope="col">Tanggal</th>
                        <th class="px-6 py-4 text-right" scope="col">Omset</th>
                        <th class="px-6 py-4 text-right" scope="col">HPP</th>
                        <th class="px-6 py-4 text-right" scope="col">Profit</th>
                        <th class="px-6 py-4 text-right" scope="col">Margin</th>
                        <th class="px-6 py-4 text-center" scope="col">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($orders as $order)
                        <?php
                            $orderHpp = 0;
                            foreach($order->items as $item) {
                                if($item->item && $item->item->hpp) {
                                    $orderHpp += $item->item->hpp * $item->quantity;
                                }
                            }
                            $orderProfit = $order->total_amount - $orderHpp;
                            $orderMargin = $order->total_amount > 0 ? ($orderProfit / $order->total_amount) * 100 : 0;
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <button onclick="showOrderDetails({{ $order->id }})" class="hover:text-primary transition-colors">
                                    #{{ $order->id }}
                                </button>
                            </td>
                            <td class="px-6 py-4">{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-red-600 dark:text-red-400">Rp {{ number_format($orderHpp, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-semibold {{ $orderProfit >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">Rp {{ number_format($orderProfit, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right {{ $orderMargin >= 0 ? 'text-purple-600 dark:text-purple-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($orderMargin, 1, ',', '.') }}%</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $order->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-gray-300 dark:text-gray-600">receipt_long</span>
                                    <p>Tidak ada transaksi untuk periode ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="p-4 border-t border-border-light dark:border-border-dark flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan {{ $orders->firstItem() }} sampai {{ $orders->lastItem() }} dari {{ $orders->total() }} transaksi
                </p>
                {{ $orders->appends(['period' => $period, 'start_date' => $startDate, 'end_date' => $endDate])->links() }}
            </div>
        @endif
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Detail Order</h3>
                <button onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="orderModalContent" class="p-6 overflow-y-auto max-h-[70vh]">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Profit Over Time Chart
        const profitCtx = document.getElementById('profitChart').getContext('2d');

        const profitLabels = @json($profitOverTime['labels'] ?? []);
        const profitRevenue = @json($profitOverTime['revenue'] ?? []);
        const profitHpp = @json($profitOverTime['hpp'] ?? []);
        const profitProfit = @json($profitOverTime['profit'] ?? []);

        new Chart(profitCtx, {
            type: 'line',
            data: {
                labels: profitLabels,
                datasets: [
                    {
                        label: 'Omset',
                        data: profitRevenue,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'HPP',
                        data: profitHpp,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Profit',
                        data: profitProfit,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgb(107, 114, 128)'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Nilai (Rp)',
                            color: 'rgb(107, 114, 128)'
                        },
                        grid: {
                            color: 'rgba(107, 114, 128, 0.1)'
                        },
                        ticks: {
                            color: 'rgb(107, 114, 128)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: 'rgb(107, 114, 128)'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Order Details Modal
        function showOrderDetails(orderId) {
            const modal = document.getElementById('orderModal');
            const content = document.getElementById('orderModalContent');

            content.innerHTML = '<p class="text-center text-gray-500">Memuat detail...</p>';
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            fetch(`{{ route('admin.reports.order-details', ':id') }}`.replace(':id', orderId))
                .then(response => response.json())
                .then(data => {
                    const order = data.order;
                    const items = data.items;

                    let html = `
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">No. Order:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-white">#${order.id}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Tanggal:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-white">${new Date(order.created_at).toLocaleString('id-ID')}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Pelanggan:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-white">${order.customer_name || '-'}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Metode Pembayaran:</span>
                                    <span class="ml-2 font-medium text-gray-900 dark:text-white">${order.payment_method || '-'}</span>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Item</h4>
                                <div class="space-y-2">
                    `;

                    items.forEach(item => {
                        html += `
                            <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">${item.name}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">${item.quantity} x Rp ${item.price.toLocaleString('id-ID')}</p>
                                </div>
                                <span class="font-semibold text-gray-900 dark:text-white">Rp ${item.subtotal.toLocaleString('id-ID')}</span>
                            </div>
                        `;
                    });

                    html += `
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Total</span>
                                    <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp ${order.total_amount.toLocaleString('id-ID')}</span>
                                </div>
                            </div>
                        </div>
                    `;

                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = '<p class="text-center text-red-500">Gagal memuat detail</p>';
                });
        }

        function closeOrderModal() {
            const modal = document.getElementById('orderModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Close modal on outside click
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
    </script>
@endpush
