<?php

use Illuminate\Support\Facades\Route;

// Branch Routes (Protected)
Route::middleware(['auth', 'restrict.cashier'])->group(function () {
    Route::get('/branch', [\App\Http\Controllers\DashboardController::class, 'index'])->name('branch.dashboard');

    // Items CRUD (Unified Products + Raw Materials)
    Route::get('/branch/items', [\App\Http\Controllers\AdminItemController::class, 'index'])->name('admin.items.index');
    Route::get('/branch/items/create', [\App\Http\Controllers\AdminItemController::class, 'create'])->name('admin.items.create');
    Route::post('/branch/items', [\App\Http\Controllers\AdminItemController::class, 'store'])->name('admin.items.store');
    Route::get('/branch/items/{id}/edit', [\App\Http\Controllers\AdminItemController::class, 'edit'])->name('admin.items.edit');
    Route::put('/branch/items/{id}', [\App\Http\Controllers\AdminItemController::class, 'update'])->name('admin.items.update');
    Route::delete('/branch/items/{id}', [\App\Http\Controllers\AdminItemController::class, 'destroy'])->name('admin.items.destroy');
    Route::post('/branch/items/{id}/restock', [\App\Http\Controllers\AdminItemController::class, 'restock'])->name('admin.items.restock');
    Route::post('/branch/items/{id}/adjust-stock', [\App\Http\Controllers\AdminItemController::class, 'adjustStock'])->name('admin.items.adjust-stock');

    // Categories
    Route::get('/branch/categories', [\App\Http\Controllers\AdminItemController::class, 'categoriesIndex'])->name('admin.categories.index');
    Route::post('/branch/categories', [\App\Http\Controllers\AdminItemController::class, 'storeCategory'])->name('admin.categories.store');
    Route::put('/branch/categories/{id}', [\App\Http\Controllers\AdminItemController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/branch/categories/{id}', [\App\Http\Controllers\AdminItemController::class, 'destroyCategory'])->name('admin.categories.destroy');

    // Settings
    Route::get('/branch/settings', [\App\Http\Controllers\AdminSettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('/branch/settings', [\App\Http\Controllers\AdminSettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('/branch/settings/reset', [\App\Http\Controllers\AdminSettingsController::class, 'reset'])->name('admin.settings.reset');

    // Reports
    Route::get('/branch/reports', [\App\Http\Controllers\AdminReportsController::class, 'index'])->name('admin.reports.index');
    Route::get('/branch/reports/sales', [\App\Http\Controllers\AdminReportsController::class, 'salesTransactions'])->name('admin.reports.sales');
    Route::get('/branch/reports/purchases', [\App\Http\Controllers\AdminReportsController::class, 'purchases'])->name('admin.reports.purchases');
    Route::get('/branch/reports/products', [\App\Http\Controllers\AdminReportsController::class, 'productSales'])->name('admin.reports.products');
    Route::get('/branch/reports/inventory', [\App\Http\Controllers\AdminReportsController::class, 'inventory'])->name('admin.reports.inventory');
    Route::get('/branch/reports/export', [\App\Http\Controllers\AdminReportsController::class, 'export'])->name('admin.reports.export');
    Route::get('/branch/reports/export-pdf', [\App\Http\Controllers\AdminReportsController::class, 'exportPdf'])->name('admin.reports.export-pdf');
    Route::get('/branch/reports/order/{id}', [\App\Http\Controllers\AdminReportsController::class, 'orderDetails'])->name('admin.reports.order-details');

    // Stock Transactions
    Route::get('/branch/stock-transactions', [\App\Http\Controllers\AdminStockTransactionsController::class, 'index'])->name('admin.stock-transactions.index');
    Route::get('/branch/stock-transactions/export', [\App\Http\Controllers\AdminStockTransactionsController::class, 'export'])->name('admin.stock-transactions.export');

    // Branch Users Management (For Branch Admin)
    Route::get('/branch/users', [\App\Http\Controllers\BranchUserController::class, 'index'])->name('admin.users.index');
    Route::get('/branch/users/create', [\App\Http\Controllers\BranchUserController::class, 'create'])->name('admin.users.create');
    Route::post('/branch/users', [\App\Http\Controllers\BranchUserController::class, 'store'])->name('admin.users.store');
    Route::get('/branch/users/{user}/edit', [\App\Http\Controllers\BranchUserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/branch/users/{user}', [\App\Http\Controllers\BranchUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/branch/users/{user}', [\App\Http\Controllers\BranchUserController::class, 'destroy'])->name('admin.users.destroy');

    // Legacy redirects
    Route::get('/branch/orders', function () {
        return redirect('/branch/reports');
    });

    // Subscription Plans (Read-only for viewing available plans)
    Route::get('/subscription-plans', [\App\Http\Controllers\SubscriptionPlanViewController::class, 'index'])->name('subscription-plans.view');

    // Subscription Management (exempt from subscription check)
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Branch\SubscriptionController::class, 'index'])->name('index');
        Route::get('/purchase', [\App\Http\Controllers\Branch\SubscriptionController::class, 'purchase'])->name('purchase');
        Route::post('/purchase', [\App\Http\Controllers\Branch\SubscriptionController::class, 'processPurchase'])->name('process');
        Route::get('/history', [\App\Http\Controllers\Branch\SubscriptionController::class, 'history'])->name('history');
    });
});
