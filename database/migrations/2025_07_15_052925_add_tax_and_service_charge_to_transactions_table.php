<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add tax and service charge fields to transactions table.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('tax_amount', 15, 2)->default(0.00)->after('total_discount')
                  ->comment('Tax amount calculated for this transaction');
            $table->decimal('tax_rate', 5, 2)->default(10.00)->after('tax_amount')
                  ->comment('Tax rate percentage used for calculation');
            $table->decimal('service_charge_amount', 15, 2)->default(0.00)->after('tax_rate')
                  ->comment('Service charge amount calculated for this transaction');
            $table->decimal('service_charge_rate', 5, 2)->default(5.00)->after('service_charge_amount')
                  ->comment('Service charge rate percentage used for calculation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'tax_amount', 
                'tax_rate', 
                'service_charge_amount', 
                'service_charge_rate'
            ]);
        });
    }
};
