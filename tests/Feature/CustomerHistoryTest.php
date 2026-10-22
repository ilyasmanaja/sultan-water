<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;
    protected Product $productRefill;
    protected PriceTier $priceTier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin_test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->customer = Customer::create([
            'name' => 'Ibu Siti',
            'whatsapp_number' => '081298765432',
            'address' => 'Jl. Merak No. 10',
            'borrowed_gallons' => 0,
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
    }

    protected function createCustomerOrder(
        string $deliveryStatus,
        int $quantity,
        float $unitPrice,
        Carbon $createdAt,
        string $gallonAction = 'tidak_ada',
        ?int $priceTierId = null
    ): Order {
        $subtotal = $quantity * $unitPrice;

        $order = new Order([
            'customer_id' => $this->customer->id,
            'order_type' => 'pesan_antar',
            'delivery_status' => $deliveryStatus,
            'payment_method' => 'tunai',
            'payment_status' => $deliveryStatus === Order::STATUS_SELESAI ? 'lunas' : 'belum_lunas',
            'total_amount' => $subtotal,
            'created_by' => $this->admin->id,
        ]);

        $order->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        $orderItem = new OrderItem([
            'order_id' => $order->id,
            'product_id' => $this->productRefill->id,
            'price_tier_id' => $priceTierId ?? $this->priceTier->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'gallon_action' => $gallonAction,
            'gallon_qty' => $gallonAction !== 'tidak_ada' ? $quantity : 0,
        ]);

        $orderItem->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $order;
    }

    public function test_agregasi_bulanan_hanya_menghitung_pesanan_yang_berstatus_selesai(): void
    {
        $this->actingAs($this->admin);

        $orderDate = Carbon::create(2026, 8, 15, 14, 0, 0);

        // 1. Pesanan selesai: 5 galon @ 6.000 = Rp 30.000 (Wajib Masuk)
        $this->createCustomerOrder(Order::STATUS_SELESAI, 5, 6000.00, $orderDate);

        // 2. Pesanan selesai: 3 galon @ 6.000 = Rp 18.000 (Wajib Masuk)
        $this->createCustomerOrder(Order::STATUS_SELESAI, 3, 6000.00, $orderDate);

        // 3. Pesanan dibatalkan: 4 galon (Wajib Dikecualikan)
        $this->createCustomerOrder(Order::STATUS_BATAL, 4, 6000.00, $orderDate);

        // 4. Pesanan masih pending/diantar: 2 galon (Wajib Dikecualikan)
        $this->createCustomerOrder(Order::STATUS_PENDING, 2, 6000.00, $orderDate);

        $response = $this->getJson(route('customers.monthly-purchases', [
            'customer' => $this->customer->id,
            'month' => 8,
            'year' => 2026,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_gallons' => 8,       // 5 + 3
                'total_spent' => 48000.00,  // 30.000 + 18.000
                'orders_count' => 2,
            ],
        ]);
    }

    public function test_transaksi_retur_murni_tidak_menambah_total_konsumsi_galon(): void
    {
        $this->actingAs($this->admin);

        $date = Carbon::create(2026, 8, 20);

        // 1. Pesanan isi ulang normal: 2 galon @ 6.000 = Rp 12.000
        $this->createCustomerOrder(Order::STATUS_SELESAI, 2, 6000.00, $date);

        // 2. Pesanan retur murni: quantity 0, unit_price 0, price_tier null
        $this->createCustomerOrder(
            Order::STATUS_SELESAI,
            0,
            0.00,
            $date,
            'kembalikan',
            null
        );

        $response = $this->getJson(route('customers.monthly-purchases', [
            'customer' => $this->customer->id,
            'month' => 8,
            'year' => 2026,
        ]));

        $response->assertOk();
        // Total galon tetap 2, bukan bertambah dari retur
        $response->assertJsonPath('data.total_gallons', 2);
        $this->assertEquals(12000, $response->json('data.total_spent'));
    }

    public function test_bulan_tanpa_transaksi_merespons_dengan_nilai_nol_secara_anggun(): void
    {
        $this->actingAs($this->admin);

        // Query pada bulan Januari 2026 (tanpa transaksi sama sekali)
        $response = $this->getJson(route('customers.monthly-purchases', [
            'customer' => $this->customer->id,
            'month' => 1,
            'year' => 2026,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'period' => [
                    'month' => 1,
                    'year' => 2026,
                ],
                'total_gallons' => 0,
                'total_spent' => 0,
                'orders_count' => 0,
                'orders' => [],
            ],
        ]);
    }
}
