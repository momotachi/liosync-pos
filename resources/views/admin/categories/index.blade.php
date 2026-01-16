@extends('layouts.admin')

@section('title', 'Manage Categories')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manage Categories</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage item categories for your branch</p>
        </div>
        <button onclick="openCreateModal()"
           class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
            <span class="material-symbols-outlined text-sm mr-1">add</span>
            Add Category
        </button>
    </div>
</div>

<!-- Categories Table -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
    @if($categories->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-light dark:divide-border-dark">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-card-light dark:bg-card-dark divide-y divide-border-light dark:border-border-dark">
                    @foreach($categories as $category)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-3">
                                        <span class="material-symbols-outlined text-purple-600 dark:text-purple-400">category</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $category->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $category->type === 'product' ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' }}">
                                    {{ $category->type === 'product' ? 'Product' : 'Material' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                    {{ $categoryCounts[$category->id] ?? 0 }} items
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openEditModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->type }}')"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-medium transition-colors">
                                        <span class="material-symbols-outlined text-sm mr-1">edit</span>
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg text-xs font-medium transition-colors">
                                            <span class="material-symbols-outlined text-sm mr-1">delete</span>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                <span class="material-symbols-outlined text-3xl text-gray-400 dark:text-gray-600">category</span>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No categories yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Add categories to organize your items</p>
            <button onclick="openCreateModal()"
               class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg text-sm font-medium transition-colors">
                <span class="material-symbols-outlined text-sm mr-1">add</span>
                Add Category
            </button>
        </div>
    @endif
</div>

<!-- Create/Edit Modal -->
<div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 dark:text-white">Add New Category</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="categoryForm" onsubmit="submitCategory(event)">
            <input type="hidden" id="categoryId" name="id">
            <input type="hidden" id="isEdit" name="is_edit" value="0">
            <div class="p-4 space-y-4">
                <div>
                    <label for="categoryName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category Name *</label>
                    <input type="text" name="name" id="categoryName" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                        placeholder="e.g., Beverages, Food, etc.">
                </div>
                <div>
                    <label for="categoryType" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category Type *</label>
                    <select name="type" id="categoryType" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        <option value="product">Product (Sales Items)</option>
                        <option value="material">Material (Purchase Items)</option>
                    </select>
                </div>
                <div id="categoryError" class="hidden text-sm text-red-600"></div>
            </div>
            <div class="flex items-center justify-end gap-2 p-4 border-t dark:border-gray-700">
                <button type="button" onclick="closeModal()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">
                    Add Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('modalTitle').textContent = 'Add New Category';
        document.getElementById('submitBtn').textContent = 'Add Category';
        document.getElementById('categoryId').value = '';
        document.getElementById('isEdit').value = '0';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryType').value = 'product';
        document.getElementById('categoryError').classList.add('hidden');
        document.getElementById('categoryModal').classList.remove('hidden');
        document.getElementById('categoryModal').classList.add('flex');
        document.getElementById('categoryName').focus();
    }

    function openEditModal(id, name, type) {
        document.getElementById('modalTitle').textContent = 'Edit Category';
        document.getElementById('submitBtn').textContent = 'Update Category';
        document.getElementById('categoryId').value = id;
        document.getElementById('isEdit').value = '1';
        document.getElementById('categoryName').value = name;
        document.getElementById('categoryType').value = type;
        document.getElementById('categoryError').classList.add('hidden');
        document.getElementById('categoryModal').classList.remove('hidden');
        document.getElementById('categoryModal').classList.add('flex');
        document.getElementById('categoryName').focus();
    }

    function closeModal() {
        document.getElementById('categoryModal').classList.add('hidden');
        document.getElementById('categoryModal').classList.remove('flex');
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryError').classList.add('hidden');
    }

    async function submitCategory(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const categoryId = document.getElementById('categoryId').value;
        const isEdit = document.getElementById('isEdit').value === '1';
        const errorDiv = document.getElementById('categoryError');

        const url = isEdit
            ? '{{ route('admin.categories.update', ':id') }}'.replace(':id', categoryId)
            : '{{ route('admin.categories.store') }}';

        const method = isEdit ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                closeModal();
                location.reload();
            } else {
                errorDiv.textContent = data.message || 'Failed to save category';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.textContent = 'Failed to save category. Please try again.';
            errorDiv.classList.remove('hidden');
        }
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
@endsection
