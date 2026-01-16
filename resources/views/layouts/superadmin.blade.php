<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', auth()->user()->roles->first()->name ?? 'Admin' . ' - Cycle')</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#F97316",
                        "primary-dark": "#EA580C",
                        secondary: "#10b981",
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

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100 transition-colors duration-200 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-surface-light dark:bg-surface-dark border-r border-border-light dark:border-border-dark shrink-0 hidden md:flex flex-col h-screen fixed left-0 top-0 z-10">
        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-border-light dark:border-border-dark">
            <img src="{{ asset('images/cycle-logo.png') }}" alt="Cycle Logo" class="h-8 w-8 mr-2">
            <div>
                <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ auth()->user()->roles->first()->name ?? 'Admin' }}</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">Cycle System</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('superadmin.companies*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                href="/superadmin/companies">
                <span class="material-symbols-outlined mr-3 {{ request()->routeIs('superadmin.companies*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">business</span>
                Companies
            </a>

            <!-- Subscription Plans Management -->
            <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('subscription-plans.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                href="/superadmin/subscription-plans">
                <span class="material-symbols-outlined mr-3 {{ request()->routeIs('subscription-plans.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">workspace_premium</span>
                Subscription Plans
            </a>

            <!-- All Subscriptions Management -->
            <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('subscriptions.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                href="/superadmin/subscriptions">
                <span class="material-symbols-outlined mr-3 {{ request()->routeIs('subscriptions.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">subscriptions</span>
                All Subscriptions
            </a>

            @if(session('company_id'))
                <div class="mt-4 px-3 py-2">
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Current Context</p>
                    <div class="flex items-center justify-between px-3 py-2 bg-primary/5 rounded-lg border border-primary/20">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-primary text-sm mr-2">domain</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ \App\Models\Company::find(session('company_id'))?->name ?? 'Unknown' }}
                            </span>
                        </div>
                        <a href="/superadmin/switch-clear" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </a>
                    </div>
                </div>

                <!-- Company Menu Items -->
                <div class="mt-2 space-y-1">
                    <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('company.branches*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                        href="/company/{{ session('company_id') }}/branches">
                        <span class="material-symbols-outlined mr-3 {{ request()->routeIs('company.branches*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">store</span>
                        Branches
                    </a>
                    <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('company.users*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                        href="/company/{{ session('company_id') }}/users">
                        <span class="material-symbols-outlined mr-3 {{ request()->routeIs('company.users*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">people</span>
                        Users
                    </a>
                    <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('subscriptions.*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                        href="/company/{{ session('company_id') }}/subscriptions">
                        <span class="material-symbols-outlined mr-3 {{ request()->routeIs('subscriptions.*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">card_membership</span>
                        Subscriptions
                    </a>
                    <a class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md {{ request()->routeIs('company.edit') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                        href="/company/{{ session('company_id') }}/edit">
                        <span class="material-symbols-outlined mr-3 {{ request()->routeIs('company.edit') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}">business</span>
                        Company Profile
                    </a>
                </div>
            @endif
        </nav>

        <!-- User Menu -->
        <div class="border-t border-border-light dark:border-border-dark p-4">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="w-full flex items-center">
                    <img alt="{{ auth()->user()->name }} profile" class="h-9 w-9 rounded-full object-contain"
                        src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=DC2626&color=fff" />
                    <div class="ml-3 flex-1 text-left">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->roles->first()->name ?? 'Admin' }}</p>
                    </div>
                    <span class="material-symbols-outlined text-gray-400">expand_more</span>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" x-transition class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1">
                    <a href="/branch" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined mr-3 text-lg">dashboard</span>
                        Admin Panel
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
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center gap-3">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>
                <p class="text-sm text-green-800 dark:text-green-300">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-center gap-3">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
                <p class="text-sm text-red-800 dark:text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-center gap-3 mb-2">
                    <span class="material-symbols-outlined text-red-600 dark:text-red-400">error</span>
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">Please fix the following errors:</p>
                </div>
                <ul class="ml-9 list-disc text-sm text-red-700 dark:text-red-400">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Dark Mode Toggle -->
    <div class="fixed bottom-6 right-6 z-50">
        <button class="p-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-full shadow-lg hover:shadow-xl transition-all"
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

        function switchCompany(companyId) {
            if (companyId) {
                window.location.href = `/superadmin/switch-company/${companyId}`;
            } else {
                window.location.href = '/superadmin/switch-clear';
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
