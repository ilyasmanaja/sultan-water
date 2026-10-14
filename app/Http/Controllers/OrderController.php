<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * [S2-BE-01] Transaksi isi ulang galon (walk-in).
     *
     * Urutan kunci (BR-INV-02 §4.2): tutup_galon -> customers.
     * Semua dalam satu DB::transaction(); ValidationException di dalamnya
     * membatalkan seluruh perubahan dan dirender sebagai 422.
     * Controller tidak menyentuh tabel inventories (lewat InventoryService).
     */
    public function storeRefill(StoreOrderRequest $request, InventoryService $inventory): JsonResponse
    {
        $user       = $request->user();
        $lines      = $request->lines();     // sudah dinormalisasi server
        $total      = $request->total();
        $customerId = $request->input('customer_id');

        $order = DB::transaction(function () use ($request, $user, $lines, $total, $customerId, $inventory) {
            // 1. Header order. Walk-in langsung 'selesai' (BR-INV-02 §4.7).
            $order = Order::create([
                'customer_id'     => $customerId,
                'order_type'      => 'ambil_sendiri',
                'delivery_status' => 'selesai',
                'payment_method'  => $request->input('payment_method'),
                'payment_status'  => 'lunas',
                'total_amount'    => $total,
                'created_by'      => $user->id,
            ]);

            // 2. Item (unit_price = snapshot, subtotal & gallon_qty dari server).
            $order->items()->createMany($lines);

            // 3. Potong tutup: Qg = sum(quantity). Isi ulang tidak mengubah G_total.
            $qg = (int) collect($lines)->sum('quantity');
            $inventory->deductCap($qg, $order, $user);

            // 4. Saldo pinjaman Gp (hanya jika ada pinjam/kembalikan).
            $this->applyLoanBalance($customerId, $lines);

            return $order;
        });

        $cash = $request->input('payment_method') === 'tunai'
            ? (float) $request->input('cash_received')
            : null;

        return response()->json([
            'message' => 'Transaksi berhasil disimpan.',
            'data'    => [
                'order_id'     => $order->id,
                'total_amount' => (float) $order->total_amount,
                'change'       => $cash !== null ? $cash - $total : null,
            ],
        ], 201);
    }

    /**
     * Gp' = Gp + g_pinjam - g_kembali  (BR-INV-02 §3.A).
     * Syarat g_kembali <= Gp dicek terhadap Gp SEBELUM transaksi, setelah
     * baris customer dikunci (mencegah race condition, TC-GAL-17).
     */
    private function applyLoanBalance(?int $customerId, array $lines): void
    {
        $loans   = collect($lines);
        $pinjam  = (int) $loans->where('gallon_action', 'pinjam')->sum('gallon_qty');
        $kembali = (int) $loans->where('gallon_action', 'kembalikan')->sum('gallon_qty');

        if ($pinjam === 0 && $kembali === 0) {
            return; // tukar_seimbang / tidak_ada tidak mengubah Gp
        }

        // customer_id dijamin terisi oleh StoreOrderRequest.
        $customer = Customer::query()->lockForUpdate()->findOrFail($customerId);

        if ($kembali > $customer->borrowed_gallons) {
            throw ValidationException::withMessages([
                'items' => sprintf(
                    'Jumlah pengembalian (%d) melebihi galon yang sedang dipinjam pelanggan (%d unit).',
                    $kembali,
                    $customer->borrowed_gallons
                ),
            ]);
        }

        $customer->borrowed_gallons = $customer->borrowed_gallons + $pinjam - $kembali;
        $customer->save();
    }
}