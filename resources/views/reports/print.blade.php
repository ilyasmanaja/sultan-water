@extends('layouts.app')

@section('title', 'Cetak Laporan Keuangan - Sultan Water')

@section('page-title', 'Cetak Laporan Keuangan')

@section('page-badge')
    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200 print:hidden">
        Ukuran A4
    </span>
@endsection

@section('page-subtitle')
    <span class="print:hidden">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</span>
@endsection

@push('styles')
<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }

        body {
            background-color: #ffffff !important;
            color: #000000 !important;
            font-size: 11pt !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Sembunyikan seluruh elemen navigasi, header sistem, dan tombol aksi */
        aside,
        header,
        .print\:hidden {
            display: none !important;
        }

        .print-container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }

        .page-break-before {
            page-break-before: always !important;
        }

        .no-break-inside {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        /* Hindari border abu-abu pudar di kertas fisik */
        .border-slate-200, .border-slate-100 {
            border-color: #cbd5e1 !important;
        }
    }
</style>
@endpush

@section('header-actions')
    <!-- Bilah Aksi Pengunduhan & Pencetakan -->
    <div class="flex items-center gap-2 print:hidden">
        <a href="{{ route('admin.dashboard') }}"
            class="px-3.5 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            <span class="hidden sm:inline">Kembali</span>
        </a>

        <!-- Unduh Spreadsheet (Excel/CSV) -->
        <a href="{{ route('admin.reports.export.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
            class="px-3.5 py-2 rounded-2xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-black transition flex items-center gap-1.5 cursor-pointer"
            title="Unduh format spreadsheet">
            <i class="fa-solid fa-file-excel text-emerald-600"></i>
            <span class="hidden sm:inline">Unduh Excel</span>
        </a>

        <!-- Unduh PDF Resmi -->
        <a href="{{ route('admin.reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
            class="px-3.5 py-2 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 text-xs font-black transition flex items-center gap-1.5 cursor-pointer"
            title="Unduh format PDF">
            <i class="fa-solid fa-file-pdf text-rose-600"></i>
            <span class="hidden sm:inline">Unduh PDF</span>
        </a>

        <!-- Tombol Cetak Browser Langsung -->
        <button type="button" onclick="window.print()"
            class="min-h-[40px] px-5 py-2 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-print"></i>
            <span>Cetak Dokumen</span>
        </button>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Petunjuk Singkat Print Preview -->
    <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-200 text-blue-900 text-xs flex items-center justify-between gap-3 print:hidden">
        <div class="flex items-center gap-2.5">
            <i class="fa-solid fa-circle-info text-blue-600 text-sm shrink-0"></i>
            <span>Halaman ini sudah dioptimasi untuk kertas standar <strong>A4 Potret</strong>. Klik tombol <strong>Cetak Dokumen</strong> untuk langsung mencetak atau simpan via print dialog.</span>
        </div>
        <button type="button" onclick="window.print()" class="text-blue-700 font-black hover:underline shrink-0">
            Cetak Sekarang
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- KERTAS LAPORAN A4 (LEMBAR KERJA UTAMA)                                    -->
    <!-- ========================================================================= -->
    <div class="print-container bg-white rounded-3xl border border-slate-200/80 shadow-md p-8 sm:p-12 text-slate-800">

        <!-- KOP DOKUMEN DEPOT SULTAN WATER -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl border border-slate-300 p-1 flex items-center justify-center shrink-0">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Sultan Water"
                        class="w-full h-full object-contain"
                        onerror="this.onerror=null; this.src='https://placehold.co/100x100/2563eb/ffffff?text=SW';">
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight uppercase">
                        Depot Air Minum Sultan Water
                    </h1>
                    <p class="text-xs text-slate-600 font-medium mt-0.5">
                        Layanan Air Minum Isi Ulang Higienis, Galon Baru & Pengantaran Cepat
                    </p>
                    <p class="text-[11px] text-slate-500 font-medium">
                        Jl. Rajawali No. 12, Pekanbaru, Riau • Telp / WhatsApp: 0812-XXXX-XXXX
                    </p>
                </div>
            </div>

            <div class="text-right">
                <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-300">
                    Dokumen Keuangan
                </span>
                <div class="text-[11px] text-slate-500 font-medium mt-2">
                    Waktu Cetak: <span class="font-bold text-slate-800">{{ $printedAt }}</span>
                </div>
            </div>
        </div>

        <!-- JUDUL & PERIODE LAPORAN -->
        <div class="py-6 text-center border-b border-slate-200">
            <h2 class="text-base sm:text-lg font-black text-slate-900 uppercase tracking-wide">
                Laporan Kinerja Keuangan & Rekapitulasi Operasional
            </h2>
            <p class="text-xs font-bold text-slate-600 mt-1">
                Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
            </p>
        </div>

        <!-- ===================================================================== -->
        <!-- 1. RINGKASAN EKSEKUTIF LABA BERSIH (NP = R - E)                       -->
        <!-- ===================================================================== -->
        <div class="mt-6 no-break-inside">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3">
                I. Ringkasan Eksekutif Keuangan (NP = R - E)
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left border border-slate-300 text-xs">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-300 text-slate-700 font-black">
                            <th class="py-2.5 px-3">Komponen Finansial</th>
                            <th class="py-2.5 px-3 text-right">Nominal (Rupiah)</th>
                            <th class="py-2.5 px-3">Keterangan Sumber Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        <tr>
                            <td class="py-2 px-3 font-bold text-slate-900">Total Omzet Penjualan (R)</td>
                            <td class="py-2 px-3 text-right font-black text-slate-900">
                                Rp {{ number_format($summary['revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-slate-500 text-[11px]">Akumulasi pesanan lunas non-batal</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-3 text-slate-700 pl-6">• Beban Kemasan & Produksi</td>
                            <td class="py-2 px-3 text-right text-slate-700">
                                Rp {{ number_format($summary['breakdown_expenses']['kemasan_produksi'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-slate-500 text-[11px]">Tutup galon segel, galon baru, tisu</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-3 text-slate-700 pl-6">• Beban Operasional & Logistik</td>
                            <td class="py-2 px-3 text-right text-slate-700">
                                Rp {{ number_format($summary['breakdown_expenses']['operasional_logistik'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-slate-500 text-[11px]">Bensin kurir, servis, ganti filter & lampu UV</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-3 text-slate-700 pl-6">• Beban Beban Tetap</td>
                            <td class="py-2 px-3 text-right text-slate-700">
                                Rp {{ number_format($summary['breakdown_expenses']['beban_tetap'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-slate-500 text-[11px]">Listrik depot, air baku tangki, sewa tempat</td>
                        </tr>
                        <tr class="bg-slate-50/80 font-bold">
                            <td class="py-2 px-3 text-slate-900">Total Beban Operasional (E)</td>
                            <td class="py-2 px-3 text-right font-black text-slate-900">
                                Rp {{ number_format($summary['expenses'], 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-slate-500 text-[11px]">Akumulasi seluruh pos biaya</td>
                        </tr>
                        <tr class="{{ $summary['is_profit'] ? 'bg-emerald-50/60' : 'bg-rose-50/60' }} font-black">
                            <td class="py-3 px-3 text-sm text-slate-900">Laba Bersih Depot (Net Profit: NP)</td>
                            <td class="py-3 px-3 text-right text-base {{ $summary['is_profit'] ? 'text-emerald-700' : 'text-rose-700' }}">
                                Rp {{ number_format($summary['net_profit'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $summary['is_profit'] ? 'bg-emerald-200 text-emerald-900' : 'bg-rose-200 text-rose-900' }}">
                                    {{ $summary['is_profit'] ? 'SURPLUS / LABA' : 'DEFISIT / RUGI' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-3 flex items-center justify-between text-xs text-slate-600 font-semibold px-1">
                <span>Volume Galon Terjual: <strong class="text-slate-900">{{ number_format($summary['total_gallons'], 0, ',', '.') }} Galon</strong></span>
                <span>Total Transaksi Selesai: <strong class="text-slate-900">{{ number_format($summary['total_orders'], 0, ',', '.') }} Transaksi</strong></span>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- 2. RINCIAN PENGELUARAN OPERASIONAL                                    -->
        <!-- ===================================================================== -->
        <div class="mt-8 no-break-inside">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-500">
                    II. Rincian Biaya Operasional (Expenses)
                </h3>
                <span class="text-[11px] font-bold text-slate-400">Total Data: {{ $expenses->count() }} Item</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border border-slate-300 text-xs">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-300 text-slate-700 font-black">
                            <th class="py-2 px-3 w-24">Tanggal</th>
                            <th class="py-2 px-3">Kategori</th>
                            <th class="py-2 px-3">Judul Pengeluaran & Rincian</th>
                            <th class="py-2 px-3 text-right w-32">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="py-1.5 px-3 text-slate-600 font-mono text-[11px]">
                                    {{ $expense->expense_date->format('d/m/Y') }}
                                </td>
                                <td class="py-1.5 px-3 text-[11px] font-bold text-slate-700 uppercase">
                                    {{ str_replace('_', ' ', $expense->category) }}
                                </td>
                                <td class="py-1.5 px-3 text-slate-900">
                                    <div class="font-bold">{{ $expense->title }}</div>
                                    @if ($expense->description)
                                        <div class="text-[10px] text-slate-500 mt-0.5">{{ $expense->description }}</div>
                                    @endif
                                </td>
                                <td class="py-1.5 px-3 text-right font-black text-slate-900">
                                    {{ number_format($expense->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-400 text-xs">
                                    Tidak ada catatan pengeluaran operasional pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 border-t-2 border-slate-300 font-black text-slate-900">
                            <td colspan="3" class="py-2 px-3 text-right uppercase text-[11px]">Subtotal Biaya (E):</td>
                            <td class="py-2 px-3 text-right">Rp {{ number_format($summary['expenses'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- 3. TANDA TANGAN & PENGESAHAN DOKUMEN                                  -->
        <!-- ===================================================================== -->
        <div class="mt-12 pt-6 border-t border-slate-200 no-break-inside">
            <div class="grid grid-cols-2 gap-8 text-center text-xs">
                <div>
                    <p class="text-slate-500 font-medium">Disiapkan Oleh (Kasir / Petugas):</p>
                    <div class="h-20 flex items-end justify-center">
                        <span class="font-bold text-slate-800 underline uppercase tracking-wide">
                            {{ auth()->user()->name ?? 'Petugas Depot' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Sistem POS Sultan Water</p>
                </div>

                <div>
                    <p class="text-slate-500 font-medium">Mengetahui & Menyetujui (Pemilik):</p>
                    <div class="h-20 flex items-end justify-center">
                        <span class="font-bold text-slate-900 underline uppercase tracking-wide">
                            Khairul Umam
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Owner Sultan Water</p>
                </div>
            </div>
        </div>

        <!-- FOOTER DOKUMEN SISTEM -->
        <div class="mt-8 pt-4 border-t border-dashed border-slate-200 text-center text-[10px] text-slate-400 font-mono">
            Dicetak secara otomatis oleh Sistem Informasi POS Sultan Water • Dokumen sah tanpa tanda tangan basah bila disertai stempel resmi depot.
        </div>

    </div>

</div>
@endsection