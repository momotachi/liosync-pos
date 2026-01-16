@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.products.index') }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Product</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Product Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Product Information</h3>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="e.g., Orange Juice">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                            <select name="category_id" id="category_id" required
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="selling_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Jual *</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                                <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price') }}" required min="0" step="1"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">HPP (Auto dari BOM)</label>
                        <div class="relative">
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 pl-3 text-gray-500 pointer-events-none">Rp</span>
                            <input type="text" id="hpp_display" readonly
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white cursor-not-allowed"
                                placeholder="0 (dihitung dari BOM)">
                            <p class="mt-1 text-xs text-gray-500">HPP dihitung otomatis dari total bahan baku (BOM)</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Master Profit (Harga Jual - HPP)</label>
                        <div class="relative">
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 pl-3 text-gray-500 pointer-events-none">Rp</span>
                            <input type="text" id="profit_display" readonly
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 font-medium cursor-not-allowed"
                                placeholder="0 (Profit)">
                            <p class="mt-1 text-xs text-gray-500">Master profit = Harga Jual - HPP</p>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Optional product description">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Image</label>
                        <input type="file" name="image" id="image" accept="image/*"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG or GIF. Max 2MB.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                            class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Active (Visible in POS)</label>
                    </div>
                </div>
            </div>

            <!-- Recipe (BOM) -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recipe (BOM) - Raw Materials</h3>
                <p class="text-sm text-gray-500 mb-4">Select the raw materials required to make this product.</p>

                <div id="recipe-container" class="space-y-3">
                    <!-- Recipe rows will be added here -->
                </div>

                <button type="button" onclick="addRecipeRow()"
                    class="mt-4 flex items-center gap-2 px-4 py-2 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:border-emerald-500 hover:text-emerald-600 transition-colors">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Add Material
                </button>
            </div>
        </div>

        <!-- Right Column: Preview -->
        <div class="space-y-6">
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Preview</h3>

                <div class="aspect-square rounded-xl bg-linear-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 flex items-center justify-center mb-4">
                    <div class="text-center">
                        <span class="material-symbols-outlined text-6xl text-emerald-300 dark:text-emerald-700">local_cafe</span>
                        <p class="mt-2 text-sm text-gray-500">Product Image</p>
                    </div>
                </div>

                <h4 class="font-bold text-gray-900 dark:text-white text-lg" id="preview-name">Product Name</h4>
                <p class="text-sm text-gray-500 mb-2" id="preview-category">Category</p>
                <p class="text-2xl font-bold text-emerald-600" id="preview-price">Rp 0</p>
            </div>

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>

                <div class="space-y-3">
                    <a href="{{ route('admin.products.index') }}"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <span class="material-symbols-outlined">cancel</span>
                        Cancel
                    </a>

                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-lg shadow-emerald-200 dark:shadow-none transition-colors">
                        <span class="material-symbols-outlined">save</span>
                        Save Product
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    const rawMaterials = @json($rawMaterials);

    function addRecipeRow() {
        const container = document.getElementById('recipe-container');
        const rowId = Date.now();

        const row = document.createElement('div');
        row.className = 'flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg';
        row.id = 'recipe-row-' + rowId;

        row.innerHTML = `
            <select name="recipes[${rowId}][raw_material_id]" required
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">Select Material</option>
                ${rawMaterials.map(m => `<option value="${m.id}">${m.name} (${m.unit})</option>`).join('')}
            </select>
            <input type="number" name="recipes[${rowId}][quantity_required]" placeholder="Qty" required min="0" step="any"
                class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
            <button type="button" onclick="removeRecipeRow('${rowId}')" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                <span class="material-symbols-outlined">delete</span>
            </button>
        `;

        container.appendChild(row);
    }

    function removeRecipeRow(rowId) {
        const row = document.getElementById('recipe-row-' + rowId);
        if (row) {
            row.remove();
        }
    }

    // Add initial row
    addRecipeRow();

    // Live preview
    document.getElementById('name').addEventListener('input', function() {
        document.getElementById('preview-name').textContent = this.value || 'Product Name';
    });

    document.getElementById('category_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        document.getElementById('preview-category').textContent = selected.text || 'Category';
    });

    document.getElementById('selling_price').addEventListener('input', function() {
        const value = parseFloat(this.value) || 0;
        document.getElementById('preview-price').textContent = 'Rp ' + value.toLocaleString('id-ID');
        calculateProfit();
    });

    // Calculate profit function
    function calculateProfit() {
        const sellingPrice = parseFloat(document.getElementById('selling_price').value) || 0;
        const hpp = parseFloat(document.getElementById('hpp_display').value.replace(/\./g, '').replace(',', '.')) || 0;
        const profit = sellingPrice - hpp;
        document.getElementById('profit_display').value = profit.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    // Initial profit calculation
    calculateProfit();
</script>
@endsection