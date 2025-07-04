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
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('restrict')->comment('Product reference');
            $table->foreignId('user_id')->constrained()->onDelete('restrict')->comment('User who logged the stock movement');
            $table->enum('type', ['in', 'out', 'adjustment'])->comment('Type of stock movement');
            $table->integer('quantity')->comment('Quantity moved (positive for in, negative for out)');
            $table->text('notes')->nullable()->comment('Additional notes about stock movement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};
