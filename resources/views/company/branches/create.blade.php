@extends('layouts.company')

@section('title', 'Create Branch - ' . $company->name)

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('company.branches.index', $company) }}" class="hover:text-primary transition-colors">Branches</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span>Create Branch</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Branch</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add a new branch to {{ $company->name }}</p>
</div>

<!-- Form Card -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
    <form method="POST" action="{{ route('company.branches.store', $company) }}">
        @csrf

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Branch Name <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   id="name"
                   name="name"
                   value="{{ old('name') }}"
                   class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                   placeholder="e.g., Main Branch, Downtown Branch"
                   required>
            @error('name')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Address -->
        <div class="mb-4">
            <label for="address" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Address
            </label>
            <textarea id="address"
                      name="address"
                      rows="3"
                      class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                      placeholder="Full branch address">{{ old('address') }}</textarea>
            @error('address')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Phone -->
        <div class="mb-4">
            <label for="phone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Phone Number
            </label>
            <input type="text"
                   id="phone"
                   name="phone"
                   value="{{ old('phone') }}"
                   class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                   placeholder="e.g., 021-12345678">
            @error('phone')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Location (Latitude/Longitude) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="latitude" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Latitude
                </label>
                <input type="number"
                       id="latitude"
                       name="latitude"
                       step="any"
                       value="{{ old('latitude') }}"
                       class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                       placeholder="e.g., -6.2088">
            </div>
            <div>
                <label for="longitude" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Longitude
                </label>
                <input type="number"
                       id="longitude"
                       name="longitude"
                       step="any"
                       value="{{ old('longitude') }}"
                       class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                       placeholder="e.g., 106.8456">
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-light dark:border-border-dark">
            <a href="{{ route('company.branches.index', $company) }}"
               class="px-6 py-2.5 rounded-lg border border-border-light dark:border-border-dark text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-medium">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-white transition-colors font-medium">
                <span class="material-symbols-outlined text-sm mr-1 align-middle">save</span>
                Create Branch
            </button>
        </div>
    </form>
</div>
@endsection
