<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']); // Optional: specific refresh logic

        // Items
        Route::apiResource('items', ItemController::class);

        // Categories
        Route::get('/categories', [ItemController::class, 'categories']);

        // Orders
        Route::apiResource('orders', OrderController::class)->except(['destroy']);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
        Route::get('/orders/{id}/receipt', [OrderController::class, 'receipt']);

        // Stock
        Route::get('/stock-transactions', [StockController::class, 'index']);
        Route::post('/stock/restock', [StockController::class, 'restock']);
        Route::post('/stock/adjust', [StockController::class, 'adjust']);
        Route::get('/stock/low-stock', [StockController::class, 'lowStock']);

        // Reports
        Route::get('/reports/sales', [ReportController::class, 'sales']);
        Route::get('/reports/inventory', [ReportController::class, 'inventory']);
        Route::post('/reports/export', [ReportController::class, 'export']);

        // Settings
        Route::get('/settings', [SettingController::class, 'index']);

        // Users
        Route::apiResource('users', UserController::class);
    });
});
