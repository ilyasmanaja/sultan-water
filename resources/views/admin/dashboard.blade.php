<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Administrator - Sultan Water</title>

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

<body class="h-full flex flex-col md:flex-row text-slate-800 antialiased overflow-hidden select-none" x-data="adminDashboard()">

    <!-- ========================================================================= -->
    <!-- 1. LEFT SIDEBAR NAVIGATION (KHUSUS ADMIN)                                 -->
    <!-- ========================================================================= -->
    <aside
        class="w-full md:w-20 bg-white border-r border-slate-200/80 flex md:flex-col items-center justify-between py-3 px-4 md:py-6 md:px-0 shrink-0 z-30 shadow-xs">
        <!-- Top: Logo -->
        <div class="flex items-center gap-3 md:flex-col">
            <a href="{{ route('admin.dashboard') }}"
                class="w-11 h-11 rounded-2xl bg-white border border-slate-200 p-1 flex items-center justify-center shadow-xs hover:scale-105 transition overflow-hidden"
                style="width: 44px; height: 44px; min-width: 44px; min-height: 44px;">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" class="w-full h-full object-contain"
                    style="max-width: 36px; max-height: 36px; object-fit: contain;"
                    onerror="this.onerror=null; this.src='https://placehold.co/80x80/2563eb/ffffff?text=SW';">
            </a>
        </div>

        <!-- Middle: Nav Icons Admin -->
        <nav class="flex md:flex-col items-center gap-2 sm:gap-3">
            <a href="{{ route('admin.dashboard') }}" title="Dashboard Admin"
                class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold shadow-sm">
                <i class="fa-solid fa-chart-pie text-base"></i>
            </a>
            <a href="{{ route('inventory.restock') }}" title="Restock Barang Operasional"
                class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-boxes-stacked text-base"></i>
            </a>
        </nav>

        <!-- Bottom: User & Logout -->
        <div class="flex md:flex-col items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center text-xs font-black text-blue-700"
                title="{{ auth()->user()->name ?? 'Administrator' }}">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
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
    <!-- 2. MAIN WORKSPACE                                                         -->
    <!-- ========================================================================= -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Top Bar -->
        <header class="px-5 lg:px-8 py-4 bg-white border-b border-slate-200/80 shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                        Dashboard Administrator
                    </h1>
                    <p class="text-xs text-slate-400 font-medium mt-0.5" x-text="todayDate"></p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-extrabold">
                        Role: Admin
                    </span>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto px-4 sm:px-5 lg:px-8 py-6 touch-scroll">
            <div class="max-w-4xl mx-auto space-y-6">

                <!-- Welcome Card -->
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700">
                            Sistem Aktif
                        </span>
                        <h2 class="text-lg sm:text-xl font-black text-slate-900 mt-1">
                            Selamat datang, {{ auth()->user()->name ?? 'Administrator' }}!
                        </h2>
                        <p class="text-xs text-slate-500 font-medium mt-1">
                            Saat ini Anda berada di halaman utama Administrator Sultan Water. Modul operasional inventaris dan restock sudah siap diakses.
                        </p>
                    </div>

                    <a href="{{ route('inventory.restock') }}"
                        class="min-h-[44px] px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center gap-2 shrink-0">
                        <i class="fa-solid fa-boxes-stacked text-xs"></i>
                        <span>Buka Menu Restock</span>
                    </a>
                </div>

                <!-- Shortcut Grid Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="{{ route('inventory.restock') }}" class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:border-blue-300 hover:shadow-md transition flex items-center gap-4 group">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shrink-0">
                            <i class="fa-solid fa-bottle-water"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900">Restock Tutup & Galon Baru</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Kelola penambahan stok bahan dan aset galon fisik.</p>
                        </div>
                    </a>

                    <div class="bg-slate-50 p-5 rounded-3xl border border-slate-200/80 shadow-xs opacity-70 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-200 text-slate-500 flex items-center justify-center text-lg font-bold shrink-0">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-700">Laporan & Analitik Keuangan</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Terjadwal pada Sprint 4.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <!-- Script Tanggal -->
    <script>
        function adminDashboard() {
            return {
                todayDate: '',
                init() {
                    const now = new Date();
                    this.todayDate = now.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            }
        }
    </script>
</body>

</html>