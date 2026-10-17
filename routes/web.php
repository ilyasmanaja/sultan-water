<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\PriceTier;
use App\Http\Controllers\PriceTierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportExportController;


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
        Route::put('/threshold', [InventoryController::class, 'updateThreshold'])->name('threshold.update');
    });

    // Endpoint CRUD Master Tier Harga (S3-BE-09 & S3-FE-07)
    Route::prefix('admin/price-tiers')->name('admin.price-tiers.')->group(function () {
        Route::get('/', function (\Illuminate\Http\Request $request) {
            $priceTiers = PriceTier::orderBy('applies_to')->orderBy('price')->get();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $priceTiers,
                ]);
            }
            return view('admin.price-tiers.index', compact('priceTiers'));
        })->name('index');

        Route::put('/{priceTier}', [PriceTierController::class, 'update'])->name('update');
    });

    // Modul Pengeluaran Operasional (Expenses - S4-FE-03 / Tiket #63)
    Route::prefix('admin/expenses')->name('admin.expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
    });

    // Modul Laporan Finansial & Agregasi Laba Bersih (S4-BE-03 / Tiket #58)
    Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/summary', [ReportController::class, 'summary'])->name('summary');
    });

    // Fitur Cetak & Ekspor Laporan Keuangan (S4-BE-04 / Tiket #59)
    Route::prefix('admin/reports/export')->name('admin.reports.export.')->group(function () {
        Route::get('/pdf', [ReportExportController::class, 'exportPdf'])->name('pdf');
        Route::get('/excel', [ReportExportController::class, 'exportExcel'])->name('excel');
    });
});

// --- RUTE TERAUTENTIKASI UMUM (BISA DIAKSES ADMIN & KASIR) ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Halaman Kasir POS Transaksi (Bisa diakses Admin & Kasir)
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

        // 3. Ambil data master price tiers aktif dari database (S3-FE-07)
        $priceTiers = PriceTier::where('is_active', true)->orderBy('price')->get();

        // 4. Ambil stok aktual dari tabel inventories
        $tutupStock = Inventory::where('item_type', 'tutup_galon')->value('quantity') ?? 0;
        $totalGalonDepot = Inventory::where('item_type', 'galon_kosong_depot')->value('quantity') ?? 0;

        return view('pos.index', compact('orders', 'customers', 'priceTiers', 'tutupStock', 'totalGalonDepot'));
    })->name('pos.dashboard');

    // Halaman Antrean Kurir Mobile (S3-FE-01 s.d. S3-FE-05)
    Route::get('/delivery/queue', function () {
        $orders = Order::with(['customer', 'items.product', 'items.priceTier'])
            ->where('order_type', 'pesan_antar')
            ->whereIn('delivery_status', [Order::STATUS_PENDING, Order::STATUS_DIANTAR])
            ->orderByRaw("FIELD(delivery_status, 'diantar', 'pending')")
            ->oldest()
            ->get()
            ->map(function ($order) {
                $firstItem = $order->items->first();
                return [
                    'id' => $order->id,
                    'order_number' => 'SW-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'time' => $order->created_at->format('H:i'),
                    'delivery_status' => $order->delivery_status,
                    'payment_status' => $order->payment_status,
                    'total_amount' => (float) $order->total_amount,
                    'customer' => [
                        'id' => $order->customer?->id,
                        'name' => $order->customer?->name ?? 'Pelanggan Terdaftar',
                        'whatsapp' => $order->customer?->whatsapp_number ?? '',
                        'address' => $order->customer?->address ?? '-',
                        'borrowed_gallons' => (int) ($order->customer?->borrowed_gallons ?? 0),
                    ],
                    'items_summary' => [
                        'tier_name' => $firstItem?->priceTier?->name ?? 'Isi Ulang',
                        'qty' => (int) ($firstItem?->quantity ?? 0),
                        'gallon_action' => $firstItem?->gallon_action ?? 'tidak_ada',
                        'gallon_qty' => (int) ($firstItem?->gallon_qty ?? 0),
                    ],
                ];
            });

        $priceTiers = PriceTier::where('is_active', true)->orderBy('price')->get();
        $tutupStock = Inventory::where('item_type', 'tutup_galon')->value('quantity') ?? 0;
        $totalGalonDepot = Inventory::where('item_type', 'galon_kosong_depot')->value('quantity') ?? 0;

        return view('courier.queue', compact('orders', 'priceTiers', 'tutupStock', 'totalGalonDepot'));
    })->name('delivery.queue');

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

    // Endpoint Smart Search, Quick Add & Riwayat Pembelian Pelanggan (S3-BE-05, S3-BE-06 & S4-BE-07)
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/search', [CustomerController::class, 'search'])->name('search');
        Route::post('/quick-add', [CustomerController::class, 'quickStore'])->name('quick-store');
        Route::get('/{customer}/monthly-purchases', [CustomerController::class, 'monthlyPurchaseHistory'])->name('monthly-purchases');
    });

    // Endpoint Status Stok & Low-Stock Alert Tutup Galon (S4-BE-06 / Tiket #76)
    Route::get('/inventory/status/cap', [InventoryController::class, 'capStatus'])->name('inventory.cap-status');
});
