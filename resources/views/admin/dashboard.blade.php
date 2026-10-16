@extends('layouts.app')

@section('title', 'Dashboard Administrator - Sultan Water')
@section('page-title', 'Dashboard Administrator')

@section('page-badge')
    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
        Role: Admin
    </span>
@endsection

@section('page-subtitle')
    <span x-text="todayDate"></span>
@endsection

@section('body-attributes')
    x-data="adminDashboard()"
@endsection

@section('content')
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
            <p class="text-xs text-slate-500 font-medium mt-1 leading-relaxed">
                Saat ini Anda berada di halaman utama Administrator Sultan Water. Modul operasional inventaris, transaksi POS, dan manajemen harga siap diakses.
            </p>
        </div>

        <a href="{{ route('inventory.restock') }}"
            class="min-h-[44px] px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center gap-2 shrink-0 cursor-pointer">
            <i class="fa-solid fa-boxes-stacked text-xs"></i>
            <span>Buka Menu Restock</span>
        </a>
    </div>

    <!-- Shortcut Grid Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Pintasan 1: POS Transaksi Kasir -->
        <a href="{{ route('pos.dashboard') }}" 
            class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:border-blue-300 hover:shadow-md transition flex items-center gap-4 group">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shrink-0 border border-blue-100">
                <i class="fa-solid fa-cash-register"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900">Kasir Penjualan POS</h3>
                <p class="text-xs text-slate-400 mt-0.5">Input transaksi langsung walk-in maupun pesan antar.</p>
            </div>
        </a>

        <!-- Pintasan 2: Restock Inventaris -->
        <a href="{{ route('inventory.restock') }}" 
            class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:border-blue-300 hover:shadow-md transition flex items-center gap-4 group">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shrink-0 border border-emerald-100">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900">Restock Tutup & Galon Baru</h3>
                <p class="text-xs text-slate-400 mt-0.5">Kelola penambahan stok bahan dan aset armada galon.</p>
            </div>
        </a>

        <!-- Pintasan 3: Kelola 5-Tier Harga (Tiket #83) -->
        <a href="{{ route('admin.price-tiers.index') }}" 
            class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:border-blue-300 hover:shadow-md transition flex items-center gap-4 group">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition shrink-0 border border-purple-100">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div>
                <h3 class="text-sm font-black text-slate-900">Master Tier Harga</h3>
                <p class="text-xs text-slate-400 mt-0.5">Atur nominal 5 varian harga produk depot Sultan Water.</p>
            </div>
        </a>

        <!-- Pintasan 4: Laporan Keuangan (Sprint 4) -->
        <div class="bg-slate-50 p-5 rounded-3xl border border-slate-200/80 shadow-xs opacity-75 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-slate-200 text-slate-500 flex items-center justify-center text-lg font-bold shrink-0">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-black text-slate-700">Laporan & Laba Bersih</h3>
                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-slate-200 text-slate-600">Sprint 4</span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Agregasi omzet, beban operasional, dan laba bersih.</p>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
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
        };
    }
</script>
@endpush