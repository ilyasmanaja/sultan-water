<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('landing-page');
});

// Rute Publik (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// --- RUTE KHUSUS ADMIN ---
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Modul Restock Tutup Galon & Pembelian Galon Baru (S2-BE-05 & S2-FE-05)
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/restock', [InventoryController::class, 'showRestockForm'])->name('restock');
        Route::post('/restock/cap', [InventoryController::class, 'restockCap'])->name('restock.cap');
        Route::post('/restock/gallon', [InventoryController::class, 'restockGallon'])->name('restock.gallon');
    });

});

// --- RUTE KHUSUS KASIR ---
Route::middleware(['auth', 'role:kasir'])->group(function () {

    Route::get('/pos', function () {
        return view('pos.index');
    })->name('pos.index');

    Route::get('/pos/dashboard', function () {
        return view('pos.index');
    })->name('pos.dashboard');

});

// --- RUTE TERAUTENTIKASI UMUM (ADMIN & KASIR) ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Endpoint Transaksi POS Kasir (S2-BE-01 & S2-BE-02)
    Route::prefix('pos/order')->name('pos.order.')->group(function () {
        Route::post('/refill', [OrderController::class, 'storeRefill'])->name('refill');
        Route::post('/new-gallon', [OrderController::class, 'storeNewGallon'])->name('new-gallon');
    });
});