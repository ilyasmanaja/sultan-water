<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Inventory;
use App\Http\Controllers\PriceTierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryController;

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

    // Endpoint CRUD Master Tier Harga (S3-BE-09)
    Route::prefix('admin/price-tiers')->name('admin.price-tiers.')->group(function () {
        Route::get('/', [PriceTierController::class, 'index'])->name('index');
        Route::put('/{priceTier}', [PriceTierController::class, 'update'])->name('update');
    });

});

// --- RUTE KHUSUS KASIR ---
Route::middleware(['auth', 'role:kasir'])->group(function () {

    Route::get('/pos', function () {
        return redirect()->route('pos.dashboard');
    })->name('pos.index');

    Route::get('/pos/dashboard', function () {
        // 1. Ambil order hari ini beserta relasinya
        $orders = Order::with(['customer', 'items.product', 'items.priceTier'])
            ->whereDate('created_at', today())
            ->latest()
            ->get()
            ->map(function ($order) {
                $firstItem = $order->items->first();
                return [
                    'id' => $order->id,
                    'order_number' => 'SW-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'time' => $order->created_at->format('H:i'),
                    'order_type' => $order->order_type,
                    'delivery_status' => $order->delivery_status,
                    'payment_status' => $order->payment_status,
                    'customer_name' => $order->customer->name ?? 'Pelanggan Umum',
                    'customer_address' => $order->customer->address ?? '-',
                    'tier_name' => $firstItem?->priceTier?->name ?? 'Isi Ulang',
                    'tier_code' => $firstItem?->priceTier?->code ?? 'letak_kedai',
                    'product_type' => $firstItem?->product?->type ?? 'isi_ulang',
                    'qty' => (int) ($firstItem?->quantity ?? 1),
                    'total_amount' => (float) $order->total_amount,
                    'gallon_action' => $firstItem?->gallon_action ?? 'tidak_ada',
                    'gallon_qty' => (int) ($firstItem?->gallon_qty ?? 0),
                    'payment_method' => $order->payment_method,
                ];
            });

        // 2. Ambil data pelanggan untuk dropdown & info pinjaman
        $customers = Customer::select('id', 'name', 'whatsapp_number as whatsapp', 'address', 'borrowed_gallons')->get();

        // 3. Ambil stok aktual dari tabel inventories
        $tutupStock = Inventory::where('item_type', 'tutup_galon')->value('quantity') ?? 0;
        $totalGalonDepot = Inventory::where('item_type', 'galon_kosong_depot')->value('quantity') ?? 0;

        return view('pos.index', compact('orders', 'customers', 'tutupStock', 'totalGalonDepot'));
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

    // Endpoint Kurir Pengantaran & Rollback Pembatalan (S3-BE-02)
    Route::prefix('delivery')->name('delivery.')->group(function () {
        Route::post('/{order}/start', [DeliveryController::class, 'start'])->name('start');
        Route::post('/{order}/complete', [DeliveryController::class, 'confirmComplete'])->name('complete');
        Route::post('/{order}/cancel', [DeliveryController::class, 'cancel'])->name('cancel');
    });

    // Endpoint Smart Search & Quick Add Pelanggan (S3-BE-05 & S3-BE-06)
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/search', [CustomerController::class, 'search'])->name('search');
        Route::post('/quick-add', [CustomerController::class, 'quickStore'])->name('quick-store');
    });
});