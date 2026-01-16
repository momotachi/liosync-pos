<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Superadmin\BranchController;
use App\Http\Controllers\Superadmin\CompanyController;
use App\Http\Controllers\Company\SubscriptionController;

// Company Admin Routes (Protected - Company Admin & Superadmin)
// Routes for company-level operations (viewing company branches, users, etc.)

Route::middleware(['auth', 'restrict.cashier'])->prefix('company')->name('company.')->group(function () {
    // Company Dashboard
    Route::get('/{company}/dashboard', [CompanyController::class, 'show'])->name('dashboard');

    // Company Profile Management (for Company Admin)
    Route::get('/{company}/edit', [CompanyController::class, 'editCompany'])->name('edit');
    Route::put('/{company}', [CompanyController::class, 'updateCompany'])->name('update');

    // Branches Management
    Route::get('/{company}/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('/{company}/branches/create', [BranchController::class, 'create'])->name('branches.create');
    Route::post('/{company}/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::get('/{company}/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
    Route::put('/{company}/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('/{company}/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    // Company Users Management (All users across all branches)
    Route::get('/{company}/users', [CompanyController::class, 'usersIndex'])->name('users.index');
    Route::get('/{company}/users/{user}/edit', [CompanyController::class, 'usersEdit'])->name('users.edit');
    Route::put('/{company}/users/{user}', [CompanyController::class, 'usersUpdate'])->name('users.update');
    Route::delete('/{company}/users/{user}', [CompanyController::class, 'usersDestroy'])->name('users.destroy');

    // Branch Users Management
    Route::get('/{company}/branches/{branch}/users', [BranchController::class, 'usersIndex'])->name('branches.users.index');
    Route::get('/{company}/branches/{branch}/users/create', [BranchController::class, 'usersCreate'])->name('branches.users.create');
    Route::post('/{company}/branches/{branch}/users', [BranchController::class, 'usersStore'])->name('branches.users.store');
    Route::get('/{company}/branches/{branch}/users/{user}/edit', [BranchController::class, 'usersEdit'])->name('branches.users.edit');
    Route::put('/{company}/branches/{branch}/users/{user}', [BranchController::class, 'usersUpdate'])->name('branches.users.update');
    Route::delete('/{company}/branches/{branch}/users/{user}', [BranchController::class, 'usersDestroy'])->name('branches.users.destroy');

    // Subscriptions Management (company's branches only)
    Route::get('/{company}/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/{company}/subscriptions/{id}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::get('/{company}/subscriptions/purchase', [SubscriptionController::class, 'purchase'])->name('subscriptions.purchase');
    Route::post('/{company}/subscriptions/purchase', [SubscriptionController::class, 'processPurchase'])->name('subscriptions.process-purchase');
    Route::post('/{company}/subscriptions/{subscription}/extend', [SubscriptionController::class, 'extendSubscription'])->name('subscriptions.extend');
    Route::post('/{company}/subscriptions/bulk-extend', [SubscriptionController::class, 'bulkExtend'])->name('subscriptions.bulk-extend');
});
