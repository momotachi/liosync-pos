<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cycle')</title>
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
    </style>

    @stack('styles')
</head>

<body
    class="bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100 transition-colors duration-200 min-h-screen flex">

    <!-- Sidebar -->
    <aside
        class="w-64 bg-surface-light dark:bg-surface-dark border-r border-border-light dark:border-border-dark shrink-0 hidden md:flex flex-col h-screen fixed left-0 top-0 z-10">
        <div class="h-16 flex items-center px-6 border-b border-border-light dark:border-border-dark">
            <img src="{{ asset('images/cycle-logo.png') }}" alt="Cycle Logo" class="h-8 w-8 mr-2">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight">Cycle</h1>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            {{-- Back to Superadmin/Branches (for superadmin and company admin users) --}}
            @if(Auth::check())
                @if(Auth::user()->isSuperAdmin())
                    <a href="/superadmin/companies"
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors mb-2">
                        <span class="material-symbols-outlined mr-3">arrow_back</span>
                        Back to Superadmin
                    </a>
                    <div class="my-2 border-t border-border-light dark:border-border-dark"></div>
                @elseif(Auth::user()->isCompanyAdmin() && Auth::user()->company)
                    <a href="{{ route('company.branches.index', Auth::user()->company) }}"
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors mb-2">
                        <span class="material-symbols-outlined mr-3">store</span>
                        Back to Branches
                    </a>
                    <div class="my-2 border-t border-border-light dark:border-border-dark"></div>
                @endif
            @endif

            <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('filament.admin.pages.dashboard') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                href="/branch">
                <span
                    class="material-symbols-outlined mr-3 {{ request()->routeIs('filament.admin.pages.dashboard') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">dashboard</span>
                Dashboard
            </a>
            <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 group transition-colors"
                href="/pos">
                <span
                    class="material-symbols-outlined mr-3 text-gray-400 group-hover:text-primary dark:group-hover:text-primary">point_of_sale</span>
                Cashier
            </a>
            <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 group transition-colors"
                href="/purchase">
                <span
                    class="material-symbols-outlined mr-3 text-gray-400 group-hover:text-primary dark:group-hover:text-primary">shopping_cart</span>
                Purchase Order
            </a>
            <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('admin.items.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                href="/branch/items">
                <span
                    class="material-symbols-outlined mr-3 {{ request()->routeIs('admin.items.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">apps</span>
                Items
            </a>
            <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('admin.categories.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                href="/branch/categories">
                <span class="material-symbols-outlined mr-3 {{ request()->routeIs('admin.categories.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">category</span>
                Categories
            </a>

            {{-- Subscription Menu --}}
            @if(Auth::check() && Auth::user()->branch && !Auth::user()->isSuperAdmin() && !Auth::user()->isCompanyAdmin())
                <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('subscription.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                    href="{{ route('subscription.index') }}">
                    <span class="material-symbols-outlined mr-3 {{ request()->routeIs('subscription.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">subscriptions</span>
                    Subscription
                    @if(Auth::user()->branch && !Auth::user()->branch->hasActiveSubscription())
                        <span class="ml-auto px-2 py-0.5 bg-red-100 text-red-600 rounded-full text-xs font-medium">Expired</span>
                    @endif
                </a>
            @endif

            <!-- Reports Menu with Submenu -->
            <div x-data="{ open: @js(request()->routeIs('admin.reports.*')) }">
                <button @click="open = !open" class="w-full flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors group">
                    <span class="material-symbols-outlined mr-3 {{ request()->routeIs('admin.reports.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">analytics</span>
                    <span class="flex-1 text-left">Reports</span>
                    <span class="material-symbols-outlined text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>
                <div x-show="open" x-transition class="mt-1 ml-6 space-y-1">
                    <a class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.index') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                        href="/branch/reports">
                        <span class="material-symbols-outlined mr-3 text-sm">show_chart</span>
                        Ringkasan Penjualan
                    </a>
                    <a class="flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.reports.purchases') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                        href="/branch/reports/purchases">
                        <span class="material-symbols-outlined mr-3 text-sm">shopping_cart</span>
                        Laporan Pembelian
                    </a>
                </div>
            </div>
            <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                href="/branch/settings">
                <span
                    class="material-symbols-outlined mr-3 {{ request()->routeIs('admin.settings.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">settings</span>
                Settings
            </a>
        </nav>
        <div class="border-t border-border-light dark:border-border-dark p-4">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="w-full flex items-center">
                    <img alt="Manager profile" class="h-9 w-9 rounded-full object-contain"
                        src="https://ui-avatars.com/api/?name={{ Auth::check() ? Auth::user()->name : 'Admin' }}&background=F97316&color=fff" />
                    <div class="ml-3 flex-1 text-left">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ Auth::user()->name ?? 'Admin' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->getRoleNames()->first() ?? 'User' }}</p>
                    </div>
                    <span class="material-symbols-outlined text-gray-400">expand_more</span>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" x-transition class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1">
                    @if(Auth::check() && (Auth::user()->isCompanyAdmin() || Auth::user()->isSuperAdmin()) && Auth::user()->company)
                        <a href="{{ route('company.edit', Auth::user()->company) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <span class="material-symbols-outlined mr-3 text-lg">business</span>
                            Company Profile
                        </a>
                    @endif
                    <a href="/branch/settings" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined mr-3 text-lg">settings</span>
                        Settings
                    </a>
                    <form action="/logout" method="POST" class="block">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <span class="material-symbols-outlined mr-3 text-lg">logout</span>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 md:ml-64 p-4 md:p-8 overflow-x-hidden">
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