<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ProductComponent table untuk define package/bundle product relationships.
     * Contoh: Paket Nasi Ayam (package) terdiri dari Nasi (component) + Ayam (component)
     */
    public function up(): void
    {
        Schema::create('product_components', function (Blueprint $table) {
            $table->id();
            
            // Package product relationship
            $table->foreignId('package_product_id')
                ->constrained('products')
                ->onDelete('cascade')
                ->comment('Product yang merupakan package/bundle');
            
            // Component product relationship  
            $table->foreignId('component_product_id')
                ->constrained('products')
                ->onDelete('cascade')
                ->comment('Product yang merupakan component dari package');
                
            // Quantity of component needed untuk 1 unit package
            $table->decimal('quantity_per_package', 8, 2)
                ->default(1.00)
                ->comment('Jumlah component yang dibutuhkan untuk 1 unit package');
                
            // Optional: Unit of measurement untuk component
            $table->string('unit', 20)->nullable()->comment('Unit pengukuran (pcs, gram, ml, dll)');
            
            // Metadata
            $table->boolean('is_active')->default(true)->comment('Status aktif component relationship');
            $table->text('notes')->nullable()->comment('Catatan tambahan tentang component');
            
            $table->timestamps();
            
            // Indexes untuk performance
            $table->index(['package_product_id', 'is_active']);
            $table->index(['component_product_id']);
            
            // Unique constraint untuk prevent duplicate component relationships
            $table->unique(['package_product_id', 'component_product_id'], 'unique_package_component');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_components');
    }
};
