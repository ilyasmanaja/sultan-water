@extends('layouts.app')

@section('title', 'Detail Pelanggan - ' . $customer->name)

@section('body-attributes')
    x-data="customerHistoryApp({{ $customer->id }})"
@endsection

@section('page-title', $customer->name)

@section('page-badge')
    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
        Pelanggan Terdaftar
    </span>
@endsection

@section('page-subtitle')
    <span>Informasi profil pelanggan, alamat pengantaran, dan rekapitulasi riwayat pembelian air</span>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('pos.dashboard') }}"
            class="px-3.5 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-cash-register text-slate-500"></i>
            <span>Kembali ke Kasir</span>
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- ========================================================================= -->
    <!-- 1. KARTU PROFIL PELANGGAN & SALDO PINJAMAN GALON (Gp)                     -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Info Identitas -->
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center text-xl font-black shrink-0">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                        {{ $customer->name }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-3 mt-1 text-xs text-slate-500 font-medium">
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', $customer->whatsapp_number) }}" target="_blank"
                            class="text-emerald-700 font-bold hover:underline flex items-center gap-1.5 bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-200">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>{{ $customer->whatsapp_number }}</span>
                        </a>
                        <span class="text-slate-300">•</span>
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-slate-400"></i>
                            <span>{{ $customer->address ?: 'Alamat belum diatur' }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kartu Saldo Galon Dipinjam (Gp) -->
            <div class="self-start md:self-center p-3.5 sm:p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-base shadow-xs shadow-amber-500/20">
                    <i class="fa-solid fa-jar"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block">
                        Galon Dipinjam (Gp)
                    </span>
                    <div class="text-lg sm:text-xl font-black {{ $customer->borrowed_gallons > 0 ? 'text-amber-800' : 'text-slate-800' }}">
                        {{ number_format($customer->borrowed_gallons, 0, ',', '.') }}
                        <span class="text-xs font-bold text-slate-400">unit</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. KOMPONEN FILTER & RIWAYAT PEMBELIAN BULANAN (TIKET #80)                -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">

        <!-- Toolbar Filter Bulan & Tahun -->
        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-black text-slate-900 tracking-tight">
                    Riwayat Pembelian Galon Bulanan
                </h3>
                <p class="text-xs text-slate-400 font-medium mt-0.5">
                    Filter konsumsi air minum per periode untuk evaluasi kebiasaan pemesanan pelanggan
                </p>
            </div>

            <!-- Kontrol Dropdown Bulan & Tahun -->
            <div class="flex items-center gap-2">
                <select x-model="selectedMonth" @change="fetchHistory()"
                    class="min-h-[40px] px-3.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mei</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>

                <select x-model="selectedYear" @change="fetchHistory()"
                    class="min-h-[40px] px-3.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-2xl font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                    @for ($y = now()->year; $y >= 2024; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>

                <button type="button" @click="fetchHistory()" title="Muat Ulang"
                    class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs transition cursor-pointer">
                    <i class="fa-solid fa-rotate text-xs" :class="isLoading ? 'fa-spin' : ''"></i>
                </button>
            </div>
        </div>

        <!-- Kartu Ringkasan Konsumsi Air Bulanan -->
        <div class="p-4 sm:p-6 bg-slate-50/70 border-b border-slate-100">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <!-- 1. Total Galon Terbeli -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">
                        Total Volume Galon
                    </span>
                    <div class="text-2xl font-black text-blue-600">
                        <span x-text="summary.total_gallons">0</span>
                        <span class="text-xs font-bold text-slate-400">Galon</span>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium mt-1">
                        Konsumsi bulan <span class="font-bold text-slate-600" x-text="monthNames[selectedMonth - 1] + ' ' + selectedYear"></span>
                    </p>
                </div>

                <!-- 2. Total Pengeluaran / Belanja -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">
                        Total Belanja Air
                    </span>
                    <div class="text-2xl font-black text-slate-900" x-text="rupiah(summary.total_spent)">
                        Rp 0
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium mt-1">
                        Akumulasi pembayaran transaksi selesai
                    </p>
                </div>

                <!-- 3. Frekuensi Transaksi -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">
                        Frekuensi Transaksi
                    </span>
                    <div class="text-2xl font-black text-slate-900">
                        <span x-text="summary.orders_count">0</span>
                        <span class="text-xs font-bold text-slate-400">Order</span>
                    </div>
                    <p class="text-[11px] text-slate-400 font-medium mt-1">
                        Pesanan berstatus pengiriman selesai
                    </p>
                </div>

            </div>
        </div>

        <!-- Tabel Riwayat Pembelian -->
        <div class="overflow-x-auto touch-scroll">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-4 sm:px-6">No. Order</th>
                        <th class="py-3 px-4">Waktu Transaksi</th>
                        <th class="py-3 px-4">Tipe Layanan</th>
                        <th class="py-3 px-4">Item & Varian Harga</th>
                        <th class="py-3 px-4 text-center">Volume</th>
                        <th class="py-3 px-4 text-right">Total Tagihan</th>
                        <th class="py-3 px-4 text-center">Pembayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">

                    <!-- Loading State -->
                    <template x-if="isLoading">
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                                <p class="font-medium text-xs">Memuat data transaksi bulanan...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State: Tidak Ada Transaksi pada Periode Ini -->
                    <template x-if="!isLoading && orders.length === 0">
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center text-xl mx-auto mb-2">
                                    <i class="fa-solid fa-basket-shopping"></i>
                                </div>
                                <p class="font-bold text-xs text-slate-600">Tidak ada transaksi pada periode ini</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    Pelanggan tidak memiliki transaksi selesai pada <span x-text="monthNames[selectedMonth - 1] + ' ' + selectedYear"></span>.
                                </p>
                            </td>
                        </tr>
                    </template>

                    <!-- Baris Transaksi Selesai -->
                    <template x-if="!isLoading && orders.length > 0">
                        <template x-for="order in orders" :key="order.order_id">
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- No. Order -->
                                <td class="py-3.5 px-4 sm:px-6 font-mono font-extrabold text-blue-600" x-text="order.order_number"></td>

                                <!-- Tanggal -->
                                <td class="py-3.5 px-4 text-slate-600 font-medium whitespace-nowrap" x-text="order.created_at"></td>

                                <!-- Tipe Layanan -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase"
                                        :class="order.order_type === 'ambil_sendiri' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-800'"
                                        x-text="order.order_type === 'ambil_sendiri' ? 'Walk-in' : 'Pesan Antar'">
                                    </span>
                                </td>

                                <!-- Rincian Item Transaksi -->
                                <td class="py-3.5 px-4">
                                    <template x-for="item in order.items" :key="item.product_name + item.tier_name">
                                        <div class="mb-1 last:mb-0">
                                            <span class="font-bold text-slate-800" x-text="item.tier_name"></span>
                                            <span class="text-slate-400 text-[11px]" x-text="' (' + item.quantity + 'x @ ' + rupiah(item.unit_price) + ')'"></span>
                                            
                                            <!-- Badge Aksi Galon -->
                                            <template x-if="item.gallon_action && item.gallon_action !== 'tidak_ada'">
                                                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[9px] font-bold"
                                                    :class="{
                                                        'bg-emerald-50 text-emerald-700': item.gallon_action === 'tukar_seimbang',
                                                        'bg-amber-50 text-amber-700': item.gallon_action === 'pinjam',
                                                        'bg-purple-50 text-purple-700': item.gallon_action === 'kembalikan'
                                                    }"
                                                    x-text="formatAction(item.gallon_action, item.gallon_qty)">
                                                </span>
                                            </template>
                                        </div>
                                    </template>
                                </td>

                                <!-- Total Galon -->
                                <td class="py-3.5 px-4 text-center font-black text-slate-800 whitespace-nowrap">
                                    <span x-text="order.gallons + ' Unit'"></span>
                                </td>

                                <!-- Total Tagihan -->
                                <td class="py-3.5 px-4 text-right font-black text-slate-900 whitespace-nowrap" x-text="rupiah(order.total_amount)"></td>

                                <!-- Metode Pembayaran -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full font-black text-[10px] uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-check text-[9px] mr-1"></i>
                                        <span x-text="order.payment_method"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </template>

                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    function customerHistoryApp(customerId) {
        return {
            customerId: customerId,
            selectedMonth: {{ now()->month }},
            selectedYear: {{ now()->year }},
            isLoading: false,

            monthNames: [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ],

            summary: {
                total_gallons: 0,
                total_spent: 0,
                orders_count: 0
            },

            orders: [],

            init() {
                this.fetchHistory();
            },

            rupiah(val) {
                return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID');
            },

            formatAction(action, qty) {
                switch(action) {
                    case 'tukar_seimbang': return 'Tukar 1:1';
                    case 'pinjam': return `Pinjam (+${qty})`;
                    case 'kembalikan': return `Kembali (-${qty})`;
                    default: return '';
                }
            },

            async fetchHistory() {
                this.isLoading = true;
                const url = `/customers/${this.customerId}/monthly-purchases?month=${this.selectedMonth}&year=${this.selectedYear}`;

                try {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Gagal memuat riwayat transaksi.');
                    }

                    const result = await response.json();
                    if (result.success && result.data) {
                        this.summary.total_gallons = result.data.total_gallons || 0;
                        this.summary.total_spent = result.data.total_spent || 0;
                        this.summary.orders_count = result.data.orders_count || 0;
                        this.orders = result.data.orders || [];
                    }
                } catch (err) {
                    console.error('Error fetching customer history:', err);
                } finally {
                    this.isLoading = false;
                }
            }
        };
    }
</script>
@endpush