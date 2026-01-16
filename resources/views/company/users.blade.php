@extends('layouts.company')

@section('title', 'Users - ' . $company->name)

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $company->name }} - Users</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                @if($branchFilter)
                    Managing users for <strong>{{ $branches->find($branchFilter)?->name ?? 'Branch' }}</strong>
                    <a href="{{ route('company.users.index', $company) }}" class="text-primary hover:underline ml-2">
                        (View All)
                    </a>
                @else
                    Manage all users across all branches
                @endif
            </p>
        </div>
        <a href="{{ route('company.users.create', $company) }}"
           class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors">
            <span class="material-symbols-outlined text-sm mr-2">add</span>
            Add User
        </a>
    </div>

    <!-- Branch Filter -->
    @if($branches->count() > 0)
    <div class="mt-4 flex items-center gap-4">
        <form method="GET" action="{{ route('company.users.index', $company) }}" class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Branch:</label>
            <select name="branch_id" onchange="this.form.submit()"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $branchFilter == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            @if($branchFilter)
                <a href="{{ route('company.users.index', $company) }}"
                   class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <span class="material-symbols-outlined text-sm align-middle">close</span>
                    Clear
                </a>
            @endif
        </form>
    </div>
    @endif
</div>

<!-- Users Table -->
<div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
    @if($users->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-light dark:divide-border-dark">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        @if(auth()->user()->isSuperAdmin())
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Password</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-card-light dark:bg-card-dark divide-y divide-border-light dark:divide-border-dark">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                                        <span class="material-symbols-outlined text-primary">person</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->created_at->format('M d, Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm">email</span>
                                        <span class="email-text">{{ $user->email }}</span>
                                        <button onclick="copyEmail('{{ $user->email }}')"
                                                class="text-gray-400 hover:text-primary transition-colors"
                                                title="Copy email">
                                            <span class="material-symbols-outlined text-sm">content_copy</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            @if(auth()->user()->isSuperAdmin())
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-gray-400">password</span>
                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded font-mono text-gray-700 dark:text-gray-300">{{ $user->password_hint ?? 'N/A' }}</code>
                                    <button onclick="copyEmail('{{ $user->password_hint ?? 'N/A' }}')"
                                            class="text-gray-400 hover:text-primary transition-colors ml-1"
                                            title="Copy password">
                                        <span class="material-symbols-outlined text-sm">content_copy</span>
                                    </button>
                                </div>
                            </td>
                            @endif
                            <td class="px-6 py-4">
                                @if($user->branch)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                        <span class="material-symbols-outlined text-xs mr-1">store</span>
                                        {{ $user->branch->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                        <span class="material-symbols-outlined text-xs mr-1">business</span>
                                        All Branches
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->roles->count() > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                                        {{ $roles[$user->roles->first()->name] ?? $user->roles->first()->name }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400 dark:text-gray-600">No role</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                        <span class="material-symbols-outlined text-xs mr-1">check_circle</span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                        <span class="material-symbols-outlined text-xs mr-1">cancel</span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Button -->
                                    <a href="{{ route('company.users.edit', [$company, $user]) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg text-xs font-medium transition-colors">
                                        <span class="material-symbols-outlined text-sm mr-1">edit</span>
                                        Edit
                                    </a>

                                    <!-- Delete Button - Company admin can delete any user except themselves -->
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('company.users.destroy', [$company, $user]) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg text-xs font-medium transition-colors">
                                                <span class="material-symbols-outlined text-sm mr-1">delete</span>
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-border-light dark:border-border-dark">
                {{ $users->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                <span class="material-symbols-outlined text-3xl text-gray-400 dark:text-gray-600">people</span>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                @if($branchFilter)
                    No users found for this branch
                @else
                    No users yet
                @endif
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                @if($branchFilter)
                    <a href="{{ route('company.users.index', $company) }}" class="text-primary hover:underline">View all users</a> or add users to this branch
                @else
                    Add users to branches to get started
                @endif
            </p>
        </div>
    @endif
</div>

@push('scripts')
<script>
function copyEmail(email) {
    // Try modern clipboard API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(email).then(function() {
            showNotification('Email copied!');
        }).catch(function(err) {
            console.error('Clipboard API failed: ', err);
            // Fallback to older method
            fallbackCopy(email);
        });
    } else {
        // Direct fallback for browsers without clipboard API
        fallbackCopy(email);
    }
}

function fallbackCopy(email) {
    const textArea = document.createElement('textarea');
    textArea.value = email;
    textArea.style.position = 'fixed';
    textArea.style.left = '-9999px';
    textArea.style.top = '0';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
        showNotification('Email copied!');
    } catch (e) {
        console.error('Failed to copy: ', e);
        showNotification('Failed to copy email', true);
    }

    document.body.removeChild(textArea);
}

function showNotification(message, isError = false) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${isError ? '#ef4444' : '#10b981'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        font-family: system-ui, -apple-system, sans-serif;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        animation: slideIn 0.3s ease-out;
    `;
    notification.innerHTML = `
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${isError
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
            }
        </svg>
        <span>${message}</span>
    `;

    // Add animation keyframes
    if (!document.getElementById('copy-notification-styles')) {
        const style = document.createElement('style');
        style.id = 'copy-notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(notification);

    setTimeout(function() {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 2000);
}
</script>
@endpush
@endsection
