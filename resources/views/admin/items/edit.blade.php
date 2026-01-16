@extends('layouts.admin')

@section('title', 'Edit Item')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.items.index') }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Item: {{ $item->name }}</h2>
    </div>
</div>

<form id="editItemForm" method="POST" action="{{ route('admin.items.update', $item->id) }}" enctype="multipart/form-data" class="space-y-6" novalidate>
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Item Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Type Selection -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Item Type</h3>
                <p class="text-sm text-gray-500 mb-4">Select what this item can be used for:</p>

                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-colors
                        {{ $item->is_purchase ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-blue-400' }}">
                        <input type="checkbox" name="is_purchase" value="1" {{ $item->is_purchase ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            onchange="togglePurchaseFields()">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Purchase</p>
                            <p class="text-xs text-gray-500">Can be bought/restocked with inventory tracking</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-colors
                        {{ $item->is_sales ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-emerald-400' }}">
                        <input type="checkbox" name="is_sales" value="1" {{ $item->is_sales ? 'checked' : '' }}
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
                        <input type="text" name="name" id="name" value="{{ old('name', $item->name) }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="e.g., Orange Juice">
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                        <select name="category_id" id="category_id"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">No Category</option>
                            @foreach($allCategories as $category)
                                <option value="{{ $category->id }}" data-type="{{ $category->type }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Image</label>
                        @if($item->image)
                            <div class="flex items-center gap-4">
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-20 h-20 object-contain rounded-lg border bg-white dark:bg-gray-800">
                                <div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">Current Image</p>
                                    <label class="flex items-center gap-2 text-sm text-emerald-600 hover:text-emerald-700 cursor-pointer">
                                        <span class="material-symbols-outlined">edit</span>
                                        Change Image
                                    </label>
                                </div>
                            </div>
                        @else
                            <div>
                                <input type="file" name="image" id="image" accept="image/*"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="mt-1 text-xs text-gray-500">JPG, PNG or GIF. Max 2MB.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Purchase Fields (shown when is_purchase = true) -->
            <div id="purchaseFields" class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 {{ !$item->is_purchase ? 'hidden' : '' }}">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Purchase Information</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit *</label>
                            <select name="unit" id="unit"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Select Unit</option>
                                <option value="pcs" {{ old('unit', $item->unit) == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                <option value="kg" {{ old('unit', $item->unit) == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                <option value="g" {{ old('unit', $item->unit) == 'g' ? 'selected' : '' }}>Grams (g)</option>
                                <option value="L" {{ old('unit', $item->unit) == 'L' ? 'selected' : '' }}>Liters (L)</option>
                                <option value="ml" {{ old('unit', $item->unit) == 'ml' ? 'selected' : '' }}>Milliliters (ml)</option>
                                <option value="tbsp" {{ old('unit', $item->unit) == 'tbsp' ? 'selected' : '' }}>Tablespoons (tbsp)</option>
                                <option value="tsp" {{ old('unit', $item->unit) == 'tsp' ? 'selected' : '' }}>Teaspoons (tsp)</option>
                                <option value="cup" {{ old('unit', $item->unit) == 'cup' ? 'selected' : '' }}>Cups (cup)</option>
                                <option value="unit" {{ old('unit', $item->unit) == 'unit' ? 'selected' : '' }}>Units (unit)</option>
                                <option value="custom" {{ old('unit', $item->unit) == 'custom' ? 'selected' : '' }}>Custom (other)</option>
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
                                <input type="number" name="unit_price" id="unit_price" value="{{ old('unit_price', $item->unit_price) }}" min="0" step="any"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="0">
                            </div>
                        </div>

                        <div>
                            <label for="current_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Stock *</label>
                            <input type="number" name="current_stock" id="current_stock" value="{{ old('current_stock', $item->current_stock) }}" min="0" step="0.01"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        <div>
                            <label for="min_stock_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Min Stock Level *</label>
                            <input type="number" name="min_stock_level" id="min_stock_level" value="{{ old('min_stock_level', $item->min_stock_level) }}" min="0" step="0.01"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Fields (shown when is_sales = true) -->
            <div id="salesFields" class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 {{ !$item->is_sales ? 'hidden' : '' }}">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sales Information</h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="selling_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Selling Price *</label>
                            <div class="relative">
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 pl-3 text-gray-500 pointer-events-none">Rp</span>
                                <input type="number" name="selling_price" id="selling_price" value="{{ old('selling_price', $item->selling_price) }}" min="0" step="1"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="0" onchange="updateHppAndProfit()">
                            </div>
                        </div>

                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SKU</label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku', $item->sku) }}"
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
                                    value="{{ number_format($item->calculated_hpp, 0, ',', '.') }}">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Calculated from total BOM ingredients</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profit</label>
                            <div class="relative">
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 pl-3 text-gray-500 pointer-events-none">Rp</span>
                                <input type="text" id="profitDisplay" readonly
                                    class="w-full pl-10 pr-4 py-2.5 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg {{ $item->profit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium"
                                    value="{{ number_format($item->profit, 0, ',', '.') }}">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Profit: <span id="profitMarginDisplay">{{ number_format($item->profit_margin, 1) }}</span>%</p>
                        </div>
                    </div>

                    <div>
                        <label for="barcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode</label>
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $item->barcode) }}"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Optional">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Optional item description">{{ old('description', $item->description) }}</textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Active (Visible in POS)</label>
                    </div>
                </div>
            </div>

            <!-- Recipe (BOM) - Only for sales items -->
            <div id="recipeFields" class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 {{ !$item->is_sales ? 'hidden' : '' }}">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recipe (BOM) - Ingredients</h3>
                <p class="text-sm text-gray-500 mb-4">Select the ingredients (purchase items) required to make this item.</p>

                <div id="recipe-container" class="space-y-3">
                    @foreach($item->itemRecipes as $index => $recipe)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg" id="recipe-row-{{ $loop->iteration }}">
                        <select name="recipes[{{ $loop->iteration }}][item_id]" required
                            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500"
                            onchange="updateRecipeAmount('{{ $loop->iteration }}')">
                            <option value="">Select Ingredient</option>
                            @foreach($items as $itemOption)
                            <option value="{{ $itemOption->id }}" {{ $recipe->ingredient_item_id == $itemOption->id ? 'selected' : '' }}
                                    data-unit-price="{{ $itemOption->unit_price }}">
                                {{ $itemOption->name }} ({{ $itemOption->unit }})
                            </option>
                            @endforeach
                        </select>
                        <input type="number" name="recipes[{{ $loop->iteration }}][quantity_required]" value="{{ $recipe->quantity_required }}" placeholder="Qty" required min="0" step="any"
                            class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500"
                            onchange="updateRecipeAmount('{{ $loop->iteration }}')">
                        <div class="w-32 px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 font-medium" id="amount-{{ $loop->iteration }}">
                            Rp {{ number_format($recipe->quantity_required * ($recipe->ingredient->unit_price ?? 0), 0, ',', '.') }}
                        </div>
                        <button type="button" onclick="removeRecipeRow('{{ $loop->iteration }}')" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                    @endforeach
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
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors relative z-10">
                        <span class="material-symbols-outlined">cancel</span>
                        Cancel
                    </a>

                    <button type="submit" id="submitBtn"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-lg shadow-emerald-200 dark:shadow-none transition-colors relative z-10">
                        <span class="material-symbols-outlined">save</span>
                        <span id="btnText">Update Item</span>
                    </button>
                </div>
            </div>

            @if($item->orderItems()->exists())
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400">warning</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-400">Order History Exists</p>
                        <p class="text-xs text-yellow-700 dark:text-yellow-500 mt-1">This item has been ordered and cannot be deleted, but can be edited.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</form>

<!-- Result Modal -->
<div id="resultModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 m-4 max-w-sm w-full transform transition-all">
        <div class="text-center">
            <div id="modalIcon" class="mx-auto mb-4"></div>
            <h3 id="modalTitle" class="text-xl font-bold text-gray-900 dark:text-white mb-2"></h3>
            <p id="modalMessage" class="text-gray-600 dark:text-gray-400 mb-6"></p>
            <div class="flex gap-3">
                <button onclick="closeModal()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    Tutup
                </button>
                <button id="modalActionBtn" onclick="redirectToList()" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    Ke List Item
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const items = @json($items);
    let recipeCount = {{ $item->itemRecipes->count() }};

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
    const currentUnit = '{{ $item->unit }}';
    const unitSelect = document.getElementById('unit');
    const customUnitContainer = document.getElementById('customUnitContainer');
    const customUnitInput = document.getElementById('custom_unit');

    // Initialize: ensure hidden field is not required
    customUnitInput.required = false;

    unitSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customUnitContainer.classList.remove('hidden');
            customUnitInput.required = true;
        } else {
            customUnitContainer.classList.add('hidden');
            customUnitInput.required = false;
        }
    });

    // Show custom unit field if item already has custom unit
    if (!['pcs', 'kg', 'g', 'L', 'ml', 'tbsp', 'tsp', 'cup', 'unit'].includes(currentUnit)) {
        unitSelect.value = 'custom';
        customUnitContainer.classList.remove('hidden');
        customUnitInput.value = currentUnit;
        customUnitInput.required = true;
    } else if (currentUnit === 'custom') {
        customUnitContainer.classList.remove('hidden');
        customUnitInput.required = true;
    } else {
        // Standard unit: ensure field is hidden and not required
        customUnitContainer.classList.add('hidden');
        customUnitInput.required = false;
    }

    function addRecipeRow() {
        const container = document.getElementById('recipe-container');
        const rowId = ++recipeCount;

        const row = document.createElement('div');
        row.className = 'flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg';
        row.id = 'recipe-row-' + rowId;

        row.innerHTML = `
            <select name="recipes[${rowId}][item_id]" required onchange="updateRecipeAmount('${rowId}')"
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">Select Ingredient</option>
                ${items.map(i => `<option value="${i.id}" data-unit-price="${i.unit_price}">${i.name} (${i.unit})</option>`).join('')}
            </select>
            <input type="number" name="recipes[${rowId}][quantity_required]" placeholder="Qty" required min="0" step="any" onchange="updateRecipeAmount('${rowId}')"
                class="w-24 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
            <div class="w-32 px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 font-medium" id="amount-${rowId}">
                Rp 0
            </div>
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

    function updateRecipeAmount(rowId) {
        const row = document.getElementById('recipe-row-' + rowId);
        if (!row) return;

        const select = row.querySelector('select[name*="[item_id]"]');
        const qtyInput = row.querySelector('input[name*="[quantity_required]"]');
        const amountDisplay = document.getElementById('amount-' + rowId);

        if (select && qtyInput && amountDisplay) {
            const selectedOption = select.options[select.selectedIndex];
            const unitPrice = parseFloat(selectedOption?.getAttribute('data-unit-price')) || 0;
            const quantity = parseFloat(qtyInput.value) || 0;
            const amount = quantity * unitPrice;

            amountDisplay.textContent = 'Rp ' + formatNumber(amount);

            // Update HPP total when amount changes
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

    // Initial calculation on page load - update all recipe amounts
    if (document.querySelector('input[name="is_sales"]').checked) {
        const container = document.getElementById('recipe-container');
        if (container) {
            const rows = container.querySelectorAll('[id^="recipe-row-"]');
            rows.forEach(row => {
                const rowId = row.id.replace('recipe-row-', '');
                updateRecipeAmount(rowId);
            });
        }
        updateHppAndProfit();
    }

    // Initial category filter based on checkbox state
    filterCategoryDropdown();

    // Form submission with AJAX
    document.getElementById('editItemForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted');

        const form = this;
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const formData = new FormData(form);

        console.log('Form action:', form.action);

        // Ensure all boolean checkboxes are included (even when unchecked)
        const checkboxNames = ['is_purchase', 'is_sales', 'is_active'];
        checkboxNames.forEach(name => {
            const checkbox = form.querySelector(`input[name="${name}"]`);
            if (checkbox) {
                // Remove all existing values for this checkbox
                formData.delete(name);
                // Add the current state (1 if checked, 0 if unchecked)
                formData.append(name, checkbox.checked ? '1' : '0');
                console.log(`Checkbox ${name}:`, checkbox.checked);
            }
        });

        // Show loading state
        submitBtn.disabled = true;
        btnText.textContent = 'Menyimpan...';
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

        console.log('Sending request...');

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.json().then(err => {
                    console.error('Validation error:', err);
                    throw new Error(err.message || 'Terjadi kesalahan validasi');
                }).catch(() => {
                    throw new Error('Terjadi kesalahan saat memproses permintaan');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showModal('success', 'Berhasil!', data.message);
            } else {
                showModal('error', 'Gagal!', data.message || 'Terjadi kesalahan.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showModal('error', 'Gagal!', error.message || 'Terjadi kesalahan saat memperbarui item.');
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            btnText.textContent = 'Update Item';
            submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        });
    });

    function showModal(type, title, message) {
        const modal = document.getElementById('resultModal');
        const modalIcon = document.getElementById('modalIcon');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const actionBtn = document.getElementById('modalActionBtn');

        modalTitle.textContent = title;
        modalMessage.textContent = message;

        if (type === 'success') {
            modalIcon.innerHTML = '<span class="material-symbols-outlined text-6xl text-green-500">check_circle</span>';
            actionBtn.classList.remove('hidden');
        } else {
            modalIcon.innerHTML = '<span class="material-symbols-outlined text-6xl text-red-500">error</span>';
            actionBtn.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        const modal = document.getElementById('resultModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function redirectToList() {
        window.location.href = '{{ route("admin.items.index") }}';
    }
</script>
@endsection
