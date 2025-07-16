<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add tax and service charge configuration to store settings.
     */
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(10.00)->after('receipt_logo_path')
                  ->comment('Tax rate percentage (PPN), default 10%');
            $table->decimal('service_charge_rate', 5, 2)->default(5.00)->after('tax_rate')
                  ->comment('Service charge percentage, default 5%');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'service_charge_rate']);
        });
    }
}; 