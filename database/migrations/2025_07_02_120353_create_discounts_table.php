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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['product', 'transaction'])->comment('Product discount or transaction discount');
            $table->enum('value_type', ['percentage', 'fixed'])->comment('Percentage or fixed amount');
            $table->decimal('value', 10, 2)->comment('Discount value (percentage or fixed amount)');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade')->comment('If null, applies to all products');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
