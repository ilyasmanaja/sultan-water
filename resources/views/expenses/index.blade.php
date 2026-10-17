@extends('layouts.app')

@section('title', 'Beban Operasional - Sultan Water')

@section('body-attributes')
    x-data="expensesApp()"
@endsection

@section('page-title', 'Beban Operasional Depot')

@section('page-badge')
    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
        Admin Only
    </span>
@endsection

@section('page-subtitle')
    <span>Pencatatan biaya bahan baku, logistik kurir, penggantian filter/UV, dan beban tetap</span>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2">
        <button type="button" @click="openExpenseModal = true"
            class="min-h-[42px] px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Catat Beban Baru</span>
        </button>
    </div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Flash Message Notifikasi Sukses -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <div class="font-black text-emerald-950">Aksi Berhasil!</div>
                    <div class="font-medium mt-0.5">{{ session('success') }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Alert Error Validasi Server -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-3 shadow-xs">
            <i class="fa-solid fa-circle-exclamation text-rose-500 text-base mt-0.5 shrink-0"></i>
            <div>
                <div class="font-extrabold text-rose-900 mb-1">Gagal menyimpan pengeluaran:</div>
                <ul class="list-disc list-inside space-y-0.5 font-semibold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 1. KARTU RINGKASAN TOTAL PENGELUARAN & PANDUAN KATEGORI                  -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- KARTU TOTAL SESUAI FILTER -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-400">Total Pengeluaran (Tersaring)</span>
                <div class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm border border-amber-100">
                    <i class="fa-solid fa-calculator"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900">
                    Rp {{ number_format($totalAmount, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-400 font-medium mt-1">
                    Akumulasi beban operasional dari filter yang sedang aktif
                </p>
            </div>
        </div>

        <!-- KARTU INFORMASI FILTER/UV -->
        <div class="md:col-span-2 bg-gradient-to-r from-blue-50/70 to-indigo-50/50 rounded-3xl p-5 border border-blue-100 shadow-xs flex items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-blue-500/20">
                    <i class="fa-solid fa-circle-info text-base"></i>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-black text-blue-950">
                        Pencatatan Biaya Perawatan & Penggantian Filter / UV
                    </h4>
                    <p class="text-[11px] sm:text-xs text-blue-800/80 font-medium mt-1 leading-relaxed">
                        Sesuai skema database enum 3 kategori, biaya penggantian filter sedimen, karbon aktif, atau lampu UV dicatat di bawah kategori 
                        <strong class="font-extrabold text-blue-950">Operasional & Logistik</strong> dengan detail komponen dituliskan pada judul pengeluaran.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. TOOLBAR FILTER & TABEL RIWAYAT BEBAN OPERASIONAL                       -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">

        <!-- Form Filter Toolbar -->
        <form action="{{ route('admin.expenses.index') }}" method="GET" class="p-4 sm:p-5 border-b border-slate-100">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">

                <!-- Pencarian Judul & Kategori -->
                <div class="flex flex-wrap items-center gap-2 flex-1">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-60">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul pengeluaran..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-slate-800">
                    </div>

                    <!-- Dropdown Kategori -->
                    <select name="category" class="min-h-[38px] px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Semua Kategori --</option>
                        <option value="kemasan_produksi" {{ request('category') === 'kemasan_produksi' ? 'selected' : '' }}>Kemasan & Produksi</option>
                        <option value="operasional_logistik" {{ request('category') === 'operasional_logistik' ? 'selected' : '' }}>Operasional & Logistik</option>
                        <option value="beban_tetap" {{ request('category') === 'beban_tetap' ? 'selected' : '' }}>Beban Tetap</option>
                    </select>
                </div>

                <!-- Pemilih Bulan, Tahun & Tombol Filter -->
                <div class="flex flex-wrap items-center gap-2">
                    <select name="month" class="min-h-[38px] px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Semua Bulan --</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('month', now()->month) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>

                    <select name="year" class="min-h-[38px] px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Semua Tahun --</option>
                        @for ($y = now()->year; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ request('year', now()->year) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>

                    <button type="submit"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-2xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-filter text-[11px]"></i>
                        <span>Terapkan</span>
                    </button>

                    @if(request()->hasAny(['q', 'category', 'month', 'year']))
                        <a href="{{ route('admin.expenses.index') }}"
                            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl text-xs font-bold transition" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>

            </div>
        </form>

        <!-- Tabel Riwayat Beban -->
        <div class="overflow-x-auto touch-scroll">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-4 sm:px-6">Tanggal</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Judul Pengeluaran</th>
                        <th class="py-3 px-4">Catatan / Deskripsi</th>
                        <th class="py-3 px-4 text-right">Nominal</th>
                        <th class="py-3 px-4">Dicatat Oleh</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($expenses as $expense)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Tanggal -->
                            <td class="py-3.5 px-4 sm:px-6 font-bold text-slate-800 whitespace-nowrap">
                                {{ $expense->expense_date->format('d/m/Y') }}
                            </td>

                            <!-- Kategori Badge -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if ($expense->category === 'kemasan_produksi')
                                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase bg-blue-50 text-blue-700 border border-blue-200">
                                        Kemasan & Produksi
                                    </span>
                                @elseif ($expense->category === 'operasional_logistik')
                                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase bg-amber-50 text-amber-700 border border-amber-200">
                                        Operasional & Logistik
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase bg-purple-50 text-purple-700 border border-purple-200">
                                        Beban Tetap
                                    </span>
                                @endif
                            </td>

                            <!-- Judul -->
                            <td class="py-3.5 px-4 font-black text-slate-900">
                                {{ $expense->title }}
                            </td>

                            <!-- Deskripsi / Catatan -->
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate" title="{{ $expense->description }}">
                                {{ $expense->description ?: '-' }}
                            </td>

                            <!-- Nominal -->
                            <td class="py-3.5 px-4 text-right font-black text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
                            </td>

                            <!-- Dicatat Oleh -->
                            <td class="py-3.5 px-4 text-slate-500 font-medium whitespace-nowrap">
                                {{ $expense->createdBy->name ?? 'Admin' }}
                            </td>

                            <!-- Aksi Hapus -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <button type="button" @click="confirmDelete('{{ route('admin.expenses.destroy', $expense->id) }}', '{{ addslashes($expense->title) }}', '{{ number_format($expense->amount, 0, ',', '.') }}')"
                                    class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 flex items-center justify-center transition cursor-pointer"
                                    title="Hapus Pencatatan">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300"></i>
                                <p class="font-semibold text-xs">Belum ada catatan pengeluaran pada periode ini</p>
                                <button type="button" @click="openExpenseModal = true"
                                    class="mt-2 text-blue-600 font-extrabold hover:underline cursor-pointer">
                                    + Catat pengeluaran baru sekarang
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        @if ($expenses->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $expenses->links() }}
            </div>
        @endif

    </div>

</div>
@endsection

@section('modals')
    <!-- ========================================================================= -->
    <!-- MODAL FORM: INPUT PENGELUARAN OPERASIONAL BARU                           -->
    <!-- ========================================================================= -->
    <div x-show="openExpenseModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        
        <div class="bg-white rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 overflow-y-auto max-h-[92vh] touch-scroll"
            @click.away="openExpenseModal = false">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900">Catat Pengeluaran Operasional</h3>
                    <p class="text-xs text-slate-400">Pastikan nominal dan kategori terisi dengan benar</p>
                </div>
                <button type="button" @click="openExpenseModal = false"
                    class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Form Body -->
            <form action="{{ route('admin.expenses.store') }}" method="POST" class="mt-4 space-y-4" @submit="validateBeforeSubmit($event)">
                @csrf

                <!-- 1. Judul Pengeluaran -->
                <div>
                    <label for="form_title" class="block text-xs font-bold text-slate-600 mb-1.5">
                        Judul Pengeluaran <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="form_title" name="title" x-model="form.title" required maxlength="100"
                        placeholder="Mis. Ganti Filter Karbon Aktif / Bensin Kurir"
                        class="w-full min-h-[42px] px-3.5 py-2 text-xs rounded-2xl bg-slate-50 border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-[10px] text-slate-400 font-medium mt-1">
                        Maksimal 100 karakter. Rincikan jenis barang/komponen bila berkaitan dengan perawatan depot.
                    </p>
                </div>

                <!-- 2. Kategori Pengeluaran (Enum 3 Kelompok) -->
                <div>
                    <label for="form_category" class="block text-xs font-bold text-slate-600 mb-1.5">
                        Kategori Beban <span class="text-rose-500">*</span>
                    </label>
                    <select id="form_category" name="category" x-model="form.category" required
                        class="w-full min-h-[42px] px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Kategori Beban --</option>
                        <option value="kemasan_produksi">Kemasan & Produksi (Tutup, Galon, Tisu)</option>
                        <option value="operasional_logistik">Operasional & Logistik (Bensin, Servis, Filter/UV)</option>
                        <option value="beban_tetap">Beban Tetap (Listrik, Sewa, Air Baku Tangki)</option>
                    </select>
                </div>

                <!-- 3. Nominal & Tanggal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="form_amount" class="block text-xs font-bold text-slate-600 mb-1.5">
                            Nominal Biaya (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" id="form_amount" name="amount" min="1" step="1" x-model.number="form.amount" required
                            placeholder="0"
                            class="w-full min-h-[42px] px-3.5 py-2 text-xs rounded-2xl bg-slate-50 border border-slate-200 font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="form_date" class="block text-xs font-bold text-slate-600 mb-1.5">
                            Tanggal Beban <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="form_date" name="expense_date" x-model="form.expense_date" required
                            class="w-full min-h-[42px] px-3.5 py-2 text-xs rounded-2xl bg-slate-50 border border-slate-200 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- 4. Deskripsi / Catatan Tambahan -->
                <div>
                    <label for="form_desc" class="block text-xs font-bold text-slate-600 mb-1.5">
                        Catatan Tambahan (Opsional)
                    </label>
                    <textarea id="form_desc" name="description" rows="2" x-model="form.description" maxlength="1000"
                        placeholder="Mis. Pembelian 2 unit housing filter sedimen 10 inch"
                        class="w-full px-3.5 py-2 text-xs rounded-2xl bg-slate-50 border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>

                <!-- Validation Error Box -->
                <div x-show="errorMessage" x-cloak
                    class="p-3 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 shrink-0"></i>
                    <span x-text="errorMessage"></span>
                </div>

                <!-- Submit Button -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="openExpenseModal = false"
                        class="px-4 py-2.5 rounded-2xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="min-h-[44px] px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Simpan Pengeluaran</span>
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL KONFIRMASI HAPUS PENGELUARAN                                       -->
    <!-- ========================================================================= -->
    <div x-show="openDeleteModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        
        <div class="bg-white rounded-3xl max-w-sm w-full p-5 sm:p-6 shadow-2xl border border-slate-200 text-center"
            @click.away="openDeleteModal = false">

            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mx-auto mb-3 border border-rose-100">
                <i class="fa-solid fa-trash-can"></i>
            </div>

            <h3 class="text-base font-black text-slate-900">Hapus Pencatatan Beban?</h3>
            <p class="text-xs text-slate-500 mt-1">
                Apakah Anda yakin ingin menghapus data pengeluaran <strong class="text-slate-900" x-text="deleteData.title"></strong> 
                sebesar <strong class="text-rose-600" x-text="'Rp ' + deleteData.amount"></strong>?
            </p>

            <form :action="deleteData.url" method="POST" class="mt-5 flex items-center justify-center gap-2">
                @csrf
                @method('DELETE')

                <button type="button" @click="openDeleteModal = false"
                    class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-xs transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs shadow-md shadow-rose-500/20 transition cursor-pointer">
                    Ya, Hapus
                </button>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
<script>
    function expensesApp() {
        return {
            openExpenseModal: false,
            openDeleteModal: false,
            errorMessage: '',

            form: {
                title: '',
                category: '',
                amount: '',
                expense_date: '{{ now()->toDateString() }}',
                description: ''
            },

            deleteData: {
                url: '',
                title: '',
                amount: ''
            },

            validateBeforeSubmit(event) {
                this.errorMessage = '';

                if (!this.form.title.trim()) {
                    this.errorMessage = 'Judul pengeluaran wajib diisi.';
                    event.preventDefault();
                    return;
                }

                if (!this.form.category) {
                    this.errorMessage = 'Kategori beban wajib dipilih.';
                    event.preventDefault();
                    return;
                }

                if (!this.form.amount || this.form.amount <= 0) {
                    this.errorMessage = 'Nominal biaya harus berupa angka positif minimal Rp1.';
                    event.preventDefault();
                    return;
                }

                if (!this.form.expense_date) {
                    this.errorMessage = 'Tanggal pengeluaran wajib diisi.';
                    event.preventDefault();
                    return;
                }
            },

            confirmDelete(url, title, formattedAmount) {
                this.deleteData.url = url;
                this.deleteData.title = title;
                this.deleteData.amount = formattedAmount;
                this.openDeleteModal = true;
            }
        };
    }
</script>
@endpush