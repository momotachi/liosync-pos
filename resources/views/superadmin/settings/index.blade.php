@extends('layouts.superadmin')

@section('title', 'Settings')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">System Settings</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage application settings</p>
        </div>
    </header>

    <!-- Group Tabs -->
    <div class="mb-6 border-b border-border-light dark:border-border-dark">
        <nav class="flex space-x-8 -mb-px">
            @foreach($groups as $groupItem)
                <a href="{{ route('superadmin.settings.index', ['group' => $groupItem]) }}"
                   class="py-4 px-1 border-b-2 font-medium text-sm @if($group === $groupItem) border-primary text-primary @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 @endif transition-colors">
                    {{ ucfirst($groupItem) }}
                </a>
            @endforeach
        </nav>
    </div>

    @if($group === 'payment')
        <!-- Payment Banks Table -->
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Payment Bank Accounts</h3>
            </div>

            <!-- Add Bank Form -->
            <form action="{{ route('superadmin.settings.banks.store') }}" method="POST" class="mb-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Name</label>
                        <input type="text" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Number</label>
                        <input type="text" name="account_number" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Name</label>
                        <input type="text" name="account_name" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium">
                            <span class="material-symbols-outlined text-sm align-middle mr-1">add</span>
                            Add Bank
                        </button>
                    </div>
                </div>
            </form>

            <!-- Banks Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-border-light dark:border-border-dark">
                        <tr>
                            <th class="px-4 py-3">Bank Name</th>
                            <th class="px-4 py-3">Account Number</th>
                            <th class="px-4 py-3">Account Name</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light dark:divide-border-dark">
                        @forelse($banks as $bank)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <form action="{{ route('superadmin.settings.banks.update', $bank) }}" method="POST" class="contents">
                                    @csrf
                                    @method('PUT')
                                    <td class="px-4 py-3">
                                        <input type="text" name="name" value="{{ $bank->name }}" required
                                               class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="account_number" value="{{ $bank->account_number }}" required
                                               class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="account_name" value="{{ $bank->account_name }}" required
                                               class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
                                    </td>
                                    <td class="px-4 py-3">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_active" value="1" {{ $bank->is_active ? 'checked' : '' }}
                                                   class="sr-only peer">
                                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/20 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                        </label>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition-colors text-xs font-medium">
                                                Save
                                            </button>
                                            <button type="button"
                                                    onclick="deleteBank('{{ route('superadmin.settings.banks.delete', $bank) }}', '{{ $bank->name }}')"
                                                    class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-xs font-medium">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                    No banks added yet. Add a bank account above.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Settings Form (hide bank settings from here) -->
    @if($group !== 'payment' || $settings->whereNotIn('key', ['bank_name', 'bank_account_number', 'bank_account_name'])->count() > 0)
        <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
            <form action="{{ route('superadmin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="group" value="{{ $group }}">

                <div class="space-y-6">
                    @foreach($settings->whereNotIn('key', ['bank_name', 'bank_account_number', 'bank_account_name']) as $setting)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $setting->label }}
                                </label>
                                @if($setting->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $setting->description }}</p>
                                @endif
                            </div>
                            <div class="md:col-span-2">
                                <input type="hidden" name="settings[{{ $loop->index }}][key]" value="{{ $setting->key }}">

                                @if($setting->type === 'boolean')
                                    <select name="settings[{{ $loop->index }}][value]" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                        <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Enabled</option>
                                        <option value="0" {{ $setting->value != '1' ? 'selected' : '' }}>Disabled</option>
                                    </select>
                                @elseif($setting->type === 'number')
                                    <input type="number"
                                           name="settings[{{ $loop->index }}][value]"
                                           value="{{ $setting->value }}"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                @elseif($setting->type === 'text')
                                    <input type="text"
                                           name="settings[{{ $loop->index }}][value]"
                                           value="{{ $setting->value }}"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                @else
                                    <textarea name="settings[{{ $loop->index }}][value]"
                                              rows="3"
                                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ $setting->value }}</textarea>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 flex items-center justify-end gap-3 pt-6 border-t border-border-light dark:border-border-dark">
                    <a href="{{ route('superadmin.settings.index', ['group' => $group]) }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    function deleteBank(url, bankName) {
        if (confirm(`Are you sure you want to delete "${bankName}"?`)) {
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message || 'Bank deleted successfully!');
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting bank');
            });
        }
    }
</script>
@endpush
