@extends('layouts.admin')

@section('title', 'Item Management')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div class="flex items-center gap-3 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0 no-scrollbar">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Items</h2>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <button onclick="openImportModal()" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">upload_file</span>
                Import
            </button>
            <a href="{{ route('admin.items.export', ['type' => $typeFilter]) }}"
                class="flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">download</span>
                Export
            </a>
            <a href="{{ route('admin.items.create') }}"
                class="flex items-center justify-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all">
                <span class="material-symbols-outlined text-lg">add</span>
                Add New Item
            </a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Total</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalItems }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Purchase</p>
            <p class="text-2xl font-bold text-blue-500 mt-1">{{ $purchaseItems }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Sales</p>
            <p class="text-2xl font-bold text-emerald-500 mt-1">{{ $salesItems }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Both</p>
            <p class="text-2xl font-bold text-purple-500 mt-1">{{ $bothItems }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Low Stock</p>
            <p class="text-2xl font-bold text-orange-500 mt-1">{{ $lowStock }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Out of Stock</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ $outOfStock }}</p>
        </div>
    </div>

    <!-- Type Filters -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.items.index', ['type' => 'all']) }}"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $typeFilter === 'all' ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            All Items
        </a>
        <a href="{{ route('admin.items.index', ['type' => 'purchase']) }}"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $typeFilter === 'purchase' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            Purchase Only
        </a>
        <a href="{{ route('admin.items.index', ['type' => 'sales']) }}"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $typeFilter === 'sales' ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            Sales Only
        </a>
        <a href="{{ route('admin.items.index', ['type' => 'both']) }}"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $typeFilter === 'both' ? 'bg-purple-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            Both
        </a>
    </div>

    <!-- Item Table -->
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-border-light dark:border-border-dark">
                        <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item Name</th>
                        <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                        <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">HPP & Profit</th>
                        <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock</th>
                        <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center overflow-hidden
                                        {{ $item->is_purchase && !$item->is_sales ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-500' : ($item->is_sales && !$item->is_purchase ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-500' : 'bg-purple-100 dark:bg-purple-900/30 text-purple-500') }}">
                                        @if($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" class="w-full h-full object-contain" alt="{{ $item->name }}">
                                        @else
                                            <span class="material-symbols-outlined text-lg">
                                                {{ $item->is_purchase && !$item->is_sales ? 'inventory_2' : ($item->is_sales && !$item->is_purchase ? 'local_cafe' : 'sync_alt') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $item->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                @if($item->is_purchase && !$item->is_sales)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                        Purchase
                                    </span>
                                @elseif($item->is_sales && !$item->is_purchase)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        Sales
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                                        Both
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <div class="space-y-1">
                                    @if($item->is_purchase)
                                        <p class="text-xs text-gray-500">Buy: <span class="font-medium text-gray-700 dark:text-gray-300">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span> / {{ $item->unit }}</p>
                                    @endif
                                    @if($item->is_sales)
                                        <p class="text-xs text-gray-500">Sell: <span class="font-medium text-emerald-600 dark:text-emerald-400">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</span></p>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                @if($item->is_sales)
                                    @php
                                                                        $calculatedHpp = $item->calculated_hpp;
                                                                        $profit = $item->profit;
                                                                        $profitMargin = $item->profit_margin;
                                                                    @endphp
                                    <div class="space-y-1">
                                        <p class="text-xs text-gray-500">HPP: <span class="font-medium text-gray-700 dark:text-gray-300">Rp {{ number_format($calculatedHpp, 0, ',', '.') }}</span></p>
                                        <p class="text-xs {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            Profit: <span class="font-medium">Rp {{ number_format($profit, 0, ',', '.') }}</span>
                                            <span class="text-xs text-gray-400">({{ number_format($profitMargin, 1) }}%)</span>
                                        </p>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($item->is_purchase)
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium {{ $item->current_stock <= 0 ? 'text-red-500' : ($item->current_stock <= $item->min_stock_level ? 'text-orange-500' : 'text-gray-900 dark:text-white') }}">
                                            {{ number_format($item->current_stock, 2) }} {{ $item->unit }}
                                        </span>
                                        <span class="text-xs text-gray-500">Min: {{ number_format($item->min_stock_level, 2) }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($item->is_sales)
                                    @if($item->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Inactive
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($item->is_purchase)
                                        <button onclick="openRestockModal({{ $item->id }}, '{{ $item->name }}', '{{ $item->unit }}')"
                                            class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-md transition-colors"
                                            title="Restock">
                                            <span class="material-symbols-outlined text-lg">add_circle</span>
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.items.edit', $item->id) }}"
                                        class="p-1.5 text-gray-500 hover:text-primary hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-md transition-colors"
                                        title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-colors"
                                            title="Delete">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-border-light dark:border-border-dark">
            {{ $items->appends(['type' => $typeFilter, 'search' => $search])->links() }}
        </div>
    </div>

    <!-- Restock Modal -->
    <div id="restockModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 m-4 max-w-sm w-full">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Restock Item</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Restock: <span id="restockItemName" class="font-medium text-gray-900 dark:text-white"></span>
            </p>
            <form id="restockForm" method="POST" action="">
                @csrf
                <input type="hidden" name="quantity" id="restockQuantity" value="">
                <div class="mb-4">
                    <label for="restockAmount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity to Add</label>
                    <input type="number" name="quantity" id="restockAmount" required min="0.01" step="any"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="mb-4">
                    <label for="restockNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (Optional)</label>
                    <textarea name="notes" id="restockNotes" rows="2"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRestockModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        Restock
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 m-4 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Import Items</h3>

            @if(session('import_errors'))
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <h4 class="text-sm font-semibold text-red-800 dark:text-red-400 mb-2">Import Errors:</h4>
                    <ul class="text-xs text-red-700 dark:text-red-300 space-y-1 max-h-40 overflow-y-auto">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-4">
                <a href="{{ route('admin.items.template') }}" class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:underline mb-4">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Download Template CSV
                </a>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Gunakan template untuk format yang benar. Format yang didukung: CSV, XLS (dari export)
                </p>
            </div>

            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-lg text-blue-600 dark:text-blue-400">info</span>
                    <div class="text-xs text-blue-700 dark:text-blue-300">
                        <p class="font-medium mb-1">Import Mendukung:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            <li>File CSV dengan data item</li>
                            <li>File XLS dari export (termasuk BOM/Resep)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.items.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="importFile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Pilih File (CSV/XLS)
                    </label>
                    <input type="file" name="file" id="importFile" accept=".csv,.txt,.xls,.xlsx" required
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeImportModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRestockModal(itemId, itemName, unit) {
            const modal = document.getElementById('restockModal');
            const form = document.getElementById('restockForm');
            document.getElementById('restockItemName').textContent = itemName + ' (' + unit + ')';
            form.action = '/branch/items/' + itemId + '/restock';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeRestockModal() {
            const modal = document.getElementById('restockModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('restockForm').reset();
        }

        function openImportModal() {
            const modal = document.getElementById('importModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeImportModal() {
            const modal = document.getElementById('importModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endsection
