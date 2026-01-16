@extends('layouts.company')

@section('title', 'Add User - ' . $company->name)

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
        <a href="{{ route('company.users.index', $company) }}" class="hover:text-primary transition-colors">Company Users</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span>Add User</span>
    </div>
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Add User</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create a new user for {{ $company->name }}</p>
    </div>
</div>

<!-- Form Card -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
    <form method="POST" action="{{ route('company.users.store', $company) }}">
        @csrf

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Full Name <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   id="name"
                   name="name"
                   value="{{ old('name') }}"
                   class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
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
                   value="{{ old('email') }}"
                   class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                   placeholder="user@example.com"
                   required>
            @error('email')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Password <span class="text-red-500">*</span>
            </label>
            <input type="password"
                   id="password"
                   name="password"
                   class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                   placeholder="Enter password (min 6 characters)"
                   required>
            @error('password')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Confirmation -->
        <div class="mb-4">
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Confirm Password <span class="text-red-500">*</span>
            </label>
            <input type="password"
                   id="password_confirmation"
                   name="password_confirmation"
                   class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                   placeholder="Confirm password"
                   required>
        </div>

        <!-- Password Hint -->
        <div class="mb-4">
            <label for="password_hint" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Password Hint <span class="text-gray-400">(optional)</span>
            </label>
            <input type="text"
                   id="password_hint"
                   name="password_hint"
                   value="{{ old('password_hint') }}"
                   class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                   placeholder="e.g., My favorite color">
            <p class="text-xs text-gray-500 mt-1">A hint to help the user remember their password</p>
            @error('password_hint')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Role -->
        <div class="mb-4">
            <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Role <span class="text-red-500">*</span>
            </label>
            <select id="role"
                    name="role"
                    class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                    required
                    onchange="toggleBranchField()">
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @error('role')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Branch (only for Branch Admin) -->
        <div class="mb-6" id="branchField" style="display: {{ old('role') === 'Branch Admin' ? 'block' : 'none' }};">
            <label for="branch_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Branch <span class="text-red-500">*</span>
            </label>
            <select id="branch_id"
                    name="branch_id"
                    class="w-full px-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                <option value="">Select Branch</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-500 mt-1">Required for Branch Admin role</p>
            @error('branch_id')
            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Info -->
        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 mb-6">
            <div class="flex items-start gap-2">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-sm mt-0.5">info</span>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    <strong>Company Admin</strong> has access to all branches within {{ $company->name }}.
                    <strong>Branch Admin</strong> has access only to the assigned branch.
                </p>
            </div>
        </div>

        <script>
        function toggleBranchField() {
            const roleSelect = document.getElementById('role');
            const branchField = document.getElementById('branchField');

            if (roleSelect.value === 'Branch Admin') {
                branchField.style.display = 'block';
            } else {
                branchField.style.display = 'none';
            }
        }
        </script>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-light dark:border-border-dark">
            <a href="{{ route('company.users.index', $company) }}"
               class="px-6 py-2.5 rounded-lg border border-border-light dark:border-border-dark text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-medium">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary-dark text-white transition-colors font-medium">
                <span class="material-symbols-outlined text-sm mr-1 align-middle">add</span>
                Create User
            </button>
        </div>
    </form>
</div>
@endsection
