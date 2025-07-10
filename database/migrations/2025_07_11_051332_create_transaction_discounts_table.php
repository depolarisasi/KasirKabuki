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
        Schema::create('transaction_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('discount_id')->nullable()->constrained()->onDelete('set null');
            $table->string('discount_name');
            $table->string('discount_type'); // 'product', 'transaction', 'adhoc'
            $table->decimal('discount_value', 10, 2); // percentage or fixed amount
            $table->string('discount_value_type'); // 'percentage' or 'fixed'
            $table->decimal('discount_amount', 10, 2); // actual discount amount applied
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            
            // Add indexes for performance
            $table->index(['transaction_id', 'discount_type']);
            $table->index(['transaction_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_discounts');
    }
};
