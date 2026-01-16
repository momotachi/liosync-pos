@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div class="flex items-center gap-3 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0 no-scrollbar">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Inventory (Raw Materials)</h2>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.raw-materials.create') }}"
                class="flex items-center justify-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all w-full sm:w-auto">
                <span class="material-symbols-outlined text-lg">add</span>
                Add New Material
            </a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div
            class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Total Items</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalItems }}</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 text-blue-500 rounded-lg">
                <span class="material-symbols-outlined">inventory_2</span>
            </div>
        </div>
        <div
            class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">In Stock</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $activeItems }}</p>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 rounded-lg">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
        </div>
        <div
            class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Out of Stock</p>
                <p class="text-2xl font-bold text-red-500 mt-1">{{ $outOfStock }}</p>
            </div>
            <div class="p-3 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-lg">
                <span class="material-symbols-outlined">warning</span>
            </div>
        </div>
        <div
            class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Low Stock</p>
                <p class="text-2xl font-bold text-orange-500 mt-1">{{ $lowStock }}</p>
            </div>
            <div class="p-3 bg-orange-50 dark:bg-orange-900/20 text-orange-500 rounded-lg">
                <span class="material-symbols-outlined">trending_down</span>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-border-light dark:border-border-dark">
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Material</th>
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Stock</th>
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Min Level</th>
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Unit Price</th>
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($rawMaterials as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    @if($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-12 h-12 rounded-lg object-contain bg-white dark:bg-gray-800">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-500">
                                            <span class="material-symbols-outlined">inventory_2</span>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Unit: {{ $item->unit }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($item->current_stock, 2) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->unit }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-gray-700 dark:text-gray-300 font-medium">
                                    {{ number_format($item->min_stock_level, 2) }} {{ $item->unit }}
                                </p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="text-gray-700 dark:text-gray-300 font-medium">
                                    @if($item->unit_price)
                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </p>
                            </td>
                            <td class="py-4 px-6">
                                @if($item->current_stock <= 0)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                        Out of Stock
                                    </span>
                                @elseif($item->current_stock <= $item->min_stock_level)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                                        Low Stock
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Good
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openRestockModal({{ $item->id }}, '{{ $item->name }}', {{ $item->current_stock }}, '{{ $item->unit }}')"
                                        class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-md transition-colors"
                                        title="Restock">
                                        <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                                    </button>
                                    <a href="{{ route('admin.raw-materials.edit', $item->id) }}"
                                        class="p-1.5 text-gray-500 hover:text-primary hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-md transition-colors"
                                        title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600 mb-3">inventory_2</span>
                                    <p class="text-gray-500 dark:text-gray-400">No raw materials found.</p>
                                    <a href="{{ route('admin.raw-materials.create') }}" class="mt-2 text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 text-sm font-medium">
                                        Add your first material →
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-border-light dark:border-border-dark">
            {{ $rawMaterials->links() }}
        </div>
    </div>

    <!-- Restock Modal -->
    <div id="restockModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Restock Material</h3>
                <button onclick="closeRestockModal()" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-gray-500">close</span>
                </button>
            </div>

            <form method="POST" action="/branch/raw-materials/id/restock" id="restockForm" class="p-6 space-y-4">
                @csrf

                <input type="hidden" name="material_id" id="restock_material_id">

                <!-- Current Stock Info -->
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Material</span>
                        <span class="font-medium text-gray-900 dark:text-white" id="restock_material_name">-</span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Current Stock</span>
                        <span class="font-medium text-gray-900 dark:text-white" id="restock_current_stock">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Min Level</span>
                        <span class="font-medium text-gray-900 dark:text-white" id="restock_min_level">-</span>
                    </div>
                </div>

                <!-- Quantity to Add -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity to Add *</label>
                    <input type="number" name="quantity" id="restock_quantity" required min="0.01" step="0.01"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="Enter quantity">
                    <p class="mt-1 text-xs text-gray-500">New stock will be: <span id="restock_new_stock" class="font-medium">-</span></p>
                </div>

                <!-- Supplier -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supplier</label>
                    <input type="text" name="supplier" id="restock_supplier"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="e.g., Fresh Farms Inc.">
                </div>

                <!-- Unit Cost -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Cost</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                        <input type="number" name="unit_cost" id="restock_unit_cost" min="0" step="1"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="0">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Total cost: <span id="restock_total_cost" class="font-medium">Rp 0</span></p>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="Optional notes..."></textarea>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeRestockModal()"
                        class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-lg shadow-emerald-200 dark:shadow-none transition-colors">
                        Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRestockModal(id, name, currentStock, unit) {
            const modal = document.getElementById('restockModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('restock_material_id').value = id;
            document.getElementById('restock_material_name').textContent = name;
            document.getElementById('restock_current_stock').textContent = currentStock + ' ' + unit;
            document.getElementById('restockForm').action = '/branch/raw-materials/' + id + '/restock';

            // Set min level (will be updated if needed)
            document.getElementById('restock_min_level').textContent = '(See edit for min level)';

            // Calculate new stock on input change
            document.getElementById('restock_quantity').addEventListener('input', function() {
                const newStock = parseFloat(currentStock) + (parseFloat(this.value) || 0);
                document.getElementById('restock_new_stock').textContent = newStock.toFixed(2) + ' ' + unit;
            });

            // Calculate total cost
            document.getElementById('restock_unit_cost').addEventListener('input', function() {
                const quantity = parseFloat(document.getElementById('restock_quantity').value) || 0;
                const unitCost = parseFloat(this.value) || 0;
                const total = quantity * unitCost;
                document.getElementById('restock_total_cost').textContent = 'Rp ' + total.toLocaleString('id-ID');
            });
        }

        function closeRestockModal() {
            const modal = document.getElementById('restockModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('restockForm').reset();
        }

        // Close modal on outside click
        document.getElementById('restockModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRestockModal();
            }
        });
    </script>
@endsection
