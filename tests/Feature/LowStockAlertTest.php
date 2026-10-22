<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LowStockAlertTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $kasir;
    protected Inventory $tutupInventory;
    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();

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

        // Buat baris tutup_galon dengan threshold default 600
        $this->tutupInventory = Inventory::create([
            'item_type' => InventoryService::ITEM_CAP,
            'quantity' => 1000,
            'low_stock_threshold' => 600,
        ]);

        $this->inventoryService = app(InventoryService::class);
    }

    public function test_stok_di_atas_ambang_batas_tidak_memicu_peringatan(): void
    {
        // Kondisi: S = 601 (S > 600)
        $this->tutupInventory->update(['quantity' => 601]);

        $status = $this->inventoryService->getCapStockStatus();

        $this->assertEquals(601, $status['current_stock']);
        $this->assertEquals(600, $status['low_stock_threshold']);
        $this->assertFalse($status['is_low_stock']);
        $this->assertStringContainsString('aman', strtolower($status['message']));
    }

    public function test_stok_tepat_di_ambang_batas_memicu_peringatan_kritis(): void
    {
        // Kondisi: S = 600 (S == S_min)
        $this->tutupInventory->update(['quantity' => 600]);

        $status = $this->inventoryService->getCapStockStatus();

        $this->assertEquals(600, $status['current_stock']);
        $this->assertTrue($status['is_low_stock']);
        $this->assertStringContainsString('peringatan', strtolower($status['message']));
    }

    public function test_stok_di_bawah_ambang_batas_memicu_peringatan_kritis(): void
    {
        // Kondisi: S = 599 (S < S_min)
        $this->tutupInventory->update(['quantity' => 599]);

        $status = $this->inventoryService->getCapStockStatus();

        $this->assertEquals(599, $status['current_stock']);
        $this->assertTrue($status['is_low_stock']);
    }

    public function test_alert_otomatis_padam_setelah_admin_melakukan_restock(): void
    {
        // 1. Awalnya stok menipis (590 unit)
        $this->tutupInventory->update(['quantity' => 590]);
        $statusAwal = $this->inventoryService->getCapStockStatus();
        $this->assertTrue($statusAwal['is_low_stock']);

        // 2. Admin melakukan restock 100 unit -> stok menjadi 690
        $this->inventoryService->restockCap(100, $this->admin->id);

        // 3. Status alert wajib otomatis mati
        $statusAkhir = $this->inventoryService->getCapStockStatus();
        $this->assertEquals(690, $statusAkhir['current_stock']);
        $this->assertFalse($statusAkhir['is_low_stock']);
    }

    public function test_perubahan_threshold_menyesuaikan_kondisi_pemicu_alert(): void
    {
        // Stok depot 550. Pada threshold 600, ini berstatus alert.
        $this->tutupInventory->update(['quantity' => 550, 'low_stock_threshold' => 600]);
        $this->assertTrue($this->inventoryService->getCapStockStatus()['is_low_stock']);

        // Admin mengubah threshold menjadi 500
        $this->inventoryService->updateThreshold(500);

        // Dengan threshold baru (500), stok 550 sekarang menjadi aman
        $status = $this->inventoryService->getCapStockStatus();
        $this->assertEquals(500, $status['low_stock_threshold']);
        $this->assertEquals(550, $status['current_stock']);
        $this->assertFalse($status['is_low_stock']);
    }

    public function test_pembaruan_threshold_menolak_angka_kurang_dari_satu(): void
    {
        $this->expectException(ValidationException::class);

        // Nilai 0 dilarang
        $this->inventoryService->updateThreshold(0);
    }

    public function test_kasir_dilarang_mengubah_threshold_via_endpoint(): void
    {
        $this->actingAs($this->kasir);

        $this->put(route('inventory.threshold.update'), [
            'low_stock_threshold' => 500,
        ])->assertForbidden();
    }
}