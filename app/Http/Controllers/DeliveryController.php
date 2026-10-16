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
                'data'    => [
                    'order_id'        => $lockedOrder->id,
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
    public function confirmComplete(Order $order): JsonResponse
    {
        return DB::transaction(function () use ($order) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            // Eksekusi transisi status ke 'selesai' (terminal)
            $lockedOrder->transitionTo(Order::STATUS_SELESAI);

            // Pelunasan otomatis untuk skema COD
            $lockedOrder->payment_status = 'lunas';
            $lockedOrder->save();

            return response()->json([
                'success' => true,
                'message' => 'Pengantaran pesanan telah selesai dan pembayaran telah dicatat lunas.',
                'data'    => [
                    'order_id'        => $lockedOrder->id,
                    'delivery_status' => $lockedOrder->delivery_status,
                    'payment_status'  => $lockedOrder->payment_status,
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
                'data'    => [
                    'order_id'        => $lockedOrder->id,
                    'delivery_status' => $lockedOrder->delivery_status,
                    'payment_status'  => $lockedOrder->payment_status,
                ],
            ]);
        });
    }
}