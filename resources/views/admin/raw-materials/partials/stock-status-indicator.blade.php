@php
    $isOutOfStock = $material->current_stock <= 0;
    $isLowStock = !$isOutOfStock && $material->current_stock <= $material->min_stock_level;
@endphp

@if($isOutOfStock)
    <div class="p-4 rounded-lg border bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
            <div>
                <p class="font-medium text-red-800 dark:text-red-400">Out of Stock</p>
                <p class="text-sm text-red-700 dark:text-red-500">{{ $material->current_stock }} {{ $material->unit }} available (min: {{ $material->min_stock_level }} {{ $material->unit }})</p>
            </div>
        </div>
    </div>
@elseif($isLowStock)
    <div class="p-4 rounded-lg border bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400">warning</span>
            <div>
                <p class="font-medium text-yellow-800 dark:text-yellow-400">Low Stock</p>
                <p class="text-sm text-yellow-700 dark:text-yellow-500">{{ $material->current_stock }} {{ $material->unit }} available (min: {{ $material->min_stock_level }} {{ $material->unit }})</p>
            </div>
        </div>
    </div>
@else
    <div class="p-4 rounded-lg border bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>
            <div>
                <p class="font-medium text-green-800 dark:text-green-400">Good Stock</p>
                <p class="text-sm text-green-700 dark:text-green-500">{{ $material->current_stock }} {{ $material->unit }} available (min: {{ $material->min_stock_level }} {{ $material->unit }})</p>
            </div>
        </div>
    </div>
@endif
