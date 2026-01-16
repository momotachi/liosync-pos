<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Purchase Order - Cycle</title>
    <link rel="icon" type="image/png" href="{{ asset('images/cycle-logo.png') }}">
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
    x-data="purchaseOrder({{ json_encode($settings) }})" @notify.window="$dispatch('toast-add', $event.detail)">

    <!-- Top Navigation -->
    <header
        class="flex-none h-14 md:h-16 bg-surface-light dark:bg-surface-dark border-b border-slate-200 dark:border-emerald-900 px-3 md:px-6 flex items-center justify-between z-20 shadow-sm">
        <a href="/branch" class="flex items-center gap-2 md:gap-4 hover:opacity-80 transition-opacity"
            title="Go to Dashboard">
            <div class="size-7 md:size-8 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl md:text-4xl">shopping_cart</span>
            </div>
            <h1 class="text-lg md:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Purchase</h1>
        </a>
        <!-- Search Bar - Hidden on mobile -->
        <div class="hidden md:flex flex-1 max-w-xl px-8">
            <div class="relative group w-full">
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                    <span class="material-symbols-outlined">search</span>
                </div>
                <input x-model="search"
                    class="block w-full pl-10 pr-3 py-2.5 border-none rounded-xl bg-slate-100 dark:bg-emerald-900/30 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary focus:bg-white dark:focus:bg-emerald-900/50 transition-all shadow-inner"
                    placeholder="Search material name or code..." type="text" />
            </div>
        </div>
        <!-- Mobile Search Button -->
        <button @click="showMobileSearch = !showMobileSearch"
            class="md:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-emerald-800 text-slate-600 dark:text-emerald-100">
            <span class="material-symbols-outlined">search</span>
        </button>
        <!-- Right Actions -->
        <div class="flex items-center gap-2 md:gap-4">
            <div class="hidden sm:flex items-center gap-2 mr-2 md:mr-4">
                <!-- Sidebar Toggle Button (Desktop) -->
                <button @click="showSidebar = !showSidebar"
                    class="hidden lg:flex p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-emerald-800 text-slate-600 dark:text-emerald-100 transition-colors"
                    :title="showSidebar ? 'Hide Cart Panel' : 'Show Cart Panel'">
                    <span class="material-symbols-outlined"
                        x-text="showSidebar ? 'right_panel_close' : 'right_panel_open'"></span>
                </button>
                <button
                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-emerald-800 text-slate-600 dark:text-emerald-100"
                    title="Network Status">
                    <span class="material-symbols-outlined text-primary">wifi</span>
                </button>
            </div>
            <div class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-emerald-800">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold leading-none">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</p>
                    <p class="text-xs text-slate-500 dark:text-emerald-300 mt-1">Purchaser</p>
                </div>
                <div
                    class="size-10 rounded-full bg-emerald-100 dark:bg-emerald-800 overflow-hidden border-2 border-primary/20">
                    <img alt="Purchaser Avatar" class="w-full h-full object-contain"
                        src="https://ui-avatars.com/api/?name={{ Auth::check() ? Auth::user()->name : 'Guest' }}&background=05945b&color=fff" />
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Search Bar (toggle on mobile) -->
    <div x-show="showMobileSearch" x-transition
        class="md:hidden bg-surface-light dark:bg-surface-dark px-3 py-2 border-b border-slate-200 dark:border-emerald-900">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <span class="material-symbols-outlined">search</span>
            </div>
            <input x-model="search"
                class="block w-full pl-10 pr-3 py-2 border-none rounded-xl bg-slate-100 dark:bg-emerald-900/30 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary"
                placeholder="Search material..." type="text" />
        </div>
    </div>

    {{-- Subscription Expired Warning Banner --}}
    @php
        $hasActiveSubscription = true;
        if(Auth::check() && !Auth::user()->isSuperAdmin()) {
            $branchId = session('active_branch_id') ?? Auth::user()->branch_id;
            if($branchId) {
                $branch = \App\Models\Branch::find($branchId);
                $hasActiveSubscription = $branch ? $branch->hasActiveSubscription() : true;
            }
        }
    @endphp
    @if(Auth::check() && !$hasActiveSubscription && !Auth::user()->isSuperAdmin())
    <div x-data="{ show: true }" x-show="show"
        class="bg-gradient-to-r from-red-500 to-orange-500 text-white shadow-lg"
        role="alert">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="flex items-center justify-between flex-wrap">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                    <div class="flex-1">
                        <p class="font-bold text-sm">
                            Langganan Telah Habis - Mode Read-Only
                        </p>
                        <p class="text-xs text-red-100">
                            Transaksi dinonaktifkan. Silakan perpanjang langganan untuk melanjutkan.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('subscription.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-white text-red-600 rounded-lg text-sm font-bold hover:bg-red-50 transition-colors shadow-sm">
                        <span class="material-symbols-outlined mr-1 text-sm">payments</span>
                        Perpanjang
                    </a>
                    <button @click="show = false"
                        class="p-1.5 rounded-lg hover:bg-red-400 text-white transition-colors"
                        title="Tutup">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col lg:flex-row overflow-hidden relative z-0">
        <!-- LEFT PANEL: Catalog (70%) -->
        <section
            class="flex-1 flex flex-col min-w-0 bg-background-light dark:bg-background-dark p-4 lg:p-6 lg:pr-3 overflow-hidden">
            <!-- Category Tabs - Improved for touch -->
            <div class="flex-none mb-4 md:mb-6 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide">
                <div class="flex gap-2 md:gap-3">
                    <button @click="filterCategory(null)"
                        :class="activeCategory === null ? 'bg-primary text-white hover:shadow-lg' : 'bg-white dark:bg-emerald-900/40 text-slate-700 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-800'"
                        class="flex items-center gap-1.5 md:gap-2 px-4 md:px-6 py-2.5 md:py-3 rounded-xl font-semibold shadow-md transition-all transform hover:-translate-y-0.5 whitespace-nowrap text-sm md:text-base">
                        <span class="material-symbols-outlined text-[18px] md:text-[20px]">grid_view</span>
                        <span class="hidden sm:inline">All Materials</span>
                        <span class="sm:hidden">All</span>
                    </button>
                    @foreach($categories as $category)
                        @if($items->contains('category_id', $category->id))
                            <button @click="filterCategory({{ $category->id }})"
                                :class="activeCategory === {{ $category->id }} ? 'bg-primary text-white hover:shadow-lg' : 'bg-white dark:bg-emerald-900/40 text-slate-700 dark:text-emerald-100 hover:bg-emerald-50 dark:hover:bg-emerald-800'"
                                class="flex items-center gap-1.5 md:gap-2 px-4 md:px-6 py-2.5 md:py-3 rounded-xl font-medium shadow-sm border border-slate-200 dark:border-emerald-800 transition-colors whitespace-nowrap text-sm md:text-base">
                                <span
                                    class="material-symbols-outlined text-[18px] md:text-[20px] text-accent">inventory_2</span>
                                {{ $category->name }}
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Material Grid - Responsive with better mobile layout -->
            <div class="flex-1 overflow-y-auto custom-scrollbar pr-1 md:pr-2 pb-24 lg:pb-4">
                <div
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2 sm:gap-3 md:gap-4">

                    @foreach($items as $item)
                        <!-- Material Card - Compact for mobile -->
                        <div x-show="isVisible({{ $item['category_id'] }}, '{{ addslashes($item['name']) }}')"
                            @click="addToCart({{ json_encode($item) }})"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                            class="group bg-surface-light dark:bg-surface-dark rounded-xl md:rounded-2xl p-2 md:p-3 shadow-sm hover:shadow-md border border-slate-100 dark:border-emerald-900/50 cursor-pointer transition-all hover:border-primary/50 relative overflow-hidden active:scale-95">
                            <div
                                class="aspect-square rounded-lg md:rounded-xl bg-emerald-50/50 dark:bg-emerald-900/20 mb-2 md:mb-3 overflow-hidden relative">
                                @if($item['image'])
                                    <img class="w-full h-full object-contain transform group-hover:scale-110 transition-transform duration-300"
                                        src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl md:text-4xl">📦</div>
                                @endif

                                <div
                                    class="absolute top-1 right-1 md:top-2 md:right-2 bg-white/90 dark:bg-black/60 backdrop-blur-sm rounded-md md:rounded-lg px-1.5 md:px-2 py-0.5 md:py-1 shadow-sm">
                                    <span
                                        class="text-xs md:text-sm font-bold text-slate-900 dark:text-white">{{ $settings['currency_symbol'] ?? 'Rp' }}
                                        {{ number_format($item['purchase_price'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <h3
                                class="font-bold text-slate-800 dark:text-slate-100 text-sm md:text-lg leading-tight mb-0.5 md:mb-1 line-clamp-2">
                                {{ $item['name'] }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 hidden sm:block">
                                {{ $item['category_name'] ?? 'Material' }}
                            </p>
                            <p class="text-xs text-primary dark:text-emerald-400 mt-0.5 md:mt-1">
                                Stock: {{ number_format($item['current_stock'], 2, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                    <div x-show="materials.length === 0" class="col-span-full text-center text-gray-500 py-10"
                        style="display: none;">
                        No materials found.
                    </div>

                </div>
            </div>
        </section>

        <!-- RIGHT PANEL: Current Purchase Order - Slide-up drawer on mobile, sidebar on desktop -->
        <!-- Mobile Cart Overlay -->
        <div x-show="showMobileCart" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="showMobileCart = false"
            class="lg:hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-40"></div>

        <aside x-show="showMobileCart || (!isMobileView && showSidebar)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full lg:translate-x-full lg:translate-y-0"
            x-transition:enter-end="translate-y-0 lg:translate-x-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0 lg:translate-x-0"
            x-transition:leave-end="translate-y-full lg:translate-x-full lg:translate-y-0" class="fixed lg:relative bottom-0 left-0 right-0 lg:left-auto lg:right-auto lg:bottom-auto
                   max-h-[85vh] lg:max-h-none lg:h-full
                   w-full lg:w-80 xl:w-96 lg:max-w-[30rem]
                   bg-surface-light dark:bg-surface-dark 
                   border-t lg:border-t-0 lg:border-l border-slate-200 dark:border-emerald-900/50 
                   flex flex-col shadow-2xl lg:shadow-xl z-50 lg:z-auto
                   rounded-t-3xl lg:rounded-none">
            <!-- Mobile Drawer Handle -->
            <div class="lg:hidden flex justify-center pt-3 pb-1">
                <div class="w-12 h-1.5 bg-slate-300 dark:bg-slate-600 rounded-full"></div>
            </div>

            <!-- Header with close button for mobile -->
            <div class="flex-none p-4 lg:p-5 border-b border-slate-100 dark:border-emerald-900/50">
                <div class="flex justify-between items-center mb-3 lg:mb-4">
                    <h2 class="text-lg lg:text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">shopping_cart</span>
                        PO #NEW
                    </h2>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-900 text-primary dark:text-emerald-300 text-xs font-bold uppercase tracking-wider">Purchase</span>
                        <!-- Close button for mobile -->
                        <button @click="showMobileCart = false"
                            class="lg:hidden p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-emerald-800 text-slate-500">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="showSupplierModal = true"
                        class="flex-1 flex items-center justify-center gap-1 py-2 px-3 rounded-lg border border-slate-200 dark:border-emerald-800 hover:bg-slate-50 dark:hover:bg-emerald-800/50 text-slate-600 dark:text-slate-300 text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-[18px]">local_shipping</span>
                        <span x-text="supplierName || 'Supplier'"></span>
                    </button>
                    <button @click="showNoteModal = true"
                        class="flex-1 flex items-center justify-center gap-1 py-2 px-3 rounded-lg border border-slate-200 dark:border-emerald-800 hover:bg-slate-50 dark:hover:bg-emerald-800/50 text-slate-600 dark:text-slate-300 text-sm font-medium transition-colors">
                        <span class="material-symbols-outlined text-[18px]">note_add</span>
                        <span x-text="poNote || 'Note'"></span>
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
                    <span class="material-symbols-outlined text-4xl mb-2">shopping_basket</span>
                    <p>Purchase order is empty</p>
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
                                    <div class="w-full h-full flex items-center justify-center text-lg">📦</div>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-slate-900 dark:text-white truncate" x-text="item.name"></h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400"
                                    x-text="formatCurrency(item.price) + ' / ' + (item.unit || 'unit')"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex items-center bg-white dark:bg-emerald-950 rounded-lg shadow-sm border border-slate-200 dark:border-emerald-800 h-8">
                                    <button @click="updateQuantity(index, -0.1)"
                                        class="w-8 h-full flex items-center justify-center text-slate-500 hover:text-primary hover:bg-slate-50 dark:hover:bg-emerald-900 rounded-l-lg transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">remove</span>
                                    </button>
                                    <span class="w-8 text-center text-sm font-bold text-slate-900 dark:text-white"
                                        x-text="Number(item.quantity).toFixed(2)"></span>
                                    <button @click="updateQuantity(index, 0.1)"
                                        class="w-8 h-full flex items-center justify-center text-slate-500 hover:text-primary hover:bg-slate-50 dark:hover:bg-emerald-900 rounded-r-lg transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">add</span>
                                    </button>
                                </div>
                                <button @click="removeFromCart(index)"
                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                    title="Remove from cart">
                                    <span class="material-symbols-outlined text-lg">delete</span>
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
                        <span x-text="isLoading ? 'Processing...' : 'CREATE PURCHASE ORDER'"></span>
                        <span x-show="!isLoading" class="material-symbols-outlined">shopping_cart</span>
                    </button>
                </div>
            </div>
        </aside>
    </main>

    <!-- Mobile Floating Cart Button -->
    <button x-show="isMobileView && !showMobileCart" @click="showMobileCart = true"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100" class="lg:hidden fixed bottom-6 right-6 z-30 
               w-16 h-16 rounded-full bg-primary text-white 
               shadow-2xl shadow-primary/40 
               flex items-center justify-center
               hover:bg-primary-hover active:scale-95
               transition-all duration-200">
        <span class="material-symbols-outlined text-3xl">shopping_cart</span>
        <!-- Cart Badge -->
        <span x-show="cart.length > 0" class="absolute -top-1 -right-1 
                   min-w-[24px] h-6 px-1.5
                   bg-accent text-white text-xs font-bold 
                   rounded-full flex items-center justify-center
                   shadow-lg animate-pulse" x-text="cart.reduce((sum, item) => sum + 1, 0)"></span>
    </button>

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
            @click.outside="showPaymentModal = false" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
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
                            <input type="radio" name="payment_method" value="transfer" x-model="paymentMethod"
                                class="peer sr-only" />
                            <div
                                class="flex flex-col items-center justify-center p-2 rounded-lg border-2 border-transparent bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-all peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                <span class="material-symbols-outlined mb-0.5 text-xl">account_balance</span>
                                <span class="text-xs font-medium mt-1">Transfer</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group relative">
                            <input type="radio" name="payment_method" value="credit" x-model="paymentMethod"
                                class="peer sr-only" />
                            <div
                                class="flex flex-col items-center justify-center p-2 rounded-lg border-2 border-transparent bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-all peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary">
                                <span class="material-symbols-outlined mb-0.5 text-xl">credit_card</span>
                                <span class="text-xs font-medium mt-1">Credit</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <!-- Footer Actions -->
            <div class="p-5 pt-0 flex gap-3">
                <button @click="showPaymentModal = false"
                    class="flex-1 py-2.5 px-3 rounded-lg border border-gray-200 dark:border-gray-600 font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-200 text-sm">
                    Cancel
                </button>
                <button type="button" @click="processPayment"
                    :class="paymentMethod ? 'hover:bg-[#047a4b] shadow-lg shadow-primary/25' : 'opacity-50 cursor-not-allowed'"
                    :disabled="!paymentMethod"
                    class="flex-2 py-2.5 px-3 rounded-lg bg-primary text-white font-bold text-base transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Finalize Purchase
                </button>
            </div>
        </div>
    </div>

    <!-- Supplier Modal -->
    <div x-show="showSupplierModal" style="display: none;"
        class="fixed inset-0 z-9999 flex items-center justify-center bg-[#0d1c16]/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
        <div class="max-w-md bg-white dark:bg-[#1a2e26] rounded-xl shadow-2xl overflow-hidden flex flex-col relative"
            @click.outside="showSupplierModal = false" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-lg font-bold tracking-tight text-[#0d1c16] dark:text-white">Supplier Information</h2>
                <button @click="showSupplierModal = false"
                    class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Supplier Name</label>
                    <input type="text" x-model="supplierName"
                        class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-black/20 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-xl text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary"
                        placeholder="Enter supplier name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Phone Number</label>
                    <input type="text" x-model="supplierPhone"
                        class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-black/20 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-xl text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary"
                        placeholder="Enter phone number (optional)">
                </div>
            </div>
            <!-- Footer Actions -->
            <div class="p-5 pt-0 flex gap-3">
                <button @click="showSupplierModal = false"
                    class="flex-1 py-2.5 px-3 rounded-lg border border-gray-200 dark:border-gray-600 font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-sm">
                    Cancel
                </button>
                <button @click="saveSupplier()"
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
            @click.outside="showNoteModal = false" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-lg font-bold tracking-tight text-[#0d1c16] dark:text-white">Purchase Note</h2>
                <button @click="showNoteModal = false"
                    class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Note for this
                        purchase</label>
                    <textarea x-model="poNote" rows="4"
                        class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-black/20 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-xl text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary resize-none"
                        placeholder="Add a note (e.g., delivery instructions, quality requirements, etc.)"></textarea>
                </div>
            </div>
            <!-- Footer Actions -->
            <div class="p-5 pt-0 flex gap-3">
                <button @click="showNoteModal = false; poNote = ''"
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

    <!-- Item Entry Modal -->
    <div x-show="showItemModal" style="display: none;"
        class="fixed inset-0 z-9999 flex items-center justify-center bg-[#0d1c16]/60 backdrop-blur-sm p-4 animate-in fade-in duration-200">
        <div class="max-w-sm bg-white dark:bg-[#1a2e26] rounded-xl shadow-2xl overflow-hidden flex flex-col relative"
            @click.outside="showItemModal = false" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-lg font-bold tracking-tight text-[#0d1c16] dark:text-white">Add Item</h2>
                <button @click="showItemModal = false"
                    class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-5 space-y-4">
                <!-- Item Name -->
                <div>
                    <label
                        class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1.5">Item</label>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div
                            class="w-10 h-10 rounded-lg bg-primary/10 dark:bg-emerald-900/30 flex items-center justify-center">
                            <span
                                class="material-symbols-outlined text-primary dark:text-emerald-400">inventory_2</span>
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white" x-text="currentItem?.name || ''"></span>
                    </div>
                </div>
                <!-- Quantity -->
                <div>
                    <label
                        class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1.5">Quantity</label>
                    <div class="relative">
                        <input x-model="itemQty" type="number" step="0.01" min="0.01"
                            class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-lg text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary"
                            placeholder="Enter quantity">
                    </div>
                </div>
                <!-- Total Cost -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase mb-1.5">Total
                        Cost</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                            <span class="text-gray-400 text-sm font-medium" x-text="currencySymbol"></span>
                        </div>
                        <input x-model="itemCost" type="number" step="0.01" min="0"
                            class="block w-full pl-10 pr-3 py-2.5 bg-gray-50 dark:bg-gray-800 border-0 ring-1 ring-inset ring-gray-200 dark:ring-gray-700 rounded-lg text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary"
                            placeholder="0">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Total cost for this item (not per unit)</p>
                </div>
            </div>
            <!-- Footer Actions -->
            <div class="p-5 pt-0 flex gap-3">
                <button @click="showItemModal = false"
                    class="flex-1 py-2.5 px-3 rounded-lg border border-gray-200 dark:border-gray-600 font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-sm">
                    Cancel
                </button>
                <button @click="saveItemToCart()"
                    class="flex-2 py-2.5 px-3 rounded-lg bg-primary text-white font-bold text-base hover:bg-[#047a4b] shadow-lg shadow-primary/25 transition-colors">
                    Add to Cart
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

        function purchaseOrder(settings = {}) {
            return {
                cart: [],
                activeCategory: null,
                search: '',
                isLoading: false,

                // Mobile Responsive State
                showMobileCart: false,
                showMobileSearch: false,
                isMobileView: window.innerWidth < 1024,

                // Sidebar Toggle State (Desktop)
                showSidebar: true,

                // Settings
                settings: settings,
                currencySymbol: settings.currency_symbol || 'Rp',

                // Materials data
                materials: {!! $itemsJson ?? '[]' !!},

                // Payment Modal State
                showPaymentModal: false,
                paymentMethod: 'cash',

                // Supplier & Note Modal State
                showSupplierModal: false,
                showNoteModal: false,
                supplierName: '',
                supplierPhone: '',
                poNote: '',

                // Item Modal State
                showItemModal: false,
                currentItem: null,
                itemQty: '',
                itemCost: '',

                init() {
                    console.log('Purchase Order initialized');

                    // Handle window resize for responsive behavior
                    const handleResize = () => {
                        this.isMobileView = window.innerWidth < 1024;
                        // Auto-hide mobile drawers when switching to desktop
                        if (!this.isMobileView) {
                            this.showMobileCart = false;
                            this.showMobileSearch = false;
                        }
                    };
                    window.addEventListener('resize', handleResize);
                },

                get total() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                formatCurrency(amount) {
                    return this.currencySymbol + ' ' + Number(amount).toLocaleString('id-ID');
                },

                isVisible(categoryId, materialName) {
                    if (this.activeCategory && this.activeCategory !== categoryId) return false;
                    if (this.search && !materialName.toLowerCase().includes(this.search.toLowerCase())) return false;
                    return true;
                },

                filterCategory(id) {
                    this.activeCategory = id;
                },

                // Open modal for item entry
                addToCart(material) {
                    this.currentItem = material;
                    this.itemQty = '';
                    this.itemCost = '';
                    this.showItemModal = true;
                },

                // Save item to cart from modal
                saveItemToCart() {
                    if (!this.currentItem || this.itemQty <= 0 || this.itemCost < 0) {
                        this.$dispatch('notify', { message: 'Please enter valid quantity and cost', type: 'error' });
                        return;
                    }

                    const qty = parseFloat(this.itemQty);
                    const totalCost = parseFloat(this.itemCost);
                    const unitPrice = qty > 0 ? totalCost / qty : totalCost;

                    // Check if item already exists in cart
                    let existingItem = this.cart.find(cartItem => cartItem.id === this.currentItem.id);
                    if (existingItem) {
                        existingItem.quantity = parseFloat(existingItem.quantity) + qty;
                        existingItem.price = unitPrice;
                    } else {
                        this.cart.push({
                            ...this.currentItem,
                            price: unitPrice,
                            quantity: qty
                        });
                    }

                    this.$dispatch('notify', { message: `${this.currentItem.name} added to purchase order` });
                    this.showItemModal = false;
                },

                // Remove item from cart
                removeFromCart(index) {
                    const item = this.cart[index];
                    this.cart.splice(index, 1);
                    this.$dispatch('notify', { message: `${item.name} removed from cart`, type: 'error' });
                },

                // Update quantity
                updateQuantity(index, delta) {
                    const item = this.cart[index];
                    const newQty = parseFloat(item.quantity) + delta;
                    if (newQty <= 0) {
                        this.removeFromCart(index);
                    } else {
                        item.quantity = newQty;
                    }
                },

                clearCart() {
                    if (confirm('Clear purchase order?')) {
                        this.cart = [];
                        this.$dispatch('notify', { message: 'Purchase order cleared', type: 'error' });
                    }
                },

                saveSupplier() {
                    this.showSupplierModal = false;
                    this.$dispatch('notify', { message: 'Supplier information saved' });
                },

                checkout() {
                    if (this.cart.length === 0) return;
                    this.paymentMethod = 'cash';
                    this.showPaymentModal = true;
                },

                processPayment() {
                    // Validate payment method
                    if (!this.paymentMethod) {
                        this.$dispatch('notify', { message: 'Please select a payment method', type: 'error' });
                        return;
                    }

                    // Validate cart
                    if (this.cart.length === 0) {
                        this.$dispatch('notify', { message: 'Cart is empty', type: 'error' });
                        return;
                    }

                    this.isLoading = true;

                    const payload = {
                        items: this.cart.map(item => ({
                            item_id: item.id,
                            quantity: parseFloat(item.quantity) || 0,
                            price: parseFloat(item.price) || 0
                        })),
                        total_amount: parseFloat(this.total) || 0,
                        payment_method: this.paymentMethod,
                        supplier_name: this.supplierName || null,
                        supplier_phone: this.supplierPhone || null,
                        notes: this.poNote || null
                    };

                    console.log('Sending payload:', payload);

                    axios.post('/purchase/store', payload)
                        .then(response => {
                            console.log('Purchase response:', response.data);
                            this.$dispatch('notify', { message: 'Purchase Order #' + response.data.purchase_id + ' Created Successfully!', type: 'success' });
                            this.cart = [];
                            this.supplierName = '';
                            this.supplierPhone = '';
                            this.poNote = '';
                            this.showPaymentModal = false;
                            this.isLoading = false;

                            // Open receipt
                            if (response.data.receipt_url) {
                                window.open(response.data.receipt_url, '_blank');
                            }
                        })
                        .catch(error => {
                            console.error('Purchase error:', error);
                            console.error('Error response:', error.response);

                            let errorMsg = 'Purchase Failed: ';
                            if (error.response && error.response.data) {
                                if (error.response.data.message) {
                                    errorMsg += error.response.data.message;
                                } else if (error.response.data.errors) {
                                    const errors = Object.values(error.response.data.errors).flat();
                                    errorMsg += errors.join(', ');
                                } else {
                                    errorMsg += JSON.stringify(error.response.data);
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