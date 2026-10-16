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
    <!-- 1. SIDEBAR NAVIGASI (ROLE-AWARE: ADMIN & KASIR/KURIR)                     -->
    <!-- ========================================================================= -->
    <aside
        class="w-full md:w-20 bg-white border-r border-slate-200/80 flex md:flex-col items-center justify-between py-3 px-4 md:py-6 md:px-0 shrink-0 z-30 shadow-xs print:hidden">

        <!-- Top: Logo Depot -->
        <div class="flex items-center gap-3 md:flex-col">
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
                class="w-11 h-11 rounded-2xl bg-white border border-slate-200 p-1 flex items-center justify-center shadow-xs hover:scale-105 transition overflow-hidden"
                style="width: 44px; height: 44px; min-width: 44px; min-height: 44px;">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Sultan Water"
                    class="w-full h-full object-contain" style="max-width: 36px; max-height: 36px; object-fit: contain;"
                    onerror="this.onerror=null; this.src='https://placehold.co/80x80/2563eb/ffffff?text=SW';">
            </a>
        </div>

        <!-- Middle: Navigasi Ikon Sesuai Role -->
        <nav class="flex md:flex-col items-center gap-2 sm:gap-3">
            @if (auth()->check() && auth()->user()->role === 'admin')
                <!-- Menu Admin 1: Dashboard -->
                <a href="{{ route('admin.dashboard') }}" title="Dashboard Admin"
                    class="w-11 h-11 rounded-2xl flex items-center justify-center transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fa-solid fa-chart-pie text-base"></i>
                </a>

                <!-- TAMBAHAN: Menu Kasir POS untuk Admin -->
                <a href="{{ route('pos.dashboard') }}" title="POS Transaksi Kasir"
                    class="w-11 h-11 rounded-2xl flex items-center justify-center transition {{ request()->routeIs('pos.*') ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fa-solid fa-cash-register text-base"></i>
                </a>

                <!-- Menu Admin 2: Restock Inventaris -->
                <a href="{{ route('inventory.restock') }}" title="Restock Barang Operasional"
                    class="w-11 h-11 rounded-2xl flex items-center justify-center transition {{ request()->routeIs('inventory.*') ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fa-solid fa-boxes-stacked text-base"></i>
                </a>

                <!-- Menu Admin 3: Kelola 5-Tier Harga (Tiket #83) -->
                @if (Route::has('admin.price-tiers.index'))
                    <a href="{{ route('admin.price-tiers.index') }}" title="Kelola Master Tier Harga"
                        class="w-11 h-11 rounded-2xl flex items-center justify-center transition {{ request()->routeIs('admin.price-tiers.*') ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="fa-solid fa-tags text-base"></i>
                    </a>
                @endif

                <!-- Menu Admin 4: Akses Cepat Antrean Kurir -->
                @if (Route::has('delivery.queue'))
                    <a href="{{ route('delivery.queue') }}" title="Antrean Kurir Lapangan"
                        class="w-11 h-11 rounded-2xl flex items-center justify-center transition {{ request()->routeIs('delivery.queue') ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="fa-solid fa-motorcycle text-base"></i>
                    </a>
                @endif
            @else
                <!-- Menu Kasir/Kurir 1: POS Transaksi -->
                <a href="{{ route('pos.dashboard') }}" title="POS Transaksi Kasir"
                    class="w-11 h-11 rounded-2xl flex items-center justify-center transition {{ request()->routeIs('pos.*') ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600' }}">
                    <i class="fa-solid fa-cash-register text-base"></i>
                </a>

                <!-- Menu Kasir/Kurir 2: Antrean Pengantaran Kurir (Tiket #44) -->
                @if (Route::has('delivery.queue'))
                    <a href="{{ route('delivery.queue') }}" title="Antrean Pengantaran Kurir"
                        class="w-11 h-11 rounded-2xl flex items-center justify-center transition {{ request()->routeIs('delivery.queue') ? 'bg-blue-600 text-white font-bold shadow-sm shadow-blue-500/20' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600' }}">
                        <i class="fa-solid fa-motorcycle text-base"></i>
                    </a>
                @endif
            @endif

            {{-- Slot tambahan icon navigasi jika halaman anak membutuhkan aksi khusus --}}
            @yield('nav-actions')
        </nav>

        <!-- Bottom: Avatar Pengguna & Tombol Logout -->
        <div class="flex md:flex-col items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl {{ auth()->user()->role === 'admin' ? 'bg-blue-50 border border-blue-200 text-blue-700' : 'bg-slate-100 border border-slate-200 text-slate-700' }} flex items-center justify-center text-xs font-black"
                title="{{ auth()->user()->name ?? 'Pengguna' }} ({{ strtoupper(auth()->user()->role ?? 'Kasir') }})">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>

            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" title="Keluar / Logout"
                    class="w-10 h-10 rounded-2xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-power-off text-sm"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- ========================================================================= -->
    <!-- 2. MAIN WORKSPACE AREA                                                    -->
    <!-- ========================================================================= -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Header Atas: Judul Halaman & Action Kanan -->
        <header class="px-5 lg:px-8 py-4 bg-white border-b border-slate-200/80 shrink-0 print:hidden">
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
