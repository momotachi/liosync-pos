<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Superadmin\CompanyController;
use App\Http\Controllers\Superadmin\BranchController;
use App\Http\Controllers\Superadmin\SwitchCompanyController;
use App\Http\Controllers\Superadmin\SubscriptionPlanController;
use App\Http\Controllers\Superadmin\SubscriptionController;

// Superadmin Routes (Protected - Superadmin Only)
Route::middleware(['auth', 'restrict.cashier'])->prefix('superadmin')->name('superadmin.')->group(function () {
    // Companies Management
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

    // Admin Password Management
    Route::put('/companies/{company}/admins/{admin}/password', [CompanyController::class, 'updateAdminPassword'])->name('companies.admins.update-password');

    // Context Switching
    Route::get('/switch-company/{company}', [SwitchCompanyController::class, 'switch'])->name('switch.company');
    Route::get('/switch-company/{company}/enter', [SwitchCompanyController::class, 'enterCompany'])->name('switch.company.enter');
    Route::get('/switch-branch/{branch}', [SwitchCompanyController::class, 'switchBranch'])->name('switch.branch');
    Route::get('/switch-clear', [SwitchCompanyController::class, 'clear'])->name('switch.clear');

    // Subscription Plans
    Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index'])->name('subscription-plans.index');
    Route::get('/subscription-plans/create', [SubscriptionPlanController::class, 'create'])->name('subscription-plans.create');
    Route::post('/subscription-plans', [SubscriptionPlanController::class, 'store'])->name('subscription-plans.store');
    Route::get('/subscription-plans/{plan}/edit', [SubscriptionPlanController::class, 'edit'])->name('subscription-plans.edit');
    Route::put('/subscription-plans/{plan}', [SubscriptionPlanController::class, 'update'])->name('subscription-plans.update');
    Route::delete('/subscription-plans/{plan}', [SubscriptionPlanController::class, 'destroy'])->name('subscription-plans.destroy');

    // Subscriptions Management (ALL companies)
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/subscriptions/{subscription}/toggle', [SubscriptionController::class, 'toggleStatus'])->name('subscriptions.toggle');
    Route::post('/payments/{payment}/confirm', [SubscriptionController::class, 'confirmPayment'])->name('payments.confirm');
    Route::post('/payments/bulk-confirm', [SubscriptionController::class, 'confirmPaymentBulk'])->name('payments.bulk-confirm');
    Route::post('/payments/{payment}/reject', [SubscriptionController::class, 'rejectPayment'])->name('payments.reject');
});
