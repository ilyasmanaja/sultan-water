@extends('layouts.app')

@section('title', 'Kelola Master Tier Harga - Sultan Water')
@section('page-title', 'Master Tier Harga')
@section('page-subtitle', 'Pengaturan nominal 5 varian tier harga & status aktif')

@section('page-badge')
    <span
        class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
        Admin Only
    </span>
@endsection

@section('header-actions')
    <a href="{{ route('admin.dashboard') }}"
        class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
        <i class="fa-solid fa-arrow-left text-[11px]"></i>
        <span>Kembali ke Dashboard</span>
    </a>
@endsection

@section('body-attributes')
    x-data="priceTierManager({{ Js::from($priceTiers ?? []) }})"
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-5">

        <!-- Alert Sukses Toast/Banner -->
        <template x-if="successMessage">
            <div
                class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <div class="font-black text-emerald-950">Berhasil Memperbarui!</div>
                        <div class="font-medium mt-0.5" x-text="successMessage"></div>
                    </div>
                </div>
                <button type="button" @click="successMessage = ''"
                    class="text-emerald-600 hover:text-emerald-900 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </template>

        <!-- Kartu Informasi & Aturan Bisnis -->
        <div class="bg-blue-50/70 border border-blue-100 rounded-3xl p-4 sm:p-5 flex items-start gap-3.5">
            <div
                class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-base shrink-0 shadow-xs">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div class="text-xs text-blue-900 leading-relaxed">
                <div class="font-extrabold text-blue-950 mb-0.5">Ketentuan Pembaruan Harga:</div>
                <p class="font-medium">
                    Perubahan harga langsung berlaku untuk transaksi kasir baru berikutnya tanpa perlu restart server.
                    Transaksi lama tetap menggunakan harga histori transaksi (<em class="font-semibold">snapshot</em>).
                    Setiap kategori (<code class="px-1 py-0.5 bg-blue-100/70 rounded text-[11px]">isi_ulang</code> & <code
                        class="px-1 py-0.5 bg-blue-100/70 rounded text-[11px]">galon_baru</code>) wajib memiliki minimal
                    satu tier harga yang aktif.
                </p>
            </div>
        </div>

        <!-- Tabel Master 5 Tier Harga -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900">Daftar Tier Harga POS</h2>
                    <p class="text-xs text-slate-400 font-medium">Total 5 varian harga produk depot</p>
                </div>
                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl"
                    x-text="tiers.length + ' Tier Terdaftar'"></span>
            </div>

            <div class="overflow-x-auto touch-scroll">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-black uppercase tracking-wider text-slate-400">
                            <th class="py-3 px-5">Kode</th>
                            <th class="py-3 px-4">Nama Tier</th>
                            <th class="py-3 px-4">Kategori Produk</th>
                            <th class="py-3 px-4 text-right">Harga Saat Ini</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <template x-for="tier in tiers" :key="tier.id">
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Kolom Kode -->
                                <td class="py-4 px-5 font-mono font-bold text-slate-600" x-text="tier.code"></td>

                                <!-- Kolom Nama -->
                                <td class="py-4 px-4 font-black text-slate-900 text-sm" x-text="tier.name"></td>

                                <!-- Kolom Kategori (applies_to) -->
                                <td class="py-4 px-4">
                                    <span
                                        class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wide border inline-flex items-center gap-1"
                                        :class="tier.applies_to === 'isi_ulang' ?
                                            'bg-blue-50 text-blue-700 border-blue-200' :
                                            'bg-emerald-50 text-emerald-700 border-emerald-200'">
                                        <i class="fa-solid"
                                            :class="tier.applies_to === 'isi_ulang' ? 'fa-bottle-water' : 'fa-jar'"></i>
                                        <span
                                            x-text="tier.applies_to === 'isi_ulang' ? 'Air Isi Ulang' : 'Galon Baru + Isi'"></span>
                                    </span>
                                </td>

                                <!-- Kolom Harga Rupiah -->
                                <td class="py-4 px-4 text-right font-black text-slate-900 text-base"
                                    x-text="rupiah(tier.price)"></td>

                                <!-- Kolom Status Aktif -->
                                <td class="py-4 px-4 text-center">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase inline-flex items-center gap-1"
                                        :class="tier.is_active ?
                                            'bg-emerald-100 text-emerald-800' :
                                            'bg-slate-100 text-slate-500'">
                                        <i class="fa-solid text-[9px]"
                                            :class="tier.is_active ? 'fa-check' : 'fa-xmark'"></i>
                                        <span x-text="tier.is_active ? 'Aktif' : 'Nonaktif'"></span>
                                    </span>
                                </td>

                                <!-- Kolom Tombol Edit -->
                                <td class="py-4 px-5 text-center">
                                    <button type="button" @click="openEditModal(tier)"
                                        class="min-h-[38px] px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 font-extrabold text-xs transition inline-flex items-center gap-1.5 active:scale-95 cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        <span>Ubah Harga</span>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@section('modals')
    <!-- ========================================================================= -->
    <!-- MODAL FORM EDIT HARGA & STATUS TIER (Tiket #83)                           -->
    <!-- ========================================================================= -->
    <div x-show="editingTier" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200"
            @click.away="closeModal()">

            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 block"
                        x-text="'Kode: ' + editForm.code"></span>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 mt-0.5" x-text="'Ubah ' + editForm.name"></h3>
                </div>
                <button type="button" @click="closeModal()"
                    class="w-8 h-8 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Error Banner Modal -->
            <template x-if="errorMessage">
                <div
                    class="mt-3 p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-2xl flex items-start gap-2 shadow-xs">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 mt-0.5 shrink-0"></i>
                    <div class="flex-1 font-semibold" x-text="errorMessage"></div>
                </div>
            </template>

            <!-- Body Form -->
            <form @submit.prevent="saveTierPrice()" novalidate class="mt-4 space-y-4">

                <!-- Input Nominal Harga -->
                <div>
                    <label for="tier_price" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nominal Harga (Rupiah) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-xs text-slate-400">Rp</span>
                        <input type="number" id="tier_price" min="500" step="100" x-model.number="editForm.price"
                            required
                            class="w-full min-h-[44px] pl-10 pr-4 text-sm bg-slate-50 border border-slate-200 rounded-2xl font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1 font-medium">
                        Format: angka bilangan bulat positif (contoh: 5000)[cite: 1].
                    </p>
                </div>

                <!-- Saklar Status Aktif / Nonaktif -->
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">Status Aktif Tier</span>
                        <span class="text-[11px] text-slate-400 font-medium">Jika nonaktif, kasir tidak dapat memilih tier
                            ini[cite: 1].</span>
                    </div>

                    <!-- Toggle Switch Berbasis Alpine.js -->
                    <button type="button" @click="editForm.is_active = !editForm.is_active"
                        :class="editForm.is_active ? 'bg-blue-600' : 'bg-slate-300'"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                        <span :class="editForm.is_active ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>

                <!-- Footer Tombol -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="closeModal()"
                        class="h-11 px-4 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-2xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        class="h-11 px-6 bg-blue-600 hover:bg-blue-700 active:scale-98 disabled:opacity-50 text-white font-black text-xs rounded-2xl shadow-md shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                        <i class="fa-solid fa-check text-xs"></i>
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function priceTierManager(initialTiers = []) {
            return {
                tiers: initialTiers,
                editingTier: null,
                isSubmitting: false,
                successMessage: '',
                errorMessage: '',

                editForm: {
                    id: null,
                    code: '',
                    name: '',
                    applies_to: '',
                    price: 0,
                    is_active: true
                },

                rupiah(val) {
                    return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID');
                },

                openEditModal(tier) {
                    this.editingTier = tier;
                    this.errorMessage = '';
                    this.editForm = {
                        id: tier.id,
                        code: tier.code,
                        name: tier.name,
                        applies_to: tier.applies_to,
                        price: Number(tier.price),
                        is_active: Boolean(tier.is_active)
                    };
                },

                closeModal() {
                    this.editingTier = null;
                    this.errorMessage = '';
                },

                async saveTierPrice() {
                    this.errorMessage = '';

                    // Validasi Client-Side
                    if (!this.editForm.price || this.editForm.price <= 0) {
                        this.errorMessage = 'Nominal harga harus berupa angka lebih besar dari 0.';
                        return;
                    }

                    // Guard Client-Side: Minimal 1 tier aktif per kategori
                    if (!this.editForm.is_active && this.editingTier.is_active) {
                        const activeCountSameCategory = this.tiers.filter(t =>
                            t.applies_to === this.editingTier.applies_to &&
                            t.is_active &&
                            t.id !== this.editingTier.id
                        ).length;

                        if (activeCountSameCategory === 0) {
                            this.errorMessage =
                                `Kategori ${this.editingTier.applies_to} minimal harus memiliki satu tier harga yang aktif.`;
                            return;
                        }
                    }

                    this.isSubmitting = true;

                    try {
                        const response = await fetch(`/admin/price-tiers/${this.editForm.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                price: this.editForm.price,
                                is_active: this.editForm.is_active
                            })
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            // Tangani pesan validasi dari Laravel
                            if (result.errors) {
                                const firstKey = Object.keys(result.errors)[0];
                                throw new Error(result.errors[firstKey][0]);
                            }
                            throw new Error(result.message || 'Gagal memperbarui harga.');
                        }

                        // Perbarui state lokal tabel secara real-time
                        const targetIndex = this.tiers.findIndex(t => t.id === this.editForm.id);
                        if (targetIndex !== -1) {
                            this.tiers[targetIndex].price = this.editForm.price;
                            this.tiers[targetIndex].is_active = this.editForm.is_active;
                        }

                        this.successMessage =
                            `Harga ${this.editForm.name} berhasil diperbarui menjadi ${this.rupiah(this.editForm.price)}.`;
                        this.closeModal();
                    } catch (err) {
                        this.errorMessage = err.message;
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>
@endpush
