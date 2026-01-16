@extends('layouts.superadmin')

@section('title', 'Create Branch - ' . $company->name)

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
        <a href="/superadmin/companies" class="hover:text-primary transition-colors">Companies</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <a href="{{ route('company.branches.index', $company) }}" class="hover:text-primary transition-colors">{{ $company->name }}</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span>Create Branch</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Branch</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add a new branch to {{ $company->name }}</p>
</div>

<!-- Form Card -->
<div class="max-w-2xl">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <form action="{{ route('company.branches.store', $company) }}" method="POST" class="p-6">
            @csrf

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch Name *</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name') }}"
                               required
                               placeholder="e.g. Jakarta Branch"
                               class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch Code *</label>
                        <input type="text"
                               name="code"
                               id="code"
                               value="{{ old('code') }}"
                               required
                               placeholder="e.g. JKT"
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
                           placeholder="branch@example.com"
                           class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    @error('email')
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
                              placeholder="Full branch address"
                              class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors resize-none">{{ old('address') }}</textarea>
                    @error('address')
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
                        <span class="font-medium">Active</span> - This branch will be visible and operational
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-border-light dark:border-border-dark">
                <a href="{{ route('company.branches.index', $company) }}"
                   class="inline-flex items-center px-4 py-2 border border-border-light dark:border-border-dark text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <span class="material-symbols-outlined text-sm mr-1">cancel</span>
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
                    <span class="material-symbols-outlined text-sm mr-1">add</span>
                    Create Branch
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
