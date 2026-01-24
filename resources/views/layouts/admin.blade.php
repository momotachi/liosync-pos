<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lio Sync')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/cycle-logo.png') }}">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <!-- Material Icons for the new Product UI (user provided link) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#F97316", // Orange-500 (Juice Theme)
                        "primary-dark": "#EA580C", // Orange-600
                        secondary: "#10b981", // Emerald-500 (Secondary/Success)
                        "background-light": "#F3F4F6",
                        "background-dark": "#111827",
                        "surface-light": "#FFFFFF",
                        "surface-dark": "#1F2937",
                        "card-light": "#FFFFFF",
                        "card-dark": "#1F2937",
                        "border-light": "#E5E7EB",
                        "border-dark": "#374151",
                    },
                    fontFamily: {
                        display: ["Inter", "sans-serif"],
                        body: ["Inter", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.5rem",
                    },
                },
            },
        };
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #4B5563;
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: #6B7280;
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        /* Alpine.js x-cloak: hide elements until Alpine is ready */
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body
    class="bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100 transition-colors duration-200 min-h-screen flex"
    x-data="{ sidebarOpen: false, sidebarCollapsed: false }">

    <!-- Mobile Sidebar Overlay -->
    <div x-cloak x-show="sidebarOpen && window.innerWidth < 1024" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-20 lg:hidden"></div>

    <!-- Mobile Hamburger Menu Button -->
    <button @click="sidebarOpen = !sidebarOpen" x-show="!sidebarOpen"
        class="fixed top-4 left-4 z-30 p-2 rounded-lg bg-surface-light dark:bg-surface-dark shadow-lg border border-border-light dark:border-border-dark lg:hidden hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <span class="material-symbols-outlined text-gray-600 dark:text-gray-300">menu</span>
    </button>

    <!-- Sidebar -->
    <aside x-cloak x-show="sidebarOpen || window.innerWidth >= 1024" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full" :class="[
            sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
        ]"
        class="w-64 bg-surface-light dark:bg-surface-dark border-r border-border-light dark:border-border-dark shrink-0 flex flex-col h-screen fixed left-0 top-0 z-20 transform lg:transform-none transition-all duration-300">
        <div class="h-16 flex items-center px-4 border-b border-border-light dark:border-border-dark justify-between">
            <div class="flex items-center" :class="sidebarCollapsed ? 'md:justify-center md:w-full' : ''">
                <img src="{{ asset('images/liosync-icon-transparent.png') }}" alt="Lio Sync Logo" class="h-10 w-auto object-contain"
                    :class="sidebarCollapsed ? '' : 'mr-2'">
                <h1 x-show="!sidebarCollapsed"
                    class="text-xl font-bold text-gray-800 dark:text-white tracking-tight hidden md:block">Lio Sync</h1>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight md:hidden ml-2">Lio Sync</h1>
            </div>
            <!-- Desktop Collapse Toggle Button -->
            <button @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden lg:flex p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors"
                :class="sidebarCollapsed ? 'mx-auto' : ''"
                :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
                <span class="material-symbols-outlined text-xl"
                    x-text="sidebarCollapsed ? 'chevron_right' : 'chevron_left'"></span>
            </button>
            <!-- Mobile Close Button -->
            <button @click="sidebarOpen = false"
                class="lg:hidden p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>
        <nav class="flex-1 py-4 space-y-1 overflow-y-auto overflow-x-hidden"
            :class="sidebarCollapsed ? 'px-2' : 'px-3'">
            {{-- Back to Superadmin/Branches (for superadmin and company admin users) --}}
            @if(Auth::check())
                @if(Auth::user()->isSuperAdmin())
                    @if(session('active_branch_id') && session('company_id'))
                        {{-- Superadmin is viewing a branch - show "Back to Company" button --}}
                        <a href="/company/{{ session('company_id') }}/branches"
                            class="flex items-center text-sm font-medium rounded-md bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors mb-2"
                            :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                            :title="sidebarCollapsed ? 'Back to Company' : ''">
                            <span class="material-symbols-outlined" :class="sidebarCollapsed ? '' : 'mr-3'">arrow_back</span>
                            <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Back to
                                Company</span>
                        </a>
                        <div class="p-2 mb-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-xs text-blue-700 dark:text-blue-400">
                            Viewing: {{ \App\Models\Branch::find(session('active_branch_id'))?->name ?? 'Branch' }}
                        </div>
                        <div class="my-2 border-t border-border-light dark:border-border-dark"></div>
                    @else
                        <a href="/superadmin/companies"
                            class="flex items-center text-sm font-medium rounded-md bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors mb-2"
                            :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                            :title="sidebarCollapsed ? 'Back to Superadmin' : ''">
                            <span class="material-symbols-outlined" :class="sidebarCollapsed ? '' : 'mr-3'">arrow_back</span>
                            <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Back to
                                Superadmin</span>
                        </a>
                        <div class="my-2 border-t border-border-light dark:border-border-dark"></div>
                    @endif
                @elseif(Auth::user()->isCompanyAdmin() && Auth::user()->company_id)
                    @if(session('active_branch_id') || request()->is('branch*'))
                        {{-- Company Admin is viewing a branch - show "Back to Company" button --}}
                        <a href="{{ route('company.branches.index', Auth::user()->company_id) }}"
                            class="flex items-center text-sm font-medium rounded-md bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors mb-2"
                            :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                            :title="sidebarCollapsed ? 'Back to Company' : ''">
                            <span class="material-symbols-outlined" :class="sidebarCollapsed ? '' : 'mr-3'">arrow_back</span>
                            <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Back to
                                Company</span>
                        </a>
                        @if(session('active_branch_id'))
                            <div class="p-2 mb-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-xs text-blue-700 dark:text-blue-400">
                                Viewing: {{ \App\Models\Branch::find(session('active_branch_id'))?->name ?? 'Branch' }}
                            </div>
                        @endif
                        <div class="my-2 border-t border-border-light dark:border-border-dark"></div>
                    @else
                        {{-- Company Admin is in company context - show "Back to Branches" button --}}
                        <a href="{{ route('company.branches.index', Auth::user()->company_id) }}"
                            class="flex items-center text-sm font-medium rounded-md bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors mb-2"
                            :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                            :title="sidebarCollapsed ? 'Back to Branches' : ''">
                            <span class="material-symbols-outlined" :class="sidebarCollapsed ? '' : 'mr-3'">store</span>
                            <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Back to
                                Branches</span>
                        </a>
                        <div class="my-2 border-t border-border-light dark:border-border-dark"></div>
                    @endif
                @endif
            @endif

            {{-- Branch Admin Menus --}}
            @if(Auth::check() && (!Auth::user()->isSuperAdmin() || session('active_branch_id')) && (!Auth::user()->isCompanyAdmin() || session('active_branch_id') || request()->is('branch*')))
                <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('filament.admin.pages.dashboard') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'" href="/branch"
                    :title="sidebarCollapsed ? 'Dashboard' : ''">
                    <span
                        class="material-symbols-outlined {{ request()->routeIs('filament.admin.pages.dashboard') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                        :class="sidebarCollapsed ? '' : 'mr-3'">dashboard</span>
                    <span x-show="!sidebarCollapsed"
                        class="whitespace-nowrap transition-opacity duration-300">Dashboard</span>
                </a>
                <a class="flex items-center text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 group transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'" href="/pos"
                    :title="sidebarCollapsed ? 'Cashier' : ''">
                    <span
                        class="material-symbols-outlined text-gray-400 group-hover:text-primary dark:group-hover:text-primary"
                        :class="sidebarCollapsed ? '' : 'mr-3'">point_of_sale</span>
                    <span x-show="!sidebarCollapsed"
                        class="whitespace-nowrap transition-opacity duration-300">Cashier</span>
                </a>
                <a class="flex items-center text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 group transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'" href="/purchase"
                    :title="sidebarCollapsed ? 'Purchase Order' : ''">
                    <span
                        class="material-symbols-outlined text-gray-400 group-hover:text-primary dark:group-hover:text-primary"
                        :class="sidebarCollapsed ? '' : 'mr-3'">shopping_cart</span>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Purchase
                        Order</span>
                </a>
                <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.items.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'" href="/branch/items"
                    :title="sidebarCollapsed ? 'Items' : ''">
                    <span
                        class="material-symbols-outlined {{ request()->routeIs('admin.items.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                        :class="sidebarCollapsed ? '' : 'mr-3'">apps</span>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Items</span>
                </a>
                <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.categories.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'" href="/branch/categories"
                    :title="sidebarCollapsed ? 'Categories' : ''">
                    <span
                        class="material-symbols-outlined {{ request()->routeIs('admin.categories.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                        :class="sidebarCollapsed ? '' : 'mr-3'">category</span>
                    <span x-show="!sidebarCollapsed"
                        class="whitespace-nowrap transition-opacity duration-300">Categories</span>
                </a>
                <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'" href="/branch/users"
                    :title="sidebarCollapsed ? 'Users' : ''">
                    <span
                        class="material-symbols-outlined {{ request()->routeIs('admin.users.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                        :class="sidebarCollapsed ? '' : 'mr-3'">people</span>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Users</span>
                </a>
            @endif

            {{-- Subscription Menu - Branch Admin --}}
            @if(Auth::check() && (!Auth::user()->isSuperAdmin() || session('active_branch_id')) && (!Auth::user()->isCompanyAdmin() || session('active_branch_id') || request()->is('branch*')))
                <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('subscription.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                    href="{{ route('subscription.index') }}" :title="sidebarCollapsed ? 'Subscription' : ''">
                    <span
                        class="material-symbols-outlined {{ request()->routeIs('subscription.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                        :class="sidebarCollapsed ? '' : 'mr-3'">subscriptions</span>
                    <span x-show="!sidebarCollapsed"
                        class="whitespace-nowrap transition-opacity duration-300">Subscription</span>
                    @if(Auth::user()->branch && !Auth::user()->branch->hasActiveSubscription())
                        <span x-show="!sidebarCollapsed"
                            class="ml-auto px-2 py-0.5 bg-red-100 text-red-600 rounded-full text-xs font-medium">Expired</span>
                        <span x-show="sidebarCollapsed" class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
                    @endif
                </a>
            @endif

            {{-- Subscription Menu - Company Admin (only when NOT in branch context) --}}
            @if(Auth::check() && Auth::user()->isCompanyAdmin() && Auth::user()->company_id && !session('active_branch_id') && !request()->is('branch*'))
                <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('company.subscriptions.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                    href="{{ route('company.subscriptions.index', Auth::user()->company_id) }}"
                    :title="sidebarCollapsed ? 'Subscriptions' : ''">
                    <span
                        class="material-symbols-outlined {{ request()->routeIs('company.subscriptions.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                        :class="sidebarCollapsed ? '' : 'mr-3'">subscriptions</span>
                    <span x-show="!sidebarCollapsed"
                        class="whitespace-nowrap transition-opacity duration-300">Subscriptions</span>
                </a>
            @endif

            {{-- Reports Menu - Branch Admin Only --}}
            @if(Auth::check() && (!Auth::user()->isSuperAdmin() || session('active_branch_id')) && (!Auth::user()->isCompanyAdmin() || session('active_branch_id') || request()->is('branch*')))
                <!-- Reports Menu with Submenu -->
                <!-- Reports Menu with Submenu -->
                <div x-data="{ open: @js(request()->routeIs('admin.reports.*')) }">
                    <button @click="open = !open"
                        class="w-full flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors group"
                        :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                        :title="sidebarCollapsed ? 'Reports' : ''">
                        <span
                            class="material-symbols-outlined {{ request()->routeIs('admin.reports.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                            :class="sidebarCollapsed ? '' : 'mr-3'">analytics</span>
                        <span x-show="!sidebarCollapsed"
                            class="flex-1 text-left whitespace-nowrap transition-opacity duration-300">Reports</span>
                        <span x-show="!sidebarCollapsed"
                            class="material-symbols-outlined text-gray-400 transition-transform"
                            :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="open" x-transition class="space-y-1" :class="sidebarCollapsed ? 'mt-1' : 'mt-1 ml-6'">
                        <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.index') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                            :class="sidebarCollapsed ? 'justify-center py-2 px-2' : 'px-3 py-2'" href="/branch/reports"
                            :title="sidebarCollapsed ? 'Ringkasan Penjualan' : ''">
                            <span class="material-symbols-outlined text-sm"
                                :class="sidebarCollapsed ? '' : 'mr-3'">show_chart</span>
                            <span x-show="!sidebarCollapsed"
                                class="whitespace-nowrap transition-opacity duration-300">Ringkasan Penjualan</span>
                        </a>
                        <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.purchases') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                            :class="sidebarCollapsed ? 'justify-center py-2 px-2' : 'px-3 py-2'"
                            href="/branch/reports/purchases" :title="sidebarCollapsed ? 'Laporan Pembelian' : ''">
                            <span class="material-symbols-outlined text-sm"
                                :class="sidebarCollapsed ? '' : 'mr-3'">shopping_cart</span>
                            <span x-show="!sidebarCollapsed"
                                class="whitespace-nowrap transition-opacity duration-300">Laporan Pembelian</span>
                        </a>
                        <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.profit') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                            :class="sidebarCollapsed ? 'justify-center py-2 px-2' : 'px-3 py-2'"
                            href="/branch/reports/profit" :title="sidebarCollapsed ? 'Laporan Profit' : ''">
                            <span class="material-symbols-outlined text-sm"
                                :class="sidebarCollapsed ? '' : 'mr-3'">account_balance_wallet</span>
                            <span x-show="!sidebarCollapsed"
                                class="whitespace-nowrap transition-opacity duration-300">Laporan Profit</span>
                        </a>
                        <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.cashflow') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                            :class="sidebarCollapsed ? 'justify-center py-2 px-2' : 'px-3 py-2'"
                            href="/branch/reports/cashflow" :title="sidebarCollapsed ? 'Laporan Cashflow' : ''">
                            <span class="material-symbols-outlined text-sm"
                                :class="sidebarCollapsed ? '' : 'mr-3'">account_balance</span>
                            <span x-show="!sidebarCollapsed"
                                class="whitespace-nowrap transition-opacity duration-300">Laporan Cashflow</span>
                        </a>
                    </div>
                </div>
                <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'" href="/branch/settings"
                    :title="sidebarCollapsed ? 'Settings' : ''">
                    <span
                        class="material-symbols-outlined {{ request()->routeIs('admin.settings.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                        :class="sidebarCollapsed ? '' : 'mr-3'">settings</span>
                    <span x-show="!sidebarCollapsed"
                        class="whitespace-nowrap transition-opacity duration-300">Settings</span>
                </a>
            @endif

            {{-- Company Admin Menus - Only show when NOT in branch context --}}
            @if(Auth::check() && Auth::user()->isCompanyAdmin() && Auth::user()->company_id && !session('active_branch_id') && !request()->is('branch*'))
                <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('company.branches.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                    href="{{ route('company.branches.index', Auth::user()->company_id) }}"
                    :title="sidebarCollapsed ? 'Branches' : ''">
                    <span
                        class="material-symbols-outlined {{ request()->routeIs('company.branches.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                        :class="sidebarCollapsed ? '' : 'mr-3'">store</span>
                    <span x-show="!sidebarCollapsed"
                        class="whitespace-nowrap transition-opacity duration-300">Branches</span>
                </a>
                <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('company.users.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                    href="{{ route('company.users.index', Auth::user()->company_id) }}"
                    :title="sidebarCollapsed ? 'Users' : ''">
                    <span
                        class="material-symbols-outlined {{ request()->routeIs('company.users.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                        :class="sidebarCollapsed ? '' : 'mr-3'">people</span>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Users</span>
                </a>
                <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('company.edit') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                    href="{{ route('company.edit', Auth::user()->company_id) }}"
                    :title="sidebarCollapsed ? 'Company Profile' : ''">
                    <span
                        class="material-symbols-outlined {{ request()->routeIs('company.edit') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                        :class="sidebarCollapsed ? '' : 'mr-3'">business</span>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Company
                        Profile</span>
                </a>
            @endif
        </nav>
        <div class="border-t border-border-light dark:border-border-dark" :class="sidebarCollapsed ? 'p-2' : 'p-4'">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="w-full flex items-center"
                    :class="sidebarCollapsed ? 'justify-center' : ''" :title="sidebarCollapsed ? 'User Profile' : ''">
                    <img alt="Manager profile" class="h-9 w-9 rounded-full object-contain"
                        src="https://ui-avatars.com/api/?name={{ Auth::check() ? Auth::user()->name : 'Admin' }}&background=F97316&color=fff" />
                    <div x-show="!sidebarCollapsed"
                        class="ml-3 flex-1 text-left overflow-hidden transition-all duration-300">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">
                            {{ Auth::user()->name ?? 'Admin' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ Auth::user()->getRoleNames()->first() ?? 'User' }}
                        </p>
                    </div>
                    <span x-show="!sidebarCollapsed" class="material-symbols-outlined text-gray-400">expand_more</span>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" x-transition
                    class="absolute bottom-full mb-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
                    :class="sidebarCollapsed ? 'left-0 w-56' : 'left-0 right-0'">
                    @if(Auth::check() && (Auth::user()->isCompanyAdmin() || Auth::user()->isSuperAdmin()) && Auth::user()->company_id)
                        <a href="{{ route('company.edit', Auth::user()->company_id) }}"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <span class="material-symbols-outlined mr-3 text-lg">business</span>
                            Company Profile
                        </a>
                    @endif
                    <form action="/logout" method="POST" class="block">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <span class="material-symbols-outlined mr-3 text-lg">logout</span>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'"
        class="flex-1 p-4 pt-20 md:p-8 md:pt-8 transition-all duration-300">

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
                class="mb-6 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/30 dark:to-orange-900/30 border-l-4 border-red-500 rounded-lg shadow-sm p-4 relative"
                role="alert">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <span class="material-symbols-outlined text-red-500 text-2xl">warning</span>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-300">
                            Langganan Anda Telah Habis
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                            <p>Langganan cabang Anda telah berakhir. Sistem sekarang dalam mode <strong>Read-Only</strong>. Anda tidak dapat menambah, mengubah, atau menghapus data.</p>
                            <p class="mt-1">Silakan perpanjang langganan untuk melanjutkan operasi normal.</p>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ route('subscription.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm hover:shadow-md">
                                <span class="material-symbols-outlined mr-2 text-lg">payments</span>
                                Perpanjang Sekarang
                            </a>
                            <a href="{{ route('subscription.history') }}"
                                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 border border-red-300 dark:border-red-700 text-sm font-medium rounded-lg transition-colors">
                                <span class="material-symbols-outlined mr-2 text-lg">history</span>
                                Lihat Riwayat
                            </a>
                        </div>
                    </div>
                    <button @click="show = false"
                        class="flex-shrink-0 ml-4 p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/50 text-red-500 hover:text-red-700 dark:hover:text-red-300 transition-colors"
                        title="Tutup peringatan">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                {{-- Animated progress bar to draw attention --}}
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-red-200 dark:bg-red-800 rounded-b-lg overflow-hidden">
                    <div class="h-full bg-red-500 animate-pulse w-full"></div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Dark Mode Toggle -->
    <div class="fixed bottom-6 right-6 z-50">
        <button
            class="p-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-full shadow-lg hover:shadow-xl transition-all"
            onclick="document.documentElement.classList.toggle('dark')">
            <span class="material-symbols-outlined dark:hidden">dark_mode</span>
            <span class="material-symbols-outlined hidden dark:block">light_mode</span>
        </button>
    </div>

    <script>
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
            if (event.matches) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        let token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }
    </script>

    @stack('scripts')
</body>

</html>