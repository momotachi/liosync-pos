@extends('layouts.superadmin')

@section('title', 'Edit Company')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
        <a href="/superadmin/companies" class="hover:text-primary transition-colors">Companies</a>
        <span class="material-symbols-outlined text-sm">chevron_right</span>
        <span>Edit Company</span>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Company</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update company information for {{ $company->name }}</p>
</div>

<!-- Form Card -->
<div class="max-w-2xl">
    <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark">
        <form action="{{ route('superadmin.companies.update', $company) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company Name *</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $company->name) }}"
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
                               value="{{ old('code', $company->code) }}"
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
                           value="{{ old('email', $company->email) }}"
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
                                   {{ old('type', $company->type) == 'toko' ? 'checked' : '' }}>
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
                                   {{ old('type', $company->type) == 'resto' ? 'checked' : '' }}>
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
                           value="{{ old('phone', $company->phone) }}"
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
                              class="w-full border border-border-light dark:border-border-dark rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors resize-none">{{ old('address', $company->address) }}</textarea>
                    @error('address')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tax_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax ID</label>
                    <input type="text"
                           name="tax_id"
                           id="tax_id"
                           value="{{ old('tax_id', $company->tax_id) }}"
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
                           {{ $company->is_active ? 'checked' : '' }}
                           class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-600 rounded">
                    <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                        <span class="font-medium">Active</span> - This company will be visible and operational
                    </label>
                </div>

                <!-- Company Structure -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Company Structure
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Single Company Option -->
                        <label class="relative flex cursor-pointer">
                            <input type="radio"
                                   name="has_branches"
                                   value="0"
                                   class="peer sr-only"
                                   {{ !$company->has_branches ? 'checked' : '' }}>
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
                                   {{ $company->has_branches ? 'checked' : '' }}>
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

            <div class="mt-6 flex justify-end gap-3 pt-6 border-t border-border-light dark:border-border-dark">
                <a href="{{ route('superadmin.companies.index') }}"
                   class="inline-flex items-center px-4 py-2 border border-border-light dark:border-border-dark text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <span class="material-symbols-outlined text-sm mr-1">cancel</span>
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
                    <span class="material-symbols-outlined text-sm mr-1">save</span>
                    Update Company
                </button>
            </div>
        </form>
    </div>

    <!-- Admin Credentials Table -->
    <div class="mt-6">
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
            <div class="p-4 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-gray-800/50">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">admin_panel_settings</span>
                    Admin Credentials
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Company admin accounts for {{ $company->name }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 border-b border-border-light dark:border-border-dark">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Password</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light dark:divide-border-dark">
                        @forelse($adminUsers as $admin)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-medium">
                                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $admin->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $admin->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">email</span>
                                        <span class="text-sm text-gray-700 dark:text-gray-300 font-mono">{{ $admin->email }}</span>
                                        <button type="button"
                                                data-copy="{!! $admin->email !!}"
                                                onclick="copyEmail(this)"
                                                class="p-1.5 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-md transition-colors"
                                                title="Copy email">
                                            <span class="material-symbols-outlined text-sm">content_copy</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <code class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-xs text-gray-600 dark:text-gray-400 font-mono">
                                            {{ $admin->password_hint ?? 'N/A' }}
                                        </code>
                                        <button type="button"
                                                data-copy="{{ $admin->password_hint ?? 'N/A' }}"
                                                onclick="copyPassword(this)"
                                                class="p-1.5 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-md transition-colors"
                                                title="Copy password">
                                            <span class="material-symbols-outlined text-sm">content_copy</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($admin->branch)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                            <span class="material-symbols-outlined text-xs mr-1">store</span>
                                            {{ $admin->branch->name }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                            <span class="material-symbols-outlined text-xs mr-1">business</span>
                                            All Branches
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($admin->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button onclick="openPasswordModal({{ $admin->id }}, '{{ $admin->name }}')"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-primary hover:text-primary-dark hover:bg-primary/10 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-sm mr-1">lock_reset</span>
                                        Update Password
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined text-4xl text-gray-300 dark:text-gray-600 mb-3">person_off</span>
                                        <p class="text-gray-500 dark:text-gray-400">No admin accounts found for this company.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Update Password Modal -->
<div id="passwordModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">lock_reset</span>
                Update Admin Password
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                For: <span id="modalAdminName" class="font-medium text-gray-700 dark:text-gray-300"></span>
            </p>
        </div>

        <form action="{{ route('superadmin.companies.admins.update-password', [$company, 'admin']) }}" method="POST" id="passwordForm" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="admin_id" id="modalAdminId" value="">

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password *</label>
                <input type="password" name="password" id="password" required minlength="8"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                    placeholder="Enter new password (min. 8 characters)">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                    placeholder="Confirm new password">
            </div>

            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-yellow-600 dark:text-yellow-400 text-sm mt-0.5">info</span>
                    <p class="text-xs text-yellow-700 dark:text-yellow-300">
                        The new password will be stored and displayed in plain text for reference purposes.
                    </p>
                </div>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closePasswordModal()"
                    class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Copy functions with fallback for non-HTTPS
function copyEmail(button) {
    const email = button.getAttribute('data-copy');
    console.log('Copying email:', email);

    // Try modern clipboard API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(email).then(() => {
            showToast('Email copied!');
            console.log('Email copied successfully');
        }).catch(err => {
            console.error('Clipboard API failed:', err);
            // Fallback to execCommand
            fallbackCopy(email);
        });
    } else {
        // Fallback for older browsers or non-HTTPS
        fallbackCopy(email);
    }
}

function copyPassword(button) {
    const password = button.getAttribute('data-copy');
    console.log('Copying password:', password);

    if (password === 'N/A') {
        alert('No password to copy');
        return;
    }

    // Try modern clipboard API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(password).then(() => {
            showToast('Password copied!');
            console.log('Password copied successfully');
        }).catch(err => {
            console.error('Clipboard API failed:', err);
            // Fallback to execCommand
            fallbackCopy(password);
        });
    } else {
        // Fallback for older browsers or non-HTTPS
        fallbackCopy(password);
    }
}

// Fallback copy function using execCommand
function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.top = '0';
    textarea.style.left = '0';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();

    try {
        const successful = document.execCommand('copy');
        document.body.removeChild(textarea);

        if (successful) {
            showToast('Copied!');
            console.log('Copied successfully using fallback');
        } else {
            alert('Failed to copy. Please copy manually: ' + text);
        }
    } catch (err) {
        document.body.removeChild(textarea);
        console.error('Fallback copy failed:', err);
        alert('Failed to copy. Please copy manually: ' + text);
    }
}

function showToast(message) {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-4 py-2 rounded-lg shadow-lg z-50 text-sm';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}

function openPasswordModal(adminId, adminName) {
    const modal = document.getElementById('passwordModal');
    const form = document.getElementById('passwordForm');
    const adminIdInput = document.getElementById('modalAdminId');
    const adminNameDisplay = document.getElementById('modalAdminName');

    // Update form action with the correct admin ID
    form.action = form.action.replace('/admin', `/admins/${adminId}/password`);
    adminIdInput.value = adminId;
    adminNameDisplay.textContent = adminName;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePasswordModal() {
    const modal = document.getElementById('passwordModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('passwordForm').reset();
}
</script>
@endsection
