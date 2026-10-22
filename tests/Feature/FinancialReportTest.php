<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kasir;
    protected Customer $customer;
    protected Product $productRefill;
    protected PriceTier $priceTier;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Pengguna langsung via User::create (tanpa factory kolom email)
        $this->admin = User::create([
            'name' => 'Administrator Depot',
            'username' => 'admin_test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->kasir = User::create([
            'name' => 'Kasir Depot',
            'username' => 'kasir_test',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
        ]);

        // 2. Setup Data Master
        $this->customer = Customer::create([
            'name' => 'Pak Budi',
            'whatsapp_number' => '081234567890',
            'address' => 'Jl. Garuda No. 5',
            'borrowed_gallons' => 0,
        ]);

        $this->productRefill = Product::create([
            'name' => 'Air Isi Ulang',
            'type' => 'isi_ulang',
            'is_active' => true,
        ]);

        $this->priceTier = PriceTier::create([
            'code' => 'letak_kedai',
            'name' => 'Harga Letak Kedai',
            'price' => 5000.00,
            'applies_to' => 'isi_ulang',
            'is_active' => true,
        ]);
    }

    /**
     * Helper untuk membuat pesanan uji.
     */
    /**
     * Helper untuk membuat pesanan uji.
     */
    protected function createOrder(
        string $orderType,
        string $deliveryStatus,
        string $paymentStatus,
        float $totalAmount,
        Carbon $createdAt
    ): Order {
        $order = new Order([
            'customer_id' => $this->customer->id,
            'order_type' => $orderType,
            'delivery_status' => $deliveryStatus,
            'payment_method' => 'tunai',
            'payment_status' => $paymentStatus,
            'total_amount' => $totalAmount,
            'created_by' => $this->admin->id,
        ]);

        // Gunakan forceFill agar Eloquent tidak menimpa created_at dengan timestamp saat ini
        $order->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        $orderItem = new OrderItem([
            'order_id' => $order->id,
            'product_id' => $this->productRefill->id,
            'price_tier_id' => $this->priceTier->id,
            'quantity' => (int) ($totalAmount / 5000),
            'unit_price' => 5000.00,
            'subtotal' => $totalAmount,
            'gallon_action' => 'tidak_ada',
            'gallon_qty' => 0,
        ]);

        $orderItem->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $order;
    }

    /**
     * Helper untuk membuat catatan beban uji.
     */
    protected function createExpense(
        string $title,
        string $category,
        float $amount,
        string $expenseDate
    ): Expense {
        return Expense::create([
            'title' => $title,
            'category' => $category,
            'amount' => $amount,
            'expense_date' => $expenseDate,
            'description' => 'Beban uji',
            'created_by' => $this->admin->id,
        ]);
    }

    // =========================================================================
    // 1. PENGUJIAN OTORISASI & PROTEKSI ROLE (RBAC)
    // =========================================================================

    public function test_kasir_dilarang_mengakses_halaman_dan_endpoint_laporan_finansial(): void
    {
        $this->actingAs($this->kasir);

        // Halaman Index Laporan
        $this->get(route('admin.reports.index'))
            ->assertForbidden();

        // Endpoint Summary JSON
        $this->getJson(route('admin.reports.summary'))
            ->assertForbidden();

        // Halaman Cetak Laporan A4
        $this->get(route('admin.reports.print'))
            ->assertForbidden();

        // Ekspor PDF & Excel
        $this->get(route('admin.reports.export.pdf'))
            ->assertForbidden();

        $this->get(route('admin.reports.export.excel'))
            ->assertForbidden();
    }

    public function test_admin_dapat_mengakses_seluruh_antarmuka_dan_endpoint_laporan(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('admin.reports.index'))
            ->assertOk()
            ->assertViewIs('reports.index')
            ->assertViewHasAll(['summary', 'orders', 'startDate', 'endDate']);

        $this->getJson(route('admin.reports.summary'))
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'revenue',
                    'expenses',
                    'breakdown_expenses',
                    'net_profit',
                    'is_profit',
                ],
            ]);

        $this->get(route('admin.reports.print'))
            ->assertOk()
            ->assertViewIs('reports.print');
    }

    // =========================================================================
    // 2. ISOLASI OMZET (R) DARI ORDER BATAL & COD BELUM LUNAS
    // =========================================================================

    public function test_omzet_hanya_menghitung_pesanan_lunas_dan_mengabaikan_order_batal_atau_belum_lunas(): void
    {
        $testDate = Carbon::create(2026, 10, 15, 10, 0, 0);

        // 1. Pesanan Lunas Walk-in (Wajib Masuk Omzet: Rp 50.000)
        $this->createOrder('ambil_sendiri', Order::STATUS_SELESAI, 'lunas', 50000.00, $testDate);

        // 2. Pesanan Lunas Pesan Antar (Wajib Masuk Omzet: Rp 30.000)
        $this->createOrder('pesan_antar', Order::STATUS_SELESAI, 'lunas', 30000.00, $testDate);

        // 3. Pesanan COD Pesan Antar Belum Selesai (Belum Lunas: Rp 40.000 -> Wajib Dikecualikan)
        $this->createOrder('pesan_antar', Order::STATUS_PENDING, 'belum_lunas', 40000.00, $testDate);

        // 4. Pesanan Dibatalkan / Rollback (Status Batal: Rp 70.000 -> Wajib Dikecualikan)
        $this->createOrder('pesan_antar', Order::STATUS_BATAL, 'belum_lunas', 70000.00, $testDate);

        // 5. Pesanan Dibatalkan tetapi memiliki flag lunas abnormal (Wajib Tetap Dikecualikan)
        $this->createOrder('pesan_antar', Order::STATUS_BATAL, 'lunas', 25000.00, $testDate);

        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $revenue = $reportService->getRevenue('2026-10-01', '2026-10-31');

        // Total omzet yang diharapkan: 50.000 + 30.000 = Rp 80.000
        $this->assertEquals(80000.00, $revenue);
    }

    public function test_pesanan_di_luar_rentang_tanggal_tidak_masuk_ke_dalam_omzet(): void
    {
        // Pesanan bulan September 2026
        $this->createOrder('ambil_sendiri', Order::STATUS_SELESAI, 'lunas', 100000.00, Carbon::create(2026, 9, 30, 23, 59, 59));

        // Pesanan bulan Oktober 2026
        $this->createOrder('ambil_sendiri', Order::STATUS_SELESAI, 'lunas', 50000.00, Carbon::create(2026, 10, 1, 0, 0, 1));

        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $revenueOctober = $reportService->getRevenue('2026-10-01', '2026-10-31');

        $this->assertEquals(50000.00, $revenueOctober);
    }

    // =========================================================================
    // 3. AGREGASI & BREAKDOWN BEBAN OPERASIONAL (E) 3 KATEGORI
    // =========================================================================

    public function test_beban_operasional_teragregasi_dan_terbagi_tepat_pada_3_kategori(): void
    {
        $date = '2026-10-10';

        // 1. Kemasan & Produksi
        $this->createExpense('Beli Tutup Galon Segel', 'kemasan_produksi', 150000.00, $date);
        $this->createExpense('Beli Tisu Galon', 'kemasan_produksi', 50000.00, $date);

        // 2. Operasional & Logistik (Termasuk Penggantian Filter / UV)
        $this->createExpense('Bensin Motor Kurir', 'operasional_logistik', 25000.00, $date);
        $this->createExpense('Penggantian Filter Sedimen & Karbon', 'operasional_logistik', 120000.00, $date);

        // 3. Beban Tetap
        $this->createExpense('Air Baku Tangki Curah', 'beban_tetap', 300000.00, $date);
        $this->createExpense('Token Listrik Depot', 'beban_tetap', 100000.00, $date);

        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $expensesData = $reportService->getExpenses('2026-10-01', '2026-10-31');

        // Total: 200.000 + 145.000 + 400.000 = Rp 745.000
        $this->assertEquals(745000.00, $expensesData['total']);

        // Verifikasi Breakdown 3 Kategori
        $this->assertEquals(200000.00, $expensesData['breakdown']['kemasan_produksi']);
        $this->assertEquals(145000.00, $expensesData['breakdown']['operasional_logistik']);
        $this->assertEquals(400000.00, $expensesData['breakdown']['beban_tetap']);
    }

    // =========================================================================
    // 4. PERHITUNGAN MATEMATIS LABA BERSIH (NP = R - E) & KONDISI RUGI
    // =========================================================================

    public function test_perhitungan_laba_bersih_saat_kondisi_surplus_laba(): void
    {
        $date = Carbon::create(2026, 10, 20);

        // Omzet (R) = Rp 500.000
        $this->createOrder('ambil_sendiri', Order::STATUS_SELESAI, 'lunas', 500000.00, $date);

        // Beban (E) = Rp 200.000
        $this->createExpense('Beban Operasional', 'operasional_logistik', 200000.00, $date->toDateString());

        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $result = $reportService->getNetProfit('2026-10-01', '2026-10-31');

        $this->assertEquals(500000.00, $result['revenue']);
        $this->assertEquals(200000.00, $result['expenses']);
        $this->assertEquals(300000.00, $result['net_profit']); // NP = 300.000
        $this->assertTrue($result['is_profit']);
    }

    public function test_perhitungan_laba_bersih_saat_kondisi_defisit_rugi_nilai_negatif(): void
    {
        $date = Carbon::create(2026, 10, 20);

        // Omzet (R) = Rp 100.000
        $this->createOrder('ambil_sendiri', Order::STATUS_SELESAI, 'lunas', 100000.00, $date);

        // Beban (E) = Rp 350.000 (Pengeluaran besar penggantian filter/air baku)
        $this->createExpense('Servis Pompa & Filter UV', 'operasional_logistik', 350000.00, $date->toDateString());

        /** @var ReportService $reportService */
        $reportService = app(ReportService::class);
        $result = $reportService->getNetProfit('2026-10-01', '2026-10-31');

        $this->assertEquals(100000.00, $result['revenue']);
        $this->assertEquals(350000.00, $result['expenses']);
        $this->assertEquals(-250000.00, $result['net_profit']); // NP = -250.000 (tidak boleh di-clamp ke 0)
        $this->assertFalse($result['is_profit']);
    }

    // =========================================================================
    // 5. PENGUJIAN EKSPOR LAPORAN (PDF & SPREADSHEET/CSV)
    // =========================================================================

    public function test_admin_dapat_mengunduh_laporan_pdf_resmi(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.reports.export.pdf', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('Laporan-Keuangan-Sultan-Water_2026-10-01_sd_2026-10-31.pdf', $response->headers->get('content-disposition'));
    }

    public function test_admin_dapat_mengunduh_rekapitulasi_spreadsheet_csv(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.reports.export.excel', [
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename="Laporan-Keuangan-Sultan-Water_2026-10-01_sd_2026-10-31.csv"', $response->headers->get('content-disposition'));
    }
}
