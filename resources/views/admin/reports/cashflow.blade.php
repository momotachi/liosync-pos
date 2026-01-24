@extends('layouts.admin')

@section('title', 'Laporan Cashflow')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Arus Kas</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Mutasi kas dan bank dengan ledger untuk periode yang dipilih.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('admin.reports.cashflow') }}" class="flex items-center gap-2">
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

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Saldo Awal Cash -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark relative group">
            <div class="flex justify-between items-start">
                <div class="flex-1 pr-10">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Saldo Awal Cash</p>
                    <h3 class="text-2xl font-bold text-gray-600 dark:text-gray-400 mt-2">Rp {{ number_format($initialBalances['cash'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <span class="material-symbols-outlined text-gray-600 dark:text-gray-400">account_balance</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Saldo sebelum periode</p>
            @if($recentCashAdjustments->count() > 0)
                <p class="text-xs text-primary dark:text-primary mt-1">
                    Terakhir update: {{ $recentCashAdjustments->first()->adjustment_date->format('d M Y H:i') }}
                </p>
            @endif
            <div class="absolute top-3 right-3 flex gap-2">
                <button onclick="openTransferModal('cash')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 shadow-md border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:border-blue-600 hover:shadow-lg transition-all cursor-pointer z-10" title="Transfer ke Bank">
                    <span class="material-symbols-outlined text-[18px]">swap_horiz</span>
                </button>
                <button onclick="openBalanceModal('cash')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 shadow-md border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:text-primary hover:border-primary hover:shadow-lg transition-all cursor-pointer z-10" title="Edit Saldo Cash">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                </button>
            </div>
        </div>

        <!-- Saldo Awal Bank -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark relative group">
            <div class="flex justify-between items-start">
                <div class="flex-1 pr-10">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Saldo Awal Bank</p>
                    <h3 class="text-2xl font-bold text-gray-600 dark:text-gray-400 mt-2">Rp {{ number_format($initialBalances['bank'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <span class="material-symbols-outlined text-gray-600 dark:text-gray-400">account_balance</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Saldo sebelum periode</p>
            @if($recentBankAdjustments->count() > 0)
                <p class="text-xs text-primary dark:text-primary mt-1">
                    Terakhir update: {{ $recentBankAdjustments->first()->adjustment_date->format('d M Y H:i') }}
                </p>
            @endif
            <div class="absolute top-3 right-3 flex gap-2">
                <button onclick="openTransferModal('bank')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 shadow-md border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:text-blue-600 hover:border-blue-600 hover:shadow-lg transition-all cursor-pointer z-10" title="Transfer ke Cash">
                    <span class="material-symbols-outlined text-[18px]">swap_horiz</span>
                </button>
                <button onclick="openBalanceModal('bank')" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 shadow-md border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:text-primary hover:border-primary hover:shadow-lg transition-all cursor-pointer z-10" title="Edit Saldo Bank">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                </button>
            </div>
        </div>

        <!-- Total Cash In -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Kas Masuk</p>
                    <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-500 mt-2">Rp {{ number_format($metrics['cash_in'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">arrow_downward</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">Dari penjualan</p>
        </div>

        <!-- Total Cash Out -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Kas Keluar</p>
                    <h3 class="text-2xl font-bold text-red-600 dark:text-red-500 mt-2">Rp {{ number_format($metrics['cash_out'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">arrow_upward</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">Untuk pembelian</p>
        </div>
    </div>

    <!-- Final Balances -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Final Cash Balance -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 dark:from-emerald-600 dark:to-emerald-700 rounded-xl p-6 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-emerald-100">Sisa Cash</p>
                    <h3 class="text-3xl font-bold text-white mt-2">Rp {{ number_format($initialBalances['cash'] + $metrics['cash_in'] - $metrics['cash_out'] + $metrics['total_adjustments_cash'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <span class="material-symbols-outlined text-white text-3xl">payments</span>
                </div>
            </div>
            <p class="text-xs text-emerald-100 mt-3">Saldo akhir kas fisik</p>
        </div>

        <!-- Final Bank Balance -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-xl p-6 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-blue-100">Sisa Bank</p>
                    <h3 class="text-3xl font-bold text-white mt-2">Rp {{ number_format($initialBalances['bank'] + collect($metrics['cash_in_by_method'])->filter(function($val, $key) { return $key !== 'cash'; })->sum() + $metrics['total_adjustments_bank'], 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <span class="material-symbols-outlined text-white text-3xl">account_balance</span>
                </div>
            </div>
            <p class="text-xs text-blue-100 mt-3">Saldo akhir rekening bank</p>
        </div>
    </div>

    <!-- Balance Adjustment History -->
    @if($recentCashAdjustments->count() > 0 || $recentBankAdjustments->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Cash Adjustments History -->
            @if($recentCashAdjustments->count() > 0)
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
                    <div class="p-4 border-b border-border-light dark:border-border-dark flex justify-between items-center">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Riwayat Penyesuaian Cash</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">5 Terakhir</span>
                    </div>
                    <div class="divide-y divide-border-light dark:divide-border-dark">
                        @foreach($recentCashAdjustments as $adjustment)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $adjustment->amount >= 0 ? '+' : '' }}Rp {{ number_format($adjustment->amount, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $adjustment->adjustment_date->format('d M Y H:i') }}
                                        @if($adjustment->note)
                                            <span class="ml-2">• {{ $adjustment->note }}</span>
                                        @endif
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $adjustment->amount >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    {{ $adjustment->amount >= 0 ? 'Masuk' : 'Keluar' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Bank Adjustments History -->
            @if($recentBankAdjustments->count() > 0)
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
                    <div class="p-4 border-b border-border-light dark:border-border-dark flex justify-between items-center">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Riwayat Penyesuaian Bank</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">5 Terakhir</span>
                    </div>
                    <div class="divide-y divide-border-light dark:divide-border-dark">
                        @foreach($recentBankAdjustments as $adjustment)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $adjustment->amount >= 0 ? '+' : '' }}Rp {{ number_format($adjustment->amount, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $adjustment->adjustment_date->format('d M Y H:i') }}
                                        @if($adjustment->note)
                                            <span class="ml-2">• {{ $adjustment->note }}</span>
                                        @endif
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $adjustment->amount >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    {{ $adjustment->amount >= 0 ? 'Masuk' : 'Keluar' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Ledger Table -->
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <div class="p-6 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ledger Arus Kas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                    <tr>
                        <th class="px-6 py-4" scope="col">Tanggal</th>
                        <th class="px-6 py-4" scope="col">Kategori</th>
                        <th class="px-6 py-4" scope="col">Deskripsi</th>
                        <th class="px-6 py-4 text-center" scope="col">Metode</th>
                        <th class="px-6 py-4 text-right" scope="col">Masuk</th>
                        <th class="px-6 py-4 text-right" scope="col">Keluar</th>
                        <th class="px-6 py-4 text-right" scope="col">Saldo Cash</th>
                        <th class="px-6 py-4 text-right" scope="col">Saldo Bank</th>
                        <th class="px-6 py-4 text-center" scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    {{-- Initial Balance Row --}}
                    <tr class="bg-gray-50 dark:bg-gray-800/30 font-semibold">
                        <td class="px-6 py-4" colspan="4">Saldo Awal</td>
                        <td class="px-6 py-4 text-right">-</td>
                        <td class="px-6 py-4 text-right">-</td>
                        <td class="px-6 py-4 text-right text-gray-900 dark:text-white">Rp {{ number_format($initialBalances['cash'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-gray-900 dark:text-white">Rp {{ number_format($initialBalances['bank'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">-</td>
                    </tr>

                    @php
                        $runningCash = $initialBalances['cash'];
                        $runningBank = $initialBalances['bank'];
                    @endphp

                    @forelse($transactions as $transaction)
                        @php
                            if ($transaction['type'] === 'in') {
                                $runningCash += $transaction['cash_amount'];
                                $runningBank += $transaction['bank_amount'];
                            } else {
                                $runningCash -= $transaction['cash_amount'];
                                $runningBank -= $transaction['bank_amount'];
                            }
                        @endphp

                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $transaction['type'] === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $transaction['date']->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $transaction['type'] === 'in' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    {{ $transaction['category'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($transaction['reference_type'] === 'order')
                                    <a href="{{ route('pos.receipt', $transaction['reference']) }}" target="_blank" class="hover:text-primary transition-colors">
                                        {{ $transaction['description'] }}
                                    </a>
                                @else
                                    {{ $transaction['description'] }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs uppercase">{{ $transaction['payment_method'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold">
                                {{ $transaction['type'] === 'in' ? 'Rp ' . number_format($transaction['amount'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold">
                                {{ $transaction['type'] === 'out' ? 'Rp ' . number_format($transaction['amount'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($runningCash, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($runningBank, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($transaction['reference_type'] === 'adjustment')
                                    <button onclick="deleteTransaction({{ $transaction['reference'] }}, 'adjustment')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors" title="Hapus Transaksi">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-gray-300 dark:text-gray-600">receipt</span>
                                    <p>Tidak ada transaksi untuk periode ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                    {{-- Final Balance Row --}}
                    <tr class="bg-primary/10 dark:bg-primary/20 font-bold">
                        <td class="px-6 py-4" colspan="4">Saldo Akhir</td>
                        <td class="px-6 py-4 text-right text-emerald-600 dark:text-emerald-400">Rp {{ number_format($metrics['cash_in'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-red-600 dark:text-red-400">Rp {{ number_format($metrics['cash_out'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-primary dark:text-primary text-lg">Rp {{ number_format($runningCash, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-primary dark:text-primary text-lg">Rp {{ number_format($runningBank, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-center">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Balance Modal -->
    <div id="balanceModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 id="balanceModalTitle" class="text-lg font-bold text-gray-900 dark:text-white">Edit Saldo</h3>
                <button onclick="closeBalanceModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="balanceForm" class="p-6">
                @csrf
                <input type="hidden" id="balanceType" name="type" value="cash">

                <div class="mb-4">
                    <label for="balanceAmount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Jumlah Penyesuaian
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">Rp</span>
                        <input type="number" id="balanceAmount" name="amount" step="0.01" required
                            class="w-full pl-12 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="0">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Masukkan nilai positif untuk menambah saldo. Gunakan nilai negatif untuk mengurangi saldo.
                    </p>
                </div>

                <div class="mb-4">
                    <label for="balanceDateTime" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tanggal & Waktu
                    </label>
                    <input type="datetime-local" id="balanceDateTime" name="adjustment_date" required
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Kapan penyesuaian saldo ini dilakukan.
                    </p>
                </div>

                <div class="mb-4">
                    <label for="balanceNote" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Catatan (Opsional)
                    </label>
                    <textarea id="balanceNote" name="note" rows="2"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                        placeholder="Keterangan penyesuaian saldo..."></textarea>
                </div>

                <div id="balanceError" class="hidden mb-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg text-sm"></div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeBalanceModal()"
                        class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transfer Balance Modal -->
    <div id="transferModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 id="transferModalTitle" class="text-lg font-bold text-gray-900 dark:text-white">Transfer Saldo</h3>
                <button onclick="closeTransferModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="transferForm" class="p-6">
                @csrf
                <input type="hidden" id="transferFrom" name="from" value="cash">

                <div class="mb-4">
                    <label for="transferFrom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Dari
                    </label>
                    <div class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        <span id="transferFromLabel" class="font-medium">Cash</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="transferTo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Ke
                    </label>
                    <div class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                        <span id="transferToLabel" class="font-medium">Bank</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="transferAmount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Jumlah Transfer
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">Rp</span>
                        <input type="number" id="transferAmount" name="amount" step="0.01" min="0" required
                            class="w-full pl-12 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="0">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="transferDateTime" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tanggal & Waktu
                    </label>
                    <input type="datetime-local" id="transferDateTime" name="transfer_date" required
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="transferNote" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Catatan (Opsional)
                    </label>
                    <textarea id="transferNote" name="note" rows="2"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                        placeholder="Keterangan transfer..."></textarea>
                </div>

                <div id="transferError" class="hidden mb-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg text-sm"></div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeTransferModal()"
                        class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Hapus Transaksi</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 dark:text-gray-400 mb-4">Apakah Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan.</p>
                <input type="hidden" id="deleteTransactionId" value="">
                <input type="hidden" id="deleteTransactionType" value="">

                <div id="deleteError" class="hidden mb-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg text-sm"></div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Batal
                    </button>
                    <button type="button" onclick="confirmDelete()"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentBalanceType = 'cash';

        function openBalanceModal(type) {
            currentBalanceType = type;
            document.getElementById('balanceType').value = type;
            document.getElementById('balanceModalTitle').textContent =
                type === 'cash' ? 'Edit Saldo Cash' : 'Edit Saldo Bank';
            document.getElementById('balanceAmount').value = '';
            document.getElementById('balanceNote').value = '';
            document.getElementById('balanceError').classList.add('hidden');

            // Set current datetime in local format
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('balanceDateTime').value = `${year}-${month}-${day}T${hours}:${minutes}`;

            const modal = document.getElementById('balanceModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('balanceAmount').focus();
        }

        function closeBalanceModal() {
            const modal = document.getElementById('balanceModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Form submission
        document.getElementById('balanceForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const errorDiv = document.getElementById('balanceError');

            try {
                const response = await fetch('{{ route('admin.cashflow.update-balance') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        type: formData.get('type'),
                        amount: parseFloat(formData.get('amount')),
                        note: formData.get('note'),
                        adjustment_date: formData.get('adjustment_date'),
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    // Refresh the page to show updated balances
                    window.location.reload();
                } else {
                    errorDiv.textContent = data.message || 'Gagal menyimpan penyesuaian saldo';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                errorDiv.classList.remove('hidden');
            }
        });

        // Close modal on outside click
        document.getElementById('balanceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeBalanceModal();
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBalanceModal();
                closeTransferModal();
                closeDeleteModal();
            }
        });

        // Transfer Modal Functions
        function openTransferModal(from) {
            const to = from === 'cash' ? 'bank' : 'cash';
            document.getElementById('transferFrom').value = from;
            document.getElementById('transferFromLabel').textContent = from === 'cash' ? 'Cash' : 'Bank';
            document.getElementById('transferToLabel').textContent = to === 'cash' ? 'Cash' : 'Bank';
            document.getElementById('transferModalTitle').textContent = from === 'cash' ? 'Transfer Cash ke Bank' : 'Transfer Bank ke Cash';
            document.getElementById('transferAmount').value = '';
            document.getElementById('transferNote').value = '';
            document.getElementById('transferError').classList.add('hidden');

            // Set current datetime
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('transferDateTime').value = `${year}-${month}-${day}T${hours}:${minutes}`;

            const modal = document.getElementById('transferModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('transferAmount').focus();
        }

        function closeTransferModal() {
            const modal = document.getElementById('transferModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Transfer form submission
        document.getElementById('transferForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const errorDiv = document.getElementById('transferError');

            try {
                const response = await fetch('{{ route('admin.cashflow.transfer') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        from: formData.get('from'),
                        amount: parseFloat(formData.get('amount')),
                        note: formData.get('note'),
                        transfer_date: formData.get('transfer_date'),
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    errorDiv.textContent = data.message || 'Gagal melakukan transfer';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                errorDiv.classList.remove('hidden');
            }
        });

        // Close transfer modal on outside click
        document.getElementById('transferModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTransferModal();
            }
        });

        // Delete Transaction Functions
        function deleteTransaction(id, type) {
            document.getElementById('deleteTransactionId').value = id;
            document.getElementById('deleteTransactionType').value = type;
            document.getElementById('deleteError').classList.add('hidden');

            const modal = document.getElementById('deleteModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('deleteTransactionId').value = '';
            document.getElementById('deleteTransactionType').value = '';
        }

        async function confirmDelete() {
            const id = document.getElementById('deleteTransactionId').value;
            const type = document.getElementById('deleteTransactionType').value;
            const errorDiv = document.getElementById('deleteError');

            try {
                const response = await fetch('{{ route('admin.cashflow.delete-transaction') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        type: type,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    window.location.reload();
                } else {
                    errorDiv.textContent = data.message || 'Gagal menghapus transaksi';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                errorDiv.classList.remove('hidden');
            }
        }

        // Close delete modal on outside click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
@endpush
