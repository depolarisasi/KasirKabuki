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
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->string('product_name'); // snapshot nama produk saat transaksi
            $table->decimal('product_price', 15, 2); // snapshot harga produk saat transaksi
            $table->integer('quantity');
            $table->decimal('subtotal', 15, 2); // quantity * product_price
            $table->decimal('discount_amount', 15, 2)->default(0); // diskon untuk item ini
            $table->decimal('total', 15, 2); // subtotal - discount_amount
            $table->timestamps();

            $table->index(['transaction_id']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
