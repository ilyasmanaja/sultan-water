<?php

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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->enum('order_type', ['ambil_sendiri', 'pesan_antar']);
            $table->enum('delivery_status', ['pending', 'diantar', 'selesai', 'batal'])->default('pending');
            $table->enum('payment_method', ['tunai', 'qris']);
            $table->enum('payment_status', ['lunas', 'belum_lunas'])->default('lunas');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
