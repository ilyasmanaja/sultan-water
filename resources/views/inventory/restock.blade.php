@extends('layouts.app')

@section('title', 'Restock & Kelola Galon - Sultan Water')

@section('body-attributes')
    x-data="restockApp({{ $capStock ?? 0 }}, {{ $fleetTotal ?? 0 }})"
@endsection

@section('page-title', 'Restock & Inventaris Galon')

@section('page-badge')
    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
        Khusus Pemilik
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

        <!-- Alert Global Error Validasi -->
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

        <!-- Alert Sukses Umum -->
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

        <!-- Alert Peringatan Form Input Frontend -->
        <template x-if="validationWarning">
            <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-2xl flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                    <span class="font-bold" x-text="validationWarning"></span>
                </div>
                <button type="button" @click="validationWarning = ''" class="text-rose-500 hover:text-rose-800">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </template>

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
                        Catat penambahan stok tutup galon baru hasil belanja/kulakan inventaris depot.
                    </p>
                </div>

                <!-- Form Restock Tutup -->
                <form id="form-restock-cap" action="{{ route('inventory.restock.cap') }}" method="POST" class="mt-5 space-y-4" novalidate>
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
                                x-model.number="capQty" required placeholder="Jumlah..."
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
                        <button type="button" @click="confirmCapRestock()"
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

                        <!-- Indikator Total Galon Dimiliki -->
                        <div class="text-right bg-slate-50 px-3 py-1.5 rounded-2xl border border-slate-200/70 shrink-0">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">Total Dimiliki</span>
                            <span class="text-base font-black {{ $fleetTotal < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ number_format($fleetTotal, 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400">unit</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 font-medium mt-3 leading-relaxed">
                        Catat pembelian galon baru dari distributor untuk menambah total kepemilikan aset depot.
                    </p>
                </div>

                <!-- Form Pembelian Galon Baru -->
                <form id="form-buy-gallon" action="{{ route('inventory.restock.gallon') }}" method="POST" class="mt-4 space-y-4" novalidate>
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
                                x-model.number="gallonQty" required placeholder="Jumlah..."
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
                            Tujuan Galon Baru <span class="text-rose-500">*</span>
                        </label>
                        <select id="allocation" name="allocation" x-model="allocation" required
                            class="w-full min-h-[42px] px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Pilih Tujuan Alokasi --</option>
                            <option value="armada_depot">Armada Depot (Pinjaman / Standby Rolling)</option>
                            <option value="stok_dijual">Stok Dijual (Beli Putus Pelanggan)</option>
                        </select>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <button type="button" @click="confirmGallonPurchase()"
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
                                    Galon Rusak / Pecah
                                </h2>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="text-right bg-rose-50/70 px-3 py-1.5 rounded-2xl border border-rose-200/60 shrink-0">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-rose-500 block">Afkir Fisik</span>
                            <span class="text-xs font-black text-rose-700">Potong Aset</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 font-medium mt-3 leading-relaxed">
                        Catat galon depot yang pecah, bocor, atau tidak layak pakai. Mengurangi total kepemilikan galon depot secara permanen.
                    </p>
                </div>

                <!-- Form Catat Galon Rusak -->
                <form id="form-write-off" action="{{ route('inventory.gallon.write-off') }}" method="POST" class="mt-4 space-y-4" novalidate>
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
                                x-model.number="writeOffQty" required placeholder="Jumlah..."
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
                        <button type="button" @click="confirmWriteOff()"
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

