@extends('layouts.admin')

@section('title', 'Create Item')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.items.index') }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Item</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.items.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf

    @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-lg shadow-sm p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <span class="material-symbols-outlined text-red-500">error</span>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Please fix the following errors:</h3>
                    <ul class="mt-2 text-sm text-red-700 dark:text-red-400 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Item Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Type Selection -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Item Type</h3>
                <p class="text-sm text-gray-500 mb-4">Select what this item can be used for:</p>

                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-colors
                        {{ old('is_purchase') ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-blue-400' }}">
                        <input type="checkbox" name="is_purchase" value="1" {{ old('is_purchase') ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            onchange="togglePurchaseFields()">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Purchase</p>
                            <p class="text-xs text-gray-500">Can be bought/restocked with inventory tracking</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-colors
                        {{ old('is_sales') ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-emerald-400' }}">
                        <input type="checkbox" name="is_sales" value="1" {{ old('is_sales') ? 'checked' : '' }}
                            class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                            onchange="toggleSalesFields()">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Sales</p>
                            <p class="text-xs text-gray-500">Can be sold in POS with BOM/recipes</p>
                        </div>
                    </label>
                </div>
                @error('type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Basic Information -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Name *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="e.g., Orange Juice">
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                        <div class="flex gap-2">
                            <select name="category_id" id="category_id"
                                class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">No Category</option>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->id }}" data-type="{{ $category->type }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" onclick="openCategoryModal()"
                                class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                title="Add New Category">
                                <span class="material-symbols-outlined">add</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Image</label>
                        <input type="file" name="image" id="image" accept="image/*"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <p class="mt-1 text-xs text-gray-500">JPG, PNG or GIF. Max 2MB.</p>
                    </div>
                </div>
            </div>

            <!-- Purchase Fields (shown when is_purchase = true) -->
            <div id="purchaseFields" class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 hidden">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Purchase Information</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit *</label>
                            <select name="unit" id="unit"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Select Unit</option>
                                <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>Grams (g)</option>
                                <option value="L" {{ old('unit') == 'L' ? 'selected' : '' }}>Liters (L)</option>
                                <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>Milliliters (ml)</option>
                                <option value="tbsp" {{ old('unit') == 'tbsp' ? 'selected' : '' }}>Tablespoons (tbsp)</option>
                                <option value="tsp" {{ old('unit') == 'tsp' ? 'selected' : '' }}>Teaspoons (tsp)</option>
                                <option value="cup" {{ old('unit') == 'cup' ? 'selected' : '' }}>Cups (cup)</option>
                                <option value="unit" {{ old('unit') == 'unit' ? 'selected' : '' }}>Units (unit)</option>
                                <option value="custom" {{ old('unit') == 'custom' ? 'selected' : '' }}>Custom (other)</option>
                            </select>
                        </div>

                        <div id="customUnitContainer" class="hidden">
                            <label for="custom_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Unit</label>
                            <input type="text" name="custom_unit" id="custom_unit" value="{{ old('custom_unit') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="e.g., pack, box, bottle">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Price *</label>
                            <div class="relative">
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 pl-3 text-gray-500 pointer-events-none">Rp</span>
                                <input type="number" name="unit_price" id="unit_price" value="{{ old('unit_price') }}" min="0" step="any"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="0">
                            </div>
                        </div>

                        <div>
                            <label for="current_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Stock *</label>
                            <input type="number" name="current_stock" id="current_stock" value="{{ old('current_stock', 0) }}" min="0" step="0.01"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label for="min_stock_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Stock Level *</label>
                            <input type="number" name="min_stock_level" id="min_stock_level" value="{{ old('min_stock_level', 0) }}" min="0" step="0.01"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Fields (shown when is_sales = true) -->
            <div id="salesFields" class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 hidden">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sales Information</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="selling_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Selling Price *</label>
                            <div class="relative">
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 pl-3 text-gray-500 pointer-events-none">Rp</span>
                                <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price') }}" min="0" step="1"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="0" onchange="updateHppAndProfit()">
                            </div>
                        </div>

                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SKU</label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="Optional">
                        </div>
                    </div>

                    <!-- HPP & Profit Display (Calculated from BOM) -->
                    <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">HPP (from BOM)</label>
                            <div class="relative">
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 pl-3 text-gray-500 pointer-events-none">Rp</span>
                                <input type="text" id="hppDisplay" readonly
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 font-medium"
                                    value="0">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Calculated from total BOM ingredients</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profit</label>
                            <div class="relative">
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 pl-3 text-gray-500 pointer-events-none">Rp</span>
                                <input type="text" id="profitDisplay" readonly
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-green-600 dark:text-green-400 font-medium"
                                    value="0">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Profit: <span id="profitMarginDisplay">0</span>%</p>
                        </div>
                    </div>

                    <div>
                        <label for="barcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode</label>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Optional">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Optional item description">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                            class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Active (Visible in POS)</label>
                    </div>
                </div>
            </div>

            <!-- Recipe (BOM) - Only for sales items -->
            <div id="recipeFields" class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 hidden">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recipe (BOM) - Ingredients</h3>
                <p class="text-sm text-gray-500 mb-4">Select the ingredients (purchase items) required to make this item.</p>

                <div id="recipe-container" class="space-y-3">
                    <!-- Recipe rows will be added here -->
                </div>

                <button type="button" onclick="addRecipeRow()"
                    class="mt-4 flex items-center gap-2 px-4 py-2 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:border-emerald-500 hover:text-emerald-600 transition-colors">
                    <span class="material-symbols-outlined text-lg">add</span>
                    Add Ingredient
                </button>
            </div>
        </div>

        <!-- Right Column: Actions -->
        <div class="space-y-6">
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>

                <div class="space-y-3">
                    <a href="{{ route('admin.items.index') }}"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <span class="material-symbols-outlined">cancel</span>
                        Cancel
                    </a>

                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-lg shadow-emerald-200 dark:shadow-none transition-colors">
                        <span class="material-symbols-outlined">save</span>
                        Save Item
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    const items = @json($items);
    let recipeCount = 0;

    function filterCategoryDropdown() {
        const purchaseCheckbox = document.querySelector('input[name="is_purchase"]');
        const salesCheckbox = document.querySelector('input[name="is_sales"]');
        const categorySelect = document.getElementById('category_id');
        const currentValue = categorySelect.value;

        // Show/hide options based on checkbox state
        const options = categorySelect.querySelectorAll('option[data-type]');
        options.forEach(option => {
            const categoryType = option.getAttribute('data-type');
            let shouldShow = false;

            if (purchaseCheckbox.checked && categoryType === 'material') {
                shouldShow = true;
            }
            if (salesCheckbox.checked && categoryType === 'product') {
                shouldShow = true;
            }

            option.style.display = shouldShow ? '' : 'none';
        });

        // If currently selected category is now hidden, clear selection
        const selectedOption = categorySelect.querySelector(`option[value="${currentValue}"]`);
        if (selectedOption && selectedOption.style.display === 'none') {
            categorySelect.value = '';
        }
    }

    function togglePurchaseFields() {
        const checkbox = document.querySelector('input[name="is_purchase"]');
        const fields = document.getElementById('purchaseFields');
        if (checkbox.checked) {
            fields.classList.remove('hidden');
        } else {
            fields.classList.add('hidden');
        }
        filterCategoryDropdown();
    }

    function toggleSalesFields() {
        const checkbox = document.querySelector('input[name="is_sales"]');
        const salesFields = document.getElementById('salesFields');
        const recipeFields = document.getElementById('recipeFields');
        if (checkbox.checked) {
            salesFields.classList.remove('hidden');
            recipeFields.classList.remove('hidden');
        } else {
            salesFields.classList.add('hidden');
            recipeFields.classList.add('hidden');
        }
        filterCategoryDropdown();
    }

    // Handle custom unit dropdown
    document.getElementById('unit').addEventListener('change', function() {
        const customContainer = document.getElementById('customUnitContainer');
        const customInput = document.getElementById('custom_unit');
        if (this.value === 'custom') {
            customContainer.classList.remove('hidden');
            customInput.required = true;
        } else {
            customContainer.classList.add('hidden');
            customInput.required = false;
        }
    });

    function addRecipeRow() {
        const container = document.getElementById('recipe-container');
        const rowId = Date.now();

        const row = document.createElement('div');
        row.className = 'flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg';
        row.id = 'recipe-row-' + rowId;

        row.innerHTML = `
            <select name="recipes[${rowId}][item_id]" required
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">Select Ingredient</option>
                ${items.map(i => `<option value="${i.id}">${i.name} (${i.unit})</option>`).join('')}
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
            updateHppAndProfit();
        }
    }

    // Calculate HPP from BOM recipes
    function calculateHppFromBom() {
        const container = document.getElementById('recipe-container');
        const rows = container.querySelectorAll('[id^="recipe-row-"]');
        let totalHpp = 0;

        rows.forEach(row => {
            const select = row.querySelector('select[name*="[item_id]"]');
            const qtyInput = row.querySelector('input[name*="[quantity_required]"]');

            if (select && qtyInput) {
                const itemId = select.value;
                const quantity = parseFloat(qtyInput.value) || 0;

                if (itemId && quantity) {
                    const item = items.find(i => i.id == itemId);
                    if (item && item.unit_price) {
                        totalHpp += quantity * item.unit_price;
                    }
                }
            }
        });

        return totalHpp;
    }

    // Update HPP and Profit displays
    function updateHppAndProfit() {
        const hpp = calculateHppFromBom();
        const sellingPrice = parseFloat(document.getElementById('selling_price').value) || 0;
        const profit = sellingPrice - hpp;
        const profitMargin = sellingPrice > 0 ? (profit / sellingPrice) * 100 : 0;

        document.getElementById('hppDisplay').value = formatNumber(hpp);
        document.getElementById('profitDisplay').value = formatNumber(profit);
        document.getElementById('profitMarginDisplay').textContent = profitMargin.toFixed(1);

        // Update profit color based on value
        const profitDisplay = document.getElementById('profitDisplay');
        if (profit >= 0) {
            profitDisplay.classList.remove('text-red-600', 'dark:text-red-400');
            profitDisplay.classList.add('text-green-600', 'dark:text-green-400');
        } else {
            profitDisplay.classList.remove('text-green-600', 'dark:text-green-400');
            profitDisplay.classList.add('text-red-600', 'dark:text-red-400');
        }
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    // Add event listeners for recipe changes
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name*="[item_id]"]') || e.target.matches('input[name*="[quantity_required]"]')) {
            updateHppAndProfit();
        }
    });

    // Add initial recipe row if sales is checked
    if (document.querySelector('input[name="is_sales"]').checked) {
        addRecipeRow();
        updateHppAndProfit();
    }

    // Initial category filter based on checkbox state
    filterCategoryDropdown();
</script>

<!-- Category Modal -->
<div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add New Category</h3>
            <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="categoryForm" onsubmit="submitCategory(event)">
            <div class="p-4 space-y-4">
                <div>
                    <label for="category_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category Name *</label>
                    <input type="text" name="name" id="category_name" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                        placeholder="e.g., Beverages, Food, etc.">
                </div>
                <div>
                    <label for="category_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category Type *</label>
                    <select name="type" id="category_type" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="product">Product (Sales Items)</option>
                        <option value="material">Material (Purchase Items)</option>
                    </select>
                </div>
                <div id="categoryError" class="hidden text-sm text-red-600"></div>
            </div>
            <div class="flex items-center justify-end gap-2 p-4 border-t dark:border-gray-700">
                <button type="button" onclick="closeCategoryModal()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">
                    Add Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCategoryModal() {
        const purchaseCheckbox = document.querySelector('input[name="is_purchase"]');
        const salesCheckbox = document.querySelector('input[name="is_sales"]');
        const typeSelect = document.getElementById('category_type');

        // Auto-select category type based on checkbox state
        if (purchaseCheckbox.checked && !salesCheckbox.checked) {
            typeSelect.value = 'material';
        } else if (salesCheckbox.checked && !purchaseCheckbox.checked) {
            typeSelect.value = 'product';
        } else {
            // Both checked or neither checked, default to product
            typeSelect.value = 'product';
        }

        document.getElementById('categoryModal').classList.remove('hidden');
        document.getElementById('categoryModal').classList.add('flex');
        document.getElementById('category_name').focus();
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.add('hidden');
        document.getElementById('categoryModal').classList.remove('flex');
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryError').classList.add('hidden');
    }

    async function submitCategory(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const errorDiv = document.getElementById('categoryError');

        try {
            const response = await fetch('{{ route('admin.categories.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Add new category to dropdown
                const select = document.getElementById('category_id');
                const option = document.createElement('option');
                option.value = data.category.id;
                option.textContent = data.category.name;
                option.setAttribute('data-type', data.category.type);
                option.selected = true;
                select.appendChild(option);

                closeCategoryModal();
            } else {
                errorDiv.textContent = data.message || 'Failed to create category';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.textContent = 'Failed to create category. Please try again.';
            errorDiv.classList.remove('hidden');
        }
    }
</script>
@endsection
