<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Restock Barang Operasional - Sultan Water</title>

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
    x-data="restockApp()">

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
                class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600 flex items-center justify-center transition">
                <i class="fa-solid fa-chart-pie text-base"></i>
            </a>
            <a href="{{ route('inventory.restock') }}" title="Restock Barang Operasional"
                class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-sm shadow-blue-500/20">
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

        <!-- Top Bar: Greeting & Tanggal -->
        <header class="px-5 lg:px-8 py-4 bg-white border-b border-slate-200/80 shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                            Restock Barang Operasional
                        </h1>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                            Admin Only
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 font-medium mt-0.5" x-text="todayDate"></p>
                </div>

                <a href="{{ route('admin.dashboard') }}"
                    class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </header>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto px-4 sm:px-5 lg:px-8 py-6 touch-scroll">
            <div class="max-w-5xl mx-auto space-y-5">

                <!-- Alert Global Error Validasi (DOC-VAL-POS-02 v1.3 Bagian 2.B) -->
                @if ($errors->any())
                    <div
                        class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-3 shadow-xs">
                        <i class="fa-solid fa-circle-exclamation text-rose-500 text-base mt-0.5"></i>
                        <div>
                            <div class="font-extrabold text-rose-900 mb-1">Gagal menyimpan data restock:</div>
                            <ul class="list-disc list-inside space-y-0.5 font-semibold">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Alert Sukses Restock Tutup Galon -->
                @if (session('success_cap'))
                    <div
                        class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <div class="font-black text-emerald-950">Restock Tutup Galon Berhasil!</div>
                                <div class="font-medium mt-0.5">{{ session('success_cap') }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Alert Sukses Pembelian Galon Baru -->
                @if (session('success_gallon'))
                    <div
                        class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <div class="font-black text-emerald-950">Pembelian Galon Baru Tercatat!</div>
                                <div class="font-medium mt-0.5">{{ session('success_gallon') }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- ================================================================= -->
                <!-- GRID 2 FORM RESTOCK (TUTUP GALON & PEMBELIAN GALON BARU)          -->
                <!-- ================================================================= -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- KARTU 1: RESTOCK TUTUP GALON -->
                    <div
                        class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6 flex flex-col justify-between">
                        <div>
                            <!-- Header Kartu -->
                            <div class="flex items-start justify-between gap-3 pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 border border-blue-100">
                                        <i class="fa-solid fa-bottle-water"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-blue-50 text-blue-700">
                                            Bahan Habis Pakai
                                        </span>
                                        <h2 class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                                            Restock Tutup Galon
                                        </h2>
                                    </div>
                                </div>

                                <!-- Indikator Saldo Saat Ini -->
                                <div class="text-right bg-slate-50 px-3.5 py-2 rounded-2xl border border-slate-200/70">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Stok
                                        Tutup</span>
                                    <span class="text-xl font-black text-blue-600">
                                        {{ number_format($capStock, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-400">unit</span>
                                </div>
                            </div>

                            <p class="text-xs text-slate-500 font-medium mt-3 leading-relaxed">
                                Tambahkan stok tutup galon segel sekali pakai yang baru dibeli. Setiap penambahan
                                otomatis dicatat pada log audit dengan alasan <code
                                    class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-[11px]">restock_stok_jual</code>.
                            </p>
                        </div>

                        <!-- Form Restock Tutup -->
                        <form action="{{ route('inventory.restock.cap') }}" method="POST" class="mt-5 space-y-4"
                            novalidate>
                            @csrf

                            <div>
                                <label for="cap_quantity" class="block text-xs font-bold text-slate-600 mb-1.5">
                                    Jumlah Tutup Galon Masuk <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl">
                                    <button type="button" @click="if (capQty > 1) capQty--"
                                        class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <input type="number" id="cap_quantity" name="quantity" min="1" step="1"
                                        x-model="capQty" required placeholder="Masukkan jumlah..."
                                        class="w-full text-center font-black text-base text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0">
                                    <button type="button" @click="capQty = (parseInt(capQty) || 0) + 1"
                                        class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>

                                <!-- Tombol Preset Cepat -->
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    <span class="text-[11px] text-slate-400 font-semibold mr-1">Isi Cepat:</span>
                                    <button type="button" @click="capQty = 100"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-[11px] font-bold text-slate-600 transition cursor-pointer">+100</button>
                                    <button type="button" @click="capQty = 500"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-[11px] font-bold text-slate-600 transition cursor-pointer">+500</button>
                                    <button type="button" @click="capQty = 1000"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-[11px] font-bold text-slate-600 transition cursor-pointer">+1.000</button>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100">
                                <button type="submit"
                                    class="w-full min-h-[46px] px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                    <span>Simpan Restock Tutup Galon</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- KARTU 2: PEMBELIAN GALON BARU (ARMADA / STOK JUAL) -->
                    <div
                        class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6 flex flex-col justify-between">
                        <div>
                            <!-- Header Kartu -->
                            <div class="flex items-start justify-between gap-3 pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 border border-emerald-100">
                                        <i class="fa-solid fa-Prescription-bottle fa-jar"></i>
                                    </div>
                                    <div>
                                        <span
                                            class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-emerald-50 text-emerald-700">
                                            Aset Galon Fisik
                                        </span>
                                        <h2 class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                                            Pembelian Galon Baru
                                        </h2>
                                    </div>
                                </div>

                                <!-- Indikator Total Galon Dimiliki (G_total) -->
                                <div class="text-right bg-slate-50 px-3.5 py-2 rounded-2xl border border-slate-200/70">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total
                                        Dimiliki</span>
                                    <span
                                        class="text-xl font-black {{ $fleetTotal < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ number_format($fleetTotal, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-400">galon</span>
                                </div>
                            </div>

                            <p class="text-xs text-slate-500 font-medium mt-3 leading-relaxed">
                                Catat pembelian galon kosong/baru dari distributor. Menambah <strong>Total Galon
                                    Dimiliki</strong> depot.
                            </p>
                        </div>

                        <!-- Form Pembelian Galon Baru -->
                        <form action="{{ route('inventory.restock.gallon') }}" method="POST" class="mt-4 space-y-4"
                            novalidate>
                            @csrf

                            <!-- Input Jumlah Galon -->
                            <div>
                                <label for="gallon_quantity" class="block text-xs font-bold text-slate-600 mb-1.5">
                                    Jumlah Galon Baru Dibeli <span class="text-rose-500">*</span>
                                </label>
                                <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl">
                                    <button type="button" @click="if (gallonQty > 1) gallonQty--"
                                        class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                    <input type="number" id="gallon_quantity" name="quantity" min="1" step="1"
                                        x-model="gallonQty" required placeholder="Masukkan jumlah..."
                                        class="w-full text-center font-black text-base text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0">
                                    <button type="button" @click="gallonQty = (parseInt(gallonQty) || 0) + 1"
                                        class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Pilihan Alokasi Restock (Wajib di S2-FE-05) -->
                            <div>
                                <label for="allocation" class="block text-xs font-bold text-slate-600 mb-1.5">
                                    Alokasi Restock <span class="text-rose-500">*</span>
                                </label>

                                <!-- Input hidden yang terhubung ke tombol interaktif & select -->
                                <select id="allocation" name="allocation" x-model="allocation" required
                                    class="w-full min-h-[42px] px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="">-- Pilih Tujuan Alokasi --</option>
                                    <option value="stok_dijual">Stok Dijual (Penjualan Galon Baru + Isi)</option>
                                    <option value="armada_depot">Armada Depot (Operasional / Pinjaman Pelanggan)
                                    </option>
                                </select>

                                <p class="mt-1.5 text-[11px] text-slate-400 font-medium leading-normal">
                                    <i class="fa-solid fa-circle-info mr-1 text-slate-400"></i>
                                    Pilihan alokasi hanya untuk pencatatan alasan (<em>reason</em>) di log audit;
                                    keduanya menambah Total Galon Dimiliki.
                                </p>
                            </div>

                            <div class="pt-3 border-t border-slate-100">
                                <button type="submit"
                                    class="w-full min-h-[46px] px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-emerald-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                    <span>Simpan Pembelian Galon Baru</span>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

            </div>
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- 3. ALPINE.JS LOGIC                                                        -->
    <!-- ========================================================================= -->
    <script>
        function restockApp() {
            return {
                todayDate: '',
                capQty: 100,
                gallonQty: 10,
                allocation: '{{ old('allocation', '') }}',

                init() {
                    const now = new Date();
                    this.todayDate = now.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            };
        }
    </script>
</body>

</html>