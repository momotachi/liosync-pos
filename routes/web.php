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

// Password Reset Routes (Contact Admin - No Email)
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');

// POS Routes (Protected - All authenticated users)
// Added mobile.auth middleware to support token-based auth for mobile WebView
Route::middleware(['mobile.auth', 'auth'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/pending-orders', [PosController::class, 'pendingOrders'])->name('pos.pending-orders');
    Route::post('/pos/orders/{id}/payment', [PosController::class, 'processPayment'])->name('pos.payment.process');
    Route::delete('/pos/orders/{id}', [PosController::class, 'deletePendingOrder'])->name('pos.order.delete');
    Route::post('/pos/orders/{id}/cancel', [PosController::class, 'cancelOrder'])->name('pos.order.cancel');
    Route::get('/pos/receipt/{id}', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::get('/pos/receipt/{id}/print', [PosController::class, 'printReceipt'])->name('pos.receipt.print');
    Route::get('/pos/receipt/{id}/kitchen', [PosController::class, 'kitchenReceipt'])->name('pos.receipt.kitchen');
});
