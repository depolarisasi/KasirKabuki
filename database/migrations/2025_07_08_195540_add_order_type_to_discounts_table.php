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
        Schema::table('discounts', function (Blueprint $table) {
            $table->enum('order_type', ['dine_in', 'take_away', 'online'])
                  ->nullable()
                  ->after('product_id')
                  ->comment('Order type for discount applicability. NULL means applies to all order types');
                  
            $table->index(['order_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropIndex(['order_type', 'is_active']);
            $table->dropColumn('order_type');
        });
    }
};
