<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for KasirKabuki adaptation.
     * Drop stock_sates table karena cabang Kabuki tidak memerlukan stock management.
     */
    public function up(): void
    {
        // Drop stock_sates table
        Schema::dropIfExists('stock_sates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate stock_sates table structure untuk rollback
        Schema::create('stock_sates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->date('tanggal_stok');
            $table->enum('jenis_sate', [
                'dada_asin', 'dada_manis', 'paha_asin', 'paha_manis',
                'usus', 'ati_ampela', 'kulit', 'kikil', 'tetelan'
            ]);
            $table->integer('stok_awal')->default(0);
            $table->integer('stok_terjual')->default(0);
            $table->string('staf_pengisi')->nullable();
            $table->string('shift')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['product_id', 'tanggal_stok', 'jenis_sate'], 'unique_stock_entry');
            $table->index(['tanggal_stok', 'jenis_sate'], 'idx_date_jenis');
            $table->index('product_id');
        });
    }
}; 