<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AdminAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AdminAuthController::class, 'register'])->name('register.store');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminProductController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders/notifications', [AdminOrderController::class, 'notifications'])->name('orders.notifications');
        Route::get('/orders/export/sales', [AdminOrderController::class, 'exportSalesCsv'])->name('orders.sales.export');
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'printInvoice'])->name('orders.invoice');
        Route::post('/orders/{order}/invoice/email', [AdminOrderController::class, 'emailInvoice'])->name('orders.invoice.email');
        Route::get('/products', [AdminProductController::class, 'products'])->name('products.index');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/inventory', [AdminProductController::class, 'inventory'])->name('inventory.index');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});
