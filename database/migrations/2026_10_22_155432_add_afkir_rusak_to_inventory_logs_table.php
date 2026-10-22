<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE inventory_logs MODIFY COLUMN reason ENUM(
            'transaksi',
            'rollback',
            'restock_stok_jual',
            'restock_armada_depot',
            'afkir_rusak'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE inventory_logs MODIFY COLUMN reason ENUM(
            'transaksi',
            'rollback',
            'restock_stok_jual',
            'restock_armada_depot'
        ) NOT NULL");
    }
};
