<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Kasir - Sultan Water</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Asset Lokal (Tailwind CSS & Alpine.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .touch-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .touch-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .touch-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
    </style>
</head>

<body class="h-full flex flex-col md:flex-row text-slate-800 antialiased overflow-hidden select-none"
    x-data="posApp()" @keydown.window.prevent.f2="openOrderModal = true">

    <!-- ========================================================================= -->
    <!-- 1. LEFT SIDEBAR NAVIGATION                                                -->
    <!-- ========================================================================= -->
    <aside
        class="w-full md:w-20 bg-white border-r border-slate-200/80 flex md:flex-col items-center justify-between py-3 px-4 md:py-6 md:px-0 shrink-0 z-30 shadow-xs">
        <!-- Top: Logo -->
        <div class="flex items-center gap-3 md:flex-col">
            <a href="{{ route('pos.dashboard') }}"
                class="w-11 h-11 rounded-2xl bg-white border border-slate-200 p-1 flex items-center justify-center shadow-xs hover:scale-105 transition overflow-hidden"
                style="width: 44px; height: 44px; min-width: 44px; min-height: 44px;">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="w-full h-full object-contain"
                    style="max-width: 36px; max-height: 36px; object-fit: contain;"
                    onerror="this.onerror=null; this.src='https://placehold.co/80x80/2563eb/ffffff?text=SW';">
            </a>
        </div>

        <!-- Middle: Nav Icons -->
        <nav class="flex md:flex-col items-center gap-2 sm:gap-3">
            <button type="button" title="Dashboard Transaksi Kasir"
                class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <i class="fa-solid fa-cash-register text-base"></i>
            </button>
            <button type="button" @click="openOrderModal = true" title="Tambah Transaksi Cepat (F2)"
                class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition active:scale-95">
                <i class="fa-solid fa-plus text-base"></i>
            </button>
        </nav>

        <!-- Bottom: User & Logout -->
        <div class="flex md:flex-col items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-black text-slate-700"
                title="{{ auth()->user()->name ?? 'Kasir' }}">
                {{ strtoupper(substr(auth()->user()->name ?? 'K', 0, 1)) }}
            </div>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" title="Keluar / Logout"
                    class="w-10 h-10 rounded-2xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition">
                    <i class="fa-solid fa-power-off text-sm"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- ========================================================================= -->
    <!-- 2. MAIN WORKSPACE (TABLE-CENTRIC)                                         -->
    <!-- ========================================================================= -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Top Bar: Greeting & Tanggal -->
        <header class="px-5 lg:px-8 py-4 bg-white border-b border-slate-200/80 shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Kasir Penjualan Sultan Water
                    </h1>
                    <p class="text-xs text-slate-400 font-medium mt-0.5" x-text="todayDate"></p>
                </div>
                <div class="hidden sm:flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-semibold">Pintasan:</span>
                    <kbd
                        class="px-2 py-1 bg-slate-100 border border-slate-300 rounded-lg text-[10px] font-bold text-slate-600">F2:
                        Transaksi Baru</kbd>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto px-4 sm:px-5 lg:px-8 py-5 touch-scroll">

            <!-- 4. TOOLBAR KENDALI & TABEL TRANSAKSI -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">

                <!-- Toolbar Atas Tabel -->
                <div
                    class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">

                    <!-- Filter Tabs & Search -->
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative w-full sm:w-64">
                            <i
                                class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" x-model="searchQuery" placeholder="Cari order / nama pelanggan..."
                                class="w-full pl-9 pr-3 py-2 text-xs rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                        </div>

                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl">
                            <button type="button" @click="statusFilter = 'all'"
                                :class="statusFilter === 'all' ? 'bg-white text-slate-900 font-bold shadow-xs' :
                                    'text-slate-500 hover:text-slate-800'"
                                class="px-3 py-1.5 text-xs rounded-xl transition">Semua</button>
                            <button type="button" @click="statusFilter = 'pending'"
                                :class="statusFilter === 'pending' ? 'bg-white text-amber-700 font-bold shadow-xs' :
                                    'text-slate-500 hover:text-slate-800'"
                                class="px-3 py-1.5 text-xs rounded-xl transition">Antrean Antar</button>
                            <button type="button" @click="statusFilter = 'selesai'"
                                :class="statusFilter === 'selesai' ? 'bg-white text-emerald-700 font-bold shadow-xs' :
                                    'text-slate-500 hover:text-slate-800'"
                                class="px-3 py-1.5 text-xs rounded-xl transition">Selesai</button>
                        </div>
                    </div>

                    <!-- Tombol Utama "+ Transaksi Baru" -->
                    <button type="button" @click="openOrderModal = true"
                        class="min-h-[44px] px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Transaksi Baru</span>
                        <kbd
                            class="hidden sm:inline-block px-1.5 py-0.5 bg-blue-700/60 rounded text-[10px] font-mono">F2</kbd>
                    </button>
                </div>

                <!-- Kontainer Tabel Data -->
                <div class="overflow-x-auto touch-scroll">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-400">
                                <th class="py-3 px-4 sm:px-5">No. Order</th>
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Tipe & Status</th>
                                <th class="py-3 px-4">Pelanggan</th>
                                <th class="py-3 px-4">Item & Opsi Galon</th>
                                <th class="py-3 px-4 text-right">Total Bayar</th>
                                <th class="py-3 px-4 text-center">Status Bayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">

                            <!-- Empty State -->
                            <template x-if="filteredOrders.length === 0">
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <i class="fa-solid fa-clipboard-list text-3xl mb-2 text-slate-300"></i>
                                        <p class="font-semibold text-xs">Belum ada transaksi hari ini</p>
                                        <button type="button" @click="openOrderModal = true"
                                            class="mt-2 text-blue-600 font-extrabold hover:underline">
                                            + Buat transaksi pertama sekarang
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <!-- Rows Transaksi -->
                            <template x-for="order in filteredOrders" :key="order.id">
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-4 sm:px-5 font-mono font-extrabold text-blue-600"
                                        x-text="order.order_number"></td>
                                    <td class="py-3.5 px-4 text-slate-400 font-medium" x-text="order.time"></td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase"
                                                :class="order.order_type === 'ambil_sendiri' ? 'bg-slate-100 text-slate-700' :
                                                    'bg-amber-100 text-amber-800'"
                                                x-text="order.order_type === 'ambil_sendiri' ? 'Walk-in' : 'Antar'"></span>

                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold"
                                                :class="{
                                                    'bg-emerald-50 text-emerald-700 border border-emerald-200': order
                                                        .delivery_status === 'selesai',
                                                    'bg-amber-50 text-amber-700 border border-amber-200 animate-pulse': order
                                                        .delivery_status === 'pending',
                                                    'bg-blue-50 text-blue-700 border border-blue-200': order
                                                        .delivery_status === 'diantar'
                                                }"
                                                x-text="order.delivery_status"></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800" x-text="order.customer_name"></div>
                                        <div class="text-[11px] text-slate-400 truncate max-w-[140px]"
                                            x-text="order.customer_address"></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <!-- Judul Tier / Produk -->
                                        <div class="font-bold text-slate-800" x-text="order.tier_name"></div>

                                        <!-- Rincian: Qty x Harga Satuan + Aksi Galon Fisik -->
                                        <div
                                            class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5 mt-0.5">
                                            <span class="font-bold text-slate-700"
                                                x-text="order.qty + 'x @ ' + rupiah(order.total_amount / order.qty)"></span>
                                            <span
                                                :class="{
                                                    'text-emerald-600 font-semibold': order
                                                        .gallon_action === 'tukar_seimbang',
                                                    'text-amber-600 font-semibold': order.gallon_action === 'pinjam',
                                                    'text-purple-600 font-semibold': order
                                                        .gallon_action === 'kembalikan',
                                                    'text-slate-400': order.gallon_action === 'tidak_ada'
                                                }"
                                                x-text="formatGallonAction(order.gallon_action, order.qty, order.product_type)"></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-black text-slate-900"
                                        x-text="rupiah(order.total_amount)"></td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span
                                            class="px-2.5 py-1 rounded-full font-black text-[10px] tracking-wide uppercase inline-flex items-center gap-1"
                                            :class="order.payment_status === 'lunas' ?
                                                'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                                'bg-rose-50 text-rose-700 border border-rose-200 animate-pulse'">
                                            <i class="fa-solid text-[9px]"
                                                :class="order.payment_status === 'lunas' ? 'fa-check' : 'fa-clock'"></i>
                                            <span
                                                x-text="order.payment_status === 'lunas' ? 'Lunas' : 'Belum Lunas'"></span>
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- 5. MODAL FORM: TRANSAKSI BARU (5-TIER HARGA + LOGIKA GALON)               -->
    <!-- ========================================================================= -->
    <div x-show="openOrderModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 overflow-y-auto max-h-[92vh] touch-scroll"
            @click.away="openOrderModal = false">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900">Transaksi Kasir Baru</h3>
                    <p class="text-xs text-slate-400">Pilih tier harga dan opsi galon fisik</p>
                </div>
                <button type="button" @click="openOrderModal = false"
                    class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <div class="mt-4 space-y-4">

                <!-- 1. Tipe Layanan (Walk-in vs Antar) -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Tipe Layanan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="newOrder.orderType = 'ambil_sendiri'"
                            :class="newOrder.orderType === 'ambil_sendiri' ? 'bg-blue-600 text-white font-black shadow-xs' :
                                'bg-slate-50 text-slate-600 border border-slate-200'"
                            class="p-2.5 rounded-2xl text-xs transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-store"></i>
                            <span>Walk-in (Ambil Sendiri)</span>
                        </button>
                        <button type="button" @click="newOrder.orderType = 'pesan_antar'"
                            :class="newOrder.orderType === 'pesan_antar' ? 'bg-blue-600 text-white font-black shadow-xs' :
                                'bg-slate-50 text-slate-600 border border-slate-200'"
                            class="p-2.5 rounded-2xl text-xs transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-motorcycle"></i>
                            <span>Pesan Antar (Kurir)</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Pilih Pelanggan -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-600">Pelanggan</label>
                        <button type="button" @click="openQuickAddModal = true"
                            class="text-xs font-extrabold text-blue-600 hover:underline">
                            + Tambah Baru
                        </button>
                    </div>
                    <select x-model="newOrder.customerId"
                        class="w-full min-h-[42px] px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500">
                        <option value="">👤 Pelanggan Umum (Walk-in)</option>
                        <template x-for="c in customers" :key="c.id">
                            <option :value="c.id" x-text="c.name + ' (' + c.whatsapp + ')'"></option>
                        </template>
                    </select>

                    <!-- Info Pinjaman Pelanggan Terpilih -->
                    <template x-if="selectedCustomer">
                        <div
                            class="mt-2 px-3 py-1.5 rounded-xl bg-blue-50/70 border border-blue-100 flex items-center justify-between text-xs">
                            <span class="text-blue-900 font-medium truncate max-w-[200px]"
                                x-text="selectedCustomer.address"></span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black"
                                :class="selectedCustomer.borrowed_gallons > 0 ? 'bg-amber-100 text-amber-800' :
                                    'bg-slate-100 text-slate-500'"
                                x-text="'Pinjam: ' + selectedCustomer.borrowed_gallons + ' Galon'"></span>
                        </div>
                    </template>
                </div>

                <!-- 3. PILIHAN 5-TIER HARGA (SEMUA JADI SATU GRID) -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Pilih Varian Produk & Tier
                        Harga</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">

                        <button type="button" @click="selectTier('sosial', 4000, 'Harga Sosial', 'isi_ulang')"
                            :class="newOrder.tier === 'sosial' ? 'bg-blue-600 text-white shadow-sm' :
                                'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[58px] active:scale-95">
                            <span class="text-[11px] font-medium"
                                :class="newOrder.tier === 'sosial' ? 'text-blue-100' : 'text-slate-400'">Sosial</span>
                            <span class="text-sm font-black">Rp 4.000</span>
                        </button>

                        <button type="button"
                            @click="selectTier('letak_kedai', 5000, 'Harga Letak Kedai', 'isi_ulang')"
                            :class="newOrder.tier === 'letak_kedai' ? 'bg-blue-600 text-white shadow-sm' :
                                'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[58px] active:scale-95">
                            <span class="text-[11px] font-medium"
                                :class="newOrder.tier === 'letak_kedai' ? 'text-blue-100' : 'text-slate-400'">Letak
                                Kedai</span>
                            <span class="text-sm font-black">Rp 5.000</span>
                        </button>

                        <button type="button"
                            @click="selectTier('antar_dekat', 6000, 'Harga Antar Dekat', 'isi_ulang')"
                            :class="newOrder.tier === 'antar_dekat' ? 'bg-blue-600 text-white shadow-sm' :
                                'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[58px] active:scale-95">
                            <span class="text-[11px] font-medium"
                                :class="newOrder.tier === 'antar_dekat' ? 'text-blue-100' : 'text-slate-400'">Antar
                                Dekat</span>
                            <span class="text-sm font-black">Rp 6.000</span>
                        </button>

                        <button type="button"
                            @click="selectTier('antar_jauh', 7000, 'Harga Antar Jauh', 'isi_ulang')"
                            :class="newOrder.tier === 'antar_jauh' ? 'bg-blue-600 text-white shadow-sm' :
                                'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200'"
                            class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[58px] active:scale-95">
                            <span class="text-[11px] font-medium"
                                :class="newOrder.tier === 'antar_jauh' ? 'text-blue-100' : 'text-slate-400'">Antar
                                Jauh</span>
                            <span class="text-sm font-black">Rp 7.000</span>
                        </button>

                        <!-- Tier 5: Galon Baru + Isi (Beli Putus) -->
                        <button type="button"
                            @click="selectTier('galon_baru_isi', 40000, 'Galon Baru + Isi', 'galon_baru')"
                            :class="newOrder.tier === 'galon_baru_isi' ? 'bg-emerald-600 text-white shadow-sm' :
                                'bg-emerald-50 text-emerald-900 border border-emerald-200 hover:bg-emerald-100'"
                            class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[58px] col-span-2 sm:col-span-2 active:scale-95">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold"
                                    :class="newOrder.tier === 'galon_baru_isi' ? 'text-emerald-100' : 'text-emerald-700'">Galon
                                    Baru + Isi</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded font-extrabold uppercase"
                                    :class="newOrder.tier === 'galon_baru_isi' ? 'bg-white/20 text-white' :
                                        'bg-emerald-200 text-emerald-800'">Beli
                                    Putus</span>
                            </div>
                            <span class="text-sm font-black">Rp 40.000</span>
                        </button>

                    </div>
                </div>

                <!-- 4. OPSI GALON FISIK -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">
                        Opsi Galon Fisik Pelanggan
                        <span x-show="newOrder.productType === 'galon_baru'"
                            class="text-slate-400 font-normal">(Terkunci: Beli Putus)</span>
                    </label>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <!-- Tukar 1:1 -->
                        <button type="button" :disabled="newOrder.productType === 'galon_baru'"
                            @click="newOrder.gallonAction = 'tukar_seimbang'"
                            :class="newOrder.gallonAction === 'tukar_seimbang' ?
                                'bg-emerald-50 border-emerald-500 text-emerald-900 font-black ring-1 ring-emerald-500' :
                                'bg-slate-50 border-slate-200 text-slate-600'"
                            class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px] disabled:opacity-40 disabled:pointer-events-none">
                            <i class="fa-solid fa-arrows-rotate text-emerald-600"></i>
                            <span>Tukar 1:1</span>
                        </button>

                        <!-- Pinjam -->
                        <button type="button" :disabled="newOrder.productType === 'galon_baru'"
                            @click="newOrder.gallonAction = 'pinjam'"
                            :class="newOrder.gallonAction === 'pinjam' ?
                                'bg-amber-50 border-amber-500 text-amber-900 font-black ring-1 ring-amber-500' :
                                'bg-slate-50 border-slate-200 text-slate-600'"
                            class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px] disabled:opacity-40 disabled:pointer-events-none">
                            <i class="fa-solid fa-hand-holding text-amber-600"></i>
                            <span>Pinjam (+Gp)</span>
                        </button>

                        <!-- Kembalikan -->
                        <button type="button" :disabled="newOrder.productType === 'galon_baru'"
                            @click="newOrder.gallonAction = 'kembalikan'"
                            :class="newOrder.gallonAction === 'kembalikan' ?
                                'bg-purple-50 border-purple-500 text-purple-900 font-black ring-1 ring-purple-500' :
                                'bg-slate-50 border-slate-200 text-slate-600'"
                            class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px] disabled:opacity-40 disabled:pointer-events-none">
                            <i class="fa-solid fa-rotate-left text-purple-600"></i>
                            <span>Kembali (-Gp)</span>
                        </button>

                        <!-- Opsi ke-4: Dinamis (Bawa Sendiri vs Beli Putus) -->
                        <button type="button" :disabled="newOrder.productType === 'galon_baru'"
                            @click="newOrder.gallonAction = 'tidak_ada'"
                            :class="newOrder.gallonAction === 'tidak_ada' ?
                                'bg-slate-200 border-slate-400 text-slate-900 font-black' :
                                'bg-slate-50 border-slate-200 text-slate-600'"
                            class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px] disabled:opacity-90">
                            <!-- Icon ikut menyesuaikan -->
                            <i class="fa-solid text-slate-500"
                                :class="newOrder.productType === 'galon_baru' ? 'fa-bag-shopping' : 'fa-minus'"></i>
                            <!-- Teks dinamis -->
                            <span
                                x-text="newOrder.productType === 'galon_baru' ? 'Beli Putus' : 'Bawa Sendiri'"></span>
                        </button>
                    </div>
                </div>

                <!-- 5. Kuantitas Galon -->
                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-600 mb-1">Jumlah Galon</label>
                    <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-2xl">
                        <button type="button" @click="if (newOrder.qty > 1) newOrder.qty--"
                            class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <input type="number" min="1" x-model.number="newOrder.qty"
                            @blur="if (!newOrder.qty || newOrder.qty < 1) newOrder.qty = 1"
                            class="w-full text-center font-black text-base text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0">
                        <button type="button" @click="newOrder.qty++"
                            class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Error Validasi Interaktif -->
                <div x-show="formValidationError"
                    class="p-2.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                    <span x-text="formValidationMessage"></span>
                </div>

                <!-- Ringkasan Total & Tombol Proses -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-[11px] text-slate-400 font-semibold block">Total Tagihan</span>
                        <span class="text-xl font-black text-slate-900"
                            x-text="rupiah(newOrder.unitPrice * newOrder.qty)">Rp 0</span>
                    </div>

                    <button type="button" @click="processNewOrder()" :disabled="formValidationError"
                        class="min-h-[46px] px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-98 disabled:opacity-40 disabled:pointer-events-none text-white font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2">
                        <span>Simpan Transaksi</span>
                        <i class="fa-solid fa-check text-xs"></i>
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 6. QUICK ADD CUSTOMER MODAL                                               -->
    <!-- ========================================================================= -->
    <div x-show="openQuickAddModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-5 shadow-xl border border-slate-200"
            @click.away="openQuickAddModal = false">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900">Tambah Pelanggan Baru</h3>
                <button type="button" @click="openQuickAddModal = false"
                    class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form @submit.prevent="saveQuickCustomer()" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Nama Pelanggan <span
                            class="text-rose-500">*</span></label>
                    <input type="text" x-model="newCustomer.name" placeholder="Mis. Warung Bu Sri" required
                        class="w-full min-h-[42px] px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 font-semibold text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">No. WhatsApp <span
                            class="text-rose-500">*</span></label>
                    <input type="tel" x-model="newCustomer.whatsapp" placeholder="08xxxxxxxxxx" required
                        class="w-full min-h-[42px] px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 font-semibold text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Alamat Lengkap</label>
                    <textarea x-model="newCustomer.address" rows="2" placeholder="Jl. Rajawali No. 12"
                        class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 font-semibold text-slate-800 resize-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openQuickAddModal = false"
                        class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl">Batal</button>
                    <button type="submit"
                        class="px-5 py-2 text-xs font-extrabold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 7. TOAST NOTIFIKASI BERHASIL                                              -->
    <!-- ========================================================================= -->
    <div x-show="showToast" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 border border-slate-800">
        <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs">
            <i class="fa-solid fa-check"></i>
        </div>
        <div class="text-xs font-bold" x-text="toastMessage">Transaksi berhasil disimpan!</div>
    </div>

    <!-- ========================================================================= -->
    <!-- 8. APP LOGIC (ALPINE.JS)                                                  -->
    <!-- ========================================================================= -->
    <script>
        function posApp() {
            return {
                todayDate: '',
                tutupStock: 1000,
                totalGalonDepot: 50,
                searchQuery: '',
                statusFilter: 'all',

                openOrderModal: false,
                openQuickAddModal: false,
                showToast: false,
                toastMessage: '',

                customers: [{
                        id: 1,
                        name: 'Pak Budi Santoso',
                        whatsapp: '081234567890',
                        address: 'Jl. Melati No. 4',
                        borrowed_gallons: 2
                    },
                    {
                        id: 2,
                        name: 'Bu Siti Rahma',
                        whatsapp: '081987654321',
                        address: 'Komplek Griya Asri Blok A',
                        borrowed_gallons: 0
                    },
                    {
                        id: 3,
                        name: 'Kedai Kopi Berkah',
                        whatsapp: '085211223344',
                        address: 'Pasar Baru No. 8',
                        borrowed_gallons: 5
                    }
                ],

                orders: [{
                        id: 1,
                        order_number: 'SW-10021',
                        time: '10:15',
                        order_type: 'ambil_sendiri',
                        delivery_status: 'selesai',
                        customer_name: 'Pelanggan Umum',
                        customer_address: '-',
                        tier_name: 'Harga Letak Kedai',
                        product_type: 'isi_ulang',
                        qty: 2,
                        total_amount: 10000,
                        gallon_action: 'tukar_seimbang',
                        payment_status: 'lunas'
                    },
                    {
                        id: 2,
                        order_number: 'SW-10022',
                        time: '10:45',
                        order_type: 'pesan_antar',
                        delivery_status: 'pending',
                        customer_name: 'Kedai Kopi Berkah',
                        customer_address: 'Pasar Baru No. 8',
                        tier_name: 'Harga Antar Dekat',
                        product_type: 'isi_ulang',
                        qty: 5,
                        total_amount: 30000,
                        gallon_action: 'pinjam',
                        payment_status: 'belum_lunas'
                    },
                    {
                        id: 3,
                        order_number: 'SW-10023',
                        time: '11:20',
                        order_type: 'ambil_sendiri',
                        delivery_status: 'selesai',
                        customer_name: 'Pak Budi Santoso',
                        customer_address: 'Jl. Melati No. 4',
                        tier_name: 'Galon Baru + Isi',
                        product_type: 'galon_baru',
                        qty: 1,
                        total_amount: 40000,
                        gallon_action: 'tidak_ada',
                        payment_status: 'lunas'
                    }
                ],

                newOrder: {
                    orderType: 'ambil_sendiri',
                    customerId: '',
                    tier: 'letak_kedai',
                    tierName: 'Harga Letak Kedai',
                    unitPrice: 5000,
                    productType: 'isi_ulang',
                    gallonAction: 'tukar_seimbang',
                    qty: 1,
                    paymentMethod: 'tunai',
                },

                newCustomer: {
                    name: '',
                    whatsapp: '',
                    address: ''
                },

                init() {
                    const now = new Date();
                    this.todayDate = now.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                },

                rupiah(val) {
                    return 'Rp ' + (val || 0).toLocaleString('id-ID');
                },

                formatGallonAction(action, qty, productType = 'isi_ulang') {
                    switch (action) {
                        case 'tukar_seimbang':
                            return '• Tukar 1:1';
                        case 'pinjam':
                            return '• Pinjam (+' + qty + ' Galon)';
                        case 'kembalikan':
                            return '• Kembali (-' + qty + ' Galon)';
                        default:
                            return productType === 'galon_baru' ? '• Beli Putus' : '• Bawa Sendiri';
                    }
                },

                get selectedCustomer() {
                    return this.customers.find(c => c.id == this.newOrder.customerId) || null;
                },

                get filteredOrders() {
                    return this.orders.filter(o => {
                        const matchStatus = this.statusFilter === 'all' || o.delivery_status === this
                            .statusFilter;
                        const matchSearch = this.searchQuery === '' ||
                            o.order_number.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            o.customer_name.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchStatus && matchSearch;
                    });
                },

                get formValidationError() {
                    if (this.newOrder.orderType === 'pesan_antar' && !this.newOrder.customerId) return true;
                    if ((this.newOrder.gallonAction === 'pinjam' || this.newOrder.gallonAction === 'kembalikan') && !
                        this.newOrder.customerId) return true;
                    if (this.newOrder.gallonAction === 'kembalikan' && this.selectedCustomer && this.newOrder.qty > this
                        .selectedCustomer.borrowed_gallons) return true;
                    if (this.newOrder.qty > this.tutupStock) return true;
                    return false;
                },

                get formValidationMessage() {
                    if (this.newOrder.orderType === 'pesan_antar' && !this.newOrder.customerId) {
                        return 'Pelanggan wajib dipilih untuk layanan Pesan Antar!';
                    }
                    if ((this.newOrder.gallonAction === 'pinjam' || this.newOrder.gallonAction === 'kembalikan') && !
                        this.newOrder.customerId) {
                        return 'Pelanggan terdaftar wajib dipilih untuk Pinjam / Kembalikan galon!';
                    }
                    if (this.newOrder.gallonAction === 'kembalikan' && this.selectedCustomer && this.newOrder.qty > this
                        .selectedCustomer.borrowed_gallons) {
                        return 'Jumlah pengembalian (' + this.newOrder.qty + ') melebihi galon yang dipinjam (' + this
                            .selectedCustomer.borrowed_gallons + ')!';
                    }
                    if (this.newOrder.qty > this.tutupStock) {
                        return 'Stok tutup galon tidak mencukupi (Tersisa: ' + this.tutupStock + ')!';
                    }
                    return '';
                },

                selectTier(code, price, name, productType) {
                    this.newOrder.tier = code;
                    this.newOrder.unitPrice = price;
                    this.newOrder.tierName = name;
                    this.newOrder.productType = productType;

                    if (productType === 'galon_baru') {
                        this.newOrder.gallonAction = 'tidak_ada';
                    } else if (this.newOrder.gallonAction === 'tidak_ada') {
                        this.newOrder.gallonAction = 'tukar_seimbang';
                    }
                },

                processNewOrder() {
                    if (this.formValidationError) return;

                    const now = new Date();
                    const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2,
                        '0');
                    const totalAmount = this.newOrder.unitPrice * this.newOrder.qty;

                    // Potong stok tutup di sisi lokal
                    this.tutupStock -= this.newOrder.qty;

                    // Update pinjaman galon jika terdaftar
                    if (this.selectedCustomer) {
                        if (this.newOrder.gallonAction === 'pinjam') {
                            this.selectedCustomer.borrowed_gallons += this.newOrder.qty;
                        } else if (this.newOrder.gallonAction === 'kembalikan') {
                            this.selectedCustomer.borrowed_gallons -= this.newOrder.qty;
                        }
                    }

                    // Potong armada jika beli galon baru
                    if (this.newOrder.productType === 'galon_baru') {
                        this.totalGalonDepot -= this.newOrder.qty;
                    }

                    // Tambah order ke baris paling atas tabel
                    // Di dalam this.orders.unshift({ ... })
                    this.orders.unshift({
                        id: Date.now(),
                        order_number: 'SW-' + Math.floor(10000 + Math.random() * 90000),
                        time: timeStr,
                        order_type: this.newOrder.orderType,
                        delivery_status: this.newOrder.orderType === 'ambil_sendiri' ? 'selesai' : 'pending',
                        payment_status: this.newOrder.orderType === 'ambil_sendiri' ? 'lunas' :
                        'belum_lunas', // <-- Tambahkan baris ini
                        customer_name: this.selectedCustomer ? this.selectedCustomer.name : 'Pelanggan Umum',
                        customer_address: this.selectedCustomer ? this.selectedCustomer.address : '-',
                        tier_name: this.newOrder.tierName,
                        product_type: this.newOrder.productType,
                        qty: this.newOrder.qty,
                        total_amount: totalAmount,
                        gallon_action: this.newOrder.gallonAction,
                    });

                    // Notifikasi & tutup modal
                    this.openOrderModal = false;
                    this.triggerToast('Transaksi ' + this.newOrder.tierName + ' berhasil disimpan!');

                    // Reset state
                    this.newOrder.qty = 1;
                    this.newOrder.customerId = '';
                    this.selectTier('letak_kedai', 5000, 'Harga Letak Kedai', 'isi_ulang');
                },

                saveQuickCustomer() {
                    if (!this.newCustomer.name || !this.newCustomer.whatsapp) return;
                    const newId = this.customers.length + 1;
                    const cust = {
                        id: newId,
                        name: this.newCustomer.name,
                        whatsapp: this.newCustomer.whatsapp,
                        address: this.newCustomer.address?.trim() || '-',
                        borrowed_gallons: 0
                    };
                    this.customers.push(cust);
                    this.newOrder.customerId = newId;
                    this.openQuickAddModal = false;
                    this.newCustomer = {
                        name: '',
                        whatsapp: '',
                        address: ''
                    };
                    this.triggerToast('Pelanggan ' + cust.name + ' berhasil ditambahkan!');
                },

                triggerToast(msg) {
                    this.toastMessage = msg;
                    this.showToast = true;
                    setTimeout(() => {
                        this.showToast = false;
                    }, 3000);
                }
            };
        }
    </script>
</body>

</html>
