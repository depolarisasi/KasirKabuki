<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter the enum column to add 'aplikasi'
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'qris', 'aplikasi')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'qris')");
    }
};
