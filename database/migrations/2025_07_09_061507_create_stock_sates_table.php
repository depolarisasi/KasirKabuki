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
        Schema::create('stock_sates', function (Blueprint $table) {
            $table->id();
            
            $table->date('tanggal_stok')
                  ->default(now()->format('Y-m-d'))
                  ->comment('Tanggal stok untuk tracking harian');
            
            $table->foreignId('staf_pengisi')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('User yang mengisi/update stok');
            
            $table->enum('jenis_sate', ['Sate Dada Asin', 'Sate Dada Pedas', 'Sate Kulit', 'Sate Paha'])
                  ->comment('Jenis sate sesuai dengan enum di products');
            
            $table->integer('stok_awal')
                  ->default(0)
                  ->nullable()
                  ->comment('Stok awal hari tersebut');
            
            $table->integer('stok_terjual')
                  ->default(0)
                  ->nullable()
                  ->comment('Stok terjual dari transaksi dan saved orders');
            
            $table->integer('stok_akhir')
                  ->default(0)
                  ->nullable()
                  ->comment('Stok akhir setelah dihitung manual');
            
            $table->integer('selisih')
                  ->default(0)
                  ->nullable()
                  ->comment('Selisih stok (stok_awal - stok_terjual - stok_akhir)');
            
            $table->text('keterangan')
                  ->nullable()
                  ->comment('Catatan tambahan untuk stok');
            
            $table->datetime('tanggalwaktu_pengisian')
                  ->default(now())
                  ->comment('Waktu pengisian atau update data');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint untuk menghindari duplicate entry per tanggal dan jenis sate
            $table->unique(['tanggal_stok', 'jenis_sate'], 'unique_daily_sate_stock');
            
            // Index untuk performance
            $table->index(['tanggal_stok', 'jenis_sate'], 'idx_tanggal_jenis_sate');
            $table->index('staf_pengisi', 'idx_staf_pengisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_sates');
    }
};
