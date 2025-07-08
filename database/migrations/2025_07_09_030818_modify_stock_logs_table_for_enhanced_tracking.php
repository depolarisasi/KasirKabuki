<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Enhance StockLog table untuk comprehensive stock tracking:
     * 1. Rename quantity to quantity_change
     * 2. Add reference_transaction_id
     * 3. Add stock_after_change 
     * 4. Update type enum dengan new values
     */
    public function up(): void
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            // Add new columns first
            $table->foreignId('reference_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->onDelete('set null')
                ->comment('Reference to transaction that caused this stock movement');
                
            $table->integer('stock_after_change')
                ->nullable()
                ->comment('Stock quantity after this movement was applied');
        });

        // Rename column (requires separate statement)
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->renameColumn('quantity', 'quantity_change');
        });

        // Update the enum type dengan new values
        DB::statement("ALTER TABLE stock_logs MODIFY COLUMN type ENUM('in', 'out', 'adjustment', 'sale', 'cancellation_return', 'initial_stock') NOT NULL COMMENT 'Type of stock movement - enhanced'");
        
        // Add indexes untuk performance
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->index(['reference_transaction_id']);
            $table->index(['product_id', 'type']);
            $table->index(['created_at', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new indexes
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropIndex(['reference_transaction_id']);
            $table->dropIndex(['product_id', 'type']);
            $table->dropIndex(['created_at', 'product_id']);
        });
        
        // Revert enum type
        DB::statement("ALTER TABLE stock_logs MODIFY COLUMN type ENUM('in', 'out', 'adjustment') NOT NULL COMMENT 'Type of stock movement'");
        
        // Rename column back
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->renameColumn('quantity_change', 'quantity');
        });
        
        // Drop new columns
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropForeign(['reference_transaction_id']);
            $table->dropColumn(['reference_transaction_id', 'stock_after_change']);
        });
    }
};
