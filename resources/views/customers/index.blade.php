@extends('layouts.app')

@section('title', 'Direktori Pelanggan & Galon Pinjaman - Sultan Water')

@section('page-title', 'Direktori Pelanggan & Galon Pinjaman')

@section('page-badge')
    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
        Aset Armada (Gp)
    </span>
@endsection

@section('page-subtitle')
    <span>Pemantauan seluruh pelanggan dan daftar aset unit galon fisik depot yang sedang dipinjam</span>
@endsection

@section('header-actions')
    <a href="{{ route('admin.dashboard') }}"
        class="px-3.5 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
        <i class="fa-solid fa-arrow-left text-[11px]"></i>
        <span>Dashboard</span>
    </a>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- ========================================================================= -->
    <!-- 1. KARTU RINGKASAN PEMANTAUAN GALON DIPINJAM                              -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <!-- KARTU 1: TOTAL GALON DIPINJAM (Gp TOTAL) -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 block mb-1">
                    Total Galon Dipinjam (Gp)
                </span>
                <div class="text-2xl sm:text-3xl font-black text-amber-900">
                    {{ number_format($totalBorrowedActive, 0, ',', '.') }}
                    <span class="text-xs font-bold text-amber-600">Unit</span>
                </div>
                <p class="text-[11px] text-slate-400 font-medium mt-1">
                    Unit fisik galon di tangan pelanggan
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-xs border border-amber-100">
                <i class="fa-solid fa-jar"></i>
            </div>
        </div>

        <!-- KARTU 2: JUMLAH PELANGGAN PEMINJAM -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">
                    Pelanggan Sedang Meminjam
                </span>
                <div class="text-2xl sm:text-3xl font-black text-slate-900">
                    {{ number_format($borrowerCount, 0, ',', '.') }}
                    <span class="text-xs font-bold text-slate-400">Orang</span>
                </div>
                <p class="text-[11px] text-slate-400 font-medium mt-1">
                    Memiliki saldo pinjaman &gt; 0
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-xs border border-blue-100">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <!-- KARTU 3: TOTAL PELANGGAN TERDAFTAR -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">
                    Total Pelanggan Terdaftar
                </span>
                <div class="text-2xl sm:text-3xl font-black text-slate-900">
                    {{ number_format($totalCustomers, 0, ',', '.') }}
                    <span class="text-xs font-bold text-slate-400">Akun</span>
                </div>
                <p class="text-[11px] text-slate-400 font-medium mt-1">
                    Basis data pelanggan aktif depot
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-600 flex items-center justify-center text-xl shadow-xs border border-slate-200">
                <i class="fa-solid fa-address-book"></i>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. TABEL DAFTAR PELANGGAN & SALDO PINJAMAN                               -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">

        <!-- Toolbar Filter & Search -->
        <form action="{{ route('admin.customers.index') }}" method="GET" class="p-4 sm:p-5 border-b border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">

                <!-- Tab Filter: Sedang Pinjam vs Semua -->
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl self-start sm:self-auto">
                    <a href="{{ route('admin.customers.index', ['filter' => 'borrowing', 'sort' => request('sort', 'borrowed_desc')]) }}"
                        class="px-3.5 py-1.5 text-xs rounded-xl font-bold transition {{ $filter === 'borrowing' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        Sedang Pinjam ({{ $borrowerCount }})
                    </a>
                    <a href="{{ route('admin.customers.index', ['filter' => 'all', 'sort' => request('sort', 'borrowed_desc')]) }}"
                        class="px-3.5 py-1.5 text-xs rounded-xl font-bold transition {{ $filter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                        Semua Pelanggan ({{ $totalCustomers }})
                    </a>
                </div>

                <!-- Input Pencarian & Urutan -->
                <div class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="filter" value="{{ $filter }}">

                    <div class="relative w-full sm:w-60">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / WhatsApp..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                    </div>

                    <select name="sort" onchange="this.form.submit()"
                        class="min-h-[38px] px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        <option value="borrowed_desc" {{ $sort === 'borrowed_desc' ? 'selected' : '' }}>Pinjaman Terbanyak</option>
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Nama (A - Z)</option>
                    </select>

                    <button type="submit"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl text-xs font-bold transition cursor-pointer">
                        Cari
                    </button>
                </div>

            </div>
        </form>

        <!-- Tabel Data -->
        <div class="overflow-x-auto touch-scroll">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-4 sm:px-6">Nama Pelanggan</th>
                        <th class="py-3 px-4">Kontak WhatsApp</th>
                        <th class="py-3 px-4">Alamat Pengantaran</th>
                        <th class="py-3 px-4 text-center">Status Galon Pinjaman (Gp)</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($customers as $cust)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Nama Pelanggan -->
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-900">
                                <a href="{{ route('customers.show', $cust->id) }}" class="text-blue-600 hover:underline flex items-center gap-1.5">
                                    <span>{{ $cust->name }}</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-blue-400"></i>
                                </a>
                            </td>

                            <!-- WhatsApp -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $cust->whatsapp_number) }}" target="_blank"
                                    class="text-emerald-700 font-bold hover:underline inline-flex items-center gap-1.5 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">
                                    <i class="fa-brands fa-whatsapp text-xs"></i>
                                    <span>{{ $cust->whatsapp_number }}</span>
                                </a>
                            </td>

                            <!-- Alamat -->
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate" title="{{ $cust->address }}">
                                {{ $cust->address ?: '-' }}
                            </td>

                            <!-- Saldo Pinjaman Gp -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if ($cust->borrowed_gallons > 0)
                                    <span class="px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-900 border border-amber-300 inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-jar text-[10px] text-amber-700"></i>
                                        <span>{{ $cust->borrowed_gallons }} Galon</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-400">
                                        Tidak Ada Pinjaman
                                    </span>
                                @endif
                            </td>

                            <!-- Tombol Aksi Detail -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('customers.show', $cust->id) }}"
                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 font-bold text-xs transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                                    <span>Riwayat Bulanan</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center text-xl mx-auto mb-2">
                                    <i class="fa-solid fa-check-circle"></i>
                                </div>
                                <p class="font-bold text-xs text-slate-600">Tidak ada pelanggan pada kategori ini</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $filter === 'borrowing' ? 'Semua galon pinjaman telah dikembalikan atau tidak ada pinjaman aktif.' : 'Belum ada data pelanggan yang terdaftar.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($customers->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $customers->links() }}
            </div>
        @endif

    </div>

</div>
@endsection