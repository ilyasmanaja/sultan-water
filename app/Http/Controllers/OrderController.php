<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceTier;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * S2-BE-01: Transaksi POS - Isi Ulang Galon (Walk-in / Ambil Sendiri)
     */
    public function storeRefill(StoreOrderRequest $request, InventoryService $inventoryService): JsonResponse
    {
        $validated = $request->validated();
        $userId = auth()->id();

        return DB::transaction(function () use ($validated, $inventoryService, $userId) {
            // 1. Eksekusi lock inventaris pertama: Tutup Galon (BR-INV-02 §4.2)
            $totalRefillQty = collect($validated['items'])->sum('quantity');

            // 2. Tangani Quick Add jika ada customer baru
            $customerId = $validated['customer_id'] ?? null;
            if (!$customerId && !empty($validated['new_customer']['name'])) {
                $newCust = Customer::create([
                    'name'             => $validated['new_customer']['name'],
                    'whatsapp_number'  => $validated['new_customer']['whatsapp_number'],
                    'address'          => $validated['new_customer']['address'] ?? null,
                    'borrowed_gallons' => 0,
                ]);
                $customerId = $newCust->id;
            }

            // 3. Lock record customer jika ada relasi customer
            $customer = null;
            if ($customerId) {
                $customer = Customer::where('id', $customerId)->lockForUpdate()->first();
            }

            // 4. Kalkulasi Server-Side untuk subtotal, snapshot unit_price, total_amount, dan Gp
            $totalAmount = 0;
            $gPinjam = 0;
            $gKembali = 0;
            $orderItemsData = [];

            // Cache price tiers untuk efisiensi
            $priceTierIds = collect($validated['items'])->pluck('price_tier_id')->unique();
            $priceTiers = PriceTier::whereIn('id', $priceTierIds)->get()->keyBy('id');

            foreach ($validated['items'] as $item) {
                $tier = $priceTiers->get($item['price_tier_id']);
                $qty = (int) $item['quantity'];
                $unitPrice = (float) $tier->price;
                $subtotal = $qty * $unitPrice;
                $totalAmount += $subtotal;

                $action = $item['gallon_action'];
                $gallonQty = 0;

                // Server-side enforcement: tukar_seimbang selalu sama dengan quantity (BR-INV-02 §4.6)
                if ($action === 'tukar_seimbang') {
                    $gallonQty = $qty;
                } elseif (in_array($action, ['pinjam', 'kembalikan'])) {
                    $gallonQty = (int) ($item['gallon_qty'] ?? 0);
                    if ($action === 'pinjam') {
                        $gPinjam += $gallonQty;
                    } elseif ($action === 'kembalikan') {
                        $gKembali += $gallonQty;
                    }
                }

                $orderItemsData[] = [
                    'product_id'    => $item['product_id'],
                    'price_tier_id' => $item['price_tier_id'],
                    'quantity'      => $qty,
                    'unit_price'    => $unitPrice,
                    'subtotal'      => $subtotal,
                    'gallon_action' => $action,
                    'gallon_qty'    => $gallonQty,
                ];
            }

            // 5. Buat Header Order (Ambil Sendiri langsung berstatus 'selesai')
            $order = Order::create([
                'customer_id'     => $customerId,
                'order_type'      => $validated['order_type'],
                'delivery_status' => $validated['order_type'] === 'ambil_sendiri' ? 'selesai' : 'pending',
                'payment_method'  => $validated['payment_method'],
                'payment_status'  => 'lunas',
                'total_amount'    => $totalAmount,
                'created_by'      => $userId,
            ]);

            // 6. Simpan detail item pesanan
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // 7. Update Saldo Pinjaman Galon Pelanggan: Gp' = Gp + g_pinjam - g_kembali (BR-INV-02 §3.A)
            if ($customer && ($gPinjam > 0 || $gKembali > 0)) {
                $customer->borrowed_gallons = $customer->borrowed_gallons + $gPinjam - $gKembali;
                $customer->save();
            }

            // 8. Pemotongan stok tutup galon secara transaksional
            $inventoryService->deductCap($totalRefillQty, $order->id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi isi ulang berhasil disimpan.',
                'data'    => [
                    'order_id'        => $order->id,
                    'total_amount'    => $order->total_amount,
                    'delivery_status' => $order->delivery_status,
                ],
            ], 201);
        });
    }

    /**
     * S2-BE-02: Transaksi POS - Galon Baru + Isi (Beli Putus)
     */
    public function storeNewGallon(StoreOrderRequest $request, InventoryService $inventoryService): JsonResponse
    {
        $validated = $request->validated();
        $userId = auth()->id();

        return DB::transaction(function () use ($validated, $inventoryService, $userId) {
            // 1. Tangani Quick Add jika ada customer baru
            $customerId = $validated['customer_id'] ?? null;
            if (!$customerId && !empty($validated['new_customer']['name'])) {
                $newCust = Customer::create([
                    'name'             => $validated['new_customer']['name'],
                    'whatsapp_number'  => $validated['new_customer']['whatsapp_number'],
                    'address'          => $validated['new_customer']['address'] ?? null,
                    'borrowed_gallons' => 0,
                ]);
                $customerId = $newCust->id;
            }

            // 2. Kalkulasi Server-Side untuk Galon Baru
            $totalAmount = 0;
            $totalNewGallonQty = 0;
            $orderItemsData = [];

            $priceTierIds = collect($validated['items'])->pluck('price_tier_id')->unique();
            $priceTiers = PriceTier::whereIn('id', $priceTierIds)->get()->keyBy('id');

            foreach ($validated['items'] as $item) {
                $tier = $priceTiers->get($item['price_tier_id']);
                $qty = (int) $item['quantity'];
                $unitPrice = (float) $tier->price;
                $subtotal = $qty * $unitPrice;
                $totalAmount += $subtotal;
                $totalNewGallonQty += $qty;

                // Produk galon baru terkunci pada opsi tidak_ada (DOC-VAL-POS-02 §3.C)
                $orderItemsData[] = [
                    'product_id'    => $item['product_id'],
                    'price_tier_id' => $item['price_tier_id'],
                    'quantity'      => $qty,
                    'unit_price'    => $unitPrice,
                    'subtotal'      => $subtotal,
                    'gallon_action' => 'tidak_ada',
                    'gallon_qty'    => 0,
                ];
            }

            // 3. Buat Header Order
            $order = Order::create([
                'customer_id'     => $customerId,
                'order_type'      => $validated['order_type'],
                'delivery_status' => $validated['order_type'] === 'ambil_sendiri' ? 'selesai' : 'pending',
                'payment_method'  => $validated['payment_method'],
                'payment_status'  => 'lunas',
                'total_amount'    => $totalAmount,
                'created_by'      => $userId,
            ]);

            // 4. Simpan detail item pesanan
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // 5. Mutasi Inventaris Atomik (Urutan lock: tutup_galon lalu galon_kosong_depot)
            // Memotong tutup galon (-Q_baru) -> 1 baris log
            $inventoryService->deductCap($totalNewGallonQty, $order->id, $userId);

            // Mengurangi Total Galon Dimiliki G_total (-Q_baru) -> 1 baris log
            $inventoryService->deductFleetTotal($totalNewGallonQty, $order->id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi galon baru berhasil disimpan.',
                'data'    => [
                    'order_id'        => $order->id,
                    'total_amount'    => $order->total_amount,
                    'delivery_status' => $order->delivery_status,
                ],
            ], 201);
        });
    }
}