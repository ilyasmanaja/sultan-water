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
            'inventory_id' => $capInventory->id,
            'change_amount' => -$qty,
            'current_stock' => $capInventory->quantity,
            'reason' => 'transaksi',
            'order_id' => $orderId,
            'created_by' => $userId,
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
            'inventory_id' => $fleetInventory->id,
            'change_amount' => -$newGallonQty,
            'current_stock' => $fleetInventory->quantity,
            'reason' => 'transaksi',
            'order_id' => $orderId,
            'created_by' => $userId,
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
                'inventory_id' => $capInventory->id,
                'change_amount' => $quantity,
                'current_stock' => $capInventory->quantity,
                'reason' => 'restock_stok_jual',
                'order_id' => null,
                'created_by' => $userId,
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
            'stok_dijual' => 'restock_stok_jual',
            'armada_depot' => 'restock_armada_depot',
            default => throw ValidationException::withMessages([
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
                'inventory_id' => $fleetInventory->id,
                'change_amount' => $quantity,
                'current_stock' => $fleetInventory->quantity,
                'reason' => $reason,
                'order_id' => null,
                'created_by' => $userId,
            ]);

            return $fleetInventory;
        });
    }

    /**
     * Membalik mutasi inventaris pada pembatalan pesanan (BR-INV-01 & BR-INV-02 §3.F).
     * Wajib dipanggil di dalam DB::transaction() milik pemanggil (DeliveryController)
     * setelah order dikunci dengan lockForUpdate(), dan sebelum CustomerLoanService::reverseOrder().
     *
     * Urutan lock anti-deadlock:
     * 1. tutup_galon
     * 2. galon_kosong_depot (jika ada galon baru)
     */
    public function rollbackOrder(\App\Models\Order $order, int $userId): void
    {
        // 1. Ambil log transaksi terkait order_id jika ada
        $transactionLogs = InventoryLog::where('order_id', $order->id)
            ->where('reason', 'transaksi')
            ->get();

        if ($transactionLogs->isNotEmpty()) {
            // Urutkan pemrosesan log agar tutup_galon selalu dikunci sebelum galon_kosong_depot
            $sortedLogs = $transactionLogs->sortBy(function ($log) {
                return $log->inventory->item_type === self::ITEM_CAP ? 1 : 2;
            });

            foreach ($sortedLogs as $log) {
                $inventory = Inventory::where('id', $log->inventory_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // change_amount saat transaksi bernilai negatif (-Q), maka pembalikannya bernilai positif (+Q)
                $reversalQty = abs($log->change_amount);

                $inventory->quantity += $reversalQty;
                $inventory->save();

                InventoryLog::create([
                    'inventory_id' => $inventory->id,
                    'change_amount' => $reversalQty,
                    'current_stock' => $inventory->quantity,
                    'reason' => 'rollback',
                    'order_id' => $order->id,
                    'created_by' => $userId,
                ]);
            }

            return;
        }

        // Fallback jika log belum tercatat order_id-nya: hitung langsung dari order_items
        $totalCapQty = 0;
        $totalNewGallonQty = 0;

        foreach ($order->items as $item) {
            $totalCapQty += (int) $item->quantity;
            if ($item->product && $item->product->type === 'galon_baru') {
                $totalNewGallonQty += (int) $item->quantity;
            }
        }

        // Lock & Rollback Tutup Galon (+Qg)
        if ($totalCapQty > 0) {
            $capInventory = Inventory::where('item_type', self::ITEM_CAP)
                ->lockForUpdate()
                ->firstOrFail();

            $capInventory->quantity += $totalCapQty;
            $capInventory->save();

            InventoryLog::create([
                'inventory_id' => $capInventory->id,
                'change_amount' => $totalCapQty,
                'current_stock' => $capInventory->quantity,
                'reason' => 'rollback',
                'order_id' => $order->id,
                'created_by' => $userId,
            ]);
        }

        // Lock & Rollback Armada G_total (+Qbaru)
        if ($totalNewGallonQty > 0) {
            $fleetInventory = Inventory::where('item_type', self::ITEM_FLEET_TOTAL)
                ->lockForUpdate()
                ->firstOrFail();

            $fleetInventory->quantity += $totalNewGallonQty;
            $fleetInventory->save();

            InventoryLog::create([
                'inventory_id' => $fleetInventory->id,
                'change_amount' => $totalNewGallonQty,
                'current_stock' => $fleetInventory->quantity,
                'reason' => 'rollback',
                'order_id' => $order->id,
                'created_by' => $userId,
            ]);
        }
    }


    /**
     * Memeriksa status ketersediaan tutup galon dan mendeteksi kondisi low-stock (S4-BE-06 / Tiket #76).
     * Alert aktif jika quantity <= low_stock_threshold.
     */
    public function getCapStockStatus(): array
    {
        $capInventory = Inventory::where('item_type', self::ITEM_CAP)->firstOrFail();

        $threshold = (int) ($capInventory->low_stock_threshold ?? 600);
        $currentStock = (int) $capInventory->quantity;
        $isLowStock = $currentStock <= $threshold;

        return [
            'item_type'           => self::ITEM_CAP,
            'current_stock'       => $currentStock,
            'low_stock_threshold' => $threshold,
            'is_low_stock'        => $isLowStock,
            'message'             => $isLowStock
                ? "Peringatan: Stok tutup galon tersisa {$currentStock} unit (di bawah atau sama dengan batas minimum {$threshold}). Segera lakukan restock!"
                : "Stok tutup galon aman ({$currentStock} unit).",
        ];
    }

    /**
     * Memperbarui batas minimum (threshold) low-stock tutup galon oleh Admin (S4-BE-06 / Tiket #76).
     * Validasi: bilangan bulat minimal 1.
     */
    public function updateThreshold(int $newThreshold): Inventory
    {
        if ($newThreshold < 1) {
            throw ValidationException::withMessages([
                'low_stock_threshold' => ['Ambang batas minimum harus berupa bilangan bulat minimal 1.'],
            ]);
        }

        return DB::transaction(function () use ($newThreshold) {
            $capInventory = Inventory::where('item_type', self::ITEM_CAP)
                ->lockForUpdate()
                ->firstOrFail();

            $capInventory->low_stock_threshold = $newThreshold;
            $capInventory->save();

            return $capInventory;
        });
    }
}
