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
        // Add 'aplikasi' to payment_method enum if not already exists
        // Check current enum values first
        $result = DB::select("SHOW COLUMNS FROM transactions LIKE 'payment_method'");
        
        if (!empty($result)) {
            $enumString = $result[0]->Type;
            
            // Check if 'aplikasi' already exists in enum
            if (strpos($enumString, 'aplikasi') === false) {
                // Update the enum to include 'aplikasi'
                DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'qris', 'aplikasi') NOT NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values (only if no 'aplikasi' payment methods exist)
        $count = DB::table('transactions')->where('payment_method', 'aplikasi')->count();
        
        if ($count === 0) {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'qris') NOT NULL");
        } else {
            throw new Exception("Cannot rollback: There are existing transactions with payment_method = 'aplikasi'");
        }
    }
};
