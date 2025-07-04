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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique(); // TRX20250702ABC123
            $table->foreignId('user_id')->constrained(); // kasir yang melayani
            $table->enum('order_type', ['dine_in', 'take_away', 'online']);
            $table->foreignId('partner_id')->nullable()->constrained(); // untuk online orders
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total_discount', 15, 2)->default(0);
            $table->decimal('partner_commission', 15, 2)->default(0); // komisi partner
            $table->decimal('final_total', 15, 2);
            $table->enum('payment_method', ['cash', 'qris']);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->json('discount_details')->nullable(); // detail diskon yang diterapkan
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['transaction_code']);
            $table->index(['user_id', 'created_at']);
            $table->index(['order_type', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
