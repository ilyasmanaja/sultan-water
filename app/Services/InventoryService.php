<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public const ITEM_CAP = 'tutup_galon';
    public const ITEM_FLEET_TOTAL = 'galon_kosong_depot'; // Penampung G_total

    /**
     * Memotong stok tutup galon sebesar Qg = Q_isi + Q_baru (BR-INV-01).
     * Wajib dipanggil di dalam DB::transaction() milik pemanggil (OrderController)
     * sebagai lock pertama sebelum galon_kosong_depot dan customers.
     */
    public function deductCap(int $qty, int $orderId, int $userId): ?Inventory
    {
        if ($qty <= 0) {
            return null;
        }

        $capInventory = Inventory::where('item_type', self::ITEM_CAP)
            ->lockForUpdate()
            ->firstOrFail();

        if ($capInventory->quantity < $qty) {
            throw ValidationException::withMessages([
                'items' => [
                    "Stok tutup galon tidak mencukupi (Tersisa: {$capInventory->quantity}, Dibutuhkan: {$qty}). Silakan lakukan restock terlebih dahulu.",
                ],
            ]);
        }

        $capInventory->quantity -= $qty;
        $capInventory->save();

        InventoryLog::create([
            'inventory_id'  => $capInventory->id,
            'change_amount' => -$qty,
            'current_stock' => $capInventory->quantity,
            'reason'        => 'transaksi',
            'order_id'      => $orderId,
            'created_by'    => $userId,
        ]);

        return $capInventory;
    }

    /**
     * Mengurangi Total Galon Dimiliki (G_total) pada baris galon_kosong_depot
     * khusus untuk penjualan Galon Baru + Isi sebesar Q_baru (BR-INV-02 §3.D).
     * Tidak memvalidasi/memblokir transaksi meskipun G_total kurang atau menjadi minus.
     * Wajib dipanggil setelah deductCap() dan sebelum lock customers.
     */
    public function deductFleetTotal(int $newGallonQty, int $orderId, int $userId): ?Inventory
    {
        if ($newGallonQty <= 0) {
            return null;
        }

        $fleetInventory = Inventory::where('item_type', self::ITEM_FLEET_TOTAL)
            ->lockForUpdate()
            ->firstOrFail();

        $fleetInventory->quantity -= $newGallonQty;
        $fleetInventory->save();

        InventoryLog::create([
            'inventory_id'  => $fleetInventory->id,
            'change_amount' => -$newGallonQty,
            'current_stock' => $fleetInventory->quantity,
            'reason'        => 'transaksi',
            'order_id'      => $orderId,
            'created_by'    => $userId,
        ]);

        return $fleetInventory;
    }

    /**
     * Restock tutup galon oleh Admin (S2-BE-05).
     * Selalu menambah baris tutup_galon dengan reason = 'restock_stok_jual'.
     */
    public function restockCap(int $quantity, int $userId): Inventory
    {
        return DB::transaction(function () use ($quantity, $userId) {
            $capInventory = Inventory::where('item_type', self::ITEM_CAP)
                ->lockForUpdate()
                ->firstOrFail();

            $capInventory->quantity += $quantity;
            $capInventory->save();

            InventoryLog::create([
                'inventory_id'  => $capInventory->id,
                'change_amount' => $quantity,
                'current_stock' => $capInventory->quantity,
                'reason'        => 'restock_stok_jual',
                'order_id'      => null,
                'created_by'    => $userId,
            ]);

            return $capInventory;
        });
    }

    /**
     * Pembelian / restock galon baru dari distributor oleh Admin (S2-BE-05, BR-INV-02 §6.B).
     * Menambah G_total (baris galon_kosong_depot) sebesar R.
     * Pilihan alokasi ('stok_dijual' | 'armada_depot') murni menentukan reason log.
     */
    public function restockGallon(int $quantity, string $allocation, int $userId): Inventory
    {
        $reason = match ($allocation) {
            'stok_dijual'  => 'restock_stok_jual',
            'armada_depot' => 'restock_armada_depot',
            default        => throw ValidationException::withMessages([
                'allocation' => ['Alokasi restock wajib dipilih (Stok Dijual / Armada Depot).'],
            ]),
        };

        return DB::transaction(function () use ($quantity, $reason, $userId) {
            $fleetInventory = Inventory::where('item_type', self::ITEM_FLEET_TOTAL)
                ->lockForUpdate()
                ->firstOrFail();

            $fleetInventory->quantity += $quantity;
            $fleetInventory->save();

            InventoryLog::create([
                'inventory_id'  => $fleetInventory->id,
                'change_amount' => $quantity,
                'current_stock' => $fleetInventory->quantity,
                'reason'        => $reason,
                'order_id'      => null,
                'created_by'    => $userId,
            ]);

            return $fleetInventory;
        });
    }
}