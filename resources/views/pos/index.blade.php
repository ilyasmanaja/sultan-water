@extends('layouts.app')

@section('title', 'POS Kasir - Sultan Water')

@section('body-attributes')
    x-data="posApp({{ Js::from($orders ?? []) }}, {{ Js::from($customers ?? []) }}, {{ Js::from($priceTiers ?? []) }}, {{ $tutupStock ?? 1000 }}, {{ $totalGalonDepot ?? 50 }})"
    @keydown.window.prevent.f2="openOrderModal = true"
@endsection

@push('styles')
    <style>
        @media print {

            #order-modal-backdrop,
            .receipt-modal-backdrop {
                background: transparent !important;
            }

            .receipt-modal-container {
                position: static !important;
                display: block !important;
                padding: 0 !important;
                background: transparent !important;
            }

            .receipt-card {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
@endpush

@section('nav-actions')
    <!-- Tombol Shortcut Tambah Transaksi Cepat di Sidebar Navigasi -->
    <button type="button" @click="openOrderModal = true" title="Tambah Transaksi Cepat (F2)"
        class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition active:scale-95 cursor-pointer">
        <i class="fa-solid fa-plus text-base"></i>
    </button>
@endsection

@section('page-title', 'Kasir Penjualan Sultan Water')

@section('page-subtitle')
    <span x-text="todayDate"></span>
@endsection

@section('header-actions')
    <div class="hidden sm:flex items-center gap-2">
        <span class="text-xs text-slate-400 font-semibold">Pintasan:</span>
        <kbd class="px-2 py-1 bg-slate-100 border border-slate-300 rounded-lg text-[10px] font-bold text-slate-600">
            F2: Transaksi Baru
        </kbd>
    </div>
@endsection

@section('content')
    <!-- Kontainer Tabel Kendali & Transaksi -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">

        <!-- Toolbar Atas Tabel -->
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">

            <!-- Filter Tabs & Search -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" placeholder="Cari order / nama pelanggan..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-2xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                </div>

                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl">
                    <button type="button" @click="statusFilter = 'all'"
                        :class="statusFilter === 'all' ? 'bg-white text-slate-900 font-bold shadow-xs' :
                            'text-slate-500 hover:text-slate-800'"
                        class="px-3 py-1.5 text-xs rounded-xl transition cursor-pointer">Semua</button>
                    <button type="button" @click="statusFilter = 'pending'"
                        :class="statusFilter === 'pending' ? 'bg-white text-amber-700 font-bold shadow-xs' :
                            'text-slate-500 hover:text-slate-800'"
                        class="px-3 py-1.5 text-xs rounded-xl transition cursor-pointer">Antrean Antar</button>
                    <button type="button" @click="statusFilter = 'selesai'"
                        :class="statusFilter === 'selesai' ? 'bg-white text-emerald-700 font-bold shadow-xs' :
                            'text-slate-500 hover:text-slate-800'"
                        class="px-3 py-1.5 text-xs rounded-xl transition cursor-pointer">Selesai</button>
                </div>
            </div>

            <!-- Tombol Utama "+ Transaksi Baru" -->
            <button type="button" @click="openOrderModal = true"
                class="min-h-[44px] px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Transaksi Baru</span>
                <kbd class="hidden sm:inline-block px-1.5 py-0.5 bg-blue-700/60 rounded text-[10px] font-mono">F2</kbd>
            </button>
        </div>

        <!-- Kontainer Tabel Data -->
        <div class="overflow-x-auto touch-scroll">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-4 sm:px-5">No. Order</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Tipe & Status</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Item & Opsi Galon</th>
                        <th class="py-3 px-4 text-right">Total Bayar</th>
                        <th class="py-3 px-4 text-center">Status Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">

                    <!-- Empty State -->
                    <template x-if="filteredOrders.length === 0">
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-clipboard-list text-3xl mb-2 text-slate-300"></i>
                                <p class="font-semibold text-xs">Belum ada transaksi hari ini</p>
                                <button type="button" @click="openOrderModal = true"
                                    class="mt-2 text-blue-600 font-extrabold hover:underline cursor-pointer">
                                    + Buat transaksi pertama sekarang
                                </button>
                            </td>
                        </tr>
                    </template>

                    <!-- Rows Transaksi -->
                    <template x-for="order in filteredOrders" :key="order.id">
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 sm:px-5 font-mono font-extrabold text-blue-600"
                                x-text="order.order_number"></td>
                            <td class="py-3.5 px-4 text-slate-400 font-medium" x-text="order.time"></td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase"
                                        :class="order.order_type === 'ambil_sendiri' ? 'bg-slate-100 text-slate-700' :
                                            'bg-amber-100 text-amber-800'"
                                        x-text="order.order_type === 'ambil_sendiri' ? 'Walk-in' : 'Antar'"></span>

                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border border-emerald-200': order
                                                .delivery_status === 'selesai',
                                            'bg-amber-50 text-amber-700 border border-amber-200 animate-pulse': order
                                                .delivery_status === 'pending',
                                            'bg-blue-50 text-blue-700 border border-blue-200': order
                                                .delivery_status === 'diantar'
                                        }"
                                        x-text="order.delivery_status"></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800" x-text="order.customer_name"></div>
                                <div class="text-[11px] text-slate-400 truncate max-w-[140px]"
                                    x-text="order.customer_address"></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800" x-text="order.tier_name"></div>
                                <div class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5 mt-0.5">
                                    <span class="font-bold text-slate-700"
                                        x-text="order.qty + 'x @ ' + rupiah(order.total_amount / order.qty)"></span>
                                    <span
                                        :class="{
                                            'text-emerald-600 font-semibold': order.gallon_action === 'tukar_seimbang',
                                            'text-amber-600 font-semibold': order.gallon_action === 'pinjam',
                                            'text-purple-600 font-semibold': order.gallon_action === 'kembalikan',
                                            'text-slate-400': order.gallon_action === 'tidak_ada'
                                        }"
                                        x-text="formatGallonAction(order.gallon_action, order.qty, order.tier_code)"></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-slate-900"
                                x-text="rupiah(order.total_amount)"></td>
                            <td class="py-3.5 px-4 text-center">
                                <span
                                    class="px-2.5 py-1 rounded-full font-black text-[10px] tracking-wide uppercase inline-flex items-center gap-1"
                                    :class="order.payment_status === 'lunas' ?
                                        'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                        'bg-amber-50 text-amber-700 border border-amber-200 animate-pulse'">
                                    <i class="fa-solid text-[9px]"
                                        :class="order.payment_status === 'lunas' ? 'fa-check' : 'fa-clock'"></i>
                                    <span x-text="order.payment_status === 'lunas' ? 'Lunas' : 'Belum Lunas (COD)'"></span>
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    </div>
@endsection

