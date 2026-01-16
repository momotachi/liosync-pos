@extends('layouts.superadmin')

@section('title', isset($plan) ? 'Edit Subscription Plan' : 'Create Subscription Plan')

@section('content')
    <header class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                @isset($plan) Edit Subscription Plan @else Create Subscription Plan @endisset
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                @isset($plan) Update plan details and features @else Define a new subscription tier @endisset
            </p>
        </div>
    </header>

    <div class="max-w-2xl">
        <form action="{{ isset($plan) ? route('superadmin.subscription-plans.update', $plan) : route('superadmin.subscription-plans.store') }}"
              method="POST"
              class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
            @csrf
            @isset($plan) @method('PUT') @endisset

            <!-- Basic Information -->
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Plan Name -->
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Plan Name</label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $plan->name ?? '') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary"
                                   placeholder="e.g., Starter, Professional, Enterprise">
                        </div>

                        <!-- Slug -->
                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Slug</label>
                            <input type="text"
                                   id="slug"
                                   name="slug"
                                   value="{{ old('slug', $plan->slug ?? '') }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary"
                                   placeholder="e.g., starter, professional">
                        </div>

                        <!-- Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price (Rp)</label>
                            <input type="number"
                                   id="price"
                                   name="price"
                                   value="{{ old('price', $plan->price ?? '') }}"
                                   required
                                   min="0"
                                   step="1000"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary"
                                   placeholder="500000">
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary"
                                      placeholder="Brief description of the plan">{{ old('description', $plan->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Limits -->
                <div class="border-t border-border-light dark:border-border-dark pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Limits</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Max Branches -->
                        <div>
                            <label for="max_branches" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Branches</label>
                            <div class="flex items-center gap-3">
                                <input type="number"
                                       id="max_branches"
                                       name="max_branches"
                                       value="{{ old('max_branches', $plan->max_branches ?? 1) }}"
                                       min="1"
                                       class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox"
                                           name="unlimited_branches"
                                           value="1"
                                           @if(!$plan || $plan->max_branches === null) checked @endif
                                           class="w-4 h-4 text-primary rounded focus:ring-primary">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Unlimited</span>
                                </label>
                            </div>
                        </div>

                        <!-- Max Users -->
                        <div>
                            <label for="max_users" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Users per Branch</label>
                            <div class="flex items-center gap-3">
                                <input type="number"
                                       id="max_users"
                                       name="max_users"
                                       value="{{ old('max_users', $plan->max_users ?? 5) }}"
                                       min="1"
                                       class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox"
                                           name="unlimited_users"
                                           value="1"
                                           @if(!$plan || $plan->max_users === null) checked @endif
                                           class="w-4 h-4 text-primary rounded focus:ring-primary">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Unlimited</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="border-t border-border-light dark:border-border-dark pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Features</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <input type="checkbox"
                                   name="features[pos]"
                                   value="1"
                                   @if(isset($plan) && isset($plan->features['pos'])) checked @endif
                                   @if(!isset($plan)) checked @endif
                                   class="w-4 h-4 text-primary rounded focus:ring-primary">
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">POS System</span>
                        </label>

                        <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <input type="checkbox"
                                   name="features[basic_reports]"
                                   value="1"
                                   @if(isset($plan) && isset($plan->features['basic_reports'])) checked @endif
                                   @if(!isset($plan)) checked @endif
                                   class="w-4 h-4 text-primary rounded focus:ring-primary">
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Basic Reports</span>
                        </label>

                        <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <input type="checkbox"
                                   name="features[inventory_management]"
                                   value="1"
                                   @if(isset($plan) && isset($plan->features['inventory_management'])) checked @endif
                                   @if(!isset($plan)) checked @endif
                                   class="w-4 h-4 text-primary rounded focus:ring-primary">
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Inventory Management</span>
                        </label>

                        <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <input type="checkbox"
                                   name="features[multi_branch]"
                                   value="1"
                                   @if(isset($plan) && isset($plan->features['multi_branch'])) checked @endif
                                   class="w-4 h-4 text-primary rounded focus:ring-primary">
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Multi-Branch</span>
                        </label>

                        <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <input type="checkbox"
                                   name="features[advanced_reports]"
                                   value="1"
                                   @if(isset($plan) && isset($plan->features['advanced_reports'])) checked @endif
                                   class="w-4 h-4 text-primary rounded focus:ring-primary">
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">Advanced Reports</span>
                        </label>

                        <label class="flex items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <input type="checkbox"
                                   name="features[api_access]"
                                   value="1"
                                   @if(isset($plan) && isset($plan->features['api_access'])) checked @endif
                                   class="w-4 h-4 text-primary rounded focus:ring-primary">
                            <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">API Access</span>
                        </label>
                    </div>
                </div>

                <!-- Settings -->
                <div class="border-t border-border-light dark:border-border-dark pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Settings</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Sort Order -->
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort Order</label>
                            <input type="number"
                                   id="sort_order"
                                   name="sort_order"
                                   value="{{ old('sort_order', $plan->sort_order ?? 0) }}"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                       name="is_active"
                                       value="1"
                                       @if(!isset($plan) || $plan->is_active) checked @endif
                                       class="w-4 h-4 text-primary rounded focus:ring-primary">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-border-light dark:border-border-dark">
                <a href="{{ route('superadmin.subscription-plans.index') }}"
                   class="px-6 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                    @isset($plan) Update Plan @else Create Plan @endisset
                </button>
            </div>
        </form>
    </div>

    <script>
        // Handle unlimited branches/users checkboxes
        document.querySelectorAll('input[name="unlimited_branches"], input[name="unlimited_users"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const input = this.closest('.flex').querySelector('input[type="number"]');
                if (this.checked) {
                    input.value = '';
                    input.disabled = true;
                    input.classList.add('bg-gray-100', 'dark:bg-gray-700');
                } else {
                    input.disabled = false;
                    input.classList.remove('bg-gray-100', 'dark:bg-gray-700');
                    input.value = input.getAttribute('min') || 1;
                }
            });

            // Trigger change on load
            checkbox.dispatchEvent(new Event('change'));
        });
    </script>
@endsection
