<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sultan Water POS')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Asset Lokal (Tailwind CSS & Alpine.js via Vite) -->
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

        @media print {

            aside,
            header,
            .print\:hidden {
                display: none !important;
            }

            body {
                background-color: #ffffff !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="h-full flex flex-col md:flex-row text-slate-800 antialiased overflow-hidden select-none" @yield('body-attributes')>

    <!-- ========================================================================= -->
    <!-- 1. SIDEBAR NAVIGASI (IKON + LABEL TEKS MINI YANG RAMAH PENGGUNA)         -->
    <!-- ========================================================================= -->
    <aside
        class="w-full md:w-24 bg-white border-r border-slate-200/80 flex md:flex-col items-center justify-between py-2.5 px-3 md:py-5 md:px-0 shrink-0 z-30 shadow-xs print:hidden">

        <!-- Top: Logo Depot -->
        <div class="flex items-center gap-3 md:flex-col shrink-0">
            @php
                $dashboardRoute =
                    auth()->user()->role === 'admin'
                        ? (Route::has('admin.dashboard')
                            ? route('admin.dashboard')
                            : '#')
                        : (Route::has('pos.dashboard')
                            ? route('pos.dashboard')
                            : '#');
            @endphp
            <a href="{{ $dashboardRoute }}"
                class="w-12 h-12 rounded-2xl bg-white border border-slate-200 p-1 flex items-center justify-center shadow-xs hover:scale-105 transition overflow-hidden"
                style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Sultan Water"
                    class="w-full h-full object-contain" style="max-width: 38px; max-height: 38px; object-fit: contain;"
                    onerror="this.onerror=null; this.src='https://placehold.co/80x80/2563eb/ffffff?text=SW';">
            </a>
        </div>

        <!-- Middle: Navigasi Menu Ikon + Teks Mini Berdasarkan Role -->
        <nav class="flex md:flex-col items-center gap-1.5 md:gap-2 my-auto overflow-x-auto md:overflow-y-auto max-w-full md:max-h-[calc(100vh-160px)] px-2 md:px-0 py-1 touch-scroll w-full">
            @if (auth()->check() && auth()->user()->role === 'admin')
                
                <!-- 1. Dashboard Pemilik -->
                <a href="{{ route('admin.dashboard') }}"
                    class="w-14 sm:w-16 md:w-[74px] py-1.5 md:py-2 rounded-2xl flex flex-col items-center justify-center shrink-0 transition text-center {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white font-black shadow-sm shadow-blue-500/25' : 'bg-slate-50 md:bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fa-solid fa-chart-pie text-sm md:text-base"></i>
                    <span class="text-[9px] md:text-[10px] mt-1 font-extrabold tracking-tight leading-none">Dashboard</span>
                </a>

                <!-- Pemisah Visual Halus -->
                <div class="hidden md:block w-8 h-px bg-slate-200/80 my-0.5"></div>

                <!-- 2. POS Transaksi Kasir -->
                <a href="{{ route('pos.dashboard') }}"
                    class="w-14 sm:w-16 md:w-[74px] py-1.5 md:py-2 rounded-2xl flex flex-col items-center justify-center shrink-0 transition text-center {{ request()->routeIs('pos.*') ? 'bg-blue-600 text-white font-black shadow-sm shadow-blue-500/25' : 'bg-slate-50 md:bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fa-solid fa-cash-register text-sm md:text-base"></i>
                    <span class="text-[9px] md:text-[10px] mt-1 font-extrabold tracking-tight leading-none">Kasir</span>
                </a>

                <!-- 3. Antrean Kurir -->
                @if (Route::has('delivery.queue'))
                    <a href="{{ route('delivery.queue') }}"
                        class="w-14 sm:w-16 md:w-[74px] py-1.5 md:py-2 rounded-2xl flex flex-col items-center justify-center shrink-0 transition text-center {{ request()->routeIs('delivery.queue') ? 'bg-blue-600 text-white font-black shadow-sm shadow-blue-500/25' : 'bg-slate-50 md:bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="fa-solid fa-motorcycle text-sm md:text-base"></i>
                        <span class="text-[9px] md:text-[10px] mt-1 font-extrabold tracking-tight leading-none">Kurir</span>
                    </a>
                @endif

                <!-- 4. Direktori Pelanggan & Galon Pinjaman -->
                @if (Route::has('admin.customers.index'))
                    <a href="{{ route('admin.customers.index') }}"
                        class="w-14 sm:w-16 md:w-[74px] py-1.5 md:py-2 rounded-2xl flex flex-col items-center justify-center shrink-0 transition text-center {{ request()->routeIs('admin.customers.*') || request()->routeIs('customers.show') ? 'bg-blue-600 text-white font-black shadow-sm shadow-blue-500/25' : 'bg-slate-50 md:bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="fa-solid fa-users text-sm md:text-base"></i>
                        <span class="text-[9px] md:text-[10px] mt-1 font-extrabold tracking-tight leading-none">Pelanggan</span>
                    </a>
                @endif

                <!-- Pemisah Visual Halus -->
                <div class="hidden md:block w-8 h-px bg-slate-200/80 my-0.5"></div>

                <!-- 5. Restock Tutup & Galon Baru -->
                <a href="{{ route('inventory.restock') }}"
                    class="w-14 sm:w-16 md:w-[74px] py-1.5 md:py-2 rounded-2xl flex flex-col items-center justify-center shrink-0 transition text-center {{ request()->routeIs('inventory.*') ? 'bg-blue-600 text-white font-black shadow-sm shadow-blue-500/25' : 'bg-slate-50 md:bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fa-solid fa-boxes-stacked text-sm md:text-base"></i>
                    <span class="text-[9px] md:text-[10px] mt-1 font-extrabold tracking-tight leading-none">Restock</span>
                </a>

                <!-- 6. Master 5-Tier Harga -->
                @if (Route::has('admin.price-tiers.index'))
                    <a href="{{ route('admin.price-tiers.index') }}"
                        class="w-14 sm:w-16 md:w-[74px] py-1.5 md:py-2 rounded-2xl flex flex-col items-center justify-center shrink-0 transition text-center {{ request()->routeIs('admin.price-tiers.*') ? 'bg-blue-600 text-white font-black shadow-sm shadow-blue-500/25' : 'bg-slate-50 md:bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="fa-solid fa-tags text-sm md:text-base"></i>
                        <span class="text-[9px] md:text-[10px] mt-1 font-extrabold tracking-tight leading-none">Harga</span>
                    </a>
                @endif

                <!-- Pemisah Visual Halus -->
                <div class="hidden md:block w-8 h-px bg-slate-200/80 my-0.5"></div>

                <!-- 7. Beban Operasional (Expenses) -->
                <a href="{{ route('admin.expenses.index') }}"
                    class="w-14 sm:w-16 md:w-[74px] py-1.5 md:py-2 rounded-2xl flex flex-col items-center justify-center shrink-0 transition text-center {{ request()->routeIs('admin.expenses.*') ? 'bg-blue-600 text-white font-black shadow-sm shadow-blue-500/25' : 'bg-slate-50 md:bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fa-solid fa-receipt text-sm md:text-base"></i>
                    <span class="text-[9px] md:text-[10px] mt-1 font-extrabold tracking-tight leading-none">Beban</span>
                </a>

                <!-- 8. Laporan Finansial -->
                @if (Route::has('admin.reports.index'))
                    <a href="{{ route('admin.reports.index') }}"
                        class="w-14 sm:w-16 md:w-[74px] py-1.5 md:py-2 rounded-2xl flex flex-col items-center justify-center shrink-0 transition text-center {{ request()->routeIs('admin.reports.*') ? 'bg-blue-600 text-white font-black shadow-sm shadow-blue-500/25' : 'bg-slate-50 md:bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="fa-solid fa-file-invoice-dollar text-sm md:text-base"></i>
                        <span class="text-[9px] md:text-[10px] mt-1 font-extrabold tracking-tight leading-none">Laporan</span>
                    </a>
                @endif

            @else
                <!-- ============================================================= -->
                <!-- NAVIGASI ROLE KASIR / KURIR (RINGKAS & JELAS)                 -->
                <!-- ============================================================= -->
                <!-- Kasir POS -->
                <a href="{{ route('pos.dashboard') }}"
                    class="w-14 sm:w-16 md:w-[74px] py-1.5 md:py-2 rounded-2xl flex flex-col items-center justify-center shrink-0 transition text-center {{ request()->routeIs('pos.*') ? 'bg-blue-600 text-white font-black shadow-sm shadow-blue-500/25' : 'bg-slate-50 md:bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fa-solid fa-cash-register text-sm md:text-base"></i>
                    <span class="text-[9px] md:text-[10px] mt-1 font-extrabold tracking-tight leading-none">Kasir</span>
                </a>

                <!-- Antrean Kurir -->
                @if (Route::has('delivery.queue'))
                    <a href="{{ route('delivery.queue') }}"
                        class="w-14 sm:w-16 md:w-[74px] py-1.5 md:py-2 rounded-2xl flex flex-col items-center justify-center shrink-0 transition text-center {{ request()->routeIs('delivery.queue') ? 'bg-blue-600 text-white font-black shadow-sm shadow-blue-500/25' : 'bg-slate-50 md:bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="fa-solid fa-motorcycle text-sm md:text-base"></i>
                        <span class="text-[9px] md:text-[10px] mt-1 font-extrabold tracking-tight leading-none">Kurir</span>
                    </a>
                @endif
            @endif
        </nav>

        <!-- Bottom: Avatar Pengguna & Tombol Logout -->
        <div class="flex md:flex-col items-center gap-2 shrink-0">
            <div class="w-10 h-10 rounded-2xl {{ auth()->user()->role === 'admin' ? 'bg-blue-50 border border-blue-200 text-blue-700' : 'bg-slate-100 border border-slate-200 text-slate-700' }} flex flex-col items-center justify-center text-xs font-black shadow-2xs"
                title="{{ auth()->user()->name ?? 'Pengguna' }} ({{ strtoupper(auth()->user()->role ?? 'Kasir') }})">
                <span>{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" title="Keluar / Logout"
                    class="w-9 h-9 rounded-2xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-power-off text-xs"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- ========================================================================= -->
    <!-- 2. MAIN WORKSPACE AREA                                                    -->
    <!-- ========================================================================= -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Header Atas: Judul Halaman & Action Kanan -->
        <header class="px-5 lg:px-8 py-3.5 bg-white border-b border-slate-200/80 shrink-0 print:hidden">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            @yield('page-title', 'Sultan Water POS')
                        </h1>
                        @yield('page-badge')
                    </div>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">
                        @yield('page-subtitle', now()->translatedFormat('l, d F Y'))
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    @yield('header-actions')
                </div>
            </div>
        </header>

        <!-- Dynamic Content Body (Scrollable Area) -->
        <div class="flex-1 overflow-y-auto px-4 sm:px-5 lg:px-8 py-5 sm:py-6 touch-scroll">
            @yield('content')
        </div>

    </main>

    <!-- Modal Containers / Global Overlays -->
    @yield('modals')

    <!-- Scripts Stack (Alpine.js Components) -->
    @stack('scripts')
</body>

</html>