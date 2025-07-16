<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for KasirKabuki adaptation.
     * Drop sate-related columns dari products table karena tidak diperlukan.
     */
    public function up(): void
    {
        // Check if table exists and columns exist before dropping
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // Drop sate-related columns hanya jika exist
                if (Schema::hasColumn('products', 'jenis_sate')) {
                    $table->dropColumn('jenis_sate');
                }
                if (Schema::hasColumn('products', 'quantity_effect')) {
                    $table->dropColumn('quantity_effect');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if table exists before adding columns back
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // Recreate sate-related columns untuk rollback jika belum ada
                if (!Schema::hasColumn('products', 'jenis_sate')) {
                    $table->enum('jenis_sate', [
                        'dada_asin', 'dada_manis', 'paha_asin', 'paha_manis',
                        'kulit_asin', 'kulit_manis'
                    ])->nullable()->after('price');
                }
                if (!Schema::hasColumn('products', 'quantity_effect')) {
                    $table->decimal('quantity_effect', 8, 2)->nullable()->after('jenis_sate')
                        ->comment('Efek kuantitas untuk stock (e.g., 1 porsi = 10 tusuk)');
                }
            });
        }
    }
}; 