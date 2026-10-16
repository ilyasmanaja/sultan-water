@extends('layouts.app')

@section('title', 'Antrean Kurir - Sultan Water')
@section('page-title', 'Antrean Pengantaran')
@section('page-subtitle', 'Daftar pengiriman aktif ramah sentuhan layar HP')

@section('page-badge')
    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
        Kurir Lapangan
    </span>
@endsection

@section('header-actions')
    <button type="button" @click="location.reload()" title="Muat Ulang Antrean"
        class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition active:scale-95 cursor-pointer">
        <i class="fa-solid fa-arrows-rotate text-sm"></i>
    </button>
@endsection

@section('body-attributes')
    x-data="courierQueueApp({{ Js::from($orders ?? []) }}, {{ Js::from($priceTiers ?? []) }}, {{ $tutupStock ?? 1000 }}, {{ $totalGalonDepot ?? 50 }})"
@endsection

@section('content')
<div class="max-w-xl mx-auto space-y-4 pb-24">

    <!-- Ringkasan Status Tab / Filter Ringkas -->
    <div class="flex items-center justify-between bg-white p-2 rounded-2xl border border-slate-200/80 shadow-xs">
        <button type="button" @click="activeFilter = 'all'"
            :class="activeFilter === 'all' ? 'bg-blue-600 text-white font-black shadow-xs' : 'text-slate-500 font-bold'"
            class="flex-1 py-2 rounded-xl text-xs transition cursor-pointer">
            Semua (<span x-text="orders.length"></span>)
        </button>
        <button type="button" @click="activeFilter = 'pending'"
            :class="activeFilter === 'pending' ? 'bg-amber-500 text-white font-black shadow-xs' : 'text-slate-500 font-bold'"
            class="flex-1 py-2 rounded-xl text-xs transition cursor-pointer">
            Menunggu (<span x-text="orders.filter(o => o.delivery_status === 'pending').length"></span>)
        </button>
        <button type="button" @click="activeFilter = 'diantar'"
            :class="activeFilter === 'diantar' ? 'bg-blue-500 text-white font-black shadow-xs' : 'text-slate-500 font-bold'"
            class="flex-1 py-2 rounded-xl text-xs transition cursor-pointer">
            Di Jalan (<span x-text="orders.filter(o => o.delivery_status === 'diantar').length"></span>)
        </button>
    </div>

    <!-- Alert Error Server Banner -->
    <template x-if="actionError">
        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-2xl flex items-start gap-2 shadow-xs">
            <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5 shrink-0"></i>
            <div class="flex-1 font-semibold" x-text="actionError"></div>
            <button type="button" @click="actionError = ''" class="text-rose-500 hover:text-rose-800 cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </template>

    <!-- State Kosong (Empty State) -->
    <template x-if="filteredOrders.length === 0">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center shadow-xs">
            <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-3xl flex items-center justify-center mx-auto text-2xl mb-3">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <h3 class="text-base font-black text-slate-900">Tidak Ada Antrean</h3>
            <p class="text-xs text-slate-400 mt-1">Semua pesanan antar sudah selesai dikirim atau belum ada order baru.</p>
            <button type="button" @click="location.reload()"
                class="mt-4 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition inline-flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-rotate-right text-xs"></i>
                <span>Muat Ulang Halaman</span>
            </button>
        </div>
    </template>

    <!-- DAFTAR KARTU ANTREAN (Tiket #44 & #45) -->
    <template x-for="order in filteredOrders" :key="order.id">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden transition hover:shadow-md">
            
            <!-- Header Kartu -->
            <div class="p-4 border-b border-slate-100 flex items-start justify-between gap-2">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-black text-sm text-blue-600" x-text="order.order_number"></span>
                        <span class="text-xs text-slate-400 font-semibold" x-text="'• ' + order.time + ' WIB'"></span>
                    </div>
                    <h3 class="text-base font-black text-slate-900 mt-1" x-text="order.customer.name"></h3>
                </div>

                <!-- Badge Status Antar -->
                <div>
                    <span x-show="order.delivery_status === 'pending'"
                        class="px-2.5 py-1 rounded-xl text-[11px] font-black uppercase bg-amber-50 text-amber-800 border border-amber-200 flex items-center gap-1.5 animate-pulse">
                        <i class="fa-solid fa-hourglass-half text-[10px]"></i>
                        <span>Menunggu</span>
                    </span>
                    <span x-show="order.delivery_status === 'diantar'"
                        class="px-2.5 py-1 rounded-xl text-[11px] font-black uppercase bg-blue-50 text-blue-800 border border-blue-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-motorcycle text-[10px]"></i>
                        <span>Diantar</span>
                    </span>
                </div>
            </div>

            <!-- Detail Alamat & Saldo Pinjaman (Tiket #48) -->
            <div class="p-4 space-y-3 bg-slate-50/50">
                <!-- Alamat & WhatsApp Link -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-2">
                        <i class="fa-solid fa-location-dot text-rose-500 mt-0.5 text-xs shrink-0"></i>
                        <span class="text-xs text-slate-700 font-semibold leading-relaxed" x-text="order.customer.address"></span>
                    </div>
                    <template x-if="order.customer.whatsapp">
                        <a :href="'https://wa.me/' + cleanWhatsapp(order.customer.whatsapp)" target="_blank"
                            class="px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 rounded-xl text-xs font-black flex items-center gap-1.5 shrink-0 transition cursor-pointer">
                            <i class="fa-brands fa-whatsapp text-sm text-emerald-600"></i>
                            <span>WA</span>
                        </a>
                    </template>
                </div>

                <!-- Saldo Pinjaman Pelanggan (Gp) (Tiket #48) -->
                <div class="flex items-center gap-2 pt-1">
                    <span class="text-[11px] text-slate-400 font-bold">Saldo Galon:</span>
                    <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-black inline-flex items-center gap-1"
                        :class="order.customer.borrowed_gallons > 0 ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-slate-200 text-slate-600'">
                        <i class="fa-solid fa-jar text-[10px]"></i>
                        <span x-text="order.customer.borrowed_gallons > 0 ? order.customer.borrowed_gallons + ' Galon Dipinjam' : 'Tidak Ada Pinjaman'"></span>
                    </span>
                </div>

                <!-- Rincian Galon & Tagihan COD -->
                <div class="p-3 bg-white rounded-2xl border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-black text-slate-800" x-text="order.items_summary.tier_name"></div>
                        <div class="text-[11px] text-slate-500 font-semibold mt-0.5">
                            <span x-text="order.items_summary.qty + ' Galon'"></span>
                            <span class="text-blue-600" x-text="formatAction(order.items_summary.gallon_action, order.items_summary.gallon_qty)"></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Tagihan COD</div>
                        <div class="text-base font-black text-slate-900" x-text="rupiah(order.total_amount)"></div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Ramah Jempol (Tiket #45 - Minimal 48px) -->
            <div class="p-3 bg-white border-t border-slate-100 flex items-center gap-2">
                <!-- Tombol Batalkan (Kecil, Pemicu Modal) -->
                <button type="button" @click="confirmCancel(order)" :disabled="isProcessing === order.id"
                    class="h-12 px-4 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs flex items-center justify-center gap-1.5 transition active:scale-95 disabled:opacity-50 cursor-pointer">
                    <i class="fa-solid fa-ban text-xs"></i>
                    <span>Batal</span>
                </button>

                <!-- Tombol Utama Status Pending: "Mulai Antar" -->
                <template x-if="order.delivery_status === 'pending'">
                    <button type="button" @click="startDelivery(order)" :disabled="isProcessing === order.id"
                        class="flex-1 h-12 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-black text-sm shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer">
                        <i class="fa-solid fa-motorcycle"></i>
                        <span x-text="isProcessing === order.id ? 'Memproses...' : 'Mulai Antar'"></span>
                    </button>
                </template>

                <!-- Tombol Utama Status Diantar: "Konfirmasi Antar & Tagih" -->
                <template x-if="order.delivery_status === 'diantar'">
                    <button type="button" @click="completeDelivery(order)" :disabled="isProcessing === order.id"
                        class="flex-1 h-12 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white font-black text-sm shadow-md shadow-emerald-500/20 transition flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer">
                        <i class="fa-solid fa-circle-check"></i>
                        <span x-text="isProcessing === order.id ? 'Menyimpan...' : 'Konfirmasi Selesai (' + rupiah(order.total_amount) + ')'"></span>
                    </button>
                </template>
            </div>

        </div>
    </template>

</div>

<!-- FLOATING ACTION BUTTON: "+ Jual Dadakan" (Tiket #46) -->
<div class="fixed bottom-6 right-6 z-40">
    <button type="button" @click="openQuickSaleModal = true"
        class="h-14 px-6 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-black text-sm rounded-full shadow-2xl shadow-blue-600/40 border-2 border-white flex items-center gap-2.5 transition cursor-pointer">
        <i class="fa-solid fa-plus text-base"></i>
        <span>Jual Dadakan</span>
    </button>
</div>
@endsection

@section('modals')
<!-- MODAL KONFIRMASI PEMBATALAN (Tiket #45) -->
<div x-show="orderToCancel" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-5 shadow-2xl border border-slate-200"
        @click.away="orderToCancel = null">
        
        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mx-auto mb-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <h3 class="text-base font-black text-slate-900 text-center">Batalkan Pesanan Ini?</h3>
        <p class="text-xs text-slate-500 text-center mt-1 leading-relaxed">
            Pembatalan order <span class="font-mono font-bold text-slate-800" x-text="orderToCancel?.order_number"></span> akan membalikkan (<em class="font-semibold">rollback</em>) stok tutup galon, galon baru, dan saldo pinjaman galon pelanggan[cite: 1, 2].
        </p>

        <div class="grid grid-cols-2 gap-2 mt-5 pt-3 border-t border-slate-100">
            <button type="button" @click="orderToCancel = null"
                class="h-11 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-xs text-slate-700 transition cursor-pointer">
                Tutup
            </button>
            <button type="button" @click="executeCancel()" :disabled="isProcessing"
                class="h-11 rounded-xl bg-rose-600 hover:bg-rose-700 font-black text-xs text-white transition active:scale-95 disabled:opacity-50 cursor-pointer">
                <span x-text="isProcessing ? 'Membatalkan...' : 'Ya, Batalkan'"></span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL FORM JUAL DADAKAN & RETUR MURNI MOBILE (Tiket #46, #47, #48) -->
<div x-show="openQuickSaleModal" x-cloak
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-xs p-0 sm:p-4">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 max-h-[92vh] overflow-y-auto touch-scroll"
        @click.away="openQuickSaleModal = false">

        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base sm:text-lg font-black text-slate-900">Jual Dadakan di Jalan</h3>
                <p class="text-xs text-slate-400">Transaksi langsung berstatus selesai[cite: 1, 5]</p>
            </div>
            <button type="button" @click="openQuickSaleModal = false"
                class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Saklar Mode -->
        <div class="mt-3 grid grid-cols-2 gap-2 bg-slate-100 p-1 rounded-2xl">
            <button type="button" @click="isPureReturn = false"
                :class="!isPureReturn ? 'bg-white text-slate-900 font-black shadow-xs' : 'text-slate-500 font-bold'"
                class="py-2 text-xs rounded-xl transition cursor-pointer">
                Isi Ulang / Beli Air
            </button>
            <button type="button" @click="setPureReturnMode()"
                :class="isPureReturn ? 'bg-purple-600 text-white font-black shadow-xs' : 'text-slate-500 font-bold'"
                class="py-2 text-xs rounded-xl transition cursor-pointer">
                Retur Galon Murni
            </button>
        </div>

        <div class="mt-4 space-y-4">
            
            <!-- SMART SEARCH AUTOCOMPLETE (Tiket #47) -->
            <div class="relative">
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-bold text-slate-700">
                        Pelanggan <span x-show="isPureReturn || ['pinjam', 'kembalikan'].includes(formOrder.gallonAction)" class="text-rose-500">*</span>
                    </label>
                    <button type="button" @click="openMiniQuickAdd = true"
                        class="text-xs font-extrabold text-blue-600 hover:underline cursor-pointer">
                        + Pelanggan Baru
                    </button>
                </div>

                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="customerSearchQuery" @input.debounce.300ms="searchCustomer()"
                        placeholder="Ketik min. 2 huruf nama/nomor WA..."
                        class="w-full pl-9 pr-8 min-h-[44px] text-xs bg-slate-50 border border-slate-200 rounded-2xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500">
                    <button type="button" x-show="selectedCustomer" @click="clearCustomer()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Dropdown Autocomplete -->
                <div x-show="customerResults.length > 0 && !selectedCustomer" x-cloak
                    class="absolute left-0 right-0 top-full mt-1 bg-white rounded-2xl shadow-xl border border-slate-200 divide-y divide-slate-100 z-50 max-h-48 overflow-y-auto">
                    <template x-for="cust in customerResults" :key="cust.id">
                        <button type="button" @click="selectCustomer(cust)"
                            class="w-full p-2.5 text-left hover:bg-blue-50 transition flex items-center justify-between text-xs cursor-pointer">
                            <div>
                                <div class="font-bold text-slate-800" x-text="cust.name"></div>
                                <div class="text-[10px] text-slate-400" x-text="cust.whatsapp_number"></div>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black"
                                :class="cust.borrowed_gallons > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500'"
                                x-text="'Pinjam: ' + cust.borrowed_gallons + ' Gp'"></span>
                        </button>
                    </template>
                </div>

                <!-- Pelanggan Terpilih -->
                <template x-if="selectedCustomer">
                    <div class="mt-2 p-2.5 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-extrabold text-blue-950" x-text="selectedCustomer.name"></div>
                            <div class="text-[11px] text-blue-700" x-text="selectedCustomer.whatsapp_number"></div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-black"
                            :class="selectedCustomer.borrowed_gallons > 0 ? 'bg-amber-200 text-amber-900' : 'bg-slate-200 text-slate-600'"
                            x-text="selectedCustomer.borrowed_gallons > 0 ? selectedCustomer.borrowed_gallons + ' Galon Dipinjam' : 'Tidak Ada Pinjaman'"></span>
                    </div>
                </template>
            </div>

            <!-- JIKA MODE ISI ULANG / PENJUALAN AIR -->
            <template x-if="!isPureReturn">
                <div class="space-y-4">
                    <!-- Pilihan 5 Tier Harga -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Varian Harga</label>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="tier in priceTiers" :key="tier.id">
                                <button type="button" @click="selectTier(tier)"
                                    :class="{
                                        'bg-blue-600 text-white ring-2 ring-blue-600 font-bold': formOrder.priceTierId === tier.id && tier.applies_to === 'isi_ulang',
                                        'bg-emerald-600 text-white ring-2 ring-emerald-600 font-bold': formOrder.priceTierId === tier.id && tier.applies_to === 'galon_baru',
                                        'bg-slate-50 text-slate-700 border border-slate-200': formOrder.priceTierId !== tier.id && tier.applies_to === 'isi_ulang',
                                        'bg-emerald-50 text-emerald-900 border border-emerald-200': formOrder.priceTierId !== tier.id && tier.applies_to === 'galon_baru',
                                        'col-span-2': tier.applies_to === 'galon_baru'
                                    }"
                                    class="p-2.5 rounded-2xl text-left transition flex flex-col justify-between min-h-[54px] active:scale-95 cursor-pointer">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px]" x-text="tier.name"></span>
                                        <span x-show="tier.applies_to === 'galon_baru'" class="text-[9px] px-1.5 py-0.5 rounded font-black uppercase bg-emerald-200 text-emerald-800">Beli Putus</span>
                                    </div>
                                    <span class="text-xs font-black" x-text="rupiah(tier.price)"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Opsi Galon Fisik -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Opsi Galon Fisik
                            <span x-show="isNewGallonTier" class="text-slate-400 font-normal">(Terkunci: Beli Putus)</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" :disabled="isNewGallonTier" @click="selectAction('tukar_seimbang')"
                                :class="formOrder.gallonAction === 'tukar_seimbang' ? 'bg-emerald-50 border-emerald-500 text-emerald-900 font-black' : 'bg-slate-50 border-slate-200 text-slate-600'"
                                class="p-2 rounded-2xl border text-xs flex items-center justify-center gap-1 min-h-[44px] disabled:opacity-40 cursor-pointer">
                                <i class="fa-solid fa-arrows-rotate text-emerald-600"></i>
                                <span>Tukar 1:1</span>
                            </button>
                            <button type="button" :disabled="isNewGallonTier" @click="selectAction('pinjam')"
                                :class="formOrder.gallonAction === 'pinjam' ? 'bg-amber-50 border-amber-500 text-amber-900 font-black' : 'bg-slate-50 border-slate-200 text-slate-600'"
                                class="p-2 rounded-2xl border text-xs flex items-center justify-center gap-1 min-h-[44px] disabled:opacity-40 cursor-pointer">
                                <i class="fa-solid fa-hand-holding text-amber-600"></i>
                                <span>Pinjam</span>
                            </button>
                            <button type="button" :disabled="isNewGallonTier" @click="selectAction('kembalikan')"
                                :class="formOrder.gallonAction === 'kembalikan' ? 'bg-purple-50 border-purple-500 text-purple-900 font-black' : 'bg-slate-50 border-slate-200 text-slate-600'"
                                class="p-2 rounded-2xl border text-xs flex items-center justify-center gap-1 min-h-[44px] disabled:opacity-40 cursor-pointer">
                                <i class="fa-solid fa-rotate-left text-purple-600"></i>
                                <span>Kembali</span>
                            </button>
                        </div>
                    </div>

                    <!-- Kuantitas Pesanan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Galon</label>
                        <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-2xl">
                            <button type="button" @click="if (formOrder.quantity > 1) formOrder.quantity--"
                                class="w-11 h-11 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <input type="number" min="1" x-model.number="formOrder.quantity"
                                class="w-full text-center font-black text-base text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0">
                            <button type="button" @click="formOrder.quantity++"
                                class="w-11 h-11 rounded-xl bg-white text-slate-800 flex items-center justify-center font-black text-sm shadow-xs active:bg-slate-200 cursor-pointer">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- JIKA MODE RETUR GALON MURNI (Tiket #46) -->
            <template x-if="isPureReturn">
                <div class="space-y-3 p-4 bg-purple-50/70 rounded-2xl border border-purple-200">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-purple-900">Jumlah Galon Dikembalikan</span>
                        <span class="text-[11px] font-semibold text-purple-700"
                            x-text="'Maksimal: ' + (selectedCustomer ? selectedCustomer.borrowed_gallons : 0) + ' Unit'"></span>
                    </div>

                    <div class="flex items-center gap-2 bg-white p-1 rounded-2xl border border-purple-200">
                        <button type="button" @click="if (formOrder.pureReturnQty > 1) formOrder.pureReturnQty--"
                            class="w-11 h-11 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center font-black text-sm active:bg-purple-200 cursor-pointer">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <input type="number" min="1" :max="selectedCustomer ? selectedCustomer.borrowed_gallons : 1"
                            x-model.number="formOrder.pureReturnQty"
                            class="w-full text-center font-black text-base text-purple-950 bg-transparent border-none focus:outline-none focus:ring-0 p-0">
                        <button type="button"
                            @click="if (!selectedCustomer || formOrder.pureReturnQty < selectedCustomer.borrowed_gallons) formOrder.pureReturnQty++"
                            class="w-11 h-11 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center font-black text-sm active:bg-purple-200 cursor-pointer">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>

                    <p class="text-[11px] text-purple-800 font-medium leading-relaxed">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Retur murni tidak memotong tutup galon dan total tagihan bernilai Rp0[cite: 2, 3].
                    </p>
                </div>
            </template>

            <!-- Banner Validasi Error Form -->
            <template x-if="formError">
                <div class="p-2.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 shrink-0"></i>
                    <span x-text="formError"></span>
                </div>
            </template>

            <!-- Total & Tombol Simpan -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                <div>
                    <span class="text-[11px] text-slate-400 font-semibold block">Total Tagihan</span>
                    <span class="text-xl font-black text-slate-900" x-text="rupiah(calculatedTotal)"></span>
                </div>

                <button type="button" @click="submitQuickSale()" :disabled="isSubmitting"
                    class="h-12 px-6 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white font-black text-xs sm:text-sm rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer">
                    <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Transaksi'"></span>
                    <i class="fa-solid fa-check text-xs"></i>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- MINI QUICK ADD CUSTOMER MODAL (Tiket #47) -->
<div x-show="openMiniQuickAdd" x-cloak
    class="fixed inset-0 z-60 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-5 shadow-2xl border border-slate-200"
        @click.away="openMiniQuickAdd = false">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-3">
            <h4 class="text-sm font-black text-slate-900">Tambah Pelanggan Cepat</h4>
            <button type="button" @click="openMiniQuickAdd = false" class="text-slate-400 hover:text-slate-600 text-xs cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama <span class="text-rose-500">*</span></label>
                <input type="text" x-model="quickAddForm.name" placeholder="Nama Pelanggan"
                    class="w-full min-h-[40px] px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">No. WhatsApp <span class="text-rose-500">*</span></label>
                <input type="tel" x-model="quickAddForm.whatsapp_number" placeholder="08xxxxxxxxxx"
                    class="w-full min-h-[40px] px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Alamat (Opsional)</label>
                <input type="text" x-model="quickAddForm.address" placeholder="Patokan / Alamat"
                    class="w-full min-h-[40px] px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" @click="openMiniQuickAdd = false"
                    class="px-3 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl cursor-pointer">Batal</button>
                <button type="button" @click="saveQuickAdd()" :disabled="isQuickAdding"
                    class="px-4 py-2 bg-blue-600 text-white font-black text-xs rounded-xl shadow-xs hover:bg-blue-700 disabled:opacity-50 cursor-pointer">
                    <span x-text="isQuickAdding ? 'Menyimpan...' : 'Simpan'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function courierQueueApp(initialOrders = [], priceTiers = [], initialTutup = 1000, initialFleet = 50) {
        return {
            orders: initialOrders,
            priceTiers: priceTiers,
            tutupStock: initialTutup,
            activeFilter: 'all',
            isProcessing: null,
            actionError: '',
            orderToCancel: null,

            // Quick Sale & Smart Search State
            openQuickSaleModal: false,
            openMiniQuickAdd: false,
            isPureReturn: false,
            isSubmitting: false,
            isQuickAdding: false,
            formError: '',

            customerSearchQuery: '',
            customerResults: [],
            selectedCustomer: null,

            quickAddForm: {
                name: '',
                whatsapp_number: '',
                address: ''
            },

            formOrder: {
                priceTierId: priceTiers.length > 0 ? priceTiers[0].id : null,
                tierPrice: priceTiers.length > 0 ? priceTiers[0].price : 5000,
                tierAppliesTo: priceTiers.length > 0 ? priceTiers[0].applies_to : 'isi_ulang',
                quantity: 1,
                gallonAction: 'tukar_seimbang',
                pureReturnQty: 1
            },

            get filteredOrders() {
                if (this.activeFilter === 'all') return this.orders;
                return this.orders.filter(o => o.delivery_status === this.activeFilter);
            },

            get calculatedTotal() {
                if (this.isPureReturn) return 0;
                return (this.formOrder.tierPrice || 0) * (this.formOrder.quantity || 1);
            },

            get isNewGallonTier() {
                return this.formOrder.tierAppliesTo === 'galon_baru';
            },

            rupiah(val) {
                return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID');
            },

            cleanWhatsapp(num) {
                let clean = String(num).replace(/[^0-9]/g, '');
                if (clean.startsWith('0')) clean = '62' + clean.slice(1);
                return clean;
            },

            formatAction(action, qty) {
                switch(action) {
                    case 'tukar_seimbang': return '• Tukar 1:1';
                    case 'pinjam': return `• Pinjam (+${qty} Gp)`;
                    case 'kembalikan': return `• Kembali (-${qty} Gp)`;
                    default: return '';
                }
            },

            // --- Delivery Controller Actions (Tiket #45) ---
            async startDelivery(order) {
                this.isProcessing = order.id;
                this.actionError = '';
                try {
                    const res = await fetch(`/delivery/${order.id}/start`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal memulai pengantaran.');
                    order.delivery_status = 'diantar';
                } catch (e) {
                    this.actionError = e.message;
                } finally {
                    this.isProcessing = null;
                }
            },

            async completeDelivery(order) {
                this.isProcessing = order.id;
                this.actionError = '';
                try {
                    const res = await fetch(`/delivery/${order.id}/complete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal menyelesaikan pengantaran.');
                    
                    this.orders = this.orders.filter(o => o.id !== order.id);
                } catch (e) {
                    this.actionError = e.message;
                } finally {
                    this.isProcessing = null;
                }
            },

            confirmCancel(order) {
                this.orderToCancel = order;
                this.actionError = '';
            },

            async executeCancel() {
                if (!this.orderToCancel) return;
                const orderId = this.orderToCancel.id;
                this.isProcessing = orderId;
                try {
                    const res = await fetch(`/delivery/${orderId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal membatalkan order.');

                    this.orders = this.orders.filter(o => o.id !== orderId);
                    this.orderToCancel = null;
                } catch (e) {
                    this.actionError = e.message;
                    this.orderToCancel = null;
                } finally {
                    this.isProcessing = null;
                }
            },

            // --- Smart Search & Quick Add (Tiket #47) ---
            async searchCustomer() {
                const q = this.customerSearchQuery.trim();
                if (q.length < 2) {
                    this.customerResults = [];
                    return;
                }
                try {
                    const res = await fetch(`/customers/search?q=${encodeURIComponent(q)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    this.customerResults = await res.json();
                } catch (e) {
                    this.customerResults = [];
                }
            },

            selectCustomer(cust) {
                this.selectedCustomer = cust;
                this.customerSearchQuery = cust.name;
                this.customerResults = [];
            },

            clearCustomer() {
                this.selectedCustomer = null;
                this.customerSearchQuery = '';
                this.customerResults = [];
            },

            async saveQuickAdd() {
                if (!this.quickAddForm.name || !this.quickAddForm.whatsapp_number) return;
                this.isQuickAdding = true;
                try {
                    const res = await fetch('{{ route('customers.quick-store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.quickAddForm)
                    });
                    const result = await res.json();
                    if (!res.ok) throw new Error(result.message || 'Gagal menambah pelanggan.');

                    this.selectCustomer(result.data);
                    this.openMiniQuickAdd = false;
                    this.quickAddForm = { name: '', whatsapp_number: '', address: '' };
                } catch (e) {
                    alert(e.message);
                } finally {
                    this.isQuickAdding = false;
                }
            },

            // --- Jual Dadakan & Retur Murni (Tiket #46) ---
            setPureReturnMode() {
                this.isPureReturn = true;
                this.formOrder.gallonAction = 'kembalikan';
            },

            selectTier(tier) {
                this.formOrder.priceTierId = tier.id;
                this.formOrder.tierPrice = tier.price;
                this.formOrder.tierAppliesTo = tier.applies_to;

                if (tier.applies_to === 'galon_baru') {
                    this.formOrder.gallonAction = 'tidak_ada';
                } else if (this.formOrder.gallonAction === 'tidak_ada') {
                    this.formOrder.gallonAction = 'tukar_seimbang';
                }
            },

            selectAction(action) {
                if (this.isNewGallonTier) return;
                this.formOrder.gallonAction = action;
            },

            async submitQuickSale() {
                this.formError = '';

                // Validasi Frontend
                if (this.isPureReturn) {
                    if (!this.selectedCustomer) {
                        this.formError = 'Pelanggan terdaftar wajib dipilih untuk retur galon!';
                        return;
                    }
                    if (this.formOrder.pureReturnQty > this.selectedCustomer.borrowed_gallons) {
                        this.formError = `Jumlah retur melebihi pinjaman pelanggan (${this.selectedCustomer.borrowed_gallons} galon).`;
                        return;
                    }
                } else {
                    // Validasi: pinjam/kembalikan WAJIB pelanggan terdaftar, tukar_seimbang & tidak_ada boleh Pelanggan Umum
                    if (['pinjam', 'kembalikan'].includes(this.formOrder.gallonAction) && !this.selectedCustomer) {
                        this.formError = 'Pelanggan terdaftar wajib dipilih jika meminjam atau mengembalikan galon.';
                        return;
                    }

                    if (this.formOrder.gallonAction === 'kembalikan' && this.selectedCustomer) {
                        if (this.formOrder.quantity > this.selectedCustomer.borrowed_gallons) {
                            this.formError = `Jumlah pengembalian (${this.formOrder.quantity}) melebihi galon yang sedang dipinjam pelanggan (${this.selectedCustomer.borrowed_gallons} unit).`;
                            return;
                        }
                    }

                    if (this.formOrder.quantity > this.tutupStock) {
                        this.formError = `Stok tutup galon tidak mencukupi (Tersisa: ${this.tutupStock}).`;
                        return;
                    }
                }

                this.isSubmitting = true;

                const isNewGallon = !this.isPureReturn && this.isNewGallonTier;
                const endpoint = isNewGallon ? '{{ route('pos.order.new-gallon') }}' : '{{ route('pos.order.refill') }}';
                const productId = isNewGallon ? 2 : 1;

                const itemsPayload = this.isPureReturn ? [{
                    product_id: 1,
                    price_tier_id: null,
                    quantity: 0,
                    gallon_action: 'kembalikan',
                    gallon_qty: this.formOrder.pureReturnQty
                }] : [{
                    product_id: productId,
                    price_tier_id: this.formOrder.priceTierId,
                    quantity: this.formOrder.quantity,
                    gallon_action: isNewGallon ? 'tidak_ada' : this.formOrder.gallonAction,
                    gallon_qty: isNewGallon ? 0 : (this.formOrder.gallonAction === 'tukar_seimbang' ? this.formOrder.quantity : this.formOrder.quantity)
                }];

                const payload = {
                    order_type: 'ambil_sendiri',
                    customer_id: this.selectedCustomer ? this.selectedCustomer.id : null,
                    payment_method: 'tunai',
                    items: itemsPayload
                };

                try {
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Transaksi gagal.');

                    // Update stok lokal
                    if (!this.isPureReturn) {
                        this.tutupStock -= this.formOrder.quantity;
                    }

                    alert(this.isPureReturn ? 'Retur galon berhasil dicatat!' : 'Transaksi dadakan berhasil disimpan!');
                    this.openQuickSaleModal = false;
                    this.clearCustomer();
                    this.formOrder.quantity = 1;
                    this.isPureReturn = false;
                } catch (e) {
                    this.formError = e.message;
                } finally {
                    this.isSubmitting = false;
                }
            }
        };
    }
</script>
@endpush