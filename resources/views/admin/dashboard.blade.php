@extends('layouts.admin')

@section('title', 'Daily Performance Dashboard')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Daily Performance Dashboard</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Real-time overview of today's financial metrics.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('branch.dashboard') }}" class="flex items-center gap-2 bg-card-light dark:bg-card-dark p-1.5 rounded-lg border border-border-light dark:border-border-dark shadow-sm">
                <select name="period" id="period" onchange="this.form.submit()"
                    class="px-3 py-1.5 text-sm font-medium rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-0 focus:ring-2 focus:ring-primary">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>

                @if($period === 'custom')
                    <input type="date" name="start_date" value="{{ $startDate ?? '' }}"
                        class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <input type="date" name="end_date" value="{{ $endDate ?? '' }}"
                        class="px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <button type="submit" class="px-4 py-1.5 bg-primary text-white text-sm rounded-lg hover:bg-primary-dark">
                        Apply
                    </button>
                @endif
            </form>
        </div>
    </header>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
        <!-- Revenue -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $period === 'today' ? "Today's" : ucfirst($period) }} Revenue</p>
                    <h3 class="text-2xl font-bold text-emerald-600 dark:text-emerald-500 mt-2">Rp {{ number_format($revenue, 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">payments</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-emerald-500 flex items-center font-medium">
                    <span class="material-symbols-outlined text-base mr-1">trending_up</span>
                    +12.5%
                </span>
                <span class="text-gray-400 dark:text-gray-500 ml-2">vs previous period</span>
            </div>
        </div>
        <!-- Purchases -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $period === 'today' ? "Today's" : ucfirst($period) }} Purchases</p>
                    <h3 class="text-2xl font-bold text-red-600 dark:text-red-500 mt-2">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">shopping_cart</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400 dark:text-gray-500">Raw materials purchased</span>
            </div>
        </div>
        <!-- Transactions -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $period === 'today' ? "Today's" : ucfirst($period) }} Transactions</p>
                    <h3 class="text-2xl font-bold text-orange-500 dark:text-orange-400 mt-2">{{ $ordersCount }}</h3>
                </div>
                <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-orange-500 dark:text-orange-400">receipt_long</span>
                </div>
            </div>
             <div class="mt-4 flex items-center text-sm">
                 <span class="text-gray-400 dark:text-gray-500">Orders completed</span>
            </div>
        </div>
         <!-- Products Sold -->
         <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Items Sold</p>
                    <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-500 mt-2">{{ $itemsSold }}</h3>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">shopping_basket</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-gray-400 dark:text-gray-500">Individual juice cups</span>
            </div>
        </div>

        <!-- Avg Order Value -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl p-6 shadow-sm border border-border-light dark:border-border-dark flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Order Value</p>
                    <h3 class="text-2xl font-bold text-purple-600 dark:text-purple-500 mt-2">Rp {{ number_format($ordersCount > 0 ? $revenue / $ordersCount : 0, 0, ',', '.') }}</h3>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                    <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">savings</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    @if($lowStockItems->count() > 0)
        <div class="mb-8 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400">warning</span>
                <h3 class="text-lg font-semibold text-red-800 dark:text-red-400">Low Stock Alert</h3>
                <span class="ml-auto text-sm text-red-600 dark:text-red-400">{{ $lowStockItems->count() }} items need restocking</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($lowStockItems as $item)
                    <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</p>
                            <p class="text-sm text-red-600 dark:text-red-400">{{ number_format($item->current_stock, 2, ',', '.') }} / {{ number_format($item->min_stock_level, 2, ',', '.') }} {{ $item->unit }}</p>
                        </div>
                        <a href="/branch/items/{{ $item->id }}/edit"
                            class="px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                            Restock
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 mb-8">
        <!-- Top Selling -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden h-full">
            <div class="p-6 border-b border-border-light dark:border-border-dark flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top Selling Items</h3>
                <button class="text-sm text-primary hover:text-primary-dark font-medium">View All</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                        <tr>
                            <th class="px-6 py-4" scope="col">Item Name</th>
                            <th class="px-6 py-4 text-center" scope="col">Quantity</th>
                            <th class="px-6 py-4 text-right" scope="col">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($topSelling as $item)
                    @if($item->item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center">
                            <div class="h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 mr-3">
                                <span class="material-symbols-outlined text-lg">local_drink</span>
                            </div>
                            {{ $item->item->name }}
                        </td>
                        <td class="px-6 py-4 text-center">{{ $item->total_qty }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @empty
                    <tr><td colspan="3" class="px-6 py-4 text-center">No sales data for this period</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Purchase Items -->
    <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 mb-8">
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden h-full">
            <div class="p-6 border-b border-border-light dark:border-border-dark flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">inventory_2</span>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top Purchase Items</h3>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Most purchased raw materials</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                        <tr>
                            <th class="px-6 py-4" scope="col">Item Name</th>
                            <th class="px-6 py-4 text-center" scope="col">Quantity</th>
                            <th class="px-6 py-4 text-right" scope="col">Total Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($topPurchaseItems as $item)
                    @if($item->item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center">
                            <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center text-red-600 mr-3">
                                <span class="material-symbols-outlined text-lg">shopping_bag</span>
                            </div>
                            {{ $item->item->name }}
                        </td>
                        <td class="px-6 py-4 text-center">{{ number_format($item->total_qty, 2, ',', '.') }} {{ $item->item->unit ?? '' }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-red-600 dark:text-red-400">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @empty
                    <tr><td colspan="3" class="px-6 py-4 text-center">No purchase data for this period</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Profit Ratio -->
    <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 mb-8">
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden h-full">
            <div class="p-6 border-b border-border-light dark:border-border-dark flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">trending_up</span>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top Profit Ratio</h3>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Items with highest profit margin</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                        <tr>
                            <th class="px-6 py-4" scope="col">Item Name</th>
                            <th class="px-6 py-4 text-center" scope="col">HPP</th>
                            <th class="px-6 py-4 text-right" scope="col">Selling Price</th>
                            <th class="px-6 py-4 text-right" scope="col">Profit Ratio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($topProfitRatio as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white flex items-center">
                            <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mr-3">
                                <span class="material-symbols-outlined text-lg">stars</span>
                            </div>
                            {{ $item->name }}
                        </td>
                        <td class="px-6 py-4 text-center">Rp {{ number_format($item->hpp, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-purple-600 dark:text-purple-400">{{ number_format($item->profit_ratio * 100, 1, ',', '.') }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-4 text-center">No items with profit data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