@section('modals')
    <!-- ========================================================================= -->
    <!-- MODAL KONFIRMASI INVENTARIS DINAMIS (TUTUP, BELI GALON, & GALON RUSAK)   -->
    <!-- ========================================================================= -->
    <div x-show="showConfirmModal" x-cloak
        class="fixed inset-0 z-60 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200"
            @click.away="showConfirmModal = false">

            <!-- Icon Header Dinamis -->
            <div class="text-center pb-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl mx-auto mb-2 border"
                    :class="confirmModal.iconBg + ' ' + confirmModal.iconColor">
                    <i :class="confirmModal.iconClass"></i>
                </div>
                <h3 class="text-base font-black text-slate-900" x-text="confirmModal.title"></h3>
                <p class="text-xs text-slate-400 mt-0.5" x-text="confirmModal.subtitle"></p>
            </div>

            <!-- Ringkasan Rincian Nilai -->
            <div class="my-4 p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-2 text-xs">
                <template x-for="(item, idx) in confirmModal.details" :key="idx">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium" x-text="item.label"></span>
                        <span class="font-bold text-slate-900" :class="item.class || ''" x-text="item.value"></span>
                    </div>
                </template>
            </div>

            <!-- Pesan Peringatan Khusus (Jika Ada) -->
            <template x-if="confirmModal.warning">
                <p class="text-[11px] p-2.5 rounded-xl border mb-4 flex items-center gap-2 leading-relaxed"
                    :class="confirmModal.warningBoxClass">
                    <i class="fa-solid fa-triangle-exclamation shrink-0"></i>
                    <span x-text="confirmModal.warning"></span>
                </p>
            </template>

            <!-- Tombol Aksi -->
            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="showConfirmModal = false"
                    class="py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 font-bold text-xs transition cursor-pointer">
                    Cek Lagi
                </button>
                <button type="button" @click="submitConfirmedForm()"
                    class="py-2.5 px-4 rounded-xl text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer"
                    :class="confirmModal.btnClass">
                    <i class="fa-solid fa-check text-xs"></i>
                    <span x-text="confirmModal.btnText"></span>
                </button>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script>
    function restockApp(initialCapStock = 0, initialFleetTotal = 0) {
        return {
            todayDate: '',
            capStock: initialCapStock,
            fleetTotal: initialFleetTotal,

            capQty: 100,
            gallonQty: 10,
            writeOffQty: 1,
            writeOffReason: '',
            allocation: '{{ old('allocation', '') }}',

            validationWarning: '',

            // State Modal Konfirmasi Dinamis
            showConfirmModal: false,
            confirmModal: {
                title: '',
                subtitle: '',
                iconClass: '',
                iconColor: '',
                iconBg: '',
                details: [],
                warning: '',
                warningBoxClass: '',
                btnText: '',
                btnClass: '',
                targetFormId: ''
            },

            init() {
                const now = new Date();
                this.todayDate = now.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            },

            // 1. Konfirmasi Tutup Galon
            confirmCapRestock() {
                this.validationWarning = '';
                if (!this.capQty || this.capQty < 1) {
                    this.validationWarning = 'Jumlah tutup masuk minimal 1 unit!';
                    return;
                }

                const futureStock = (Number(this.capStock) || 0) + Number(this.capQty);

                this.confirmModal = {
                    title: 'Konfirmasi Tutup Galon',
                    subtitle: 'Pastikan jumlah fisik tutup galon sesuai belanja',
                    iconClass: 'fa-solid fa-bottle-water',
                    iconColor: 'text-blue-600',
                    iconBg: 'bg-blue-50 border-blue-100',
                    details: [
                        { label: 'Stok Saat Ini', value: (this.capStock || 0).toLocaleString('id-ID') + ' unit' },
                        { label: 'Jumlah Tambahan', value: '+' + Number(this.capQty).toLocaleString('id-ID') + ' unit', class: 'text-blue-600 font-black' },
                        { label: 'Stok Setelah Disimpan', value: futureStock.toLocaleString('id-ID') + ' unit', class: 'text-emerald-700 font-black' }
                    ],
                    warning: '',
                    warningBoxClass: '',
                    btnText: 'Ya, Simpan',
                    btnClass: 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/20',
                    targetFormId: 'form-restock-cap'
                };

                this.showConfirmModal = true;
            },

            // 2. Konfirmasi Pembelian Galon Baru
            confirmGallonPurchase() {
                this.validationWarning = '';
                if (!this.gallonQty || this.gallonQty < 1) {
                    this.validationWarning = 'Jumlah galon baru minimal 1 unit!';
                    return;
                }
                if (!this.allocation) {
                    this.validationWarning = 'Tujuan alokasi galon baru wajib dipilih!';
                    return;
                }

                const allocationLabel = this.allocation === 'stok_dijual' 
                    ? 'Stok Dijual (Beli Putus)' 
                    : 'Armada Depot (Rolling/Pinjaman)';

                const futureFleet = (Number(this.fleetTotal) || 0) + Number(this.gallonQty);

                this.confirmModal = {
                    title: 'Konfirmasi Galon Baru',
                    subtitle: 'Penambahan aset galon fisik depot',
                    iconClass: 'fa-solid fa-jar',
                    iconColor: 'text-emerald-600',
                    iconBg: 'bg-emerald-50 border-emerald-100',
                    details: [
                        { label: 'Total Aset Saat Ini', value: (this.fleetTotal || 0).toLocaleString('id-ID') + ' unit' },
                        { label: 'Beli Galon Baru', value: '+' + Number(this.gallonQty).toLocaleString('id-ID') + ' unit', class: 'text-emerald-600 font-black' },
                        { label: 'Tujuan Alokasi', value: allocationLabel, class: 'text-slate-800 font-bold' },
                        { label: 'Aset Total Menjadi', value: futureFleet.toLocaleString('id-ID') + ' unit', class: 'text-emerald-700 font-black' }
                    ],
                    warning: '',
                    warningBoxClass: '',
                    btnText: 'Ya, Simpan',
                    btnClass: 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20',
                    targetFormId: 'form-buy-gallon'
                };

                this.showConfirmModal = true;
            },

            // 3. Konfirmasi Galon Rusak / Pecah
            confirmWriteOff() {
                this.validationWarning = '';
                if (!this.writeOffQty || this.writeOffQty < 1) {
                    this.validationWarning = 'Jumlah galon rusak minimal 1 unit!';
                    return;
                }
                if (!this.writeOffReason || this.writeOffReason.trim().length < 3) {
                    this.validationWarning = 'Penyebab kerusakan wajib diisi dengan jelas!';
                    return;
                }

                const futureFleet = (Number(this.fleetTotal) || 0) - Number(this.writeOffQty);

                this.confirmModal = {
                    title: 'Catat Galon Rusak?',
                    subtitle: 'Aset galon depot akan berkurang',
                    iconClass: 'fa-solid fa-trash-can',
                    iconColor: 'text-rose-600',
                    iconBg: 'bg-rose-50 border-rose-100',
                    details: [
                        { label: 'Total Aset Sekarang', value: (this.fleetTotal || 0).toLocaleString('id-ID') + ' unit' },
                        { label: 'Jumlah Rusak / Afkir', value: '-' + Number(this.writeOffQty).toLocaleString('id-ID') + ' unit', class: 'text-rose-600 font-black' },
                        { label: 'Penyebab', value: this.writeOffReason.trim() },
                        { label: 'Aset Tersisa Nanti', value: futureFleet.toLocaleString('id-ID') + ' unit', class: 'text-slate-900 font-black' }
                    ],
                    warning: 'Tindakan ini permanen memotong buku kepemilikan aset galon depot.',
                    warningBoxClass: 'bg-rose-50 text-rose-800 border-rose-200',
                    btnText: 'Ya, Hapus Aset',
                    btnClass: 'bg-rose-600 hover:bg-rose-700 shadow-rose-500/20',
                    targetFormId: 'form-write-off'
                };

                this.showConfirmModal = true;
            },

            submitConfirmedForm() {
                if (!this.confirmModal.targetFormId) return;
                const form = document.getElementById(this.confirmModal.targetFormId);
                if (form) {
                    form.submit();
                }
            }
        };
    }
</script>
@endpush