<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PriceTier;
use App\Services\CustomerLoanService;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * S2-BE-01 & S3-BE-01/03: Transaksi POS - Isi Ulang Galon & Retur Murni
     */
    public function storeRefill(
        StoreOrderRequest $request,
        InventoryService $inventoryService,
        CustomerLoanService $customerLoanService
    ): JsonResponse {
        $validated = $request->validated();
        $userId = auth()->id();

        return DB::transaction(function () use ($validated, $inventoryService, $customerLoanService, $userId) {
            // Hitung total tutup galon yang dibutuhkan (Qg)
            $totalRefillQty = collect($validated['items'])->sum('quantity');

            // 1. Eksekusi lock inventaris pertama: Tutup Galon (BR-INV-02 §4.2)
            // Jika retur murni ($totalRefillQty = 0), deductCap tidak memotong dan tidak membuat log audit
            $inventoryService->deductCap($totalRefillQty, 0, $userId);

            // 2. Tangani Quick Add jika ada pelanggan baru
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

            // 3. Kalkulasi Server-Side untuk items, subtotal, dan mutasi galon pinjaman
            $totalAmount = 0;
            $gPinjam = 0;
            $gKembali = 0;
            $orderItemsData = [];

            $priceTierIds = collect($validated['items'])->pluck('price_tier_id')->filter()->unique();
            $priceTiers = PriceTier::whereIn('id', $priceTierIds)->get()->keyBy('id');

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity'];
                $action = $item['gallon_action'];
                $tier = !empty($item['price_tier_id']) ? $priceTiers->get($item['price_tier_id']) : null;

                // Retur galon murni: unit_price = 0, subtotal = 0, price_tier_id = null
                $unitPrice = $qty > 0 && $tier ? (float) $tier->price : 0.00;
                $subtotal = $qty * $unitPrice;
                $totalAmount += $subtotal;

                $gallonQty = 0;
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
                    'price_tier_id' => $qty > 0 ? $item['price_tier_id'] : null,
                    'quantity'      => $qty,
                    'unit_price'    => $unitPrice,
                    'subtotal'      => $subtotal,
                    'gallon_action' => $action,
                    'gallon_qty'    => $gallonQty,
                ];
            }

            // 4. Buat Header Order
            $isWalkIn = $validated['order_type'] === 'ambil_sendiri';
            $order = Order::create([
                'customer_id'     => $customerId,
                'order_type'      => $validated['order_type'],
                'delivery_status' => $isWalkIn ? Order::STATUS_SELESAI : Order::STATUS_PENDING,
                'payment_method'  => $validated['payment_method'],
                'payment_status'  => $isWalkIn ? 'lunas' : 'belum_lunas',
                'total_amount'    => $totalAmount,
                'created_by'      => $userId,
            ]);

            // 5. Simpan detail item pesanan
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // 6. Mutasi Pinjaman Galon Pelanggan via CustomerLoanService (Locking urutan ke-3)
            if ($customerId && ($gPinjam > 0 || $gKembali > 0)) {
                $customer = Customer::findOrFail($customerId);
                $customerLoanService->apply($customer, $gPinjam, $gKembali);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi isi ulang berhasil disimpan.',
                'data'    => [
                    'order_id'        => $order->id,
                    'total_amount'    => $order->total_amount,
                    'delivery_status' => $order->delivery_status,
                    'payment_status'  => $order->payment_status,
                ],
            ], 201);
        });
    }

    /**
     * S2-BE-02 & S3-BE-01: Transaksi POS - Galon Baru + Isi (Beli Putus)
     */
    public function storeNewGallon(
        StoreOrderRequest $request,
        InventoryService $inventoryService
    ): JsonResponse {
        $validated = $request->validated();
        $userId = auth()->id();

        return DB::transaction(function () use ($validated, $inventoryService, $userId) {
            $totalNewGallonQty = collect($validated['items'])->sum('quantity');

            // 1. Eksekusi lock inventaris: Tutup Galon lalu Galon Kosong Depot (G_total)
            $inventoryService->deductCap($totalNewGallonQty, 0, $userId);
            $inventoryService->deductFleetTotal($totalNewGallonQty, 0, $userId);

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

            // 3. Kalkulasi Server-Side untuk Galon Baru
            $totalAmount = 0;
            $orderItemsData = [];

            $priceTierIds = collect($validated['items'])->pluck('price_tier_id')->filter()->unique();
            $priceTiers = PriceTier::whereIn('id', $priceTierIds)->get()->keyBy('id');

            foreach ($validated['items'] as $item) {
                $tier = $priceTiers->get($item['price_tier_id']);
                $qty = (int) $item['quantity'];
                $unitPrice = (float) $tier->price;
                $subtotal = $qty * $unitPrice;
                $totalAmount += $subtotal;

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

            // 4. Buat Header Order
            $isWalkIn = $validated['order_type'] === 'ambil_sendiri';
            $order = Order::create([
                'customer_id'     => $customerId,
                'order_type'      => $validated['order_type'],
                'delivery_status' => $isWalkIn ? Order::STATUS_SELESAI : Order::STATUS_PENDING,
                'payment_method'  => $validated['payment_method'],
                'payment_status'  => $isWalkIn ? 'lunas' : 'belum_lunas',
                'total_amount'    => $totalAmount,
                'created_by'      => $userId,
            ]);

            // 5. Simpan detail item pesanan
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi galon baru berhasil disimpan.',
                'data'    => [
                    'order_id'        => $order->id,
                    'total_amount'    => $order->total_amount,
                    'delivery_status' => $order->delivery_status,
                    'payment_status'  => $order->payment_status,
                ],
            ], 201);
        });
    }
}