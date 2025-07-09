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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('jenis_sate', ['Sate Dada Asin', 'Sate Dada Pedas', 'Sate Kulit', 'Sate Paha'])
                  ->nullable()
                  ->after('photo')
                  ->comment('Jenis sate untuk stock management');
            
            $table->integer('quantity_effect')
                  ->nullable()
                  ->after('jenis_sate')
                  ->comment('Jumlah sate yang dihasilkan dari 1 unit produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['jenis_sate', 'quantity_effect']);
        });
    }
};
