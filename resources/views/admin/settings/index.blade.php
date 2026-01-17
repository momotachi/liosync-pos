@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
{{-- Branch Context Indicator --}}
@if(session('active_branch_id'))
    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg flex items-center gap-3">
        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-2xl">store</span>
        <div>
            <p class="text-sm font-medium text-blue-900 dark:text-blue-300">Managing Settings for Branch</p>
            <p class="text-xs text-blue-700 dark:text-blue-400">{{ \App\Models\Branch::find(session('active_branch_id'))?->name ?? 'Unknown Branch' }}</p>
        </div>
        <a href="/company/{{ session('company_id') }}/branches"
           class="ml-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Back to Branches
        </a>
    </div>
@elseif(auth()->user()->branch)
    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-center gap-3">
        <span class="material-symbols-outlined text-green-600 dark:text-green-400 text-2xl">store</span>
        <div>
            <p class="text-sm font-medium text-green-900 dark:text-green-300">Your Branch Settings</p>
            <p class="text-xs text-green-700 dark:text-green-400">{{ auth()->user()->branch->name }}</p>
        </div>
    </div>
@endif

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
    <div class="flex items-center gap-3">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">System Settings</h2>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="confirmReset()" class="flex items-center justify-center gap-2 px-4 py-2.5 border border-red-300 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm font-medium rounded-lg transition-colors">
            <span class="material-symbols-outlined text-lg">restart_alt</span>
            Reset to Defaults
        </button>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf
    @method('PUT')

    <div class="space-y-6">
        <!-- Change Password Section -->
        <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
            <div class="px-6 py-4 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">lock</span>
                    Change Password
                </h3>
            </div>

            <div class="p-6">
                <form method="POST" action="{{ route('admin.settings.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Password</label>
                            <input type="password" name="current_password" id="current_password" required
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('current_password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                            <input type="password" name="new_password" id="new_password" required
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('new_password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('new_password_confirmation')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if(session('password_success'))
                        <div class="p-4 mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <p class="text-sm text-green-800 dark:text-green-200 flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">check_circle</span>
                                {{ session('password_success') }}
                            </p>
                        </div>
                    @endif

                    @if(session('password_error'))
                        <div class="p-4 mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-sm text-red-800 dark:text-red-200 flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">error</span>
                                {{ session('password_error') }}
                            </p>
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-lg shadow-primary-200 dark:shadow-none transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined">save</span>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @foreach($settings as $group => $settingsCollection)
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
                <div class="px-6 py-4 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        @if($group === 'general')
                            <span class="material-symbols-outlined text-primary">settings</span>
                        @elseif($group === 'pos')
                            <span class="material-symbols-outlined text-primary">point_of_sale</span>
                        @elseif($group === 'receipt')
                            <span class="material-symbols-outlined text-primary">receipt</span>
                        @elseif($group === 'tax')
                            <span class="material-symbols-outlined text-primary">percent</span>
                        @elseif($group === 'inventory')
                            <span class="material-symbols-outlined text-primary">inventory</span>
                        @else
                            <span class="material-symbols-outlined text-primary">tune</span>
                        @endif
                        {{ ucfirst($group) }}
                    </h3>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($settingsCollection as $setting)
                            <div class="@if(in_array($setting->key, ['store_name', 'store_address', 'receipt_header', 'receipt_footer', 'default_customer_name', 'tax_name'])) md:col-span-2 @endif">
                                @if($setting->type === 'boolean')
                                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $setting->label }}</label>
                                            @if($setting->description)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $setting->description }}</p>
                                            @endif
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="{{ $setting->key }}" value="1" {{ $setting->value ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                                        </label>
                                    </div>
                                @elseif($setting->type === 'number')
                                    <div>
                                        <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ $setting->label }}
                                        </label>
                                        @if($setting->description)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $setting->description }}</p>
                                        @endif
                                        <input type="number" name="{{ $setting->key }}" id="{{ $setting->key }}"
                                            value="{{ old($setting->key, $setting->value) }}"
                                            step="any"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                @elseif($setting->type === 'text' && in_array($setting->key, ['store_address', 'receipt_header', 'receipt_footer']))
                                    <div>
                                        <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ $setting->label }}
                                        </label>
                                        @if($setting->description)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $setting->description }}</p>
                                        @endif
                                        <textarea name="{{ $setting->key }}" id="{{ $setting->key }}" rows="2"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old($setting->key, $setting->value) }}</textarea>
                                    </div>
                                @else
                                    <div>
                                        <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            {{ $setting->label }}
                                        </label>
                                        @if($setting->description)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $setting->description }}</p>
                                        @endif
                                        <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}"
                                            value="{{ old($setting->key, $setting->value) }}"
                                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Actions -->
    <div class="mt-6 flex items-center justify-end gap-3">
        <a href="/branch"
            class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            Cancel
        </a>
        <button type="submit"
            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-lg shadow-emerald-200 dark:shadow-none transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined">save</span>
            Save Settings
        </button>
    </div>
</form>

<!-- Reset Confirmation Modal -->
<div id="resetModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-red-500">warning</span>
                Confirm Reset
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-600 dark:text-gray-400">Are you sure you want to reset all settings to their default values? This action cannot be undone.</p>
        </div>
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex gap-3">
            <button onclick="closeResetModal()"
                class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Cancel
            </button>
            <a href="{{ route('admin.settings.reset') }}"
                class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-lg shadow-red-200 dark:shadow-none transition-colors text-center">
                Reset Settings
            </a>
        </div>
    </div>
</div>

<script>
    function confirmReset() {
        const modal = document.getElementById('resetModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeResetModal() {
        const modal = document.getElementById('resetModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal on outside click
    document.getElementById('resetModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeResetModal();
        }
    });
</script>
@endsection
