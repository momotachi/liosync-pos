<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cycle Cashier Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom scrollbar for product grid */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }

        /* Material Symbols overrides if needed */
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 1,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark font-display h-screen flex flex-col overflow-hidden text-slate-800 dark:text-white"
    x-data="pos({{ json_encode($settings) }})" @notify.window="$dispatch('toast-add', $event.detail)">

    <!-- Top Navigation -->
    <header
        class="flex-none h-16 bg-surface-light dark:bg-surface-dark border-b border-slate-200 dark:border-emerald-900 px-6 flex items-center justify-between z-20 shadow-sm">
        <a href="/branch" class="flex items-center gap-4 hover:opacity-80 transition-opacity" title="Go to Dashboard">
            <div class="size-8 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-4xl">local_cafe</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">JuicePOS</h1>
        </a>
        <!-- Search Bar -->
        <div class="flex-1 max-w-xl px-8">
            <div class="relative group">
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                    <span class="material-symbols-outlined">search</span>
                </div>
                <input x-model="search"
                    class="block w-full pl-10 pr-3 py-2.5 border-none rounded-xl bg-slate-100 dark:bg-emerald-900/30 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary focus:bg-white dark:focus:bg-emerald-900/50 transition-all shadow-inner"
                    placeholder="Search item name or code (Alt+S)..." type="text" />
            </div>
        </div>
        <!-- Barcode Scanner -->
        <div class="flex items-center gap-2">
            <button @click="$refs.barcodeInput.focus()" class="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 hover:bg-emerald-200 dark:hover:bg-emerald-900/50 transition-colors" title="Scan Barcode (F2)">
                <span class="material-symbols-outlined">qr_code_scanner</span>
            </button>
            <input x-ref="barcodeInput"
                x-model="barcodeScan"
                @keyup.enter="scanBarcode()"
                class="w-32 px-3 py-2 border-none rounded-lg bg-slate-100 dark:bg-emerald-900/30 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary text-sm font-medium"
                placeholder="Barcode (F2)">
        </div>
        <!-- Right Actions -->
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 mr-4">
                <button
                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-emerald-800 text-slate-600 dark:text-emerald-100 relative"
                    title="Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                    <span
                        class="absolute top-2 right-2 size-2 bg-accent rounded-full border border-white dark:border-surface-dark"></span>
                </button>
                <button
                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-emerald-800 text-slate-600 dark:text-emerald-100"
                    title="Network Status">
                    <span class="material-symbols-outlined text-primary">wifi</span>
                </button>
            </div>
            <div class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-emerald-800" x-data="{ open: false }" style="position: relative; z-index: 50;">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold leading-none text-slate-900 dark:text-white">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</p>
                    <p class="text-xs text-slate-500 dark:text-emerald-300 mt-1">Cashier</p>
                </div>
                <div class="relative">
                    <button @click.stop="open = !open"
                        class="size-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 dark:from-emerald-500 dark:to-emerald-700 overflow-hidden border-2 border-white dark:border-emerald-400 shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <img alt="Cashier Avatar" class="w-full h-full object-contain p-0.5"
                            src="https://ui-avatars.com/api/?name={{ Auth::check() ? Auth::user()->name : 'Guest' }}&background=05945b&color=fff" />
                    </button>
                    <!-- Dropdown Menu -->
                    <div x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]"
                        @click.outside="open = false"
                        class="absolute right-0 mt-3 w-56 min-w-56 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-slate-200 dark:border-gray-700 py-2"
                        style="display: none; right: -8px;">
                        <!-- User Info Section -->
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-gray-700 bg-gradient-to-r from-slate-50 to-white dark:from-gray-700/50 dark:to-gray-800">
                            <div class="flex flex-col items-center gap-2 text-center">
                                <div class="size-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-bold text-sm shadow">
                                    {{ substr(Auth::check() ? Auth::user()->name : 'G', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-gray-400 truncate">{{ Auth::check() ? Auth::user()->email : '' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-1">
                            <!-- Logout Button -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-center px-4 py-2.5 mx-1 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 rounded-lg flex items-center justify-center gap-3 group">
                                    <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform duration-200">logout</span>
                                    <span class="font-medium">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col lg:flex-row overflow-hidden relative z-0">
        <!-- Pending Orders Panel (Resto Only) -->
        <div x-show="companyType === 'resto'" x-transition.opacity.duration.300ms class="flex-none w-80 bg-surface-light dark:bg-surface-dark border-r border-slate-200 dark:border-emerald-900/50 p-4 overflow-y-auto custom-scrollbar lg:block">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">receipt_long</span>
                    Pending Orders
                </h2>
                <button @click="loadPendingOrders()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-emerald-800 text-slate-600 dark:text-emerald-100 transition-colors" title="Refresh">
                    <span class="material-symbols-outlined">refresh</span>
                </button>
            </div>

            <!-- Pending Orders List -->
            <div class="space-y-3">
                <template x-for="order in pendingOrders" :key="order.id">
                    <div class="p-3 bg-white dark:bg-emerald-900/20 rounded-xl border border-slate-200 dark:border-emerald-800 hover:border-primary dark:hover:border-primary/50 transition-colors cursor-pointer"
                         @click="openPaymentModal(order)">
                        <!-- Order Header -->
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-primary" x-text="'#' + order.id"></span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': order.order_type === 'dine_in',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': order.order_type === 'takeaway',
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': order.order_type === 'direct'
                                    }"
                                    x-text="order.order_type === 'dine_in' ? 'Dine In' : (order.order_type === 'takeaway' ? 'Takeaway' : 'Direct')">
                                </span>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="new Date(order.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })"></span>
                        </div>

                        <!-- Cashier Name -->
                        <div class="flex items-center gap-1 mb-2">
                            <span class="material-symbols-outlined text-[12px] text-gray-400">person</span>
                            <span class="text-xs text-gray-600 dark:text-gray-400" x-text="order.user?.name || 'Unknown'"></span>
                        </div>

                        <!-- Table Number (if dine in) -->
                        <div x-show="order.table_number" class="mb-2">
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                <span class="material-symbols-outlined text-[14px]">table_restaurant</span>
                                Table <span x-text="order.table_number" class="font-medium"></span>
                            </span>
                        </div>

                        <!-- Order Items -->
                        <div class="mb-2">
                            <template x-for="item in (order.items || []).slice(0, 3)" :key="item.id">
                                <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                                    <span x-text="item.quantity + 'x ' + (item.item?.name || 'Item')"></span>
                                    <span x-text="formatCurrency(item.subtotal || (item.price * item.quantity))"></span>
                                </div>
                            </template>
                            <div x-show="(order.items || []).length > 3" class="text-xs text-gray-500 dark:text-gray-500 italic">
                                + <span x-text="(order.items || []).length - 3"></span> more items
                            </div>
                        </div>

                        <!-- Total & Actions -->
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                            <span class="text-sm font-bold text-slate-900 dark:text-white" x-text="formatCurrency(order.total_amount || 0)"></span>
                            <div class="flex items-center gap-1">
                                <button @click.stop="printKitchenReceipt(order.id)" class="p-1.5 text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors" title="Print Kitchen Receipt">
                                    <span class="material-symbols-outlined text-[18px]">restaurant</span>
                                </button>
                                <button @click.stop="printTableReceipt(order.id)" class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Print Table Receipt">
                                    <span class="material-symbols-outlined text-[18px]">print</span>
                                </button>
                                <button @click.stop="deletePendingOrder(order.id)" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                                <button @click.stop="openPaymentModal(order)" class="px-3 py-1.5 bg-primary text-white text-xs font-medium rounded-lg hover:bg-[#047a4b] transition-colors">
                                    Pay
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="pendingOrders.length === 0" class="text-center py-8">
                    <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600 mb-2">receipt</span>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No pending orders</p>
                </div>
            </div>
        </div>

        <!-- LEFT PANEL: Catalog (70%) -->
        <section
            class="flex-1 flex flex-col min-w-0 bg-background-light dark:bg-background-dark p-4 lg:p-6 lg:pr-3 overflow-hidden">
            <!-- Category Tabs -->
            <div class="flex-none mb-6 overflow-x-auto pb-2 scrollbar-hide">
                <div class="flex gap-3">
                    <button @click="filterCategory(null)"
                        :class="activeCategory === null ? 'bg-primary text-white hover:shadow-lg' : 'bg-white dark:bg-emerald-900/40 text-slate-700 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-800'"
                        class="flex items-center gap-2 px-6 py-3 rounded-xl font-semibold shadow-md transition-all transform hover:-translate-y-0.5">
                        <span class="material-symbols-outlined text-[20px]">grid_view</span>
                        All Items
                    </button>
                    @foreach($categories as $category)
                        <button @click="filterCategory({{ $category->id }})"
                            :class="activeCategory === {{ $category->id }} ? 'bg-primary text-white hover:shadow-lg' : 'bg-white dark:bg-emerald-900/40 text-slate-700 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-800'"
                            class="flex items-center gap-2 px-6 py-3 rounded-xl font-medium shadow-sm border border-slate-200 dark:border-emerald-800 transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-accent">local_drink</span>
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 pb-20">

                    @foreach($items as $item)
                        <!-- Card -->
                        <div x-show="isVisible({{ $item->category_id }}, '{{ addslashes($item->name) }}')"
                            @click="addToCart({{ json_encode($item) }})"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                            class="group bg-surface-light dark:bg-surface-dark rounded-2xl p-3 shadow-sm hover:shadow-md border border-slate-100 dark:border-emerald-900/50 cursor-pointer transition-all hover:border-primary/50 relative overflow-hidden">
                            <div
                                class="aspect-square rounded-xl bg-emerald-50/50 dark:bg-emerald-900/20 mb-3 overflow-hidden relative">
                                @if($item->image)
                                    <img class="w-full h-full object-contain transform group-hover:scale-110 transition-transform duration-300"
                                        src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-4xl">🥤</div>
                                @endif

                                <div
                                    class="absolute top-2 right-2 bg-white/90 dark:bg-black/60 backdrop-blur-sm rounded-lg px-2 py-1 shadow-sm">
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-white">{{ $settings['currency_symbol'] ?? 'Rp' }} {{ number_format($item->selling_price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-lg leading-tight mb-1">
                                {{ $item->name }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $item->category->name ?? 'Product' }}
                            </p>
                        </div>
                    @endforeach
                    <div x-show="products.length === 0" class="col-span-full text-center text-gray-500 py-10"
                        style="display: none;">
                        No items found.
                    </div>

                </div>
            </div>
        </section>

        <!-- RIGHT PANEL: Current Order (30%) -->
        <aside
            class="flex-3 bg-surface-light dark:bg-surface-dark border-l border-slate-200 dark:border-emerald-900/50 flex flex-col shadow-xl max-w-112.5">
            <!-- Header -->
            <div class="flex-none p-5 border-b border-slate-100 dark:border-emerald-900/50">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">receipt_long</span>
                        Order #NEW
                    </h2>
                    <span
                        class="px-2.5 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-900 text-primary dark:text-emerald-300 text-xs font-bold uppercase tracking-wider">Dine
                        In</span>
                </div>
                <div class="flex gap-2">
                    <button @click="showCustomerModal = true"
                        class="flex-1 flex items-center justify-center gap-1 py-2 px-3 rounded-lg border border-slate-200 dark:border-emerald-800 hover:bg-slate-50 dark:hover:bg-emerald-800/50 text-slate-600 dark:text-slate-300 text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                        <span x-text="customerName || 'Customer'"></span>
                    </button>
                    <button @click="showNoteModal = true"
                        class="flex-1 flex items-center justify-center gap-1 py-2 px-3 rounded-lg border border-slate-200 dark:border-emerald-800 hover:bg-slate-50 dark:hover:bg-emerald-800/50 text-slate-600 dark:text-slate-300 text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-[18px]">note_add</span>
                        <span x-text="orderNote || 'Note'"></span>
                    </button>
                    <button @click="clearCart()"
                        class="p-2 rounded-lg border border-slate-200 dark:border-emerald-800 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400 text-slate-400 transition-colors"
                        title="Clear Cart">
                        <span class="material-symbols-outlined text-[20px]">delete_sweep</span>
                    </button>
                </div>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto custom-scrollbar p-2">

                <!-- Empty State -->
                <div x-show="cart.length === 0" class="flex flex-col gap-2 p-5 text-center text-slate-400">
                    <span class="material-symbols-outlined text-4xl mb-2">shopping_bag</span>
                    <p>Cart is empty</p>
                </div>

                <!-- Cart Items -->
                <div class="flex flex-col gap-2">
                    <template x-for="(item, index) in cart" :key="index">
                        <div x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-x-2"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-2"
                            class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-emerald-900/20 hover:bg-slate-100 dark:hover:bg-emerald-900/30 transition-colors group">
                            <div class="size-10 rounded-lg bg-white dark:bg-emerald-800 overflow-hidden shrink-0">
                                <template x-if="item.image">
                                    <img class="w-full h-full object-contain" :src="'/storage/' + item.image"
                                        :alt="item.name">
                                </template>
                                <template x-if="!item.image">
                                    <div class="w-full h-full flex items-center justify-center text-lg">🥤</div>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-slate-900 dark:text-white truncate" x-text="item.name"></h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400"
                                    x-text="formatCurrency(item.price) + ' / ea'"></p>
                                <template x-if="item.note">
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">edit_note</span>
                                        <span x-text="item.note" class="truncate"></span>
                                    </p>
                                </template>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex items-center bg-white dark:bg-emerald-950 rounded-lg shadow-sm border border-slate-200 dark:border-emerald-800 h-8">
                                    <button @click="updateQuantity(index, -1)"
                                        class="w-8 h-full flex items-center justify-center text-slate-500 hover:text-primary hover:bg-slate-50 dark:hover:bg-emerald-900 rounded-l-lg transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">remove</span>
                                    </button>
                                    <span class="w-6 text-center text-sm font-bold text-slate-900 dark:text-white"
                                        x-text="item.quantity"></span>
                                    <button @click="updateQuantity(index, 1)"
                                        class="w-8 h-full flex items-center justify-center text-slate-500 hover:text-primary hover:bg-slate-50 dark:hover:bg-emerald-900 rounded-r-lg transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">add</span>
                                    </button>
                                </div>
                                <button @click="openItemNoteModal(index)"
                                    class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                    :class="item.note ? 'text-amber-600' : ''"
                                    title="Add Note">
                                    <span class="material-symbols-outlined text-lg">note</span>
                                </button>
                                <span class="font-bold text-slate-900 dark:text-white min-w-17.5 text-right"
                                    x-text="formatCurrency(item.price * item.quantity)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer / Totals -->
            <div
                class="flex-none bg-surface-light dark:bg-surface-dark p-5 border-t border-slate-200 dark:border-emerald-900/50 shadow-[0_-5px_20px_-5px_rgba(0,0,0,0.05)]">
                <div class="flex flex-col gap-2 mb-4">
                    <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-medium text-slate-900 dark:text-white"
                            x-text="formatCurrency(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm text-slate-500 dark:text-slate-400">
                        <span x-text="taxName + ' (' + taxRate + '%)'"></span>
                        <span class="font-medium text-slate-900 dark:text-white" x-text="formatCurrency(tax)"></span>
                    </div>
                    <div
                        class="flex justify-between text-lg font-bold text-slate-900 dark:text-white border-t border-dashed border-slate-200 dark:border-emerald-800 pt-3 mt-1">
                        <span>Total</span>
                        <span class="text-primary text-2xl" x-text="formatCurrency(total)"></span>
                    </div>
                </div>
                <!-- FIXED CHECKOUT BUTTON -->
                <div>
                    <button @click="checkout()" :disabled="cart.length === 0"
                        :class="cart.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary-hover hover:shadow-xl hover:-translate-y-0.5'"
                        class="w-full rounded-xl bg-primary text-white font-bold text-lg shadow-lg shadow-emerald-200 dark:shadow-none flex items-center justify-center gap-3 py-4 transition-all transform active:scale-[0.98]">
                        <span x-text="isLoading ? 'Processing...' : 'CHECKOUT'"></span>
                        <span x-show="!isLoading" class="bg-white/20 px-2 py-0.5 rounded text-sm font-medium">F12</span>
                    </button>
                </div>
            </div>
        </aside>
    </main>

    <div x-data="toast()" @toast-add.window="add($event.detail.message, $event.detail.type)"
        class="fixed bottom-5 right-5 z-50 flex flex-col gap-2">
        <template x-for="notif in notifications" :key="notif.id">
            <div x-show="true" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                class="bg-slate-900 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 min-w-75"
                :class="notif.type === 'error' ? 'bg-red-600' : 'bg-slate-900'">
                <span class="material-symbols-outlined text-green-400"
                    x-show="notif.type === 'success'">check_circle</span>
                <span class="material-symbols-outlined text-white" x-show="notif.type === 'error'">error</span>
                <span x-text="notif.message" class="font-medium text-sm"></span>
            </div>
        </template>
    </div>

    <!-- Payment Modal -->
    <div x-show="showPaymentModal" style="display: none;"
        class="fixed inset-0 z-9999 flex items-center justify-center bg-[#0d1c16]/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
        <!-- Modal Container - Centered with fixed width -->
        <div class="max-w-md bg-white dark:bg-[#1a2e26] rounded-xl shadow-2xl overflow-hidden flex flex-col relative"
            @click.outside="showPaymentModal = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-lg font-bold tracking-tight text-[#0d1c16] dark:text-white">Payment Details</h2>
                <button @click="showPaymentModal = false"
                    class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-5 space-y-4">
                <!-- Total Due -->
                <div class="flex flex-col items-center justify-center space-y-1 py-1">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total
                        Due</span>
                    <h1 class="text-4xl font-bold text-primary tracking-tight" x-text="formatCurrency(total)"></h1>
                </div>
                <!-- Payment Methods -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase ml-1">Payment
                        Method</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer group relative">
                            <input type="radio" name="payment_method" value="cash" x-model="paymentMethod"
                                class="peer sr-only" />
                            <div
                                class="flex flex-col items-center justify-center p-2 rounded-lg border-2 border-primary bg-primary/10 text-primary transition-all peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary hover:bg-primary/5">
                                <span class="material-symbols-outlined mb-0.5 text-xl">payments</span>
                                <span class="text-xs font-semibold mt-1">Cash</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group relative">
                            <input type="radio" name="payment_method" value="qris" x-model="paymentMethod"
                                class="peer sr-only" />
                            <div
                                class="flex flex-col items-center justify-center p-2 rounded-lg border-2 border-transparent bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-all peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                <span class="material-symbols-outlined mb-0.5 text-xl">qr_code_scanner</span>
                                <span class="text-xs font-medium mt-1">QRIS</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group relative">
                            <input type="radio" name="payment_method" value="debit" x-model="paymentMethod"
                                class="peer sr-only" />
                            <div
                                class="flex flex-col items-center justify-center p-2 rounded-lg border-2 border-transparent bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-all peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                <span class="material-symbols-outlined mb-0.5 text-xl">credit_card</span>
                                <span class="text-xs font-medium mt-1">Debit</span>
                            </div>
                        </label>
                    </div>
                </div>
                <!-- Cash Calculation (Visible only if Cash) -->
                <div x-show="paymentMethod === 'cash'" class="space-y-3 pt-1" x-transition>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1.5 ml-1">Cash
                            Received</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm font-medium" x-text="currencySymbol"></span>
                            </div>
                            <input type="number" x-model="cashReceived"
                                class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-black/20 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-lg text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary text-base font-bold transition-shadow shadow-sm"
                                placeholder="0">
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-between p-3 bg-primary/5 border border-dashed border-primary/20 rounded-lg">
                        <div class="flex items-center gap-1.5 text-primary">
                            <span class="material-symbols-outlined text-[18px]">currency_exchange</span>
                            <span class="text-xs font-semibold">Change Return</span>
                        </div>
                        <span class="text-lg font-bold text-primary"
                            x-text="formatCurrency(Math.max(0, cashReceived - total))"></span>
                    </div>
                </div>
                <!-- Receipt Toggle -->
                <div class="flex items-center justify-between pt-1">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Print Receipt?</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="printReceipt" class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 dark:peer-focus:ring-primary/30 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-primary">
                        </div>
                    </label>
                </div>
            </div>
            <!-- Footer Actions -->
            <div class="p-5 pt-0 flex gap-3">
                <button @click="showPaymentModal = false"
                    class="flex-1 py-2.5 px-3 rounded-lg border border-gray-200 dark:border-gray-600 font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-200 text-sm">
                    Cancel
                </button>
                <button @click="processPayment" :disabled="paymentMethod === 'cash' && parseFloat(cashReceived) < parseFloat(total)"
                    :class="paymentMethod === 'cash' && parseFloat(cashReceived) < parseFloat(total) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#047a4b] shadow-lg shadow-primary/25'"
                    class="flex-2 py-2.5 px-3 rounded-lg bg-primary text-white font-bold text-base transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Finalize Payment
                </button>
            </div>
        </div>
    </div>

    <!-- Customer Modal -->
    <div x-show="showCustomerModal" style="display: none;"
        class="fixed inset-0 z-9999 flex items-center justify-center bg-[#0d1c16]/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
        <div class="max-w-md bg-white dark:bg-[#1a2e26] rounded-xl shadow-2xl overflow-hidden flex flex-col relative"
            @click.outside="showCustomerModal = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-lg font-bold tracking-tight text-[#0d1c16] dark:text-white">Customer Information</h2>
                <button @click="showCustomerModal = false"
                    class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Customer Name</label>
                    <input type="text" x-model="customerName"
                        class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-black/20 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-xl text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary"
                        placeholder="Enter customer name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Phone Number</label>
                    <input type="text" x-model="customerPhone"
                        class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-black/20 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-xl text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary"
                        placeholder="Enter phone number (optional)">
                </div>
            </div>
            <!-- Footer Actions -->
            <div class="p-5 pt-0 flex gap-3">
                <button @click="showCustomerModal = false"
                    class="flex-1 py-2.5 px-3 rounded-lg border border-gray-200 dark:border-gray-600 font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-sm">
                    Cancel
                </button>
                <button @click="saveCustomer()"
                    class="flex-2 py-2.5 px-3 rounded-lg bg-primary text-white font-bold text-base hover:bg-[#047a4b] transition-colors">
                    Save
                </button>
            </div>
        </div>
    </div>

    <!-- Note Modal -->
    <div x-show="showNoteModal" style="display: none;"
        class="fixed inset-0 z-9999 flex items-center justify-center bg-[#0d1c16]/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
        <div class="max-w-md bg-white dark:bg-[#1a2e26] rounded-xl shadow-2xl overflow-hidden flex flex-col relative"
            @click.outside="showNoteModal = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-lg font-bold tracking-tight text-[#0d1c16] dark:text-white">Order Note</h2>
                <button @click="showNoteModal = false"
                    class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Note for this order</label>
                    <textarea x-model="orderNote" rows="4"
                        class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-black/20 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-xl text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary resize-none"
                        placeholder="Add a note (e.g., less sugar, no ice, etc.)"></textarea>
                </div>
            </div>
            <!-- Footer Actions -->
            <div class="p-5 pt-0 flex gap-3">
                <button @click="showNoteModal = false; orderNote = ''"
                    class="flex-1 py-2.5 px-3 rounded-lg border border-gray-200 dark:border-gray-600 font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-sm">
                    Clear
                </button>
                <button @click="showNoteModal = false"
                    class="flex-2 py-2.5 px-3 rounded-lg bg-primary text-white font-bold text-base hover:bg-[#047a4b] transition-colors">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- Item Note Modal -->
    <div x-show="showItemNoteModal" style="display: none;"
        class="fixed inset-0 z-9999 flex items-center justify-center bg-[#0d1c16]/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
        <div class="max-w-md bg-white dark:bg-[#1a2e26] rounded-xl shadow-2xl overflow-hidden flex flex-col relative"
            @click.outside="showItemNoteModal = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-lg font-bold tracking-tight text-[#0d1c16] dark:text-white">Item Note</h2>
                <button @click="showItemNoteModal = false"
                    class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-5 space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2" x-text="'Note for ' + currentItemName"></p>
                    <textarea x-model="currentItemNote" rows="3"
                        class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-black/20 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-xl text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary resize-none"
                        placeholder="e.g., less sugar, no ice, extra hot, etc."></textarea>
                </div>
            </div>
            <!-- Footer Actions -->
            <div class="p-5 pt-0 flex gap-3">
                <button @click="showItemNoteModal = false; saveItemNote()"
                    class="flex-1 py-2.5 px-3 rounded-lg bg-primary text-white font-bold text-base hover:bg-[#047a4b] transition-colors">
                    Save
                </button>
            </div>
        </div>
    </div>

    <!-- Order Type Selection Modal (Resto Only) -->
    <div x-show="showOrderTypeModal" style="display: none;"
        class="fixed inset-0 z-9999 flex items-center justify-center bg-[#0d1c16]/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
        <div class="max-w-lg bg-white dark:bg-[#1a2e26] rounded-xl shadow-2xl overflow-hidden flex flex-col relative"
            @click.outside="showOrderTypeModal = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-lg font-bold tracking-tight text-[#0d1c16] dark:text-white">Select Order Type</h2>
                <button @click="showOrderTypeModal = false"
                    class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-5 space-y-3">
                <!-- Direct Payment -->
                <label class="relative flex cursor-pointer">
                    <input type="radio" name="order_type" value="direct" x-model="selectedOrderType" class="peer sr-only">
                    <div class="w-full p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 dark:hover:border-gray-500">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-600 dark:text-green-400">payments</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">Direct Payment</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Order and pay immediately</p>
                            </div>
                        </div>
                    </div>
                </label>

                <!-- Dine In -->
                <label class="relative flex cursor-pointer">
                    <input type="radio" name="order_type" value="dine_in" x-model="selectedOrderType" class="peer sr-only">
                    <div class="w-full p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 dark:hover:border-gray-500">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">restaurant</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">Dine In</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Order first, pay later</p>
                            </div>
                        </div>
                    </div>
                </label>

                <!-- Takeaway -->
                <label class="relative flex cursor-pointer">
                    <input type="radio" name="order_type" value="takeaway" x-model="selectedOrderType" class="peer sr-only">
                    <div class="w-full p-4 border-2 border-gray-200 dark:border-gray-600 rounded-lg transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 dark:hover:border-gray-500">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">shopping_bag</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">Takeaway</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Order first, pay later</p>
                            </div>
                        </div>
                    </div>
                </label>

                <!-- Table Number (for Dine In) -->
                <div x-show="selectedOrderType === 'dine_in'" class="pt-2" x-transition>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Table Number</label>
                    <input type="text" x-model="tableNumber"
                        class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-black/20 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-lg text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary"
                        placeholder="Enter table number">
                </div>
            </div>
            <!-- Footer Actions -->
            <div class="p-5 pt-0 flex gap-3">
                <button @click="showOrderTypeModal = false"
                    class="flex-1 py-2.5 px-3 rounded-lg border border-gray-200 dark:border-gray-600 font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-sm">
                    Cancel
                </button>
                <button @click="processOrderType()"
                    class="flex-2 py-2.5 px-3 rounded-lg bg-primary text-white font-bold text-base hover:bg-[#047a4b] transition-colors">
                    Continue
                </button>
            </div>
        </div>
    </div>

    <!-- Pending Payment Modal -->
    <div x-show="showPendingPaymentModal" style="display: none;"
        class="fixed inset-0 z-9999 flex items-center justify-center bg-[#0d1c16]/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
        <div class="max-w-md bg-white dark:bg-[#1a2e26] rounded-xl shadow-2xl overflow-hidden flex flex-col relative"
            @click.outside="showPendingPaymentModal = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-lg font-bold tracking-tight text-[#0d1c16] dark:text-white">Process Payment</h2>
                <button @click="showPendingPaymentModal = false"
                    class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-5 space-y-4">
                <!-- Order Info -->
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Order #</p>
                        <p class="font-bold text-gray-900 dark:text-white" x-text="selectedPendingOrder?.id"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total Due</p>
                        <p class="font-bold text-primary text-lg" x-text="formatCurrency(selectedPendingOrder?.total_amount || 0)"></p>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="space-y-2">
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase ml-1">Payment Method</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="pending_payment_method" value="cash" x-model="paymentMethod" class="peer sr-only">
                            <div class="flex flex-col items-center justify-center p-2 rounded-lg border-2 border-primary bg-primary/10 text-primary transition-all peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary hover:bg-primary/5">
                                <span class="material-symbols-outlined mb-0.5 text-xl">payments</span>
                                <span class="text-xs font-semibold mt-1">Cash</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="pending_payment_method" value="qris" x-model="paymentMethod" class="peer sr-only">
                            <div class="flex flex-col items-center justify-center p-2 rounded-lg border-2 border-transparent bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-all peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                <span class="material-symbols-outlined mb-0.5 text-xl">qr_code_scanner</span>
                                <span class="text-xs font-medium mt-1">QRIS</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="pending_payment_method" value="debit" x-model="paymentMethod" class="peer sr-only">
                            <div class="flex flex-col items-center justify-center p-2 rounded-lg border-2 border-transparent bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-all peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                <span class="material-symbols-outlined mb-0.5 text-xl">credit_card</span>
                                <span class="text-xs font-medium mt-1">Debit</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Cash Calculation (Visible only if Cash) -->
                <div x-show="paymentMethod === 'cash'" class="space-y-3 pt-1" x-transition>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1.5 ml-1">Cash Received</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 text-sm font-medium" x-text="currencySymbol"></span>
                            </div>
                            <input type="number" x-model="cashReceived"
                                class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-black/20 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-lg text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary text-base font-bold transition-shadow"
                                placeholder="0">
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-primary/5 border border-dashed border-primary/20 rounded-lg">
                        <div class="flex items-center gap-1.5 text-primary">
                            <span class="material-symbols-outlined text-[18px]">currency_exchange</span>
                            <span class="text-xs font-semibold">Change Return</span>
                        </div>
                        <span class="text-lg font-bold text-primary"
                            x-text="formatCurrency(Math.max(0, cashReceived - (selectedPendingOrder?.total_amount || 0)))"></span>
                    </div>
                </div>
            </div>
            <!-- Footer Actions -->
            <div class="p-5 pt-0 flex gap-3">
                <button @click="showPendingPaymentModal = false"
                    class="flex-1 py-2.5 px-3 rounded-lg border border-gray-200 dark:border-gray-600 font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-sm">
                    Cancel
                </button>
                <button @click="processPendingPayment" :disabled="paymentMethod === 'cash' && parseFloat(cashReceived) < parseFloat(selectedPendingOrder?.total_amount || 0)"
                    :class="paymentMethod === 'cash' && parseFloat(cashReceived) < parseFloat(selectedPendingOrder?.total_amount || 0) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#047a4b] shadow-lg'"
                    class="flex-2 py-2.5 px-3 rounded-lg bg-primary text-white font-bold text-base transition-colors">
                    Process Payment
                </button>
            </div>
        </div>
    </div>

    <script>
        // Configure axios with CSRF token
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        let token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }

        // Pass server data to window
        window.pendingOrdersData = @json($pendingOrders ?? []);

        document.addEventListener('alpine:init', () => {
            Alpine.data('toast', () => ({
                notifications: [],
                add(message, type = 'success') {
                    const id = Date.now();
                    this.notifications.push({ id, message, type });
                    setTimeout(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    }, 3000);
                }
            }));
        });

        function pos(settings = {}) {
            return {
                cart: [],
                activeCategory: null,
                search: '',
                isLoading: false,
                barcodeScan: '',

                // Settings
                settings: settings,
                currencySymbol: settings.currency_symbol || 'Rp',
                taxRate: settings.tax_rate || 0,
                taxName: settings.tax_name || 'Tax',
                companyType: settings.company_type || 'toko',

                // Products data for barcode lookup
                products: @json($items),

                // Resto Mode State
                pendingOrders: window.pendingOrdersData || [],
                selectedOrderType: 'direct',
                tableNumber: '',
                showOrderTypeModal: false,

                // Payment Modal State
                showPaymentModal: false,
                showPendingPaymentModal: false,
                paymentMethod: 'cash',
                cashReceived: 0,
                printReceipt: true,
                selectedPendingOrder: null,

                // Customer & Note Modal State
                showCustomerModal: false,
                showNoteModal: false,
                customerName: '',
                customerPhone: '',
                orderNote: '',

                // Item Note Modal State
                showItemNoteModal: false,
                currentItemIndex: null,
                currentItemName: '',
                currentItemNote: '',

                init() {
                    // Test notification system on load
                    console.log('POS initialized, company type:', this.companyType);
                    // Load pending orders for resto
                    if (this.companyType === 'resto') {
                        this.loadPendingOrders();
                    }
                    // Uncomment to test notification:
                    // this.$dispatch('notify', { message: 'POS Ready!', type: 'success' });
                },

                // Load pending orders for resto
                loadPendingOrders() {
                    axios.get('/pos/pending-orders')
                        .then(response => {
                            this.pendingOrders = response.data.orders || [];
                        })
                        .catch(error => {
                            console.error('Failed to load pending orders:', error);
                        });
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get tax() {
                    return this.subtotal * (this.taxRate / 100);
                },

                get total() {
                    return this.subtotal + this.tax;
                },

                formatCurrency(amount) {
                    return this.currencySymbol + ' ' + Number(amount).toLocaleString('id-ID');
                },

                isVisible(categoryId, productName) {
                    if (this.activeCategory && this.activeCategory !== categoryId) return false;
                    if (this.search && !productName.toLowerCase().includes(this.search.toLowerCase())) return false;
                    return true;
                },

                filterCategory(id) {
                    this.activeCategory = id;
                },

                addToCart(product) {
                    let existingItem = this.cart.find(item => item.id === product.id);
                    if (existingItem) {
                        existingItem.quantity++;
                    } else {
                        this.cart.push({
                            ...product,
                            price: product.selling_price || product.price,
                            quantity: 1
                        });
                    }
                    this.$dispatch('notify', { message: `Added ${product.name} to cart` });
                },

                updateQuantity(index, change) {
                    if (this.cart[index].quantity + change > 0) {
                        this.cart[index].quantity += change;
                    } else {
                        this.cart.splice(index, 1);
                    }
                },

                clearCart() {
                    if (confirm('Clear cart?')) {
                        this.cart = [];
                        this.$dispatch('notify', { message: 'Cart cleared', type: 'error' });
                    }
                },

                // Save Customer Information
                saveCustomer() {
                    this.showCustomerModal = false;
                    this.$dispatch('notify', { message: 'Customer information saved' });
                },

                // Item Note Functions
                openItemNoteModal(index) {
                    this.currentItemIndex = index;
                    this.currentItemName = this.cart[index].name;
                    this.currentItemNote = this.cart[index].note || '';
                    this.showItemNoteModal = true;
                },

                saveItemNote() {
                    if (this.currentItemIndex !== null) {
                        this.cart[this.currentItemIndex].note = this.currentItemNote || null;
                        this.$dispatch('notify', { message: 'Note saved for ' + this.currentItemName });
                    }
                    this.showItemNoteModal = false;
                },

                // Barcode Scanner Function
                scanBarcode() {
                    if (!this.barcodeScan.trim()) return;

                    // Search for product by barcode or SKU
                    const product = this.products.find(p =>
                        p.barcode === this.barcodeScan.trim() ||
                        p.sku === this.barcodeScan.trim()
                    );

                    if (product) {
                        this.addToCart(product);
                        this.barcodeScan = '';
                        this.$refs.barcodeInput.focus();
                    } else {
                        this.$dispatch('notify', { message: 'Product not found for barcode: ' + this.barcodeScan, type: 'error' });
                        this.barcodeScan = '';
                    }
                },

                // Triggered by the Main Checkout Button
                checkout() {
                    if (this.cart.length === 0) return;

                    // For resto type, show order type selection modal
                    if (this.companyType === 'resto') {
                        this.showOrderTypeModal = true;
                        return;
                    }

                    // For toko type, go directly to payment
                    this.paymentMethod = 'cash';
                    this.cashReceived = 0;
                    this.showPaymentModal = true;
                },

                // Process checkout with selected order type (for resto)
                processOrderType() {
                    this.showOrderTypeModal = false;

                    // Direct payment type - show payment modal
                    if (this.selectedOrderType === 'direct') {
                        this.paymentMethod = 'cash';
                        this.cashReceived = 0;
                        this.showPaymentModal = true;
                    } else {
                        // Dine-in or Takeaway - create order without payment
                        this.createPendingOrder();
                    }
                },

                // Create pending order for dine-in/takeaway
                createPendingOrder() {
                    this.isLoading = true;

                    const payload = {
                        items: this.cart.map(item => ({
                            item_id: item.id,
                            quantity: item.quantity,
                            price: item.price,
                            note: item.note || null
                        })),
                        subtotal: this.subtotal,
                        tax: this.tax,
                        total_amount: this.total,
                        order_type: this.selectedOrderType,
                        table_number: this.tableNumber || null,
                        skip_payment: true,
                        customer_name: this.customerName,
                        customer_phone: this.customerPhone
                    };

                    axios.post('/checkout', payload)
                        .then(response => {
                            this.$dispatch('notify', { message: 'Order created successfully!', type: 'success' });
                            this.cart = [];
                            this.customerName = '';
                            this.customerPhone = '';
                            this.tableNumber = '';
                            this.selectedOrderType = 'direct';
                            this.isLoading = false;

                            // Reload pending orders
                            if (this.companyType === 'resto') {
                                this.loadPendingOrders();
                            }
                        })
                        .catch(error => {
                            console.error('Order creation error:', error);
                            const errorMsg = error.response?.data?.message || 'Failed to create order';
                            this.$dispatch('notify', { message: errorMsg, type: 'error' });
                            this.isLoading = false;
                        });
                },

                // Open payment modal for pending order
                openPaymentModal(order) {
                    this.selectedPendingOrder = order;
                    this.paymentMethod = 'cash';
                    this.cashReceived = 0;
                    this.showPendingPaymentModal = true;
                },

                // Process payment for pending order
                processPendingPayment() {
                    if (!this.selectedPendingOrder) return;

                    if (this.paymentMethod === 'cash' && (!this.cashReceived || parseFloat(this.cashReceived) < this.selectedPendingOrder.total_amount)) {
                        this.$dispatch('notify', { message: 'Insufficient cash received', type: 'error' });
                        return;
                    }

                    this.isLoading = true;

                    const payload = {
                        payment_method: this.paymentMethod,
                        cash_received: this.cashReceived || 0
                    };

                    axios.post(`/pos/orders/${this.selectedPendingOrder.id}/payment`, payload)
                        .then(response => {
                            this.$dispatch('notify', { message: 'Payment processed successfully!', type: 'success' });
                            this.showPendingPaymentModal = false;
                            this.selectedPendingOrder = null;
                            this.isLoading = false;

                            // Reload pending orders
                            this.loadPendingOrders();

                            // Open receipt
                            if (response.data.receipt_url) {
                                window.open(response.data.receipt_url, '_blank');
                            }
                        })
                        .catch(error => {
                            console.error('Payment error:', error);
                            const errorMsg = error.response?.data?.message || 'Payment failed';
                            this.$dispatch('notify', { message: errorMsg, type: 'error' });
                            this.isLoading = false;
                        });
                },

                // Delete pending order
                deletePendingOrder(orderId) {
                    if (!confirm('Are you sure you want to delete this order?')) return;

                    axios.delete(`/pos/orders/${orderId}`)
                        .then(response => {
                            this.$dispatch('notify', { message: 'Order deleted successfully!', type: 'success' });
                            this.loadPendingOrders();
                        })
                        .catch(error => {
                            console.error('Delete error:', error);
                            const errorMsg = error.response?.data?.message || 'Failed to delete order';
                            this.$dispatch('notify', { message: errorMsg, type: 'error' });
                        });
                },

                // Print kitchen receipt
                printKitchenReceipt(orderId) {
                    window.open(`/pos/receipt/${orderId}/kitchen`, '_blank', 'width=400,height=600');
                },

                // Print table receipt
                printTableReceipt(orderId) {
                    window.open(`/pos/receipt/${orderId}/table`, '_blank', 'width=400,height=600');
                },

                // Actual Submission
                processPayment() {
                    console.log('processPayment called', {
                        paymentMethod: this.paymentMethod,
                        cashReceived: this.cashReceived,
                        total: this.total,
                        cartLength: this.cart.length
                    });

                    // Only validate cash payment for exact amount
                    if (this.paymentMethod === 'cash' && (!this.cashReceived || parseFloat(this.cashReceived) < this.total)) {
                        this.$dispatch('notify', { message: 'Insufficient cash received', type: 'error' });
                        return;
                    }

                    this.isLoading = true;

                    // Prepare checkout data
                    const payload = {
                        items: this.cart.map(item => ({
                            item_id: item.id,
                            quantity: item.quantity,
                            price: item.price,
                            note: item.note || null
                        })),
                        subtotal: this.subtotal,
                        tax: this.tax,
                        total_amount: this.total,
                        payment_method: this.paymentMethod,
                        cash_received: this.cashReceived || 0,
                        change_return: (this.cashReceived || 0) - this.total,
                        print_receipt: this.printReceipt,
                        customer_name: this.customerName,
                        customer_phone: this.customerPhone,
                        order_note: this.orderNote
                    };

                    console.log('Sending payload:', payload);

                    // Send to backend
                    axios.post('/checkout', payload)
                        .then(response => {
                            console.log('Checkout success:', response.data);
                            this.$dispatch('notify', { message: 'Order #' + response.data.order_id + ' Berhasil Dibuat!', type: 'success' });
                            this.cart = [];
                            this.customerName = '';
                            this.customerPhone = '';
                            this.orderNote = '';
                            this.showPaymentModal = false;
                            this.isLoading = false;

                            // Open receipt in new tab
                            if (response.data.receipt_url) {
                                window.open(response.data.receipt_url, '_blank');
                            }
                        })
                        .catch(error => {
                            console.error('Checkout error:', error);
                            console.error('Error response:', error.response);

                            let errorMsg = 'Checkout Failed: ';
                            if (error.response) {
                                if (error.response.data) {
                                    if (error.response.data.message) {
                                        errorMsg += error.response.data.message;
                                    } else if (error.response.data.errors) {
                                        const errors = Object.values(error.response.data.errors).flat();
                                        errorMsg += errors.join(', ');
                                    } else {
                                        errorMsg += 'Server error';
                                    }
                                } else {
                                    errorMsg += 'Server error ' + error.response.status;
                                }
                            } else if (error.message) {
                                errorMsg += error.message;
                            } else {
                                errorMsg += 'Unknown error';
                            }

                            this.$dispatch('notify', { message: errorMsg, type: 'error' });
                            this.isLoading = false;
                        });
                }
            }
        }
    </script>
</body>

</html>