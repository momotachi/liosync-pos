<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Company Admin - Lio Sync')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/cycle-logo.png') }}">
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

        /* Alpine.js x-cloak: hide elements until Alpine is ready */
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-background-light dark:bg-background-dark text-gray-900 dark:text-gray-100 transition-colors duration-200 min-h-screen flex"
    x-data="{ sidebarOpen: false, sidebarCollapsed: false }">

    <!-- Mobile Sidebar Overlay -->
    <div x-cloak x-show="sidebarOpen && window.innerWidth < 768"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-20 md:hidden"></div>

    <!-- Mobile Hamburger Menu Button -->
    <button @click="sidebarOpen = !sidebarOpen"
        class="fixed top-4 left-4 z-30 p-2 rounded-lg bg-surface-light dark:bg-surface-dark shadow-lg border border-border-light dark:border-border-dark md:hidden hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <span class="material-symbols-outlined text-gray-600 dark:text-gray-300" x-text="sidebarOpen ? 'close' : 'menu'"></span>
    </button>

    <!-- Sidebar -->
    <aside x-cloak :class="[
            sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
            sidebarCollapsed ? 'md:w-20' : 'md:w-64'
        ]"
        class="w-64 bg-surface-light dark:bg-surface-dark border-r border-border-light dark:border-border-dark shrink-0 flex flex-col h-screen fixed left-0 top-0 z-20 transform transition-all duration-300">
        <!-- Logo & Company -->
        <div class="h-16 flex items-center px-4 border-b border-border-light dark:border-border-dark justify-between">
            <div class="flex items-center" :class="sidebarCollapsed ? 'md:justify-center md:w-full' : ''">
                <img src="{{ asset('images/liosync-logo.png') }}" alt="Lio Sync Logo" class="h-10 w-auto object-contain" :class="sidebarCollapsed ? '' : 'mr-2'">
                <div x-show="!sidebarCollapsed" class="flex-1 hidden md:block">
                    <h1 class="text-lg font-bold text-gray-800 dark:text-white">Company Admin</h1>
                    @if(auth()->user()->company)
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->company->name }}</p>
                    @endif
                </div>
                <div class="flex-1 md:hidden ml-2">
                    <h1 class="text-lg font-bold text-gray-800 dark:text-white">Company Admin</h1>
                </div>
            </div>
            <!-- Desktop Collapse Toggle Button -->
            <button @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden md:flex p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors"
                :class="sidebarCollapsed ? 'mx-auto' : ''"
                :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
                <span class="material-symbols-outlined text-xl" x-text="sidebarCollapsed ? 'chevron_right' : 'chevron_left'"></span>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            {{-- Back to Superadmin (for superadmin viewing company) --}}
            @if(Auth::check() && Auth::user()->isSuperAdmin() && session('company_id'))
                <a href="/superadmin/companies"
                    class="flex items-center text-sm font-medium rounded-md bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-colors mb-2"
                    :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                    :title="sidebarCollapsed ? 'Back to Superadmin' : ''">
                    <span class="material-symbols-outlined" :class="sidebarCollapsed ? '' : 'mr-3'">arrow_back</span>
                    <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Back to
                        Superadmin</span>
                </a>
                <div class="p-2 mb-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-xs text-blue-700 dark:text-blue-400">
                    Viewing: {{ \App\Models\Company::find(session('company_id'))?->name ?? 'Company' }}
                </div>
                <div class="my-2 border-t border-border-light dark:border-border-dark"></div>
            @endif

            <!-- Dashboard -->
            <!-- Dashboard -->
            <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('company.dashboard') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                href="/company/{{ session('company_id') ?? auth()->user()->company_id }}/dashboard"
                :title="sidebarCollapsed ? 'Dashboard' : ''">
                <span class="material-symbols-outlined {{ request()->routeIs('company.dashboard') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                    :class="sidebarCollapsed ? '' : 'mr-3'">dashboard</span>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Dashboard</span>
            </a>

            <!-- Branches -->
            <!-- Branches -->
            <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('company.branches*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                href="/company/{{ session('company_id') ?? auth()->user()->company_id }}/branches"
                :title="sidebarCollapsed ? 'Branches' : ''">
                <span class="material-symbols-outlined {{ request()->routeIs('company.branches*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                    :class="sidebarCollapsed ? '' : 'mr-3'">store</span>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Branches</span>
            </a>

            <!-- Users -->
            <!-- Users -->
            <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('company.users*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                href="/company/{{ session('company_id') ?? auth()->user()->company_id }}/users"
                :title="sidebarCollapsed ? 'Users' : ''">
                <span class="material-symbols-outlined {{ request()->routeIs('company.users*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                    :class="sidebarCollapsed ? '' : 'mr-3'">people</span>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Users</span>
            </a>

            <!-- Subscriptions -->
            <!-- Subscriptions -->
            <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('company.subscriptions*') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                href="/company/{{ session('company_id') ?? auth()->user()->company_id }}/subscriptions"
                :title="sidebarCollapsed ? 'Subscriptions' : ''">
                <span class="material-symbols-outlined {{ request()->routeIs('company.subscriptions*') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                    :class="sidebarCollapsed ? '' : 'mr-3'">card_membership</span>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Subscriptions</span>
                @php $companyId = session('company_id') ?? auth()->user()->company_id; @endphp
                @php $pendingCount = \App\Models\SubscriptionPayment::whereHas('branchSubscription.branch', function($q) use ($companyId) {
                    return $q->where('company_id', $companyId);
                })->where('status', 'pending')->count(); @endphp
                @if($pendingCount > 0)
                    <span x-show="!sidebarCollapsed" class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                    <span x-show="sidebarCollapsed" class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
                @endif
            </a>

            <!-- Company Profile -->
            <!-- Company Profile -->
            <a class="flex items-center text-sm font-medium rounded-md {{ request()->routeIs('company.edit') ? 'bg-primary/10 text-primary dark:text-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }} transition-colors"
                :class="sidebarCollapsed ? 'justify-center py-2.5 px-2' : 'px-3 py-2.5'"
                href="/company/{{ session('company_id') ?? auth()->user()->company_id }}/edit"
                :title="sidebarCollapsed ? 'Company Profile' : ''">
                <span class="material-symbols-outlined {{ request()->routeIs('company.edit') ? 'text-primary' : 'text-gray-400 group-hover:text-primary' }}"
                    :class="sidebarCollapsed ? '' : 'mr-3'">business</span>
                <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity duration-300">Company Profile</span>
            </a>

            @if(auth()->user()->company && auth()->user()->company->type === 'multi')
                <div x-show="!sidebarCollapsed" class="mt-4 px-3 py-2">
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Switch to Branch</p>
                    <div class="space-y-1">
                        @foreach(auth()->user()->company->branches()->active()->get() as $branch)
                            <a href="/switch-branch/{{ $branch->id }}"
                               class="flex items-center px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                <span class="material-symbols-outlined mr-2 text-lg text-gray-400">storefront</span>
                                {{ $branch->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </nav>

        <!-- User Menu -->
        <div class="border-t border-border-light dark:border-border-dark" :class="sidebarCollapsed ? 'p-2' : 'p-4'">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="w-full flex items-center"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                    :title="sidebarCollapsed ? 'User Profile' : ''">
                    <img alt="{{ auth()->user()->name }} profile" class="h-9 w-9 rounded-full object-contain"
                        src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=F97316&color=fff" />
                    <div x-show="!sidebarCollapsed" class="ml-3 flex-1 text-left overflow-hidden transition-all duration-300">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Company Admin</p>
                    </div>
                    <span x-show="!sidebarCollapsed" class="material-symbols-outlined text-gray-400">expand_more</span>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" x-transition 
                    class="absolute bottom-full mb-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
                    :class="sidebarCollapsed ? 'left-0 w-56' : 'left-0 right-0'">
                    <a href="/branch" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <span class="material-symbols-outlined mr-3 text-lg">storefront</span>
                        Branch Panel
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
    <main :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'" 
        class="flex-1 p-4 md:p-8 overflow-x-hidden transition-all duration-300">
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
    </script>

    @stack('scripts')
</body>
</html>
