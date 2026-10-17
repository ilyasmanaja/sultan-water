php artisan make:migration make_price_tier_id_nullable_on_order_items_table<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Drop foreign key lama terlebih dahulu agar alter column tidak terkendala di MySQL
            $table->dropForeign(['price_tier_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            // Jadikan price_tier_id nullable untuk mengakomodasi retur galon murni (quantity = 0)
            $table->foreignId('price_tier_id')
                ->nullable()
                ->change()
                ->constrained('price_tiers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['price_tier_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('price_tier_id')
                ->nullable(false)
                ->change()
                ->constrained('price_tiers');
        });
    }
};