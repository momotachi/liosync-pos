@extends('layouts.admin')

@section('title', 'Stock Transactions')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
    <div class="flex items-center gap-3">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Stock Transactions</h2>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.stock-transactions.export', $_GET) }}"
            class="flex items-center justify-center gap-2 px-4 py-2.5 border border-emerald-300 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-sm font-medium rounded-lg transition-colors">
            <span class="material-symbols-outlined text-lg">download</span>
            Export CSV
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Total Transactions</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_transactions'] }}</p>
        </div>
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 text-blue-500 rounded-lg">
            <span class="material-symbols-outlined">receipt_long</span>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Total Stock In</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['total_in'], 2) }}</p>
        </div>
        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 rounded-lg">
            <span class="material-symbols-outlined">trending_up</span>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Total Stock Out</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($stats['total_out'], 2) }}</p>
        </div>
        <div class="p-3 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-lg">
            <span class="material-symbols-outlined">trending_down</span>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Total Purchase Cost</p>
            <p class="text-2xl font-bold text-purple-600 mt-1">Rp {{ number_format($stats['total_cost'], 0, ',', '.') }}</p>
        </div>
        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 text-purple-500 rounded-lg">
            <span class="material-symbols-outlined">attach_money</span>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-6">
    <form method="GET" action="{{ route('admin.stock-transactions.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Material</label>
            <select name="material_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">All Materials</option>
                @foreach($rawMaterials as $material)
                    <option value="{{ $material->id }}" {{ request('material_id') == $material->id ? 'selected' : '' }}>
                        {{ $material->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
            <select name="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">All Types</option>
                <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Stock In</option>
                <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Stock Out</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm font-medium">
                Filter
            </button>
            <a href="{{ route('admin.stock-transactions.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm">
                Clear
            </a>
        </div>
    </form>

    @if(request()->hasAny(['material_id', 'type', 'start_date', 'end_date']))
        <div class="mt-4 flex items-center gap-2">
            <span class="text-sm text-gray-500">Active filters:</span>
            @if(request('material_id'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    Material: {{ $rawMaterials->find(request('material_id'))->name ?? request('material_id') }}
                </span>
            @endif
            @if(request('type'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ request('type') === 'in' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                    {{ request('type') === 'in' ? 'Stock In' : 'Stock Out' }}
                </span>
            @endif
            @if(request('start_date') || request('end_date'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ request('start_date') ?? '...' }} to {{ request('end_date') ?? '...' }}
                </span>
            @endif
        </div>
    @endif
</div>

<!-- Transactions Table -->
<div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800 border-b border-border-light dark:border-border-dark">
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Date & Time</th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Material</th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Type</th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Quantity</th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Supplier</th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Cost</th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Description</th>
                    <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Reference</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="py-4 px-6">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $transaction->created_at->format('M d, Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $transaction->created_at->format('H:i:s') }}
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-500">
                                    <span class="material-symbols-outlined text-lg">inventory_2</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $transaction->rawMaterial->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->rawMaterial->unit ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            @if($transaction->type === 'in')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    Stock In
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                    Stock Out
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-gray-700 dark:text-gray-300 font-medium">
                            <span class="{{ $transaction->type === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'in' ? '+' : '-' }}{{ number_format($transaction->quantity, 2) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-gray-600 dark:text-gray-400 text-sm">
                            {{ $transaction->supplier ?? '-' }}
                        </td>
                        <td class="py-4 px-6 text-gray-900 dark:text-white font-medium">
                            @if($transaction->type === 'in' && $transaction->total_cost)
                                <div>Rp {{ number_format($transaction->total_cost, 0, ',', '.') }}</div>
                                @if($transaction->unit_cost)
                                    <div class="text-xs text-gray-500">Rp {{ number_format($transaction->unit_cost, 0, ',', '.') }}/{{ $transaction->rawMaterial->unit ?? 'unit' }}</div>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                {{ $transaction->description ?? '-' }}
                            </p>
                        </td>
                        <td class="py-4 px-6">
                            @if($transaction->reference_id && $transaction->order)
                                <a href="{{ route('pos.receipt', $transaction->reference_id) }}"
                                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    Order #{{ str_pad($transaction->reference_id, 6, '0', STR_PAD_LEFT) }}
                                </a>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-4xl text-gray-300 mb-2">receipt_long</span>
                                <p class="text-gray-500">No stock transactions found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-border-light dark:border-border-dark">
            {{ $transactions->appends($_GET)->links() }}
        </div>
    @endif
</div>
@endsection
