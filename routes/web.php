<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
| Route Organization:
| - superadmin.php  -> Superadmin routes (companies, branches management)
| - company.php     -> Company admin routes
| - branch.php      -> Branch-level routes (dashboard, items, settings, reports)
|
*/

Route::get('/', function () {
    return redirect('/pos');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.process');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get');

// Registration Routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.process');

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// POS Routes (Protected - All authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/pending-orders', [PosController::class, 'pendingOrders'])->name('pos.pending-orders');
    Route::post('/pos/orders/{id}/payment', [PosController::class, 'processPayment'])->name('pos.payment.process');
    Route::delete('/pos/orders/{id}', [PosController::class, 'deletePendingOrder'])->name('pos.order.delete');
    Route::get('/pos/receipt/{id}', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::get('/pos/receipt/{id}/print', [PosController::class, 'printReceipt'])->name('pos.receipt.print');
    Route::get('/pos/receipt/{id}/kitchen', [PosController::class, 'kitchenReceipt'])->name('pos.receipt.kitchen');
    Route::get('/pos/receipt/{id}/table', [PosController::class, 'tableReceipt'])->name('pos.receipt.table');
});

// Purchase Order Routes (Protected - Not for Cashiers)
Route::middleware(['auth', 'restrict.cashier'])->group(function () {
    Route::get('/purchase', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('purchase.index');
    Route::post('/purchase/store', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('purchase.store');
    Route::get('/purchase/receipt/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'receipt'])->name('purchase.receipt');
});

// Load additional route files
require base_path('routes/superadmin.php');
require base_path('routes/company.php');
require base_path('routes/branch.php');
