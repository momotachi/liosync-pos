@extends('layouts.admin')

@section('title', 'Create Raw Material')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.raw-materials.index') }}" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Material</h2>
    </div>
</div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.raw-materials.store') }}" enctype="multipart/form-data" class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        @csrf

        <div class="p-6 space-y-6">
            <!-- Material Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Material Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="e.g., Oranges">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <!-- Unit -->
                <div>
                    <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit *</label>
                    <select name="unit" id="unit" required
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
                    @error('unit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom Unit Input (hidden by default) -->
                <div id="customUnitContainer" class="hidden">
                    <label for="custom_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Unit *</label>
                    <input type="text" name="custom_unit" id="custom_unit" value="{{ old('custom_unit') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="e.g., pack, box, bottle">
                    @error('custom_unit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Stock -->
                <div>
                    <label for="current_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Initial Stock *</label>
                    <input type="number" name="current_stock" id="current_stock" value="{{ old('current_stock', 0) }}" required min="0" step="0.01"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="0">
                    @error('current_stock')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Unit Price -->
            <div>
                <label for="unit_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Beli per Satuan *</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 font-medium">Rp</span>
                    <input type="number" name="unit_price" id="unit_price" value="{{ old('unit_price') }}" required min="0" step="any"
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="0">
                </div>
                @error('unit_price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Image Upload -->
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image</label>
                <div class="mt-1">
                    <!-- Image Preview (hidden by default) -->
                    <div id="imagePreviewContainer" class="hidden mb-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <img id="imagePreview" src="" alt="Preview" class="h-24 w-24 object-contain rounded-lg bg-white dark:bg-gray-800">
                            <div class="flex-1">
                                <p id="imageFileName" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                                <p id="imageFileSize" class="text-xs text-gray-500"></p>
                            </div>
                            <button type="button" id="removeImageBtn" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </div>

                    <!-- Upload Area -->
                    <div id="uploadArea" class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg bg-white dark:bg-gray-800">
                        <div class="space-y-1 text-center">
                            <span class="material-symbols-outlined mx-auto h-12 w-12 text-gray-400">image</span>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                <label for="image" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-emerald-600 hover:text-emerald-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-emerald-500 focus-within:ring-offset-2">
                                    <span>Upload a file</span>
                                    <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                        </div>
                    </div>
                </div>
                @error('image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Min Stock Level -->
            <div>
                <label for="min_stock_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Minimum Stock Level *</label>
                <div class="relative">
                    <input type="number" name="min_stock_level" id="min_stock_level" value="{{ old('min_stock_level', 10) }}" required min="0" step="0.01"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="10">
                    <p class="mt-1 text-xs text-gray-500">Alert when stock falls below this level</p>
                </div>
                @error('min_stock_level')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3 rounded-b-xl">
            <a href="{{ route('admin.raw-materials.index') }}"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-lg shadow-emerald-200 dark:shadow-none transition-colors">
                Create Material
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('image');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const imageFileName = document.getElementById('imageFileName');
    const imageFileSize = document.getElementById('imageFileSize');
    const uploadArea = document.getElementById('uploadArea');
    const removeImageBtn = document.getElementById('removeImageBtn');
    const unitSelect = document.getElementById('unit');
    const customUnitContainer = document.getElementById('customUnitContainer');

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Handle custom unit dropdown
    unitSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customUnitContainer.classList.remove('hidden');
        } else {
            customUnitContainer.classList.add('hidden');
        }
    });

    // Show custom unit field if already selected
    if (unitSelect.value === 'custom') {
        customUnitContainer.classList.remove('hidden');
    }

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imageFileName.textContent = file.name;
                imageFileSize.textContent = formatFileSize(file.size);
                imagePreviewContainer.classList.remove('hidden');
                uploadArea.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    removeImageBtn.addEventListener('click', function() {
        imageInput.value = '';
        imagePreview.src = '';
        imagePreviewContainer.classList.add('hidden');
        uploadArea.classList.remove('hidden');
    });
});
</script>
@endsection
