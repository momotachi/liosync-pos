<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Company\SubscriptionController;

// Company Admin Routes (Protected - Company Admin & Superadmin)
// Routes for company-level operations (viewing company branches, users, etc.)

Route::middleware(['auth', 'restrict.cashier'])->prefix('company')->name('company.')->group(function () {
    // Company Dashboard
    Route::get('/{company}/dashboard', [CompanyController::class, 'dashboard'])->name('dashboard');

    // Company Profile Management (for Company Admin)
    Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
    Route::put('/{company}', [CompanyController::class, 'update'])->name('update');

    // Branches Management
    Route::get('/{company}/branches', [CompanyController::class, 'branchesIndex'])->name('branches.index');
    Route::get('/{company}/branches/create', [CompanyController::class, 'branchesCreate'])->name('branches.create');
    Route::post('/{company}/branches', [CompanyController::class, 'branchesStore'])->name('branches.store');
    Route::get('/{company}/branches/{branch}/edit', [CompanyController::class, 'branchesEdit'])->name('branches.edit');
    Route::put('/{company}/branches/{branch}', [CompanyController::class, 'branchesUpdate'])->name('branches.update');
    Route::delete('/{company}/branches/{branch}', [CompanyController::class, 'branchesDestroy'])->name('branches.destroy');
    Route::get('/{company}/branches/{branch}/switch', [CompanyController::class, 'switchToBranch'])->name('branches.switch');
    Route::get('/switch-back', [CompanyController::class, 'switchToCompany'])->name('switch-back');

    // Company Users Management (All users across all branches)
    Route::get('/{company}/users', [CompanyController::class, 'usersIndex'])->name('users.index');
    Route::get('/{company}/users/create', [CompanyController::class, 'usersCreate'])->name('users.create');
    Route::post('/{company}/users', [CompanyController::class, 'usersStore'])->name('users.store');
    Route::get('/{company}/users/{user}/edit', [CompanyController::class, 'usersEdit'])->name('users.edit');
    Route::put('/{company}/users/{user}', [CompanyController::class, 'usersUpdate'])->name('users.update');
    Route::delete('/{company}/users/{user}', [CompanyController::class, 'usersDestroy'])->name('users.destroy');

    // Subscriptions Management (company's branches only)
    Route::get('/{company}/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/{company}/subscriptions/purchase', [SubscriptionController::class, 'purchase'])->name('subscriptions.purchase');
    Route::post('/{company}/subscriptions/purchase', [SubscriptionController::class, 'processPurchase'])->name('subscriptions.process-purchase');
    Route::post('/{company}/subscriptions/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::post('/{company}/subscriptions/bulk-renew', [SubscriptionController::class, 'bulkRenew'])->name('subscriptions.bulk-renew');
    Route::get('/{company}/subscriptions/{id}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/{company}/subscriptions/{subscription}/extend', [SubscriptionController::class, 'extendSubscription'])->name('subscriptions.extend');
    Route::post('/{company}/subscriptions/bulk-extend', [SubscriptionController::class, 'bulkExtend'])->name('subscriptions.bulk-extend');
});
