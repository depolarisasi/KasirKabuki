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
        // Backup table first before dropping (optional)
        // DB::statement('CREATE TABLE stock_logs_backup AS SELECT * FROM stock_logs');
        
        Schema::dropIfExists('stock_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['initial_stock', 'sale', 'return', 'adjustment', 'cancellation_return']);
            $table->integer('quantity_change');
            $table->integer('stock_after_change');
            $table->string('notes')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['product_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('transaction_id');
        });
    }
};
