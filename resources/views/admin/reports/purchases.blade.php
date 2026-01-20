@extends('layouts.admin')

@section('title', 'Laporan Pembelian')

@push('styles')
<style>
    .purchase-row {
        cursor: pointer;
    }
    .purchase-row:hover {
        background-color: rgb(249 250 251);
    }
    .dark .purchase-row:hover {
        background-color: rgb(55 65 81);
    }
    canvas {
        max-height: 300px;
    }
</style>
@endpush

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Pembelian</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Rekapitulasi pembelian bahan baku dengan filter periode.</p>
        </div>
        <form method="GET" action="{{ route('admin.reports.purchases') }}" class="flex items-center gap-3">
            <!-- Search -->
            <div class="relative">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari No. PO / Supplier..."
                    class="pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent w-64">
                <span class="material-symbols-outlined absolute left-3 top-2 text-gray-400">search</span>
            </div>

            <!-- Period Filter -->
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
    </header>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Purchase Value -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Nilai Pembelian</p>
                    <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-500 mt-2">Rp {{ number_format($metrics['total_purchase_value'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">payments</span>
                </div>
            </div>
        </div>

        <!-- Total Purchases -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah Transaksi</p>
                    <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-500 mt-2">{{ $metrics['total_purchases'] }}</h3>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">shopping_cart</span>
                </div>
            </div>
        </div>

        <!-- Average Value -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Rata-rata Nilai</p>
                    <h3 class="text-2xl font-bold text-purple-600 dark:text-purple-500 mt-2">Rp {{ number_format($metrics['average_purchase_value'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">calculate</span>
                </div>
            </div>
        </div>

        <!-- Total Items -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Item Dibeli</p>
                    <h3 class="text-2xl font-bold text-orange-600 dark:text-orange-500 mt-2">{{ number_format($metrics['total_items'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">inventory_2</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Purchases Over Time Chart -->
        <div class="lg:col-span-2 bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
            <div class="p-6 border-b border-border-light dark:border-border-dark">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Grafik Pembelian</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tren pembelian bahan baku periode ini</p>
            </div>
            <div class="p-6">
                <canvas id="purchasesChart"></canvas>
            </div>
        </div>

        <!-- Top Purchased Items -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
            <div class="p-6 border-b border-border-light dark:border-border-dark">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Item Terbanyak Dibeli</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Bahan baku paling banyak dibeli</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($topItems as $item)
                    @if($item->item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                <span class="material-symbols-outlined text-red-600 dark:text-red-400">shopping_bag</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->item->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($item->total_quantity, 2, ',', '.') }} {{ $item->item->unit ?? '' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->purchase_count }} pembelian</p>
                        </div>
                    </div>
                    @endif
                    @empty
                    <p class="text-center text-gray-500 dark:text-gray-400 py-4">Tidak ada data pembelian</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Transactions Table -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="p-6 border-b border-border-light dark:border-border-dark flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Riwayat Transaksi Pembelian</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Klik baris untuk melihat detail</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                    <tr>
                        <th class="px-6 py-4" scope="col">No. PO</th>
                        <th class="px-6 py-4" scope="col">Tanggal</th>
                        <th class="px-6 py-4" scope="col">Supplier</th>
                        <th class="px-6 py-4 text-center" scope="col">Total Item</th>
                        <th class="px-6 py-4 text-right" scope="col">Nilai</th>
                        <th class="px-6 py-4 text-center" scope="col">Metode</th>
                        <th class="px-6 py-4 text-center" scope="col">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($purchases as $purchase)
                    <tr class="purchase-row hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" onclick="showPurchaseDetail({{ $purchase->id }})">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            #{{ str_pad($purchase->id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $purchase->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $purchase->supplier_name }}</span>
                                @if($purchase->supplier_phone)
                                <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ $purchase->supplier_phone }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center font-semibold">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ $purchase->items->sum('quantity') }} item
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium @if($purchase->payment_method == 'cash') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 @elseif($purchase->payment_method == 'transfer') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 @else bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 @endif">
                                {{ ucfirst($purchase->payment_method) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $purchase->user->name ?? '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-4 text-center">Belum ada pembelian untuk periode ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($purchases->hasPages())
        <div class="px-6 py-4 border-t border-border-light dark:border-border-dark flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan {{ $purchases->firstItem() }} sampai {{ $purchases->lastItem() }} dari {{ $purchases->total() }} pembelian
            </p>
            {{ $purchases->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    <!-- Purchase Detail Modal -->
    <div id="purchaseModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" onclick="closePurchaseModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                    <!-- Modal Header -->
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 flex justify-between items-center">
                        <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">Detail Purchase Order</h3>
                        <button type="button" onclick="closePurchaseModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-4 py-5 sm:p-6" id="modalContent">
                        <div class="text-center py-8">
                            <span class="material-symbols-outlined animate-spin text-4xl text-primary">refresh</span>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">Loading...</p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6" id="modalFooter">
                        <button type="button" onclick="closePurchaseModal()" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-600 sm:ml-3 sm:w-auto">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Data (Hidden) -->
    <script>
        window.purchasesData = @json($purchases->items());
        window.canCancelPurchase = {{ auth()->check() && !auth()->user()->isCashier() ? 'true' : 'false' }};
        window.csrfToken = '{{ csrf_token() }}';
    </script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Purchases Over Time Chart
    const purchasesCtx = document.getElementById('purchasesChart').getContext('2d');

    const purchasesLabels = @json($purchasesOverTime['labels'] ?? []);
    const purchasesAmount = @json($purchasesOverTime['amount'] ?? []);
    const purchasesCount = @json($purchasesOverTime['purchases'] ?? []);

    new Chart(purchasesCtx, {
        type: 'line',
        data: {
            labels: purchasesLabels,
            datasets: [
                {
                    label: 'Nilai Pembelian',
                    data: purchasesAmount,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Jumlah Transaksi',
                    data: purchasesCount,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
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
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Nilai (Rp)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Transaksi'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    let currentPurchaseId = null;

    function showPurchaseDetail(purchaseId) {
        const purchase = window.purchasesData.find(p => p.id === purchaseId);
        if (!purchase) return;

        currentPurchaseId = purchaseId;
        const modalContent = document.getElementById('modalContent');
        const modalFooter = document.getElementById('modalFooter');

        const isCancelled = purchase.cancelled_at !== null;
        const canCancel = window.canCancelPurchase && !isCancelled;

        let itemsHtml = purchase.items.map(item => `
            <tr class="border-b dark:border-gray-700">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${item.item?.name || 'N/A'}</td>
                <td class="px-4 py-3 text-sm text-center text-gray-600 dark:text-gray-400">${parseFloat(item.quantity).toFixed(2)} ${item.item?.unit || ''}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-400">Rp ${parseFloat(item.price).toLocaleString('id-ID')}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-white font-semibold">Rp ${parseFloat(item.subtotal).toLocaleString('id-ID')}</td>
            </tr>
        `).join('');

        modalContent.innerHTML = `
            <div class="space-y-4">
                <!-- Status Badge -->
                ${isCancelled ? `
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-red-600 dark:text-red-400">cancel</span>
                        <div>
                            <p class="text-sm font-semibold text-red-800 dark:text-red-300">Transaksi Dibatalkan</p>
                            ${purchase.cancel_reason ? `<p class="text-xs text-red-600 dark:text-red-400 mt-1">Alasan: ${purchase.cancel_reason}</p>` : ''}
                            ${purchase.cancelled_by ? `<p class="text-xs text-red-600 dark:text-red-400">Oleh: ${purchase.cancelled_by_user?.name || '-'}</p>` : ''}
                            <p class="text-xs text-red-500 dark:text-red-500 mt-1">${new Date(purchase.cancelled_at).toLocaleString('id-ID')}</p>
                        </div>
                    </div>
                </div>
                ` : ''}

                <!-- PO Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">No. PO</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">#${String(purchase.id).padStart(4, '0')}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">${new Date(purchase.created_at).toLocaleString('id-ID')}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Supplier</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${purchase.supplier_name}</p>
                        ${purchase.supplier_phone ? `<p class="text-xs text-gray-500 dark:text-gray-400">${purchase.supplier_phone}</p>` : ''}
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Metode Pembayaran</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                            purchase.payment_method === 'cash' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                            purchase.payment_method === 'transfer' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' :
                            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400'
                        }">
                            ${purchase.payment_method.charAt(0).toUpperCase() + purchase.payment_method.slice(1)}
                        </span>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mt-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">Item yang Dibeli</p>
                    <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                                <tr>
                                    <th class="px-4 py-2">Nama Item</th>
                                    <th class="px-4 py-2 text-center">Jumlah</th>
                                    <th class="px-4 py-2 text-right">Harga</th>
                                    <th class="px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                                <tr class="bg-gray-50 dark:bg-gray-700 font-semibold">
                                    <td colspan="3" class="px-4 py-3 text-sm text-right text-gray-900 dark:text-white">Total</td>
                                    <td class="px-4 py-3 text-sm text-right ${isCancelled ? 'text-gray-400 line-through' : 'text-emerald-600 dark:text-emerald-400'}">Rp ${parseFloat(purchase.total_amount).toLocaleString('id-ID')}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                ${purchase.notes ? `
                <div class="mt-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1">Catatan</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 p-2 rounded">${purchase.notes}</p>
                </div>
                ` : ''}

                <!-- User Info -->
                <div class="mt-4 pt-4 border-t dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Dibuat oleh: <span class="font-medium text-gray-900 dark:text-white">${purchase.user?.name || '-'}</span></p>
                </div>
            </div>
        `;

        // Update footer based on cancel status
        if (canCancel) {
            modalFooter.innerHTML = `
                <button type="button" onclick="cancelPurchase()" class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 sm:ml-3 sm:w-auto">Batalkan Transaksi</button>
                <button type="button" onclick="closePurchaseModal()" class="mt-3 sm:mt-0 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 sm:w-auto">Tutup</button>
            `;
        } else {
            modalFooter.innerHTML = `
                <button type="button" onclick="closePurchaseModal()" class="inline-flex w-full justify-center rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-600 sm:ml-3 sm:w-auto">Tutup</button>
            `;
        }

        document.getElementById('purchaseModal').classList.remove('hidden');
    }

    function cancelPurchase() {
        if (!currentPurchaseId) return;

        const reason = prompt('Masukkan alasan pembatalan:');
        if (!reason || reason.trim() === '') {
            alert('Alasan pembatalan harus diisi');
            return;
        }

        fetch(`/purchase/${currentPurchaseId}/cancel`, {
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

    function closePurchaseModal() {
        document.getElementById('purchaseModal').classList.add('hidden');
    }

    // Close modal on ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePurchaseModal();
        }
    });
</script>
@endpush
