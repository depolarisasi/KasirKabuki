<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductComponent extends Model
{
    protected $fillable = [
        'package_product_id',
        'component_product_id', 
        'quantity_per_package',
        'unit',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'quantity_per_package' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship dengan package product
     */
    public function packageProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'package_product_id');
    }

    /**
     * Relationship dengan component product
     */
    public function componentProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'component_product_id');
    }

    /**
     * Scope untuk active components
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk specific package
     */
    public function scopeForPackage($query, $packageProductId)
    {
        return $query->where('package_product_id', $packageProductId);
    }

    /**
     * Get formatted quantity dengan unit
     */
    public function getFormattedQuantityAttribute()
    {
        $quantity = number_format($this->quantity_per_package, 2);
        return $this->unit ? "{$quantity} {$this->unit}" : $quantity;
    }

    /**
     * Calculate total component quantity needed untuk multiple packages
     */
    public function calculateTotalComponentQuantity($packageQuantity)
    {
        return $this->quantity_per_package * $packageQuantity;
    }

    /**
     * Check apakah component product punya stock cukup untuk package quantity
     * KasirKabuki tidak menggunakan stock management - selalu return true
     */
    public function hasEnoughComponentStock($packageQuantity)
    {
        // KasirKabuki tidak menggunakan stock management
        // Semua component dianggap selalu tersedia
        return true;
    }

    /**
     * Get component stock status untuk package
     * KasirKabuki tidak menggunakan stock management - return simplified status
     */
    public function getComponentStockStatus($packageQuantity = 1)
    {
        $requiredQuantity = $this->calculateTotalComponentQuantity($packageQuantity);
        
        return [
            'component_name' => $this->componentProduct->name,
            'required_quantity' => $requiredQuantity,
            'current_stock' => null, // Tidak ada stock tracking
            'is_sufficient' => true, // Selalu cukup
            'shortage' => 0, // Tidak ada shortage
            'unit' => $this->unit,
            'stock_management_enabled' => false
        ];
    }
}