@section('modals')
    <!-- ========================================================================= -->
    <!-- MODAL FORM: TRANSAKSI BARU (5-TIER HARGA + LOGIKA GALON)                  -->
    <!-- ========================================================================= -->
    <div x-show="openOrderModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 overflow-y-auto max-h-[92vh] touch-scroll"
            @click.away="openOrderModal = false">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900">Transaksi Kasir Baru</h3>
                    <p class="text-xs text-slate-400">Pilih tier harga dan opsi galon fisik</p>
                </div>
                <button type="button" @click="openOrderModal = false"
                    class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <div class="mt-4 space-y-4">

                <!-- 1. Tipe Layanan (Walk-in vs Antar) -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Tipe Layanan</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="newOrder.orderType = 'ambil_sendiri'"
                            :class="newOrder.orderType === 'ambil_sendiri' ? 'bg-blue-600 text-white font-black shadow-xs' :
                                'bg-slate-50 text-slate-600 border border-slate-200'"
                            class="p-2.5 rounded-2xl text-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-store"></i>
                            <span>Walk-in (Ambil Sendiri)</span>
                        </button>
                        <button type="button" @click="newOrder.orderType = 'pesan_antar'"
                            :class="newOrder.orderType === 'pesan_antar' ? 'bg-blue-600 text-white font-black shadow-xs' :
                                'bg-slate-50 text-slate-600 border border-slate-200'"
                            class="p-2.5 rounded-2xl text-xs transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-motorcycle"></i>
                            <span>Pesan Antar (Kurir)</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Pilih Pelanggan -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold text-slate-600">Pelanggan</label>
                        <button type="button" @click="openQuickAddModal = true"
                            class="text-xs font-extrabold text-blue-600 hover:underline cursor-pointer">
                            + Tambah Baru
                        </button>
                    </div>
                    <select x-model="newOrder.customerId"
                        class="w-full min-h-[42px] px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500">
                        <option value="">👤 Pelanggan Umum (Walk-in)</option>
                        <template x-for="c in customers" :key="c.id">
                            <option :value="c.id" x-text="c.name + ' (' + c.whatsapp + ')'"></option>
                        </template>
                    </select>

                    <!-- Info Pinjaman Pelanggan Terpilih -->
                    <template x-if="selectedCustomer">
                        <div
                            class="mt-2 px-3 py-1.5 rounded-xl bg-blue-50/70 border border-blue-100 flex items-center justify-between text-xs">
                            <span class="text-blue-900 font-medium truncate max-w-[200px]"
                                x-text="selectedCustomer.address"></span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black"
                                :class="selectedCustomer.borrowed_gallons > 0 ? 'bg-amber-100 text-amber-800' :
                                    'bg-slate-100 text-slate-500'"
                                x-text="'Pinjam: ' + selectedCustomer.borrowed_gallons + ' Galon'"></span>
                        </div>
                    </template>
                </div>

                <!-- 3. PILIHAN 5-TIER HARGA -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Pilih Varian Produk & Tier Harga</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <template x-for="tier in priceTiers" :key="tier.id">
                            <button type="button" @click="selectTier(tier)"
                                :class="{
                                    'bg-blue-600 text-white shadow-sm ring-2 ring-blue-600': newOrder.priceTierId ===
                                        tier.id && tier.applies_to === 'isi_ulang',
                                    'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-600': newOrder
                                        .priceTierId === tier.id && tier.applies_to === 'galon_baru',
                                    'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200': newOrder
                                        .priceTierId !== tier.id && tier.applies_to === 'isi_ulang',
                                    'bg-emerald-50 text-emerald-900 border border-emerald-200 hover:bg-emerald-100': newOrder
                                        .priceTierId !== tier.id && tier.applies_to === 'galon_baru',
                                    'col-span-2 sm:col-span-2': tier.applies_to === 'galon_baru'
                                }"
                                class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[58px] active:scale-95 cursor-pointer">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-medium" x-text="tier.name"></span>
                                    <span x-show="tier.applies_to === 'galon_baru'"
                                        class="text-[9px] px-1.5 py-0.5 rounded font-extrabold uppercase bg-emerald-200 text-emerald-800"
                                        :class="newOrder.priceTierId === tier.id ? 'bg-white/20 text-white' : ''">Beli
                                        Putus</span>
                                </div>
                                <span class="text-sm font-black" x-text="rupiah(tier.price)"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 4. OPSI GALON FISIK -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">
                        Opsi Galon Fisik Pelanggan
                        <span x-show="newOrder.tierCode === 'galon_baru_isi'"
                            class="text-slate-400 font-normal">(Terkunci: Beli Putus)</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button type="button" :disabled="newOrder.tierCode === 'galon_baru_isi'"
                            @click="selectGallonAction('tukar_seimbang')"
                            :class="newOrder.gallonAction === 'tukar_seimbang' ?
                                'bg-emerald-50 border-emerald-500 text-emerald-900 font-black ring-1 ring-emerald-500' :
                                'bg-slate-50 border-slate-200 text-slate-600'"
                            class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px] disabled:opacity-40 disabled:pointer-events-none cursor-pointer">
                            <i class="fa-solid fa-arrows-rotate text-emerald-600"></i>
                            <span>Tukar 1:1</span>
                        </button>

                        <button type="button" :disabled="newOrder.tierCode === 'galon_baru_isi'"
                            @click="selectGallonAction('pinjam')"
                            :class="newOrder.gallonAction === 'pinjam' ?
                                'bg-amber-50 border-amber-500 text-amber-900 font-black ring-1 ring-amber-500' :
                                'bg-slate-50 border-slate-200 text-slate-600'"
                            class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px] disabled:opacity-40 disabled:pointer-events-none cursor-pointer">
                            <i class="fa-solid fa-hand-holding text-amber-600"></i>
                            <span>Pinjam (+Gp)</span>
                        </button>

                        <button type="button" :disabled="newOrder.tierCode === 'galon_baru_isi'"
                            @click="selectGallonAction('kembalikan')"
                            :class="newOrder.gallonAction === 'kembalikan' ?
                                'bg-purple-50 border-purple-500 text-purple-900 font-black ring-1 ring-purple-500' :
                                'bg-slate-50 border-slate-200 text-slate-600'"
                            class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px] disabled:opacity-40 disabled:pointer-events-none cursor-pointer">
                            <i class="fa-solid fa-rotate-left text-purple-600"></i>
                            <span>Kembali (-Gp)</span>
                        </button>

                        <button type="button" :disabled="newOrder.tierCode === 'galon_baru_isi'"
                            @click="selectGallonAction('tidak_ada')"
                            :class="newOrder.gallonAction === 'tidak_ada' ?
                                'bg-slate-200 border-slate-400 text-slate-900 font-black' :
                                'bg-slate-50 border-slate-200 text-slate-600'"
                            class="p-2 rounded-2xl border text-xs transition flex items-center justify-center gap-1.5 min-h-[44px] disabled:opacity-90 cursor-pointer">
                            <i class="fa-solid"
                                :class="newOrder.tierCode === 'galon_baru_isi' ? 'fa-bag-shopping text-emerald-700' :
                                    'fa-minus text-slate-500'"></i>
                            <span x-text="newOrder.tierCode === 'galon_baru_isi' ? 'Beli Putus' : 'Bawa Sendiri'"></span>
                        </button>
                    </div>
                </div>

                <!-- 5. KUANTITAS ITEM & RETURN CONTROL -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Jumlah Galon (Order)</label>
                        <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-2xl">
                            <button type="button" @click="updateQuantity(-1)"
                                class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number" min="1" x-model.number="newOrder.quantity"
                                @change="updateQuantity(0)"
                                class="w-full text-center font-black text-base text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0">
                            <button type="button" @click="updateQuantity(1)"
                                class="w-10 h-10 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Khusus Input Retur Galon jika gallonAction === 'kembalikan' -->
                    <div x-show="newOrder.gallonAction === 'kembalikan'">
                        <label class="block text-xs font-bold text-purple-800 mb-1">Jml Galon Dikembalikan</label>
                        <div class="flex items-center gap-2 bg-purple-50 border border-purple-200 p-1 rounded-2xl">
                            <button type="button" @click="if (newOrder.gallonQty > 1) newOrder.gallonQty--"
                                class="w-10 h-10 rounded-xl bg-white text-purple-800 flex items-center justify-center font-black text-sm shadow-xs cursor-pointer">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number" min="1"
                                :max="selectedCustomer ? selectedCustomer.borrowed_gallons : 1"
                                x-model.number="newOrder.gallonQty"
                                class="w-full text-center font-black text-base text-purple-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0">
                            <button type="button"
                                @click="if (!selectedCustomer || newOrder.gallonQty < selectedCustomer.borrowed_gallons) newOrder.gallonQty++"
                                class="w-10 h-10 rounded-xl bg-white text-purple-800 flex items-center justify-center font-black text-sm shadow-xs cursor-pointer">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 6. INDIKATOR STATUS PEMBAYARAN -->
                <div class="pt-3 border-t border-slate-100">
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-700 block">Metode: Tunai</span>
                            <span class="text-[11px] text-slate-400">Status pembayaran otomatis disesuaikan layanan</span>
                        </div>
                        <div>
                            <!-- Badge Status Walk-in (Lunas) -->
                            <span x-show="newOrder.orderType === 'ambil_sendiri'"
                                class="px-3 py-1 rounded-xl text-xs font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-check text-[10px]"></i>
                                <span>Lunas</span>
                            </span>

                            <!-- Badge Status Pesan Antar (Belum Lunas / COD) -->
                            <span x-show="newOrder.orderType === 'pesan_antar'"
                                class="px-3 py-1 rounded-xl text-xs font-black uppercase bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1.5 animate-pulse">
                                <i class="fa-solid fa-clock text-[10px]"></i>
                                <span>Belum Lunas (COD)</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Validasi Error Banner -->
                <div x-show="formValidationError"
                    class="p-2.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 shrink-0"></i>
                    <span x-text="formValidationMessage"></span>
                </div>

                <!-- Ringkasan Tagihan & Submit -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-[11px] text-slate-400 font-semibold block">Total Tagihan</span>
                        <span class="text-xl font-black text-slate-900" x-text="rupiah(totalAmount)">Rp 0</span>
                    </div>

                    <button type="button" @click="processNewOrder()" :disabled="formValidationError || isSubmitting"
                        class="min-h-[46px] px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-98 disabled:opacity-40 disabled:pointer-events-none text-white font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                        <span x-text="isSubmitting ? 'Memproses...' : 'Simpan Transaksi'"></span>
                        <i class="fa-solid fa-check text-xs"></i>
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- QUICK ADD CUSTOMER MODAL                                                  -->
    <!-- ========================================================================= -->
    <div x-show="openQuickAddModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-5 shadow-xl border border-slate-200"
            @click.away="openQuickAddModal = false">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900">Tambah Pelanggan Baru</h3>
                <button type="button" @click="openQuickAddModal = false"
                    class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form @submit.prevent="saveQuickCustomer()" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Nama Pelanggan <span
                            class="text-rose-500">*</span></label>
                    <input type="text" x-model="newCustomer.name" placeholder="Mis. Warung Bu Sri" required
                        class="w-full min-h-[42px] px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 font-semibold text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">No. WhatsApp <span
                            class="text-rose-500">*</span></label>
                    <input type="tel" x-model="newCustomer.whatsapp" placeholder="08xxxxxxxxxx" required
                        class="w-full min-h-[42px] px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 font-semibold text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Alamat Lengkap</label>
                    <textarea x-model="newCustomer.address" rows="2" placeholder="Jl. Rajawali No. 12"
                        class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 font-semibold text-slate-800 resize-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openQuickAddModal = false"
                        class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl cursor-pointer">Batal</button>
                    <button type="submit"
                        class="px-5 py-2 text-xs font-extrabold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-xs cursor-pointer">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TOAST NOTIFIKASI BERHASIL                                                 -->
    <!-- ========================================================================= -->
    <div x-show="showToast" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 border border-slate-800">
        <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs">
            <i class="fa-solid fa-check"></i>
        </div>
        <div class="text-xs font-bold" x-text="toastMessage">Transaksi berhasil disimpan!</div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL STRUK / KONFIRMASI TRANSAKSI BERHASIL (S2-FE-04)                    -->
    <!-- ========================================================================= -->
    <div x-show="openReceiptModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 receipt-modal-backdrop">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 flex flex-col receipt-card"
            @click.away="openReceiptModal = false">

            <!-- Status Icon Header -->
            <div class="flex flex-col items-center text-center pb-4 border-b border-dashed border-slate-200">
                <div
                    class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mb-2">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900">Pembayaran Berhasil!</h3>
                <p class="text-xs text-slate-400 font-mono" x-text="lastOrder ? lastOrder.order_number : ''"></p>
            </div>

            <!-- Rincian Nota / Struk -->
            <template x-if="lastOrder">
                <div class="py-4 space-y-2.5 text-xs border-b border-dashed border-slate-200">
                    <div class="flex justify-between text-slate-500">
                        <span>Pelanggan</span>
                        <span class="font-bold text-slate-800" x-text="lastOrder.customer_name"></span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Layanan</span>
                        <span class="font-bold text-slate-800"
                            x-text="lastOrder.order_type === 'ambil_sendiri' ? 'Walk-in (Ambil Sendiri)' : 'Pesan Antar'"></span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Item & Varian</span>
                        <span class="font-bold text-slate-800" x-text="lastOrder.tier_name"></span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Qty & Opsi Galon</span>
                        <div class="text-right">
                            <span class="font-bold text-slate-800" x-text="lastOrder.qty + ' Galon'"></span>
                            <div class="text-[10px] text-blue-600 font-semibold"
                                x-text="formatGallonAction(lastOrder.gallon_action, lastOrder.gallon_qty, lastOrder.tier_code)">
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex justify-between items-center text-sm">
                        <span class="font-black text-slate-900">Total Tagihan</span>
                        <span class="font-black text-slate-900" x-text="rupiah(lastOrder.total_amount)"></span>
                    </div>

                    <!-- Ringkasan Pembayaran & Status -->
                    <div class="pt-2 bg-slate-50 p-2.5 rounded-xl space-y-1">
                        <div class="flex justify-between text-slate-600">
                            <span>Metode</span>
                            <span class="font-bold uppercase">Tunai</span>
                        </div>
                        <div class="flex justify-between text-slate-600 items-center">
                            <span>Status</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase"
                                :class="lastOrder.payment_status === 'lunas' ? 'bg-emerald-100 text-emerald-800' :
                                    'bg-amber-100 text-amber-800'"
                                x-text="lastOrder.payment_status === 'lunas' ? 'Lunas' : 'Belum Lunas (COD)'">
                            </span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Action Buttons -->
            <div class="pt-4 flex items-center gap-2 print:hidden">
                <button type="button" @click="window.print()"
                    class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Nota</span>
                </button>
                <button type="button" @click="openReceiptModal = false"
                    class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-xs transition cursor-pointer">
                    Selesai / Baru
                </button>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function posApp(initialOrders = [], initialCustomers = [], initialPriceTiers = [], initialTutupStock = 1000,
            initialGalonStock = 50) {
            return {
                // ... state metadata ...
                todayDate: '',
                tutupStock: initialTutupStock,
                totalGalonDepot: initialGalonStock,
                searchQuery: '',
                statusFilter: 'all',

                // Ambil data priceTiers langsung dari database
                priceTiers: initialPriceTiers,

                products: {
                    refill: {
                        id: 1,
                        name: 'Air Isi Ulang',
                        type: 'isi_ulang'
                    },
                    new_gallon: {
                        id: 2,
                        name: 'Galon Baru + Isi',
                        type: 'galon_baru'
                    }
                },

                // ... modal & feedback states ...
                openOrderModal: false,
                openQuickAddModal: false,
                openReceiptModal: false,
                isSubmitting: false,
                showToast: false,
                toastMessage: '',

                newOrder: {
                    orderType: 'ambil_sendiri',
                    customerId: '',
                    productId: 1,
                    priceTierId: initialPriceTiers.length > 0 ? initialPriceTiers[0].id : 2,
                    tierCode: initialPriceTiers.length > 0 ? initialPriceTiers[0].code : 'letak_kedai',
                    unitPrice: initialPriceTiers.length > 0 ? initialPriceTiers[0].price : 5000,
                    quantity: 1,
                    gallonAction: 'tukar_seimbang',
                    gallonQty: 1,
                    paymentMethod: 'tunai',
                },

                // --- State Data Transaksi Terakhir untuk Struk ---
                lastOrder: null,

                // --- Quick Add Customer State ---
                newCustomer: {
                    name: '',
                    whatsapp: '',
                    address: ''
                },

                customers: initialCustomers,
                orders: initialOrders,

                init() {
                    const now = new Date();
                    this.todayDate = now.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                },

                rupiah(val) {
                    return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID');
                },

                get selectedCustomer() {
                    return this.customers.find(c => c.id == this.newOrder.customerId) || null;
                },

                get totalAmount() {
                    return (this.newOrder.unitPrice || 0) * (this.newOrder.quantity || 1);
                },

                // Validasi Frontend DOC-VAL-POS-02 v1.3
                get formValidationError() {
                    const o = this.newOrder;
                    if (o.orderType === 'pesan_antar' && !o.customerId) return true;
                    if ((o.gallonAction === 'pinjam' || o.gallonAction === 'kembalikan') && !o.customerId) return true;
                    if (o.gallonAction === 'kembalikan' && this.selectedCustomer && o.gallonQty > this.selectedCustomer
                        .borrowed_gallons) return true;
                    if (o.quantity > this.tutupStock) return true;
                    return false;
                },

                get formValidationMessage() {
                    const o = this.newOrder;
                    if (o.orderType === 'pesan_antar' && !o.customerId) {
                        return 'Pelanggan wajib dipilih untuk layanan Pesan Antar!';
                    }
                    if ((o.gallonAction === 'pinjam' || o.gallonAction === 'kembalikan') && !o.customerId) {
                        return 'Pelanggan wajib dipilih jika meminjam atau mengembalikan galon.';
                    }
                    if (o.gallonAction === 'kembalikan' && this.selectedCustomer && o.gallonQty > this.selectedCustomer
                        .borrowed_gallons) {
                        return `Jumlah pengembalian (${o.gallonQty}) melebihi galon yang sedang dipinjam pelanggan (${this.selectedCustomer.borrowed_gallons} unit).`;
                    }
                    if (o.quantity > this.tutupStock) {
                        return `Stok tutup galon tidak mencukupi (Tersisa: ${this.tutupStock}, Dibutuhkan: ${o.quantity}). Silakan lakukan restock terlebih dahulu.`;
                    }
                    return '';
                },

                get filteredOrders() {
                    return this.orders.filter(o => {
                        const matchStatus = this.statusFilter === 'all' || o.delivery_status === this
                            .statusFilter;
                        const matchSearch = this.searchQuery === '' ||
                            o.order_number.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            o.customer_name.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchStatus && matchSearch;
                    });
                },

                selectTier(tier) {
                    this.newOrder.priceTierId = tier.id;
                    this.newOrder.tierCode = tier.code;
                    this.newOrder.unitPrice = tier.price;

                    if (tier.applies_to === 'galon_baru') {
                        this.newOrder.productId = this.products.new_gallon.id;
                        this.newOrder.gallonAction = 'tidak_ada';
                        this.newOrder.gallonQty = 0;
                    } else {
                        this.newOrder.productId = this.products.refill.id;
                        if (this.newOrder.gallonAction === 'tidak_ada') {
                            this.newOrder.gallonAction = 'tukar_seimbang';
                            this.newOrder.gallonQty = this.newOrder.quantity;
                        }
                    }
                },

                updateQuantity(delta) {
                    let nextQty = this.newOrder.quantity + delta;
                    if (nextQty < 1) nextQty = 1;
                    this.newOrder.quantity = nextQty;

                    if (this.newOrder.gallonAction === 'tukar_seimbang') {
                        this.newOrder.gallonQty = nextQty;
                    } else if (this.newOrder.gallonAction === 'kembalikan' && this.selectedCustomer) {
                        if (this.newOrder.gallonQty > this.selectedCustomer.borrowed_gallons) {
                            this.newOrder.gallonQty = this.selectedCustomer.borrowed_gallons;
                        }
                    }
                },

                selectGallonAction(action) {
                    if (this.newOrder.tierCode === 'galon_baru_isi') return;
                    this.newOrder.gallonAction = action;

                    if (action === 'tukar_seimbang') {
                        this.newOrder.gallonQty = this.newOrder.quantity;
                    } else if (action === 'pinjam') {
                        this.newOrder.gallonQty = this.newOrder.quantity;
                    } else if (action === 'kembalikan') {
                        const maxReturn = this.selectedCustomer ? this.selectedCustomer.borrowed_gallons : 1;
                        this.newOrder.gallonQty = Math.min(this.newOrder.quantity, maxReturn || 1);
                    } else {
                        this.newOrder.gallonQty = 0;
                    }
                },

                formatGallonAction(action, qty, tierCode) {
                    if (tierCode === 'galon_baru_isi') return '• Beli Putus';
                    switch (action) {
                        case 'tukar_seimbang':
                            return '• Tukar 1:1';
                        case 'pinjam':
                            return `• Pinjam (+${qty} Gp)`;
                        case 'kembalikan':
                            return `• Kembali (-${qty} Gp)`;
                        default:
                            return '• Bawa Sendiri';
                    }
                },

                async processNewOrder() {
                    if (this.formValidationError || this.isSubmitting) return;
                    this.isSubmitting = true;

                    const isNewGallon = this.newOrder.tierCode === 'galon_baru_isi';
                    const endpoint = isNewGallon ? '{{ route('pos.order.new-gallon') }}' :
                        '{{ route('pos.order.refill') }}';

                    const payload = {
                        order_type: this.newOrder.orderType,
                        customer_id: this.newOrder.customerId || null,
                        payment_method: this.newOrder.paymentMethod,
                        items: [{
                            product_id: this.newOrder.productId,
                            price_tier_id: this.newOrder.priceTierId,
                            quantity: this.newOrder.quantity,
                            gallon_action: this.newOrder.gallonAction,
                            gallon_qty: this.newOrder.gallonAction === 'tidak_ada' ? 0 : this.newOrder
                                .gallonQty
                        }]
                    };

                    try {
                        const response = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify(payload)
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            throw new Error(result.message || 'Gagal menyimpan transaksi.');
                        }

                        this.tutupStock -= this.newOrder.quantity;
                        if (isNewGallon) this.totalGalonDepot -= this.newOrder.quantity;

                        let customerName = 'Pelanggan Umum';
                        if (this.selectedCustomer) {
                            customerName = this.selectedCustomer.name;
                            if (this.newOrder.gallonAction === 'pinjam') {
                                this.selectedCustomer.borrowed_gallons += this.newOrder.gallonQty;
                            } else if (this.newOrder.gallonAction === 'kembalikan') {
                                this.selectedCustomer.borrowed_gallons -= this.newOrder.gallonQty;
                            }
                        }

                        const now = new Date();
                        const calculatedPaymentStatus = this.newOrder.orderType === 'ambil_sendiri' ? 'lunas' :
                            'belum_lunas';

                        const orderData = {
                            id: result.data.order_id,
                            order_number: 'SW-' + String(result.data.order_id).padStart(5, '0'),
                            time: String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(
                                2, '0'),
                            order_type: this.newOrder.orderType,
                            delivery_status: result.data.delivery_status,
                            payment_status: calculatedPaymentStatus,
                            customer_name: customerName,
                            customer_address: this.selectedCustomer ? (this.selectedCustomer.address || '-') : '-',
                            tier_name: this.priceTiers.find(t => t.id === this.newOrder.priceTierId).name,
                            tier_code: this.newOrder.tierCode,
                            qty: this.newOrder.quantity,
                            unit_price: this.newOrder.unitPrice,
                            total_amount: this.totalAmount,
                            gallon_action: this.newOrder.gallonAction,
                            gallon_qty: this.newOrder.gallonQty,
                            payment_method: this.newOrder.paymentMethod
                        };

                        this.orders.unshift(orderData);
                        this.lastOrder = orderData;

                        this.openOrderModal = false;
                        this.openReceiptModal = true;
                        this.triggerToast('Transaksi berhasil disimpan!');
                        this.resetForm();
                    } catch (err) {
                        alert('Kesalahan Transaksi: ' + err.message);
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                resetForm() {
                    this.newOrder.quantity = 1;
                    this.newOrder.customerId = '';
                    this.newOrder.orderType = 'ambil_sendiri';
                    this.newOrder.paymentMethod = 'tunai';
                    const defaultTier = this.priceTiers.find(t => t.code === 'letak_kedai');
                    if (defaultTier) this.selectTier(defaultTier);
                },

                saveQuickCustomer() {
                    if (!this.newCustomer.name || !this.newCustomer.whatsapp) return;
                    const newId = Date.now();
                    const cust = {
                        id: newId,
                        name: this.newCustomer.name,
                        whatsapp: this.newCustomer.whatsapp,
                        address: this.newCustomer.address?.trim() || '-',
                        borrowed_gallons: 0
                    };
                    this.customers.push(cust);
                    this.newOrder.customerId = newId;
                    this.openQuickAddModal = false;
                    this.newCustomer = {
                        name: '',
                        whatsapp: '',
                        address: ''
                    };
                    this.triggerToast('Pelanggan ' + cust.name + ' berhasil ditambahkan!');
                },

                triggerToast(msg) {
                    this.toastMessage = msg;
                    this.showToast = true;
                    setTimeout(() => {
                        this.showToast = false;
                    }, 3000);
                }
            };
        }
    </script>
@endpush
