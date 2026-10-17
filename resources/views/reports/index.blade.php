@extends('layouts.app')

@section('title', 'Laporan Keuangan - Sultan Water')

@section('page-title', 'Laporan Finansial & Operasional')

@section('page-badge')
    <span
        class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
        Admin Only
    </span>
@endsection

@section('page-subtitle')
    <span>Rekapitulasi omzet penjualan, pos biaya operasional, dan laba bersih depot</span>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2">
        <!-- Tombol Cetak / Print View -->
        <a href="{{ route('admin.reports.print', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-black shadow-md shadow-blue-500/20 transition flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-print"></i>
            <span>Cetak A4</span>
        </a>

        <!-- Unduh Excel -->
        <a href="{{ route('admin.reports.export.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
            class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-2xl text-xs font-black transition flex items-center gap-1.5 cursor-pointer"
            title="Unduh format spreadsheet">
            <i class="fa-solid fa-file-excel text-emerald-600"></i>
            <span class="hidden sm:inline">Excel</span>
        </a>

        <!-- Unduh PDF -->
        <a href="{{ route('admin.reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
            class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 rounded-2xl text-xs font-black transition flex items-center gap-1.5 cursor-pointer"
            title="Unduh dokumen PDF">
            <i class="fa-solid fa-file-pdf text-rose-600"></i>
            <span class="hidden sm:inline">PDF</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Filter Periode Laporan -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-4 sm:p-5">
            <form action="{{ route('admin.reports.index') }}" method="GET"
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800">Rentang Waktu Laporan</h3>
                        <p class="text-[11px] text-slate-400">Pilih periode tanggal awal dan tanggal akhir</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="min-h-[38px] px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="text-xs font-bold text-slate-400">-</span>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="min-h-[38px] px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                        class="min-h-[38px] px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-filter text-[10px]"></i>
                        <span>Tampilkan</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Ringkasan Finansial Utama -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Omzet -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 block mb-1">
                    Total Omzet (R)
                </span>
                <div class="text-2xl font-black text-slate-900">
                    Rp {{ number_format($summary['revenue'], 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-500 font-medium mt-1">
                    {{ $summary['total_orders'] }} Transaksi • {{ number_format($summary['total_gallons'], 0, ',', '.') }}
                    Galon Terjual
                </p>
            </div>

            <!-- Beban -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 block mb-1">
                    Beban Operasional (E)
                </span>
                <div class="text-2xl font-black text-slate-900">
                    Rp {{ number_format($summary['expenses'], 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-400 font-medium mt-1">
                    Akumulasi seluruh pengeluaran operasional
                </p>
            </div>

            <!-- Laba Bersih -->
            <div
                class="bg-white rounded-3xl p-5 border {{ $summary['is_profit'] ? 'border-emerald-200 bg-emerald-50/20' : 'border-rose-200 bg-rose-50/20' }} shadow-xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">
                        Laba Bersih (NP = R - E)
                    </span>
                    <span
                        class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ $summary['is_profit'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                        {{ $summary['is_profit'] ? 'Surplus' : 'Defisit' }}
                    </span>
                </div>
                <div class="text-2xl font-black {{ $summary['is_profit'] ? 'text-emerald-600' : 'text-rose-600' }}">
                    Rp {{ number_format($summary['net_profit'], 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-500 font-medium mt-1">
                    Hasil laba/rugi bersih periode berjalan
                </p>
            </div>
        </div>

        <!-- Rincian Breakdown Beban per 3 Kategori -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
            <h3 class="text-base font-black text-slate-900 tracking-tight mb-4">
                Rincian Beban Operasional per Kategori
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <span class="text-[11px] font-bold text-slate-500 uppercase block mb-1">1. Kemasan & Produksi</span>
                    <div class="text-xl font-black text-slate-900">
                        Rp {{ number_format($summary['breakdown_expenses']['kemasan_produksi'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Tutup segel, galon baru, dan perlengkapan produksi</p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <span class="text-[11px] font-bold text-slate-500 uppercase block mb-1">2. Operasional & Logistik</span>
                    <div class="text-xl font-black text-slate-900">
                        Rp {{ number_format($summary['breakdown_expenses']['operasional_logistik'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Bensin kurir, servis kendaraan, penggantian filter/UV</p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                    <span class="text-[11px] font-bold text-slate-500 uppercase block mb-1">3. Beban Tetap</span>
                    <div class="text-xl font-black text-slate-900">
                        Rp {{ number_format($summary['breakdown_expenses']['beban_tetap'] ?? 0, 0, ',', '.') }}
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Tagihan listrik depot, air baku tangki, dan sewa</p>
                </div>
            </div>
        </div>

        <!-- Tabel Rekapitulasi Tren Harian -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-900">Rekapitulasi Penjualan Harian</h3>
                <span class="text-xs text-slate-400 font-medium">Periode: {{ $startDate }} s/d
                    {{ $endDate }}</span>
            </div>

            <div class="overflow-x-auto touch-scroll">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-400">
                            <th class="py-3 px-4 sm:px-6">Tanggal</th>
                            <th class="py-3 px-4 text-center">Jumlah Transaksi</th>
                            <th class="py-3 px-4 text-center">Volume Galon</th>
                            <th class="py-3 px-4 text-right">Omzet Harian (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($summary['daily_trend'] as $day)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 sm:px-6 font-bold text-slate-800">
                                    {{ \Carbon\Carbon::parse($day['date'])->translatedFormat('d F Y') }}
                                </td>
                                <td class="py-3 px-4 text-center text-slate-600 font-medium">
                                    {{ $day['orders_count'] }} Order
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-blue-600">
                                    {{ $day['total_gallons'] }} Galon
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900">
                                    Rp {{ number_format($day['revenue'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">
                                    Belum ada data transaksi pada rentang tanggal ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TABEL DAFTAR TRANSAKSI PENJUALAN (LUNAS)                                 -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div
                class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-black text-slate-900">Daftar Transaksi Penjualan (Lunas)</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Seluruh pesanan selesai dan terbayar pada periode
                        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d
                        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                </div>
                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-xl self-start sm:self-auto">
                    Total: {{ $orders->count() }} Transaksi
                </span>
            </div>

            <div class="overflow-x-auto touch-scroll">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-400">
                            <th class="py-3 px-4 sm:px-6">No. Order</th>
                            <th class="py-3 px-4">Waktu Transaksi</th>
                            <th class="py-3 px-4">Tipe Pesanan</th>
                            <th class="py-3 px-4">Nama Pelanggan</th>
                            <th class="py-3 px-4">Rincian Item</th>
                            <th class="py-3 px-4 text-center">Metode Bayar</th>
                            <th class="py-3 px-4 text-right">Total Tagihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- No. Order -->
                                <td class="py-3.5 px-4 sm:px-6 font-mono font-extrabold text-blue-600 whitespace-nowrap">
                                    SW-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                </td>

                                <!-- Waktu Transaksi -->
                                <td class="py-3.5 px-4 text-slate-600 font-medium whitespace-nowrap">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>

                                <!-- Tipe Pesanan -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ $order->order_type === 'ambil_sendiri' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $order->order_type === 'ambil_sendiri' ? 'Walk-in' : 'Pesan Antar' }}
                                    </span>
                                </td>

                                <!-- Nama Pelanggan -->
                                <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap">
                                    @if ($order->customer_id)
                                        <a href="{{ route('customers.show', $order->customer_id) }}"
                                            class="text-blue-600 hover:underline inline-flex items-center gap-1"
                                            title="Buka profil pelanggan">
                                            <span>{{ $order->customer->name ?? 'Pelanggan Terdaftar' }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-blue-400"></i>
                                        </a>
                                    @else
                                        <span>Pelanggan Umum</span>
                                    @endif
                                </td>

                                <!-- Rincian Item -->
                                <td class="py-3.5 px-4">
                                    @foreach ($order->items as $item)
                                        <div class="text-[11px] text-slate-700">
                                            <span
                                                class="font-bold">{{ $item->priceTier->name ?? ($item->product->name ?? 'Air Minum') }}</span>
                                            <span class="text-slate-400">({{ $item->quantity }}x)</span>
                                        </div>
                                    @endforeach
                                </td>

                                <!-- Metode Pembayaran -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ strtoupper($order->payment_method) }}
                                    </span>
                                </td>

                                <!-- Total Tagihan -->
                                <td class="py-3.5 px-4 text-right font-black text-slate-900 whitespace-nowrap">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Tidak ada transaksi penjualan lunas pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
