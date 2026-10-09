<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS Kasir - Sulthan Water</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Asset Lokal Ringan (Vite: Tailwind CSS & Alpine.js terkompilasi offline) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
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

        @media print {
            body * { visibility: hidden; }
            #receipt-modal, #receipt-modal * { visibility: visible; }
            #receipt-modal { position: absolute; left: 0; top: 0; width: 100%; margin: 0; }
        }
    </style>
</head>

<body class="h-full flex flex-col md:flex-row text-slate-800 antialiased overflow-hidden select-none"
      x-data="posApp()">

    <!-- ========================================================================= -->
    <!-- 1. LEFT SIDEBAR NAVIGATION (PERSIS REFERENSI GAMBAR)                      -->
    <!-- ========================================================================= -->
    <aside class="w-full md:w-20 lg:w-22 bg-white border-r border-slate-200/80 flex md:flex-col items-center justify-between py-3 px-4 md:py-6 md:px-0 shrink-0 z-30 shadow-xs">
        
        <!-- Top: Logo -->
        <div class="flex items-center gap-3 md:flex-col">
            <a href="{{ route('pos.dashboard') }}" class="w-11 h-11 rounded-2xl bg-white border border-slate-200 p-1 flex items-center justify-center shadow-xs hover:scale-105 transition overflow-hidden" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px;">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="w-full h-full object-contain" style="max-width: 36px; max-height: 36px; object-fit: contain;"
                     onerror="this.onerror=null; this.src='https://placehold.co/80x80/2563eb/ffffff?text=SW';">
            </a>
        </div>

        <!-- Middle: Nav Icons -->
        <nav class="flex md:flex-col items-center gap-2 sm:gap-3">
            <!-- Mode Walk-in (Ambil Sendiri) -->
            <button type="button" 
                    @click="orderType = 'ambil_sendiri'"
                    title="Ambil Sendiri (Walk-in)"
                    class="w-11 h-11 rounded-2xl flex items-center justify-center transition active:scale-95"
                    :class="orderType === 'ambil_sendiri' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'">
                <i class="fa-solid fa-house text-base"></i>
            </button>

            <!-- Mode Pesan Antar (Kurir / Motor) -->
            <button type="button" 
                    @click="orderType = 'pesan_antar'"
                    title="Pesan Antar (Kurir)"
                    class="w-11 h-11 rounded-2xl flex items-center justify-center transition active:scale-95"
                    :class="orderType === 'pesan_antar' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'">
                <i class="fa-solid fa-motorcycle text-base"></i>
            </button>

            <!-- Cart Toggle on Mobile / Tablet -->
            <button type="button" 
                    @click="mobileCartOpen = !mobileCartOpen"
                    class="md:hidden relative w-11 h-11 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center">
                <i class="fa-solid fa-bag-shopping text-base"></i>
                <span x-show="cart.length > 0" 
                      class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] font-extrabold flex items-center justify-center"
                      x-text="totalGalonQty">0</span>
            </button>
        </nav>

        <!-- Bottom: User & Logout -->
        <div class="flex md:flex-col items-center gap-2.5">
            <div class="relative w-10 h-10 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-black text-slate-700" title="{{ auth()->user()->name ?? 'Kasir Depot' }}">
                {{ strtoupper(substr(auth()->user()->name ?? 'K', 0, 1)) }}
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" title="Keluar / Logout" class="w-10 h-10 rounded-2xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition">
                    <i class="fa-solid fa-power-off text-sm"></i>
                </button>
            </form>
        </div>

    </aside>

    <!-- ========================================================================= -->
    <!-- 2. MAIN CONTENT AREA (PEMBERSIHAN & PENYELARASAN DESAIN BEHANCE)           -->
    <!-- ========================================================================= -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <!-- Top Bar: Greeting, Category Pills & Quick Search -->
        <header class="px-5 lg:px-8 py-4 sm:py-5 bg-white md:bg-transparent border-b md:border-b-0 border-slate-200/80 shrink-0">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                
                <!-- Greeting & Date -->
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Welcome back, <span class="text-blue-600">{{ auth()->user()->name ?? 'Kasir' }}</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 font-medium mt-0.5" x-text="todayDate">Selasa, 12 Juni 2026</p>
                </div>

                <!-- Top Category Pills (Persis Referensi Atas) -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 touch-scroll">
                    <!-- Semua Produk -->
                    <button type="button" 
                            @click="selectedCategory = 'all'"
                            :class="selectedCategory === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap flex items-center gap-2">
                        <span>Semua Produk</span>
                        <span class="text-[11px] opacity-70">2 Items</span>
                    </button>

                    <!-- Air Isi Ulang -->
                    <button type="button" 
                            @click="selectedCategory = 'isi_ulang'"
                            :class="selectedCategory === 'isi_ulang' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap flex items-center gap-2">
                        <span>Air Isi Ulang</span>
                        <span class="text-[11px] opacity-70">4 Tier</span>
                    </button>

                    <!-- Galon Baru + Isi -->
                    <button type="button" 
                            @click="selectedCategory = 'galon_baru'"
                            :class="selectedCategory === 'galon_baru' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap flex items-center gap-2">
                        <span>Galon Baru + Isi</span>
                        <span class="text-[11px] opacity-70">1 Item</span>
                    </button>

                    <!-- Order Type Pill Toggle -->
                    <button type="button" 
                            @click="orderType = orderType === 'ambil_sendiri' ? 'pesan_antar' : 'ambil_sendiri'"
                            class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap flex items-center gap-2 border"
                            :class="orderType === 'ambil_sendiri' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-amber-50 border-amber-200 text-amber-800'">
                        <i class="fa-solid" :class="orderType === 'ambil_sendiri' ? 'fa-store' : 'fa-motorcycle'"></i>
                        <span x-text="orderType === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Pesan Antar'">Ambil Sendiri</span>
                    </button>
                </div>

            </div>
        </header>

        <!-- Main Body: 4 KPI Cards Top + Split Content (Product Catalog & Quick POS Cart) -->
        <div class="flex-1 overflow-y-auto px-4 sm:px-5 lg:px-8 pb-28 lg:pb-6 touch-scroll">
            
            <!-- ================================================================= -->
            <!-- 3. TOP KPI METRICS BAR (PERSIS 4 KARTU PADA GAMBAR)                -->
            <!-- ================================================================= -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
                
                <!-- Card 1: Solid Blue Featured Card (Sales Today) -->
                <div class="bg-blue-600 rounded-3xl p-4 sm:p-5 text-white shadow-md shadow-blue-500/20 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs sm:text-sm font-semibold text-blue-100">Total Tagihan</span>
                        <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center">
                            <i class="fa-solid fa-bag-shopping text-white text-xs"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-xl sm:text-2xl font-black tracking-tight" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')">Rp 0</div>
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-bold" x-text="totalGalonQty + ' Galon'">0 Galon</span>
                            <span class="text-[11px] text-blue-100 truncate">Pesanan kasir aktif</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: White Metric Card (Orders) -->
                <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs sm:text-sm font-semibold text-slate-500">Jumlah Item</span>
                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                            <i class="fa-solid fa-list-check text-xs"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-xl sm:text-2xl font-black text-slate-900" x-text="cart.length + ' Jenis'">0 Jenis</div>
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold" x-text="orderType === 'ambil_sendiri' ? 'Walk-in' : 'Antar'">Walk-in</span>
                            <span class="text-[11px] text-slate-400">Siap diproses</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: White Metric Card (Low Stock Alert Tutup Galon - BR-INV-01) -->
                <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs sm:text-sm font-semibold text-slate-500">Stok Tutup Galon</span>
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                             :class="tutupStock <= 600 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'">
                            <i class="fa-solid fa-box text-xs"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-xl sm:text-2xl font-black text-slate-900" x-text="tutupStock.toLocaleString('id-ID') + ' pcs'">1.000 pcs</div>
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                                  :class="tutupStock <= 600 ? 'bg-rose-100 text-rose-700 animate-pulse' : 'bg-emerald-100 text-emerald-800'"
                                  x-text="tutupStock <= 600 ? 'Kritis!' : 'Aman'">Aman</span>
                            <span class="text-[11px] text-slate-400">Ambang: 600 pcs</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: White Metric Card (Armada Galon Depot - BR-INV-02) -->
                <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs sm:text-sm font-semibold text-slate-500">Armada Galon Depot</span>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-bottle-water text-xs"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-xl sm:text-2xl font-black text-slate-900" x-text="totalGalonDepot + ' Unit'">50 Unit</div>
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-extrabold">Standby</span>
                            <span class="text-[11px] text-slate-400">Total aset depot</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ================================================================= -->
            <!-- 4. TWO-COLUMN POS WORKSPACE: KATALOG PRODUK & QUICK POS SUMMARY   -->
            <!-- ================================================================= -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

                <!-- ------------------------------------------------------------- -->
                <!-- KOLOM KIRI (7/12): KATALOG PRODUK TOUCH-FRIENDLY               -->
                <!-- ------------------------------------------------------------- -->
                <div class="lg:col-span-7 space-y-4">
                    
                    <!-- KARTU PRODUK 1: AIR ISI ULANG -->
                    <div x-show="selectedCategory === 'all' || selectedCategory === 'isi_ulang'"
                         class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition">
                        
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-13 h-13 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shrink-0">
                                    <i class="fa-solid fa-faucet-drip"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-black text-slate-900 leading-tight">Air Isi Ulang</h3>
                                        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-blue-100 text-blue-800 rounded-md">Refill</span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5">Air rebusan matang 100°C higienis & sehat</p>
                                </div>
                            </div>
                        </div>

                        <!-- 4 Pilihan Tier Harga (Tombol Pill Besar & Ergonomis) -->
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Pilih Tier Harga:</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                
                                <button type="button" 
                                        @click="refillForm.tier = 'sosial'; refillForm.price = 4000; refillForm.tierName = 'Harga Sosial'"
                                        :class="refillForm.tier === 'sosial' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200/70'"
                                        class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[56px] active:scale-95">
                                    <span class="text-[11px] font-medium" :class="refillForm.tier === 'sosial' ? 'text-blue-100' : 'text-slate-500'">Sosial</span>
                                    <span class="text-sm font-black">Rp 4.000</span>
                                </button>

                                <button type="button" 
                                        @click="refillForm.tier = 'letak_kedai'; refillForm.price = 5000; refillForm.tierName = 'Harga Letak Kedai'"
                                        :class="refillForm.tier === 'letak_kedai' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200/70'"
                                        class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[56px] active:scale-95">
                                    <span class="text-[11px] font-medium" :class="refillForm.tier === 'letak_kedai' ? 'text-blue-100' : 'text-slate-500'">Letak Kedai</span>
                                    <span class="text-sm font-black">Rp 5.000</span>
                                </button>

                                <button type="button" 
                                        @click="refillForm.tier = 'antar_dekat'; refillForm.price = 6000; refillForm.tierName = 'Harga Antar Dekat'"
                                        :class="refillForm.tier === 'antar_dekat' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200/70'"
                                        class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[56px] active:scale-95">
                                    <span class="text-[11px] font-medium" :class="refillForm.tier === 'antar_dekat' ? 'text-blue-100' : 'text-slate-500'">Antar Dekat</span>
                                    <span class="text-sm font-black">Rp 6.000</span>
                                </button>

                                <button type="button" 
                                        @click="refillForm.tier = 'antar_jauh'; refillForm.price = 7000; refillForm.tierName = 'Harga Antar Jauh'"
                                        :class="refillForm.tier === 'antar_jauh' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200/70'"
                                        class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[56px] active:scale-95">
                                    <span class="text-[11px] font-medium" :class="refillForm.tier === 'antar_jauh' ? 'text-blue-100' : 'text-slate-500'">Antar Jauh</span>
                                    <span class="text-sm font-black">Rp 7.000</span>
                                </button>

                            </div>
                        </div>

                        <!-- Opsi Galon Fisik Pelanggan (BR-INV-02) -->
                        <div class="mt-4">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Opsi Galon Fisik Pelanggan:</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                
                                <button type="button" 
                                        @click="refillForm.gallonAction = 'tukar_seimbang'"
                                        :class="refillForm.gallonAction === 'tukar_seimbang' ? 'bg-emerald-50 border-emerald-500 text-emerald-900 font-extrabold ring-1 ring-emerald-500' : 'bg-slate-50 border-slate-200 text-slate-600'"
                                        class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px]">
                                    <i class="fa-solid fa-arrows-rotate text-emerald-600"></i>
                                    <span>Tukar 1:1</span>
                                </button>

                                <button type="button" 
                                        @click="refillForm.gallonAction = 'pinjam'"
                                        :class="refillForm.gallonAction === 'pinjam' ? 'bg-amber-50 border-amber-500 text-amber-900 font-extrabold ring-1 ring-amber-500' : 'bg-slate-50 border-slate-200 text-slate-600'"
                                        class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px]">
                                    <i class="fa-solid fa-hand-holding text-amber-600"></i>
                                    <span>Pinjam (+Gp)</span>
                                </button>

                                <button type="button" 
                                        @click="refillForm.gallonAction = 'kembalikan'"
                                        :class="refillForm.gallonAction === 'kembalikan' ? 'bg-purple-50 border-purple-500 text-purple-900 font-extrabold ring-1 ring-purple-500' : 'bg-slate-50 border-slate-200 text-slate-600'"
                                        class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px]">
                                    <i class="fa-solid fa-rotate-left text-purple-600"></i>
                                    <span>Kembali (-Gp)</span>
                                </button>

                                <button type="button" 
                                        @click="refillForm.gallonAction = 'tidak_ada'"
                                        :class="refillForm.gallonAction === 'tidak_ada' ? 'bg-slate-200 border-slate-400 text-slate-900 font-extrabold' : 'bg-slate-50 border-slate-200 text-slate-600'"
                                        class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px]">
                                    <i class="fa-solid fa-minus text-slate-500"></i>
                                    <span>Bawa Sendiri</span>
                                </button>

                            </div>
                        </div>

                        <!-- Stepper Kuantitas & Tombol Tambah -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-2xl">
                                <button type="button" @click="if (refillForm.qty > 1) refillForm.qty--" class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-bold text-sm shadow-xs active:bg-slate-200">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" 
                                       min="1" 
                                       x-model.number="refillForm.qty" 
                                       @blur="if (!refillForm.qty || refillForm.qty < 1) refillForm.qty = 1"
                                       class="w-14 text-center font-black text-base text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" @click="refillForm.qty++" class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-bold text-sm shadow-xs active:bg-slate-200">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>

                            <button type="button" 
                                    @click="addToCart('isi_ulang')"
                                    class="flex-1 min-h-[48px] px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-extrabold text-sm rounded-2xl shadow-sm transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-plus"></i>
                                <span>Tambah ke Pesanan</span>
                                <span class="opacity-80" x-text="'(' + rupiah(refillForm.price * refillForm.qty) + ')'"></span>
                            </button>
                        </div>

                    </div>

                    <!-- KARTU PRODUK 2: GALON BARU + ISI -->
                    <div x-show="selectedCategory === 'all' || selectedCategory === 'galon_baru'"
                         class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition">
                        
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shrink-0">
                                    <i class="fa-solid fa-bottle-water"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg font-black text-slate-900 leading-tight">Galon Baru + Isi</h3>
                                        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-emerald-100 text-emerald-800 rounded-md">Beli Putus</span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5">Unit galon baru tebal siap minum (Termasuk air)</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold text-slate-400 block">Harga Tetap</span>
                                <span class="text-lg font-black text-emerald-600">Rp 40.000</span>
                            </div>
                        </div>

                        <!-- Stepper Kuantitas & Tombol Tambah -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-2xl">
                                <button type="button" @click="if (newGallonForm.qty > 1) newGallonForm.qty--" class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-bold text-sm shadow-xs active:bg-slate-200">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" 
                                       min="1" 
                                       x-model.number="newGallonForm.qty" 
                                       @blur="if (!newGallonForm.qty || newGallonForm.qty < 1) newGallonForm.qty = 1"
                                       class="w-14 text-center font-black text-base text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" @click="newGallonForm.qty++" class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-bold text-sm shadow-xs active:bg-slate-200">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>

                            <button type="button" 
                                    @click="addToCart('galon_baru')"
                                    class="flex-1 min-h-[48px] px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white font-extrabold text-sm rounded-2xl shadow-sm transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-plus"></i>
                                <span>Tambah ke Pesanan</span>
                                <span class="opacity-80" x-text="'(' + rupiah(40000 * newGallonForm.qty) + ')'"></span>
                            </button>
                        </div>

                    </div>

                </div>

                <!-- ------------------------------------------------------------- -->
                <!-- KOLOM KANAN (5/12): QUICK POS PANEL (PERSIS GAYA BEHANCE)     -->
                <!-- ------------------------------------------------------------- -->
                <div class="lg:col-span-5"
                     :class="mobileCartOpen ? 'fixed inset-0 z-50 p-0 sm:p-4 bg-slate-900/50 backdrop-blur-xs flex items-end sm:items-center justify-center' : 'hidden lg:block'">
                    
                    <div class="bg-white rounded-t-3xl sm:rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-2xl flex flex-col justify-between max-w-lg w-full max-h-[88dvh] sm:max-h-[90vh] overflow-y-auto touch-scroll">
                        
                        <div>
                            <!-- Mobile Bottom Sheet Drag Handle Indicator -->
                            <div class="sm:hidden w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-3"></div>
                            <!-- Header Quick POS -->
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div>
                                    <h2 class="text-base font-black text-slate-900">Quick POS</h2>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="'No. Transaksi #' + orderNumber">#TRX-001</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" 
                                            @click="resetCart()"
                                            :disabled="cart.length === 0"
                                            title="Kosongkan Pesanan"
                                            class="w-9 h-9 rounded-xl bg-slate-50 text-slate-400 hover:text-rose-600 flex items-center justify-center disabled:opacity-30">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                    <button type="button" 
                                            @click="mobileCartOpen = false" 
                                            class="lg:hidden w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Pilih Pelanggan (Minimalis) -->
                            <div class="mt-4">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-slate-600">Pelanggan</span>
                                    <button type="button" @click="openQuickAddModal = true" class="text-xs font-extrabold text-blue-600 hover:underline">
                                        + Tambah Baru
                                    </button>
                                </div>
                                <select x-model="selectedCustomerId" 
                                        class="w-full min-h-[44px] px-3.5 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-2xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500">
                                    <option value="">👤 Pelanggan Umum (Walk-in)</option>
                                    <template x-for="c in customers" :key="c.id">
                                        <option :value="c.id" x-text="c.name"></option>
                                    </template>
                                </select>

                                <!-- Status Pinjaman Pelanggan Terpilih -->
                                <template x-if="selectedCustomer">
                                    <div class="mt-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                                        <span class="text-slate-500" x-text="selectedCustomer.whatsapp"></span>
                                        <template x-if="selectedCustomer.borrowed_gallons > 0">
                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-bold">
                                                Pinjam: <strong x-text="selectedCustomer.borrowed_gallons"></strong> Galon
                                            </span>
                                        </template>
                                        <template x-if="selectedCustomer.borrowed_gallons === 0">
                                            <span class="text-[11px] text-slate-400 font-medium">Tidak ada pinjaman galon</span>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <!-- List Item Pesanan (Clean Item Card Style ala Behance) -->
                            <div class="mt-4 space-y-2.5 max-h-56 overflow-y-auto touch-scroll pr-1">
                                
                                <div x-show="cart.length === 0" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-bag-shopping text-2xl mb-2 text-slate-300"></i>
                                    <p class="text-xs font-semibold">Belum ada item dipilih</p>
                                </div>

                                <template x-for="(item, index) in cart" :key="index">
                                    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100 gap-2">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="text-xs font-bold text-slate-800 truncate" x-text="item.name"></h4>
                                                <span class="text-[10px] font-extrabold px-1.5 py-0.2 rounded bg-blue-100 text-blue-700" x-text="item.tierName"></span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                                <span x-text="rupiah(item.unitPrice)"></span>
                                                <template x-if="item.gallonAction === 'pinjam'">
                                                    <span class="text-amber-600 font-bold">• Pinjam (+<span x-text="item.qty"></span>)</span>
                                                </template>
                                                <template x-if="item.gallonAction === 'kembalikan'">
                                                    <span class="text-purple-600 font-bold">• Kembali (-<span x-text="item.qty"></span>)</span>
                                                </template>
                                                <template x-if="item.gallonAction === 'tukar_seimbang'">
                                                    <span class="text-emerald-600 font-bold">• Tukar 1:1</span>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Stepper & Price -->
                                        <div class="flex items-center gap-2 shrink-0">
                                            <div class="flex items-center gap-1 bg-white p-0.5 rounded-xl border border-slate-200">
                                                <button type="button" @click="decreaseCartQty(index)" class="w-6 h-6 rounded-lg text-slate-600 flex items-center justify-center text-xs">
                                                    <i class="fa-solid fa-minus"></i>
                                                </button>
                                                <input type="number" 
                                                       min="1" 
                                                       x-model.number="item.qty" 
                                                       @blur="if (!item.qty || item.qty < 1) item.qty = 1"
                                                       class="w-7 text-center font-black text-xs text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                                <button type="button" @click="increaseCartQty(index)" class="w-6 h-6 rounded-lg text-slate-600 flex items-center justify-center text-xs">
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                            </div>

                                            <div class="text-right w-16">
                                                <span class="text-xs font-black text-slate-900" x-text="rupiah(item.unitPrice * item.qty)"></span>
                                            </div>

                                            <button type="button" @click="removeCartItem(index)" class="text-slate-400 hover:text-rose-600 p-1">
                                                <i class="fa-solid fa-xmark text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                            </div>

                            <!-- Peringatan Validasi Interaktif Pelanggan -->
                            <div x-show="customerValidationError" class="mt-3 p-2 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                                <span x-text="customerValidationMessage"></span>
                            </div>

                            <!-- Metode Pembayaran -->
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" 
                                            @click="paymentMethod = 'tunai'; cashReceived = grandTotal"
                                            :class="paymentMethod === 'tunai' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-50 text-slate-600 border border-slate-200'"
                                            class="min-h-[42px] p-2 rounded-2xl text-xs flex items-center justify-center gap-1.5 transition">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                        <span>Tunai</span>
                                    </button>
                                    <button type="button" 
                                            @click="paymentMethod = 'qris'"
                                            :class="paymentMethod === 'qris' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-50 text-slate-600 border border-slate-200'"
                                            class="min-h-[42px] p-2 rounded-2xl text-xs flex items-center justify-center gap-1.5 transition">
                                        <i class="fa-solid fa-qrcode"></i>
                                        <span>QRIS</span>
                                    </button>
                                </div>

                                <!-- Input Tunai & Kembalian -->
                                <div x-show="paymentMethod === 'tunai'" class="mt-3 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-500 font-medium">Uang Diterima:</span>
                                        <input type="number" 
                                               x-model.number="cashReceived" 
                                               class="w-28 text-right font-black text-xs px-2.5 py-1.5 rounded-xl border border-slate-300 bg-white"
                                               placeholder="0">
                                    </div>
                                    <div class="flex justify-between text-xs font-bold">
                                        <span class="text-slate-500">Kembalian:</span>
                                        <span :class="changeAmount >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                                              x-text="changeAmount >= 0 ? rupiah(changeAmount) : 'Kurang ' + rupiah(Math.abs(changeAmount))">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Total & Checkout Button -->
                        <div class="mt-5 pt-3 border-t border-slate-100">
                            <div class="flex items-baseline justify-between mb-3">
                                <div>
                                    <span class="text-xs text-slate-400 block font-semibold">Total Tagihan</span>
                                    <span class="text-xs text-slate-500" x-text="totalGalonQty + ' Galon (-' + totalGalonQty + ' Tutup)'"></span>
                                </div>
                                <span class="text-2xl font-black text-slate-900" x-text="rupiah(grandTotal)">Rp 0</span>
                            </div>

                            <button type="button" 
                                    @click="processTransaction()"
                                    :disabled="cart.length === 0 || (paymentMethod === 'tunai' && cashReceived < grandTotal) || customerValidationError"
                                    class="w-full min-h-[50px] py-3 px-5 bg-blue-600 hover:bg-blue-700 active:scale-98 disabled:opacity-40 disabled:pointer-events-none text-white font-black text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2">
                                <span>Bayar Sekarang</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>
                        </div>

                    </div>
                </div>

            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- STICKY THUMB-ZONE BOTTOM ACTION BAR (KHUSUS MOBILE - JANGKAUAN JEMPOL)    -->
        <!-- ========================================================================= -->
        <div x-show="!mobileCartOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-6"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="fixed bottom-0 inset-x-0 z-40 p-3 bg-white/95 backdrop-blur-md border-t border-slate-200/80 shadow-2xl lg:hidden">
            <div class="max-w-md mx-auto flex items-center justify-between gap-3">
                
                <!-- Kiri: Info Ringkas Status Pesanan & Saklar Mode Cepat -->
                <div class="min-w-0 flex items-center gap-2.5">
                    <button type="button" 
                            @click="orderType = orderType === 'ambil_sendiri' ? 'pesan_antar' : 'ambil_sendiri'"
                            class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 border transition active:scale-95 shadow-xs"
                            :class="orderType === 'ambil_sendiri' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-amber-50 border-amber-200 text-amber-700'"
                            title="Ganti Mode Pesanan">
                        <i class="fa-solid" :class="orderType === 'ambil_sendiri' ? 'fa-store text-base' : 'fa-motorcycle text-base'"></i>
                    </button>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400" x-text="orderType === 'ambil_sendiri' ? 'Walk-in' : 'Antar Kurir'"></span>
                            <template x-if="cart.length > 0">
                                <span class="px-1.5 py-0.2 bg-blue-100 text-blue-800 text-[10px] font-extrabold rounded-md" x-text="totalGalonQty + ' Galon'"></span>
                            </template>
                        </div>
                        <div class="text-sm font-black text-slate-900 leading-tight" x-text="cart.length > 0 ? rupiah(grandTotal) : 'Keranjang Kosong'"></div>
                    </div>
                </div>

                <!-- Kanan: Tombol Thumb-Friendly Buka Quick POS / Checkout -->
                <button type="button" 
                        @click="mobileCartOpen = true"
                        class="min-h-[46px] px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/25 flex items-center gap-2 active:scale-95 transition shrink-0">
                    <i class="fa-solid fa-cart-shopping text-xs"></i>
                    <span>Quick POS</span>
                    <span x-show="cart.length > 0" class="w-5 h-5 rounded-full bg-white text-blue-600 text-[10px] font-black flex items-center justify-center" x-text="cart.length"></span>
                </button>
            </div>
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- 5. QUICK ADD CUSTOMER MODAL                                               -->
    <!-- ========================================================================= -->
    <div x-show="openQuickAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-5 shadow-xl border border-slate-200" @click.away="openQuickAddModal = false">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900">Tambah Pelanggan Baru</h3>
                <button type="button" @click="openQuickAddModal = false" class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form @submit.prevent="saveQuickCustomer()" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Nama Pelanggan / Usaha <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="newCustomer.name" placeholder="Mis. Kedai Berkah / Pak Anton" required class="w-full min-h-[42px] px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 font-semibold text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">No. WhatsApp <span class="text-rose-500">*</span></label>
                    <input type="tel" x-model="newCustomer.whatsapp" placeholder="08xxxxxxxxxx" required class="w-full min-h-[42px] px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 font-semibold text-slate-800">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-600">Alamat & Patokan Rute</label>
                        <span class="text-[10px] font-bold" :class="orderType === 'pesan_antar' ? 'text-rose-500' : 'text-slate-400'" x-text="orderType === 'pesan_antar' ? '*Wajib untuk Antar' : 'Opsional'"></span>
                    </div>
                    <textarea x-model="newCustomer.address" :required="orderType === 'pesan_antar'" rows="2" placeholder="Jl. Melati No. 4 (Depan Masjid)" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 font-semibold text-slate-800 resize-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openQuickAddModal = false" class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-extrabold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 6. RECEIPT MODAL                                                          -->
    <!-- ========================================================================= -->
    <div x-show="openReceiptModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4">
        <div id="receipt-modal" class="bg-white rounded-3xl max-w-sm w-full p-5 sm:p-6 shadow-xl border border-slate-200 text-center" @click.away="openReceiptModal = false">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-xl mb-3">
                <i class="fa-solid fa-check"></i>
            </div>
            <h3 class="text-base font-black text-slate-900">Transaksi Selesai</h3>
            <p class="text-xs text-slate-400" x-text="'No. Order: #' + lastOrderData.orderNumber"></p>

            <!-- Info Header Struk -->
            <div class="mt-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 text-left text-xs space-y-1">
                <div class="flex justify-between"><span class="text-slate-400">Tipe Pesanan:</span><span class="font-bold text-slate-800" x-text="lastOrderData.orderType"></span></div>
                <div class="flex justify-between"><span class="text-slate-400">Pelanggan:</span><span class="font-bold text-slate-800" x-text="lastOrderData.customerName"></span></div>
                <div class="flex justify-between"><span class="text-slate-400">Metode Bayar:</span><span class="font-bold text-slate-800 uppercase" x-text="lastOrderData.paymentMethod"></span></div>
            </div>

            <!-- Daftar Rincian Seluruh Item Pesanan (Bisa Banyak Item) -->
            <div class="mt-2.5 p-3 rounded-2xl bg-slate-50 border border-slate-100 text-left text-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Rincian Item (<span x-text="(lastOrderData.items || []).length"></span>):</span>
                <div class="max-h-36 overflow-y-auto touch-scroll space-y-2 pr-1">
                    <template x-for="(item, idx) in (lastOrderData.items || [])" :key="idx">
                        <div class="flex justify-between items-start gap-2 border-b border-slate-200/50 pb-1.5 last:border-0 last:pb-0">
                            <div>
                                <div class="font-bold text-slate-800" x-text="item.name + ' (' + item.tierName + ')'"></div>
                                <div class="text-[11px] text-slate-500">
                                    <span x-text="item.qty + 'x @ ' + rupiah(item.unitPrice)"></span>
                                    <template x-if="item.gallonAction === 'pinjam'">
                                        <span class="text-amber-600 font-bold">• Pinjam</span>
                                    </template>
                                    <template x-if="item.gallonAction === 'kembalikan'">
                                        <span class="text-purple-600 font-bold">• Kembali</span>
                                    </template>
                                    <template x-if="item.gallonAction === 'tukar_seimbang'">
                                        <span class="text-emerald-600 font-bold">• Tukar 1:1</span>
                                    </template>
                                </div>
                            </div>
                            <span class="font-black text-slate-900 shrink-0" x-text="rupiah(item.unitPrice * item.qty)"></span>
                        </div>
                    </template>
                </div>

                <div class="mt-2.5 pt-2 border-t border-slate-200 flex justify-between items-baseline">
                    <span class="font-bold text-slate-700">Total Tagihan:</span>
                    <span class="font-black text-blue-600 text-sm" x-text="rupiah(lastOrderData.grandTotal)"></span>
                </div>

                <template x-if="lastOrderData.paymentMethod === 'tunai'">
                    <div class="mt-1 flex justify-between text-[11px] text-slate-500">
                        <span>Kembalian:</span>
                        <span class="font-bold text-emerald-600" x-text="rupiah(lastOrderData.changeAmount)"></span>
                    </div>
                </template>
            </div>

            <div class="mt-4 flex gap-2">
                <button type="button" @click="window.print()" class="flex-1 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold">Cetak</button>
                <button type="button" @click="openReceiptModal = false" class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-black">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 7. APP LOGIC (ALPINE.JS)                                                  -->
    <!-- ========================================================================= -->
    <script>
        function posApp() {
            return {
                todayDate: '',
                init() {
                    const now = new Date();
                    const opt = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    this.todayDate = now.toLocaleDateString('id-ID', opt);
                },

                rupiah(num) {
                    return 'Rp ' + (num || 0).toLocaleString('id-ID');
                },

                tutupStock: 1000,
                totalGalonDepot: 50,
                orderType: 'ambil_sendiri',
                selectedCategory: 'all',
                orderNumber: 'SW-' + Math.floor(100000 + Math.random() * 900000),
                mobileCartOpen: false,

                refillForm: {
                    tier: 'letak_kedai',
                    tierName: 'Harga Letak Kedai',
                    price: 5000,
                    gallonAction: 'tukar_seimbang',
                    qty: 1
                },
                newGallonForm: { qty: 1 },

                customers: [
                    { id: 1, name: 'Pak Budi Santoso', whatsapp: '081234567890', address: 'Jl. Melati No. 4', borrowed_gallons: 2 },
                    { id: 2, name: 'Bu Siti Rahma', whatsapp: '081987654321', address: 'Komplek Griya Asri', borrowed_gallons: 0 },
                    { id: 3, name: 'Kedai Kopi Berkah', whatsapp: '085211223344', address: 'Pasar Lama', borrowed_gallons: 5 }
                ],
                selectedCustomerId: '',
                get selectedCustomer() {
                    return this.customers.find(c => c.id == this.selectedCustomerId) || null;
                },

                cart: [],

                addToCart(type) {
                    if (type === 'isi_ulang') {
                        const existing = this.cart.find(i => i.productId === 1 && i.tier === this.refillForm.tier && i.gallonAction === this.refillForm.gallonAction);
                        if (existing) {
                            existing.qty += this.refillForm.qty;
                        } else {
                            this.cart.push({
                                productId: 1,
                                name: 'Air Isi Ulang',
                                tier: this.refillForm.tier,
                                tierName: this.refillForm.tierName,
                                unitPrice: this.refillForm.price,
                                gallonAction: this.refillForm.gallonAction,
                                qty: this.refillForm.qty
                            });
                        }
                        this.refillForm.qty = 1;
                    } else {
                        const existing = this.cart.find(i => i.productId === 2);
                        if (existing) {
                            existing.qty += this.newGallonForm.qty;
                        } else {
                            this.cart.push({
                                productId: 2,
                                name: 'Galon Baru + Isi',
                                tier: 'galon_baru_isi',
                                tierName: 'Galon Baru',
                                unitPrice: 40000,
                                gallonAction: 'tidak_ada',
                                qty: this.newGallonForm.qty
                            });
                        }
                        this.newGallonForm.qty = 1;
                    }
                },

                increaseCartQty(idx) { this.cart[idx].qty++; },
                decreaseCartQty(idx) {
                    if (this.cart[idx].qty > 1) this.cart[idx].qty--;
                    else this.removeCartItem(idx);
                },
                removeCartItem(idx) { this.cart.splice(idx, 1); },
                resetCart() { this.cart = []; this.cashReceived = 0; },

                get totalGalonQty() { return this.cart.reduce((s, i) => s + i.qty, 0); },
                get grandTotal() { return this.cart.reduce((s, i) => s + (i.unitPrice * i.qty), 0); },

                paymentMethod: 'tunai',
                cashReceived: 0,
                get changeAmount() { return this.cashReceived - this.grandTotal; },

                get requiresCustomer() {
                    if (this.orderType === 'pesan_antar') return true;
                    return this.cart.some(i => i.gallonAction === 'pinjam' || i.gallonAction === 'kembalikan');
                },
                get customerValidationError() {
                    return this.requiresCustomer && !this.selectedCustomerId;
                },
                get customerValidationMessage() {
                    if (this.orderType === 'pesan_antar') return 'Pelanggan wajib dipilih untuk Pesan Antar!';
                    return 'Pelanggan wajib dipilih untuk Pinjam / Kembalikan galon!';
                },

                openQuickAddModal: false,
                newCustomer: { name: '', whatsapp: '', address: '' },
                saveQuickCustomer() {
                    if (!this.newCustomer.name || !this.newCustomer.whatsapp) return;
                    if (this.orderType === 'pesan_antar' && !this.newCustomer.address?.trim()) {
                        alert('Alamat pengantaran wajib diisi untuk layanan pesan antar!');
                        return;
                    }
                    const newId = this.customers.length + 1;
                    this.customers.push({ 
                        id: newId, 
                        name: this.newCustomer.name, 
                        whatsapp: this.newCustomer.whatsapp, 
                        address: this.newCustomer.address?.trim() || '-', 
                        borrowed_gallons: 0 
                    });
                    this.selectedCustomerId = newId;
                    this.openQuickAddModal = false;
                    this.newCustomer = { name: '', whatsapp: '', address: '' };
                },

                openReceiptModal: false,
                lastOrderData: {},
                processTransaction() {
                    if (this.totalGalonQty > this.tutupStock) {
                        alert('Stok tutup galon tidak mencukupi!');
                        return;
                    }
                    this.tutupStock -= this.totalGalonQty;
                    this.lastOrderData = {
                        orderNumber: this.orderNumber,
                        orderType: this.orderType === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Pesan Antar',
                        customerName: this.selectedCustomer ? this.selectedCustomer.name : 'Pelanggan Umum',
                        items: JSON.parse(JSON.stringify(this.cart)),
                        totalGalon: this.totalGalonQty,
                        grandTotal: this.grandTotal,
                        paymentMethod: this.paymentMethod,
                        cashReceived: this.paymentMethod === 'tunai' ? this.cashReceived : this.grandTotal,
                        changeAmount: this.paymentMethod === 'tunai' ? this.changeAmount : 0
                    };
                    this.openReceiptModal = true;
                    this.cart = [];
                    this.cashReceived = 0;
                    this.orderNumber = 'SW-' + Math.floor(100000 + Math.random() * 900000);
                }
            };
        }
    </script>

</body>
</html>
