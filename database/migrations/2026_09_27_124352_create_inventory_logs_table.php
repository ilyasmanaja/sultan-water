<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories');
            $table->integer('change_amount');
            $table->integer('current_stock')->default(0); // 👈 1. Ditambahkan
            $table->enum('reason', [
                'transaksi',
                'restock_stok_jual',
                'restock_armada_depot',
                'rollback',     // 👈 2. Ditambahkan
            ]);
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
