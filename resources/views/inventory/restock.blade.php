@extends('layouts.app')

@section('title', 'Restock & Kelola Galon - Sultan Water')

@section('body-attributes')
    x-data="restockApp()"
@endsection

@section('page-title', 'Restock & Inventaris Galon')

@section('page-badge')
    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
        Admin Only
    </span>
@endsection

@section('page-subtitle')
    <span x-text="todayDate"></span>
@endsection

@section('header-actions')
    <a href="{{ route('admin.dashboard') }}"
        class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
        <i class="fa-solid fa-arrow-left text-[11px]"></i>
        <span>Kembali ke Dashboard</span>
    </a>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-5">

        <!-- Alert Global Error Validasi (DOC-VAL-POS-02 v1.3 Bagian 2.B) -->
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-3 shadow-xs">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-base mt-0.5"></i>
                <div>
                    <div class="font-extrabold text-rose-900 mb-1">Gagal memproses data inventaris:</div>
                    <ul class="list-disc list-inside space-y-0.5 font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Alert Sukses Umum (Termasuk Galon Rusak / Afkir) -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <div class="font-black text-emerald-950">Aksi Inventaris Berhasil!</div>
                        <div class="font-medium mt-0.5">{{ session('success') }}</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Alert Sukses Restock Tutup Galon -->
        @if (session('success_cap'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
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
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
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
        <!-- GRID FORM RESTOCK & AFKIR (TUTUP, PEMBELIAN GALON, & GALON RUSAK) -->
        <!-- ================================================================= -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- KARTU 1: RESTOCK TUTUP GALON -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <!-- Header Kartu -->
                    <div class="flex items-start justify-between gap-3 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0 border border-blue-100">
                                <i class="fa-solid fa-bottle-water"></i>
                            </div>
                            <div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-blue-50 text-blue-700">
                                    Bahan Habis Pakai
                                </span>
                                <h2 class="text-base font-black text-slate-900 mt-0.5">
                                    Restock Tutup Galon
                                </h2>
                            </div>
                        </div>

                        <!-- Indikator Saldo Saat Ini -->
                        <div class="text-right bg-slate-50 px-3 py-1.5 rounded-2xl border border-slate-200/70 shrink-0">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">Stok Tutup</span>
                            <span class="text-base font-black text-blue-600">
                                {{ number_format($capStock, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400">unit</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 font-medium mt-3 leading-relaxed">
                        Tambahkan stok tutup galon segel sekali pakai yang baru dibeli ke database.
                    </p>
                </div>

                <!-- Form Restock Tutup -->
                <form action="{{ route('inventory.restock.cap') }}" method="POST" class="mt-5 space-y-4" novalidate>
                    @csrf

                    <div>
                        <label for="cap_quantity" class="block text-xs font-bold text-slate-600 mb-1.5">
                            Jumlah Tutup Masuk <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl">
                            <button type="button" @click="if (capQty > 1) capQty--"
                                class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number" id="cap_quantity" name="quantity" min="1" step="1"
                                x-model="capQty" required placeholder="Jumlah..."
                                class="w-full text-center font-black text-base text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0">
                            <button type="button" @click="capQty = (parseInt(capQty) || 0) + 1"
                                class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>

                        <!-- Tombol Preset Cepat -->
                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                            <span class="text-[10px] text-slate-400 font-semibold mr-0.5">Isi Cepat:</span>
                            <button type="button" @click="capQty = 100"
                                class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-[10px] font-bold text-slate-600 transition cursor-pointer">+100</button>
                            <button type="button" @click="capQty = 500"
                                class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-[10px] font-bold text-slate-600 transition cursor-pointer">+500</button>
                            <button type="button" @click="capQty = 1000"
                                class="px-2 py-0.5 rounded-lg bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-[10px] font-bold text-slate-600 transition cursor-pointer">+1.000</button>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <button type="submit"
                            class="w-full min-h-[44px] px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-black text-xs rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Simpan Tutup Galon</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- KARTU 2: PEMBELIAN GALON BARU (TAMBAH ASET) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <!-- Header Kartu -->
                    <div class="flex items-start justify-between gap-3 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0 border border-emerald-100">
                                <i class="fa-solid fa-jar"></i>
                            </div>
                            <div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-emerald-50 text-emerald-700">
                                    Tambah Aset Galon
                                </span>
                                <h2 class="text-base font-black text-slate-900 mt-0.5">
                                    Beli Galon Baru
                                </h2>
                            </div>
                        </div>

                        <!-- Indikator Total Galon Dimiliki (G_total) -->
                        <div class="text-right bg-slate-50 px-3 py-1.5 rounded-2xl border border-slate-200/70 shrink-0">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">Total Dimiliki</span>
                            <span class="text-base font-black {{ $fleetTotal < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ number_format($fleetTotal, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400">unit</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 font-medium mt-3 leading-relaxed">
                        Catat pembelian galon baru dari distributor untuk menambah <strong>Total Galon Dimiliki</strong>.
                    </p>
                </div>

                <!-- Form Pembelian Galon Baru -->
                <form action="{{ route('inventory.restock.gallon') }}" method="POST" class="mt-4 space-y-4" novalidate>
                    @csrf

                    <div>
                        <label for="gallon_quantity" class="block text-xs font-bold text-slate-600 mb-1.5">
                            Jumlah Galon Baru <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl">
                            <button type="button" @click="if (gallonQty > 1) gallonQty--"
                                class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number" id="gallon_quantity" name="quantity" min="1" step="1"
                                x-model="gallonQty" required placeholder="Jumlah..."
                                class="w-full text-center font-black text-base text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0">
                            <button type="button" @click="gallonQty = (parseInt(gallonQty) || 0) + 1"
                                class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Pilihan Alokasi Restock -->
                    <div>
                        <label for="allocation" class="block text-xs font-bold text-slate-600 mb-1.5">
                            Alokasi Pembelian <span class="text-rose-500">*</span>
                        </label>
                        <select id="allocation" name="allocation" x-model="allocation" required
                            class="w-full min-h-[42px] px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Tujuan Alokasi --</option>
                            <option value="stok_dijual">Stok Dijual (Beli Putus Galon Baru)</option>
                            <option value="armada_depot">Armada Depot (Pinjaman / Standby)</option>
                        </select>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <button type="submit"
                            class="w-full min-h-[44px] px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white font-black text-xs rounded-2xl shadow-md shadow-emerald-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Simpan Galon Baru</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- KARTU 3: CATAT GALON RUSAK / AFKIR (PENGURANGAN ASET) -->
            <div class="bg-white rounded-3xl border border-rose-200/80 shadow-xs p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <!-- Header Kartu -->
                    <div class="flex items-start justify-between gap-3 pb-4 border-b border-rose-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0 border border-rose-100">
                                <i class="fa-solid fa-trash-can"></i>
                            </div>
                            <div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-rose-50 text-rose-700">
                                    Pengurangan Aset
                                </span>
                                <h2 class="text-base font-black text-slate-900 mt-0.5">
                                    Galon Rusak / Afkir
                                </h2>
                            </div>
                        </div>

                        <!-- Status Icon -->
                        <div class="text-right bg-rose-50/70 px-3 py-1.5 rounded-2xl border border-rose-200/60 shrink-0">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-rose-500 block">Afkir Fisik</span>
                            <span class="text-xs font-black text-rose-700">Kurangi G<sub>total</sub></span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 font-medium mt-3 leading-relaxed">
                        Catat galon depot yang pecah, bocor, atau tidak layak pakai. Mengurangi total kepemilikan galon depot secara permanen.
                    </p>
                </div>

                <!-- Form Catat Galon Rusak -->
                <form action="{{ route('inventory.gallon.write-off') }}" method="POST" class="mt-4 space-y-4" novalidate
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus galon yang rusak ini dari inventaris depot?');">
                    @csrf

                    <div>
                        <label for="writeoff_quantity" class="block text-xs font-bold text-slate-600 mb-1.5">
                            Jumlah Galon Rusak <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl">
                            <button type="button" @click="if (writeOffQty > 1) writeOffQty--"
                                class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number" id="writeoff_quantity" name="quantity" min="1" step="1"
                                x-model="writeOffQty" required placeholder="Jumlah..."
                                class="w-full text-center font-black text-base text-rose-700 bg-transparent border-none focus:outline-none focus:ring-0 p-0">
                            <button type="button" @click="writeOffQty = (parseInt(writeOffQty) || 0) + 1"
                                class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="writeoff_reason" class="block text-xs font-bold text-slate-600 mb-1.5">
                            Penyebab Kerusakan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="writeoff_reason" name="reason" x-model="writeOffReason" required
                            placeholder="Mis: Pecah saat pencucian, retak aus usia..."
                            class="w-full min-h-[42px] px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500">
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <button type="submit"
                            class="w-full min-h-[44px] px-4 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-98 text-white font-black text-xs rounded-2xl shadow-md shadow-rose-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                            <span>Catat Galon Rusak</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
<script>
    function restockApp() {
        return {
            todayDate: '',
            capQty: 100,
            gallonQty: 10,
            writeOffQty: 1,
            writeOffReason: '',
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
@endpush