@extends('layouts.admin')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('admin.users.index') }}" class="hover:text-primary transition-colors">Users</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span>Edit User</span>
    </div>
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit User</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update user information for {{ $user->name }}</p>
    </div>
</div>

<!-- Form Card -->
<div class="max-w-3xl">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $user->name) }}"
                       class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                       placeholder="Enter full name"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email', $user->email) }}"
                       class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                       placeholder="user@example.com"
                       required>
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Password <span class="text-gray-400">(leave blank to keep current)</span>
                </label>
                <input type="password"
                       id="password"
                       name="password"
                       class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                       placeholder="Enter new password">
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Confirm Password <span class="text-gray-400">(leave blank to keep current)</span>
                </label>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                       placeholder="Confirm new password">
            </div>

            <!-- Branch (Read-only) -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Branch <span class="text-gray-400">(read-only)</span>
                </label>
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">store</span>
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ $user->branch->name ?? 'N/A' }}</p>
                            <p class="text-xs text-blue-700 dark:text-blue-300">Branch ID: {{ $user->branch_id }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role -->
            <div class="mb-6">
                <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select id="role"
                        name="role"
                        class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                        required>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" {{ old('role', $user->roles->first()->name ?? '') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-light dark:border-border-dark">
                <a href="{{ route('admin.users.index') }}"
                   class="px-6 py-2.5 rounded-lg border border-border-light dark:border-border-dark text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-medium">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-white transition-colors font-medium">
                    <span class="material-symbols-outlined text-sm mr-1 align-middle">save</span>
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
