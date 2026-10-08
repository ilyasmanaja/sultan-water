<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\PriceTier;
use App\Models\Inventory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Akun Pengguna
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Kasir Depot',
            'username' => 'kasir',
            'password' => Hash::make('kasir123'),
            'role' => 'kasir',
        ]);

        // 2. Data Master Produk
        $isiUlang = Product::create([
            'name' => 'Air Isi Ulang',
            'type' => 'isi_ulang',
            'is_active' => true,
        ]);

        $galonBaru = Product::create([
            'name' => 'Galon Baru + Isi',
            'type' => 'galon_baru',
            'is_active' => true,
        ]);

        // 3. Master 5-Tier Harga
        PriceTier::create([
            'code' => 'sosial',
            'name' => 'Harga Sosial',
            'price' => 4000.00,
            'applies_to' => 'isi_ulang',
            'is_active' => true,
        ]);

        PriceTier::create([
            'code' => 'letak_kedai',
            'name' => 'Harga Letak Kedai',
            'price' => 5000.00,
            'applies_to' => 'isi_ulang',
            'is_active' => true,
        ]);

        PriceTier::create([
            'code' => 'antar_dekat',
            'name' => 'Harga Antar Dekat',
            'price' => 6000.00,
            'applies_to' => 'isi_ulang',
            'is_active' => true,
        ]);

        PriceTier::create([
            'code' => 'antar_jauh',
            'name' => 'Harga Antar Jauh',
            'price' => 7000.00,
            'applies_to' => 'isi_ulang',
            'is_active' => true,
        ]);

        PriceTier::create([
            'code' => 'galon_baru_isi',
            'name' => 'Harga Galon Baru + Isi',
            'price' => 40000.00,
            'applies_to' => 'galon_baru',
            'is_active' => true,
        ]);

        // 4. Baris Tetap Inventaris (Sesuai Data Dictionary v1.3)
        Inventory::create([
            'item_type' => 'tutup_galon',
            'quantity' => 1000, // Stok awal tutup
            'low_stock_threshold' => 600,
        ]);

        Inventory::create([
            'item_type' => 'galon_siap_jual',
            'quantity' => 0, // Tidak dipakai operasional
            'low_stock_threshold' => null,
        ]);

        Inventory::create([
            'item_type' => 'galon_kosong_depot',
            'quantity' => 50, // Penampung G_total (Total Galon Dimiliki awal)
            'low_stock_threshold' => null,
        ]);
    }
}