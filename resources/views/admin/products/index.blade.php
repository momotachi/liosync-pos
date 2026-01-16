@extends('layouts.admin')

@section('title', 'Product Management')

@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div class="flex items-center gap-3 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0 no-scrollbar">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Products</h2>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="/branch/products/create"
                class="flex items-center justify-center gap-2 px-4 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all w-full sm:w-auto">
                <span class="material-symbols-outlined text-lg">add</span>
                Add New Product
            </a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div
            class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Total Products</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalProducts }}</p>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 text-blue-500 rounded-lg">
                <span class="material-symbols-outlined">inventory</span>
            </div>
        </div>
        <div
            class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Active</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $activeProducts }}</p>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 rounded-lg">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
        </div>
        <div
            class="bg-surface-light dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Inactive / Out</p>
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

    <!-- Product Table -->
    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-border-light dark:border-border-dark">
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Product Name</th>
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Category</th>
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Harga Jual</th>
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status</th>
                        <th
                            class="py-4 px-6 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-500 overflow-hidden">
                                        @if($product->image)
                                            <img src="{{ Storage::url($product->image) }}" class="w-full h-full object-contain"
                                                alt="{{ $product->name }}">
                                        @else
                                            <span class="material-symbols-outlined text-lg">local_drink</span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $product->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-gray-700 dark:text-gray-300 font-medium">
                                Rp {{ number_format($product->selling_price ?? $product->price, 0, ',', '.') }}</td>
                            <td class="py-4 px-6">
                                @if($product->is_active)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="/branch/products/{{ $product->id }}/edit"
                                        class="p-1.5 text-gray-500 hover:text-primary hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-md transition-colors"
                                        title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-border-light dark:border-border-dark">
            {{ $products->links() }}
        </div>
    </div>
@endsection