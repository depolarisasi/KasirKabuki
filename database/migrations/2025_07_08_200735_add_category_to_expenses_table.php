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
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('category', [
                'gaji',
                'bahan_baku_makanan_lain',
                'listrik',
                'air',
                'gas',
                'promosi_marketing',
                'pemeliharaan_alat',
            ])->after('description')->comment('Kategori pengeluaran bisnis restoran');
            
            $table->index(['category', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['category', 'date']);
            $table->dropColumn('category');
        });
    }
};
