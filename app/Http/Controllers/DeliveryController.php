<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CustomerLoanService;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryController extends Controller
{
    /**
     * S3-BE-02: Mulai Pengantaran (Kurir)
     * POST /delivery/{order}/start
     * Transisi: pending -> diantar
     */
    public function start(Order $order): JsonResponse
    {
        return DB::transaction(function () use ($order) {
            // Lock order untuk mencegah update bersamaan
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            $lockedOrder->transitionTo(Order::STATUS_DIANTAR);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil diubah ke status Diantar.',
                'data' => [
                    'order_id' => $lockedOrder->id,
                    'delivery_status' => $lockedOrder->delivery_status,
                ],
            ]);
        });
    }

    /**
     * S3-BE-02: Konfirmasi Antar Selesai & Pelunasan COD (Kurir)
     * POST /delivery/{order}/complete
     * Transisi: diantar -> selesai (payment_status: lunas)
     */
    public function confirmComplete(
        \Illuminate\Http\Request $request,
        Order $order,
        InventoryService $inventoryService
    ): JsonResponse {
        $userId = auth()->id() ?? 1;

        return DB::transaction(function () use ($request, $order, $inventoryService, $userId) {
            // 1. Lock order beserta item dan produknya
            $lockedOrder = Order::with(['items.product', 'customer'])
                ->where('id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Validasi transisi status
            if (!$lockedOrder->canTransitionTo(Order::STATUS_SELESAI)) {
                throw ValidationException::withMessages([
                    'delivery_status' => ["Pesanan tidak dapat diselesaikan dari status {$lockedOrder->delivery_status}."],
                ]);
            }

            // Validasi input dari kurir
            $validated = $request->validate([
                'actual_quantity' => 'nullable|integer|min:1',
                'gallon_action' => 'nullable|in:tukar_seimbang,pinjam,kembalikan,tidak_ada',
                'gallon_qty' => 'nullable|integer|min:0',
            ]);

            if (in_array($validated['gallon_action'] ?? '', ['pinjam', 'kembalikan'], true) && !$lockedOrder->customer_id) {
                throw ValidationException::withMessages([
                    'gallon_action' => ['Opsi pinjam atau kembali galon hanya bisa dilakukan untuk pelanggan terdaftar.'],
                ]);
            }

            // Ambil item utama (biasanya pesanan antar hanya memiliki 1 item galon)
            $mainItem = $lockedOrder->items->first();

            if ($mainItem && isset($validated['actual_quantity'])) {
                $oldQty = (int) $mainItem->quantity;
                $newQty = (int) $validated['actual_quantity'];
                $deltaQty = $newQty - $oldQty;

                // A. Penyesuaian Stok Tutup Galon berdasarkan selisih (delta)
                if ($deltaQty > 0) {
                    // Jika kurir membawa/mengisi lebih banyak galon, potong stok tutup tambahan
                    $inventoryService->deductCap($deltaQty, $lockedOrder->id, $userId);
                } elseif ($deltaQty < 0) {
                    // Jika pelanggan membeli lebih sedikit dari estimasi awal kasir, kembalikan stok tutup
                    $capInventory = \App\Models\Inventory::where('item_type', InventoryService::ITEM_CAP)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $refundQty = abs($deltaQty);
                    $capInventory->quantity += $refundQty;
                    $capInventory->save();

                    \App\Models\InventoryLog::create([
                        'inventory_id' => $capInventory->id,
                        'change_amount' => $refundQty,
                        'current_stock' => $capInventory->quantity,
                        'reason' => 'penyesuaian_pengantaran',
                        'order_id' => $lockedOrder->id,
                        'created_by' => $userId,
                    ]);
                }

                // B. Penyesuaian Saldo Pinjaman Galon Pelanggan (Gp)
                if ($lockedOrder->customer_id) {
                    $customer = \App\Models\Customer::where('id', $lockedOrder->customer_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    // 1) Netralkan efek lama dari pesanan kasir
                    if ($mainItem->gallon_action === 'pinjam') {
                        $customer->borrowed_gallons -= (int) $mainItem->gallon_qty;
                    } elseif ($mainItem->gallon_action === 'kembalikan') {
                        $customer->borrowed_gallons += (int) $mainItem->gallon_qty;
                    }

                    // 2) Terapkan aksi baru yang dipilih kurir di lapangan
                    $newAction = $validated['gallon_action'] ?? $mainItem->gallon_action;
                    $newGallonQty = isset($validated['gallon_qty']) ? (int) $validated['gallon_qty'] : $newQty;

                    if ($newAction === 'pinjam') {
                        $customer->borrowed_gallons += $newGallonQty;
                    } elseif ($newAction === 'kembalikan') {
                        if ($newGallonQty > $customer->borrowed_gallons) {
                            throw ValidationException::withMessages([
                                'gallon_qty' => ["Jumlah galon kembali ({$newGallonQty}) melebihi saldo pinjaman pelanggan ({$customer->borrowed_gallons} galon)."],
                            ]);
                        }
                        $customer->borrowed_gallons -= $newGallonQty;
                    }

                    $customer->save();

                    // Update data di item
                    $mainItem->gallon_action = $newAction;
                    $mainItem->gallon_qty = ($newAction === 'tidak_ada') ? 0 : $newGallonQty;
                }

                // C. Update kuantitas dan subtotal item
                $mainItem->quantity = $newQty;
                $mainItem->subtotal = $newQty * $mainItem->unit_price;
                $mainItem->save();

                // D. Hitung ulang total_amount pesanan
                $lockedOrder->total_amount = $lockedOrder->items()->sum('subtotal');
            }

            // Eksekusi transisi status ke 'selesai' dan pelunasan COD
            $lockedOrder->transitionTo(Order::STATUS_SELESAI);
            $lockedOrder->payment_status = 'lunas';
            $lockedOrder->save();

            return response()->json([
                'success' => true,
                'message' => 'Pengantaran selesai, kuantitas disesuaikan, dan pembayaran dicatat lunas.',
                'data' => [
                    'order_id' => $lockedOrder->id,
                    'delivery_status' => $lockedOrder->delivery_status,
                    'payment_status' => $lockedOrder->payment_status,
                    'total_amount' => $lockedOrder->total_amount,
                ],
            ]);
        });
    }

    /**
     * S3-BE-02: Pembatalan Pesanan dengan Rollback Atomik Anti-Deadlock
     * POST /delivery/{order}/cancel
     * Transisi: pending|diantar -> batal
     */
    public function cancel(
        Order $order,
        InventoryService $inventoryService,
        CustomerLoanService $customerLoanService
    ): JsonResponse {
        $userId = auth()->id();

        return DB::transaction(function () use ($order, $inventoryService, $customerLoanService, $userId) {
            // 1. Lock baris Order terlebih dahulu (Anti-pembatalan ganda)
            $lockedOrder = Order::with(['items.product'])->where('id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Proteksi: Transaksi walk-in tidak dapat dibatalkan
            if ($lockedOrder->order_type === 'ambil_sendiri') {
                throw ValidationException::withMessages([
                    'order' => ['Order walk-in (ambil sendiri) bersifat final dan tidak dapat dibatalkan.'],
                ]);
            }

            // Proteksi: Order yang sudah terminal (selesai / batal) tidak dapat diubah
            if (in_array($lockedOrder->delivery_status, [Order::STATUS_SELESAI, Order::STATUS_BATAL], true)) {
                throw ValidationException::withMessages([
                    'order' => ["Order dengan status {$lockedOrder->delivery_status} tidak dapat dibatalkan."],
                ]);
            }

            // Validasi transisi status
            if (!$lockedOrder->canTransitionTo(Order::STATUS_BATAL)) {
                throw ValidationException::withMessages([
                    'delivery_status' => ["Transisi status tidak valid (dari {$lockedOrder->delivery_status} ke batal)."],
                ]);
            }

            // 2. Lock & Rollback Inventaris (Tutup Galon lalu Galon Kosong Depot)
            $inventoryService->rollbackOrder($lockedOrder, $userId);

            // 3. Lock & Rollback Saldo Pinjaman Galon Pelanggan (Gp)
            // Akan melempar ValidationException 422 jika pembalikan menghasilkan saldo negatif
            $customerLoanService->reverseOrder($lockedOrder);

            // 4. Update status order menjadi batal (payment_status tetap belum_lunas)
            $lockedOrder->delivery_status = Order::STATUS_BATAL;
            $lockedOrder->save();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan dan seluruh stok serta pinjaman telah di-rollback.',
                'data' => [
                    'order_id' => $lockedOrder->id,
                    'delivery_status' => $lockedOrder->delivery_status,
                    'payment_status' => $lockedOrder->payment_status,
                ],
            ]);
        });
    }
}