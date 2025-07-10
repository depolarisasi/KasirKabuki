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
        Schema::table('transactions', function (Blueprint $table) {
            // Add transaction_date column (nullable first for backfill)
            $table->timestamp('transaction_date')->nullable()->after('notes');
        });

        // Backfill existing transactions: set transaction_date = created_at for all existing records
        DB::statement('UPDATE transactions SET transaction_date = created_at WHERE transaction_date IS NULL');

        // Make transaction_date NOT NULL after backfill
        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('transaction_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_date');
        });
    }
};
