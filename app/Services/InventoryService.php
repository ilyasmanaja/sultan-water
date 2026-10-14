<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * STUB sementara untuk S2-BE-01 supaya storeRefill bisa dijalankan & diuji.
 * Implementasi final (termasuk deductNewGallon / G_total, restock) ada di
 * S2-BE-03 dan S2-BE-05; ganti/gabungkan file ini saat tiket itu dikerjakan.
 *
 * Wajib dipanggil di dalam DB::transaction() milik pemanggil.
 * Butuh kolom inventory_logs.current_stock (migrasi S2-BE-05).
 */
class InventoryService
{
    public function deductCap(int $qg, Order $order, User $user): void
    {
        if ($qg <= 0) {
            return; // Qg = 0 -> tidak ada mutasi dan tidak ada log
        }

        $cap = Inventory::query()
            ->where('item_type', 'tutup_galon')
            ->lockForUpdate()
            ->firstOrFail();

        if ($cap->quantity < $qg) {
            throw ValidationException::withMessages([
                'items' => sprintf(
                    'Stok tutup galon tidak mencukupi (Tersisa: %d, Dibutuhkan: %d). Silakan lakukan restock terlebih dahulu.',
                    $cap->quantity,
                    $qg
                ),
            ]);
        }

        $cap->quantity -= $qg;
        $cap->save();

        InventoryLog::create([
            'inventory_id'  => $cap->id,
            'order_id'      => $order->id,
            'reason'        => 'transaksi',
            'change_amount' => -$qg,
            'current_stock' => $cap->quantity,
            'created_by'    => $user->id,
        ]);
    }
}