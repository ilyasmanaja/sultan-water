@extends('layouts.app')

@section('title', 'Dashboard Pemilik - Sultan Water')

@section('body-attributes')
    x-data="ownerDashboardApp()"
@endsection

@section('page-title', 'Dashboard Pemilik Depot')

@section('page-badge')
    <span
        class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
        Administrator
    </span>
@endsection

@section('page-subtitle')
    <span>Ringkasan performa finansial, aset armada galon, dan tren penjualan operasional</span>
@endsection

@section('header-actions')
    <!-- Filter Periode Cepat -->
    <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center gap-2">
        <div class="hidden sm:flex items-center gap-1.5 bg-slate-100 p-1 rounded-2xl border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-500 pl-2">Periode:</span>
            <input type="date" name="start_date" value="{{ $startDate }}"
                class="px-2.5 py-1 text-xs font-bold rounded-xl bg-white text-slate-700 border-none focus:ring-0 focus:outline-none">
            <span class="text-xs text-slate-400 font-bold">-</span>
            <input type="date" name="end_date" value="{{ $endDate }}"
                class="px-2.5 py-1 text-xs font-bold rounded-xl bg-white text-slate-700 border-none focus:ring-0 focus:outline-none">
            <button type="submit" title="Terapkan Filter"
                class="w-7 h-7 rounded-xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center text-xs transition cursor-pointer">
                <i class="fa-solid fa-filter text-[10px]"></i>
            </button>
        </div>

        <a href="{{ route('admin.reports.index') }}"
            class="px-3.5 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
            <i class="fa-solid fa-file-invoice text-slate-500"></i>
            <span class="hidden md:inline">Laporan Detail</span>
        </a>
    </form>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- ===================================================================== -->
        <!-- 1. INDIKATOR VISUAL LOW-STOCK ALERT TUTUP GALON (TIKET #77)           -->
        <!-- ===================================================================== -->
        <x-low-stock-alert :stock="$capStatus['stock']" :threshold="$capStatus['threshold']" :is-low-stock="$capStatus['is_low_stock']" />

        <!-- ===================================================================== -->
        <!-- 2. PENANDA VISUAL AUDIT ARMADA GALON JIKA MINUS (BR-INV-02 §6.A)      -->
        <!-- ===================================================================== -->
        @if ($gallonFleet['needs_audit'])
            <div
                class="p-4 sm:p-5 rounded-3xl bg-rose-50 border-2 border-rose-300 text-rose-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div
                        class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-rose-500/30">
                        <i class="fa-solid fa-triangle-exclamation text-base"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-rose-950">
                            Total Galon Dimiliki Perlu Diperiksa!
                        </h4>
                        <p class="text-xs text-rose-800 font-medium mt-0.5">
                            Saldo armada galon bernilai minus (Total Dimiliki:
                            <strong>{{ $gallonFleet['total_owned'] }}</strong>,
                            Standby di Depot: <strong>{{ $gallonFleet['standby'] }}</strong>).
                            Hal ini terjadi jika penjualan galon baru melebihi catatan pembelian dari distributor.
                        </p>
                    </div>
                </div>
                <a href="{{ route('inventory.restock') }}"
                    class="px-4 py-2 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black transition shrink-0 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Catat Pembelian Galon Baru</span>
                </a>
            </div>
        @endif

        <!-- ===================================================================== -->
        <!-- 3. KARTU RINGKASAN FINANSIAL (NP = R - E)                              -->
        <!-- ===================================================================== -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">Kinerja Finansial Periode Terpilih</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Berdasarkan order lunas dan biaya operasional yang
                        dicatat</p>
                </div>
                <span class="text-[11px] font-bold text-slate-400">
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                    {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- KARTU A: OMZET PENJUALAN (R) -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Omzet Penjualan
                            (R)</span>
                        <div
                            class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm border border-blue-100">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl font-black text-slate-900">
                            Rp {{ number_format($financialSummary['revenue'], 0, ',', '.') }}
                        </div>
                        <div class="flex items-center gap-1.5 mt-1 text-[11px] text-slate-500 font-medium">
                            <span class="font-bold text-blue-600">{{ $financialSummary['total_orders'] }} Transaksi</span>
                            <span>•</span>
                            <span>{{ number_format($financialSummary['total_gallons'], 0, ',', '.') }} Galon</span>
                        </div>
                    </div>
                </div>

                <!-- KARTU B: BEBAN OPERASIONAL (E) -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Total Beban (E)</span>
                        <div
                            class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm border border-amber-100">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-2xl font-black text-slate-900">
                            Rp {{ number_format($financialSummary['expenses'], 0, ',', '.') }}
                        </div>
                        <div class="mt-1 text-[11px] text-slate-400 font-medium truncate"
                            title="3 Kelompok: Kemasan, Logistik & Beban Tetap">
                            3 Kategori Operasional
                        </div>
                    </div>
                </div>

                <!-- KARTU C: LABA BERSIH (NET PROFIT: NP = R - E) -->
                <div
                    class="bg-white rounded-3xl p-5 border {{ $financialSummary['is_profit'] ? 'border-emerald-200/80 bg-gradient-to-br from-white to-emerald-50/30' : 'border-rose-200/80 bg-gradient-to-br from-white to-rose-50/30' }} shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Laba Bersih (NP)</span>
                        <div
                            class="w-9 h-9 rounded-2xl {{ $financialSummary['is_profit'] ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-rose-50 text-rose-600 border border-rose-200' }} flex items-center justify-center text-sm">
                            <i
                                class="fa-solid {{ $financialSummary['is_profit'] ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div
                            class="text-2xl font-black {{ $financialSummary['is_profit'] ? 'text-emerald-600' : 'text-rose-600' }}">
                            Rp {{ number_format($financialSummary['net_profit'], 0, ',', '.') }}
                        </div>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span
                                class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $financialSummary['is_profit'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $financialSummary['is_profit'] ? 'Surplus / Laba' : 'Defisit / Rugi' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- KARTU D: STATUS STOK TUTUP AKTIF -->
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Stok Tutup Segel</span>
                        <div
                            class="w-9 h-9 rounded-2xl {{ $capStatus['is_low_stock'] ? 'bg-amber-50 text-amber-600' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center text-sm border border-slate-100">
                            <i class="fa-solid fa-bottle-water"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div
                            class="text-2xl font-black {{ $capStatus['is_low_stock'] ? 'text-amber-600' : 'text-blue-600' }}">
                            {{ number_format($capStatus['stock'], 0, ',', '.') }}
                            <span class="text-xs font-bold text-slate-400">unit</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px]">
                            <span class="text-slate-400 font-medium">Batas: {{ $capStatus['threshold'] }} unit</span>
                            @if ($capStatus['is_low_stock'])
                                <span class="font-extrabold text-amber-600">Menipis!</span>
                            @else
                                <span class="font-extrabold text-emerald-600">Stok Aman</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- 4. TIGA KARTU PEMANTAUAN MAKRO ARMADA GALON (BR-INV-02 §6.A)          -->
        <!-- ===================================================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-emerald-50 text-emerald-700">
                            Aset Fisik
                        </span>
                        <h3 class="text-base font-black text-slate-900 tracking-tight">
                            Pemantauan Makro Armada Galon Depot
                        </h3>
                    </div>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">
                        Tiga angka kunci untuk pertimbangan keputusan pembelian galon baru saat pelanggan bertambah
                    </p>
                </div>
                <a href="{{ route('inventory.restock') }}"
                    class="text-xs font-extrabold text-blue-600 hover:text-blue-700 flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                    <span>Kelola Restock</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mt-5">

                <!-- KARTU MAKRO 1: TOTAL GALON DIMILIKI (G_total) -->
                <div
                    class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">
                            1. Total Galon Dimiliki (G<sub class="font-bold">total</sub>)
                        </span>
                        <div
                            class="text-2xl sm:text-3xl font-black {{ $gallonFleet['total_owned'] < 0 ? 'text-rose-600' : 'text-slate-900' }}">
                            {{ number_format($gallonFleet['total_owned'], 0, ',', '.') }}
                            <span class="text-xs font-bold text-slate-400">galon</span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium mt-1">
                            Di depot maupun di tangan pelanggan
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-white text-slate-700 flex items-center justify-center text-xl shadow-xs border border-slate-200">
                        <i class="fa-solid fa-jar"></i>
                    </div>
                </div>

                <!-- KARTU MAKRO 2: GALON DIPINJAM PELANGGAN (G_dipinjam) -->
                <a href="{{ route('admin.customers.index', ['filter' => 'borrowing']) }}"
                    class="p-4 sm:p-5 rounded-2xl bg-amber-50/50 border border-amber-200/80 flex items-center justify-between hover:bg-amber-100/50 transition cursor-pointer"
                    title="Lihat daftar pelanggan yang sedang meminjam galon">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 block mb-1">
                            2. Galon Dipinjam (G<sub class="font-bold">dipinjam</sub>)
                        </span>
                        <div class="text-2xl sm:text-3xl font-black text-amber-900">
                            {{ number_format($gallonFleet['borrowed'], 0, ',', '.') }}
                            <span class="text-xs font-bold text-amber-600">galon</span>
                        </div>
                        <p class="text-[11px] text-amber-700/80 font-medium mt-1">
                            Akumulasi saldo pinjaman seluruh pelanggan
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-white text-amber-600 flex items-center justify-center text-xl shadow-xs border border-amber-200">
                        <i class="fa-solid fa-hand-holding-hand"></i>
                    </div>
                </a>

                <!-- KARTU MAKRO 3: GALON STANDBY DI DEPOT (G_standby) -->
                <div
                    class="p-4 sm:p-5 rounded-2xl bg-emerald-50/50 border border-emerald-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 block mb-1">
                            3. Galon Standby (G<sub class="font-bold">standby</sub>)
                        </span>
                        <div
                            class="text-2xl sm:text-3xl font-black {{ $gallonFleet['standby'] < 0 ? 'text-rose-600' : 'text-emerald-900' }}">
                            {{ number_format($gallonFleet['standby'], 0, ',', '.') }}
                            <span class="text-xs font-bold text-emerald-600">galon</span>
                        </div>
                        <p class="text-[11px] text-emerald-700/80 font-medium mt-1">
                            Siap pakai di depot (G<sub class="font-bold">total</sub> - G<sub
                                class="font-bold">dipinjam</sub>)
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-2xl bg-white text-emerald-600 flex items-center justify-center text-xl shadow-xs border border-emerald-200">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>

            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- 5. GRAFIK TREN PENJUALAN MINGGUAN/BULANAN & RINCIAN BEBAN             -->
        <!-- ===================================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- KIRI: GRAFIK PENJUALAN CHART.JS (COL-SPAN-2) -->
            <div
                class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Tren Penjualan Harian</h3>
                            <p class="text-xs text-slate-400 font-medium">Visualisasi omzet harian dan volume galon yang
                                terjual</p>
                        </div>

                        <!-- Saklar Tampilan Grafik -->
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl self-start sm:self-auto">
                            <button type="button" @click="activeMetric = 'revenue'; updateChart()"
                                :class="activeMetric === 'revenue' ? 'bg-white text-blue-600 font-black shadow-xs' :
                                    'text-slate-500 font-bold'"
                                class="px-3 py-1 text-xs rounded-xl transition cursor-pointer">
                                Omzet (Rp)
                            </button>
                            <button type="button" @click="activeMetric = 'gallons'; updateChart()"
                                :class="activeMetric === 'gallons' ? 'bg-white text-emerald-600 font-black shadow-xs' :
                                    'text-slate-500 font-bold'"
                                class="px-3 py-1 text-xs rounded-xl transition cursor-pointer">
                                Galon (Unit)
                            </button>
                        </div>
                    </div>

                    <!-- Kontainer Canvas Grafik -->
                    <div class="mt-4 relative h-72 sm:h-80 w-full">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>

                <div
                    class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 font-medium">
                    <span>Sumber: Transaksi POS Lunas non-batal</span>
                    <span class="font-bold text-slate-600">Sultan Water Analytics</span>
                </div>
            </div>

            <!-- KANAN: BREAKDOWN BEBAN OPERASIONAL (3 KATEGORI) -->
            <div
                class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Breakdown Beban</h3>
                            <p class="text-xs text-slate-400 font-medium">Rincian 3 kelompok pengeluaran</p>
                        </div>
                        <a href="{{ route('admin.expenses.index') }}"
                            class="text-xs font-black text-blue-600 hover:underline">
                            + Catat
                        </a>
                    </div>

                    <div class="mt-5 space-y-4">
                        <!-- Kategori 1: Kemasan & Produksi -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-extrabold text-slate-700">1. Kemasan & Produksi</span>
                                <span class="font-black text-slate-900">
                                    Rp
                                    {{ number_format($financialSummary['breakdown_expenses']['kemasan_produksi'] ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Tutup galon segel, galon fisik, tisu galon</p>
                        </div>

                        <!-- Kategori 2: Operasional & Logistik -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-extrabold text-slate-700">2. Operasional & Logistik</span>
                                <span class="font-black text-slate-900">
                                    Rp
                                    {{ number_format($financialSummary['breakdown_expenses']['operasional_logistik'] ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Bensin kurir, servis motor, ganti filter/lampu UV
                            </p>
                        </div>

                        <!-- Kategori 3: Beban Tetap -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-extrabold text-slate-700">3. Beban Tetap</span>
                                <span class="font-black text-slate-900">
                                    Rp
                                    {{ number_format($financialSummary['breakdown_expenses']['beban_tetap'] ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Air baku tangki, listrik depot, biaya sewa</p>
                        </div>
                    </div>
                </div>

                <!-- Navigasi Cepat Admin -->
                <div class="pt-5 border-t border-slate-100 mt-5">
                    <a href="{{ route('admin.expenses.index') }}"
                        class="w-full py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-list-check text-xs"></i>
                        <span>Buka Riwayat Beban Operasional</span>
                    </a>
                </div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <!-- Library CDN Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function ownerDashboardApp() {
            return {
                activeMetric: 'revenue',
                chartInstance: null,
                rawChartData: @json($chartData),

                init() {
                    this.$nextTick(() => {
                        this.renderChart();
                    });
                },

                renderChart() {
                    const ctx = document.getElementById('salesTrendChart');
                    if (!ctx) return;

                    const isRevenue = this.activeMetric === 'revenue';
                    const datasetData = isRevenue ? this.rawChartData.revenue : this.rawChartData.gallons;
                    const labelTitle = isRevenue ? 'Omzet Penjualan (Rp)' : 'Jumlah Galon Terjual (Unit)';
                    const borderColor = isRevenue ? '#2563eb' : '#059669';
                    const backgroundColor = isRevenue ? 'rgba(37, 99, 235, 0.1)' : 'rgba(5, 150, 105, 0.1)';

                    this.chartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: this.rawChartData.labels,
                            datasets: [{
                                label: labelTitle,
                                data: datasetData,
                                borderColor: borderColor,
                                backgroundColor: backgroundColor,
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: borderColor,
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    titleFont: {
                                        size: 12,
                                        weight: 'bold',
                                        family: "'Plus Jakarta Sans', sans-serif"
                                    },
                                    bodyFont: {
                                        size: 12,
                                        family: "'Plus Jakarta Sans', sans-serif"
                                    },
                                    padding: 10,
                                    cornerRadius: 10,
                                    callbacks: {
                                        label: function(context) {
                                            let val = context.parsed.y;
                                            if (isRevenue) {
                                                return 'Omzet: Rp ' + Number(val).toLocaleString('id-ID');
                                            }
                                            return 'Galon: ' + Number(val).toLocaleString('id-ID') + ' unit';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11,
                                            family: "'Plus Jakarta Sans', sans-serif"
                                        },
                                        color: '#94a3b8'
                                    }
                                },
                                y: {
                                    border: {
                                        dash: [4, 4]
                                    },
                                    grid: {
                                        color: '#f1f5f9'
                                    },
                                    ticks: {
                                        font: {
                                            size: 11,
                                            family: "'Plus Jakarta Sans', sans-serif"
                                        },
                                        color: '#94a3b8',
                                        callback: function(value) {
                                            if (isRevenue) {
                                                if (value >= 1000000) return (value / 1000000) + ' Jt';
                                                if (value >= 1000) return (value / 1000) + ' Rb';
                                                return value;
                                            }
                                            return value;
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                updateChart() {
                    if (this.chartInstance) {
                        this.chartInstance.destroy();
                    }
                    this.renderChart();
                }
            };
        }
    </script>
@endpush
