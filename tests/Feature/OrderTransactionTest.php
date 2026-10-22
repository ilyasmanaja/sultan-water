<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $kasir;
    protected User $kurir;
    protected Customer $customer;
    protected Product $productRefill;
    protected PriceTier $priceTier;
    protected Inventory $capInventory;
    protected Inventory $depotGallonInventory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kasir = User::create([
            'name' => 'Kasir Test',
            'username' => 'kasir_pos',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
        ]);

        $this->kurir = User::create([
            'name' => 'Kurir Test',
            'username' => 'kurir_delivery',
            'password' => bcrypt('password123'),
            'role' => 'admin', // atau role yang memiliki otorisasi delivery
        ]);

        $this->customer = Customer::create([
            'name' => 'Warung Pak Joko',
            'whatsapp_number' => '081234567899',
            'address' => 'Jl. Kenari No. 12',
            'borrowed_gallons' => 2, // saldo awal pinjam 2 galon
        ]);

        $this->productRefill = Product::create([
            'name' => 'Air Isi Ulang',
            'type' => 'isi_ulang',
            'is_active' => true,
        ]);

        $this->priceTier = PriceTier::create([
            'code' => 'antar_dekat',
            'name' => 'Harga Antar Dekat',
            'price' => 6000.00,
            'applies_to' => 'isi_ulang',
            'is_active' => true,
        ]);

        // Stok awal inventaris
        $this->capInventory = Inventory::create([
            'item_type' => InventoryService::ITEM_CAP,
            'quantity' => 1000,
            'low_stock_threshold' => 600,
        ]);

        $this->depotGallonInventory = Inventory::create([
            'item_type' => 'galon_kosong_depot',
            'quantity' => 50,
            'low_stock_threshold' => 10,
        ]);
    }

    public function test_transaksi_walk_in_langsung_berstatus_lunas_dan_memotong_tutup_galon(): void
    {
        $this->actingAs($this->kasir);

        $payload = [
            'order_type' => 'ambil_sendiri',
            'customer_id' => null,
            'payment_method' => 'tunai',
            'items' => [
                [
                    'product_id' => $this->productRefill->id,
                    'price_tier_id' => $this->priceTier->id,
                    'quantity' => 3,
                    'gallon_action' => 'tukar_seimbang',
                    'gallon_qty' => 3,
                ],
            ],
        ];

        $response = $this->postJson(route('pos.order.refill'), $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('data.payment_status', 'lunas');
        $response->assertJsonPath('data.delivery_status', Order::STATUS_SELESAI);

        // Stok tutup galon berkurang dari 1000 -> 997
        $this->assertEquals(997, $this->capInventory->fresh()->quantity);
    }

    public function test_transaksi_pesan_antar_berstatus_belum_lunas_dan_menambah_pinjaman_galon(): void
    {
        $this->actingAs($this->kasir);

        $payload = [
            'order_type' => 'pesan_antar',
            'customer_id' => $this->customer->id,
            'payment_method' => 'tunai',
            'items' => [
                [
                    'product_id' => $this->productRefill->id,
                    'price_tier_id' => $this->priceTier->id,
                    'quantity' => 2,
                    'gallon_action' => 'pinjam',
                    'gallon_qty' => 2,
                ],
            ],
        ];

        $response = $this->postJson(route('pos.order.refill'), $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('data.payment_status', 'belum_lunas');
        $response->assertJsonPath('data.delivery_status', Order::STATUS_PENDING);

        // Saldo pinjam bertambah: 2 + 2 = 4 galon
        $this->assertEquals(4, $this->customer->fresh()->borrowed_gallons);
        // Stok tutup galon berkurang dari 1000 -> 998
        $this->assertEquals(998, $this->capInventory->fresh()->quantity);
    }

    public function test_konfirmasi_pengantaran_kurir_mengubah_status_menjadi_lunas(): void
    {
        $this->actingAs($this->kurir);

        $order = Order::create([
            'customer_id' => $this->customer->id,
            'order_type' => 'pesan_antar',
            'delivery_status' => Order::STATUS_DIANTAR,
            'payment_method' => 'tunai',
            'payment_status' => 'belum_lunas',
            'total_amount' => 12000.00,
            'created_by' => $this->kasir->id,
        ]);

        $response = $this->postJson(route('delivery.complete', $order));

        $response->assertOk();
        $response->assertJsonPath('data.delivery_status', Order::STATUS_SELESAI);
        $response->assertJsonPath('data.payment_status', 'lunas');

        $this->assertEquals('lunas', $order->fresh()->payment_status);
    }

    public function test_pembatalan_pesanan_oleh_kurir_melakukan_rollback_stok_dan_saldo_pinjaman(): void
    {
        $this->actingAs($this->kurir);

        // Simulasi pesanan yang meminjam 2 galon
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'order_type' => 'pesan_antar',
            'delivery_status' => Order::STATUS_PENDING,
            'payment_method' => 'tunai',
            'payment_status' => 'belum_lunas',
            'total_amount' => 12000.00,
            'created_by' => $this->kasir->id,
        ]);

        $order->items()->create([
            'product_id' => $this->productRefill->id,
            'price_tier_id' => $this->priceTier->id,
            'quantity' => 2,
            'unit_price' => 6000.00,
            'subtotal' => 12000.00,
            'gallon_action' => 'pinjam',
            'gallon_qty' => 2,
        ]);

        // Simulasikan stok terpotong & galon terpinjam
        $this->capInventory->update(['quantity' => 998]);
        $this->customer->update(['borrowed_gallons' => 4]);

        // Eksekusi pembatalan order
        $response = $this->postJson(route('delivery.cancel', $order));

        $response->assertOk();
        $this->assertEquals(Order::STATUS_BATAL, $order->fresh()->delivery_status);

        // Rollback inventaris: tutup galon balik ke 1000
        $this->assertEquals(1000, $this->capInventory->fresh()->quantity);
        // Rollback pinjaman pelanggan: saldo pinjam balik ke 2
        $this->assertEquals(2, $this->customer->fresh()->borrowed_gallons);
    }
}