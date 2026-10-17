@props([
    'stock' => 0,
    'threshold' => 600,
    'isLowStock' => false,
    'dismissible' => true,
])

@if ($isLowStock)
    <div x-data="{ show: true }" x-show="show" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="p-4 sm:p-5 rounded-3xl bg-amber-500/10 border-2 border-amber-500/30 text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm backdrop-blur-xs">
        
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm shadow-amber-500/30">
                <i class="fa-solid fa-triangle-exclamation text-lg animate-bounce"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-amber-500 text-white">
                        Peringatan Stok Kritis
                    </span>
                    <span class="text-xs font-bold text-amber-800">
                        Bahan Baku Habis Pakai
                    </span>
                </div>
                <h4 class="text-sm sm:text-base font-black text-slate-900 mt-1">
                    Stok Tutup Galon Segel Menipis!
                </h4>
                <p class="text-xs text-slate-600 font-medium mt-0.5 leading-relaxed">
                    Peringatan: Stok tutup galon tersisa <strong class="text-amber-900 font-black">{{ number_format($stock, 0, ',', '.') }}</strong> unit 
                    (di bawah atau mencapai batas minimum <span class="font-bold text-slate-700">{{ number_format($threshold, 0, ',', '.') }}</span>). 
                    Segera lakukan restock agar transaksi kasir tidak terhenti!
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
            <a href="{{ route('inventory.restock') }}"
                class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 active:scale-98 text-white text-xs font-black shadow-md shadow-amber-500/20 transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-boxes-stacked text-xs"></i>
                <span>Restock Sekarang</span>
            </a>

            @if ($dismissible)
                <button type="button" @click="show = false" title="Tutup Notifikasi"
                    class="w-9 h-9 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-900 flex items-center justify-center text-xs transition cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            @endif
        </div>
    </div>
@endif