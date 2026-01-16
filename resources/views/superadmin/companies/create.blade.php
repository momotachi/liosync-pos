@extends('layouts.superadmin')

@section('title', 'Create Company')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
        <a href="/superadmin/companies" class="hover:text-primary transition-colors">Companies</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span>Create Company</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Company</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add a new company to the system</p>
</div>

<!-- Form Card -->
<div class="max-w-2xl">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <form action="{{ route('superadmin.companies.store') }}" method="POST" class="p-6">
            @csrf

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name *</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name') }}"
                               required
                               placeholder="e.g. Juice Store Inc."
                               class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Code *</label>
                        <input type="text"
                               name="code"
                               id="code"
                               value="{{ old('code') }}"
                               required
                               placeholder="e.g. JUICE001"
                               class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        @error('code')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           placeholder="company@example.com"
                           class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Company Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Company Type *
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Toko (Shop) -->
                        <label class="relative flex cursor-pointer">
                            <input type="radio"
                                   name="type"
                                   value="toko"
                                   class="peer sr-only"
                                   {{ old('type', 'toko') == 'toko' ? 'checked' : '' }}>
                            <div class="w-full p-4 border-2 border-border-light dark:border-border-dark rounded-lg transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 dark:hover:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">shopping_bag</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Toko (Shop)</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Direct payment</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                                    Standard retail flow - pay immediately
                                </div>
                            </div>
                        </label>

                        <!-- Resto (Restaurant) -->
                        <label class="relative flex cursor-pointer">
                            <input type="radio"
                                   name="type"
                                   value="resto"
                                   class="peer sr-only"
                                   {{ old('type') == 'resto' ? 'checked' : '' }}>
                            <div class="w-full p-4 border-2 border-border-light dark:border-border-dark rounded-lg transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 dark:hover:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-orange-600 dark:text-orange-400">restaurant</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Resto (Restaurant)</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Order then pay</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                                    Supports order-first-pay-later flow
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('type')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                    <input type="text"
                           name="phone"
                           id="phone"
                           value="{{ old('phone') }}"
                           placeholder="+62 xxx xxxx xxxx"
                           class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    @error('phone')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <textarea name="address"
                              id="address"
                              rows="3"
                              placeholder="Full company address"
                              class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors resize-none">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tax_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax ID</label>
                    <input type="text"
                           name="tax_id"
                           id="tax_id"
                           value="{{ old('tax_id') }}"
                           placeholder="e.g. 01.234.567.8-901.000"
                           class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    @error('tax_id')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-border-light dark:border-border-dark">
                    <input type="checkbox"
                           name="is_active"
                           id="is_active"
                           value="1"
                           checked
                           class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-600 rounded">
                    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <span class="font-medium">Active</span> - This company will be visible and operational
                    </label>
                </div>

                <!-- Company Structure -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Company Structure *
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Single Company Option -->
                        <label class="relative flex cursor-pointer">
                            <input type="radio"
                                   name="has_branches"
                                   value="0"
                                   class="peer sr-only"
                                   {{ old('has_branches', '0') == '0' ? 'checked' : '' }}>
                            <div class="w-full p-4 border-2 border-border-light dark:border-border-dark rounded-lg transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 dark:hover:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-primary">store</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Single Company</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">No branches, one location</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                                    Direct access to admin panel without branch management
                                </div>
                            </div>
                        </label>

                        <!-- Multi-Branch Option -->
                        <label class="relative flex cursor-pointer">
                            <input type="radio"
                                   name="has_branches"
                                   value="1"
                                   class="peer sr-only"
                                   {{ old('has_branches', '0') == '1' ? 'checked' : '' }}>
                            <div class="w-full p-4 border-2 border-border-light dark:border-border-dark rounded-lg transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300 dark:hover:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-lg bg-secondary/10 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-secondary">account_tree</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Multi-Branch</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Has multiple branches</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                                    Can manage multiple branches with branch-specific admins
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('has_branches')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Company Admin Section -->
            <div class="mt-8 pt-6 border-t border-border-light dark:border-border-dark">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">person_admin</span>
                        Company Admin Account
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Create an admin account for this company</p>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="admin_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Admin Name *</label>
                            <input type="text"
                                   name="admin_name"
                                   id="admin_name"
                                   value="{{ old('admin_name') }}"
                                   required
                                   placeholder="e.g. John Doe"
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            @error('admin_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="admin_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Admin Email *</label>
                            <input type="email"
                                   name="admin_email"
                                   id="admin_email"
                                   value="{{ old('admin_email') }}"
                                   required
                                   placeholder="admin@company.com"
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            @error('admin_email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Admin Password *</label>
                        <div class="relative">
                            <input type="password"
                                   name="admin_password"
                                   id="admin_password"
                                   value="{{ old('admin_password') }}"
                                   required
                                   placeholder="Enter password"
                                   class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors pr-10">
                            <button type="button"
                                    x-data="{ show: false }"
                                    @click="show = !show; $el.previousElementSibling.type = show ? 'text' : 'password'"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                        @error('admin_password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Minimum 8 characters</p>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-primary/5 dark:bg-primary/10 rounded-lg border border-primary/20">
                        <span class="material-symbols-outlined text-primary mt-0.5">info</span>
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <p class="font-medium">Company Admin Permissions</p>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">The company admin will have full access to manage this company's data, branches, and users. They can also create branch admins and stock admins.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-border-light dark:border-border-dark">
                <a href="{{ route('superadmin.companies.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-border-light dark:border-border-dark text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <span class="material-symbols-outlined text-sm mr-1">cancel</span>
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
                    <span class="material-symbols-outlined text-sm mr-1">add</span>
                    Create Company
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
