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
     */
    public function hasEnoughComponentStock($packageQuantity)
    {
        $requiredQuantity = $this->calculateTotalComponentQuantity($packageQuantity);
        $currentStock = $this->componentProduct->getCurrentStock();
        
        return $currentStock >= $requiredQuantity;
    }

    /**
     * Get component stock status untuk package
     */
    public function getComponentStockStatus($packageQuantity = 1)
    {
        $requiredQuantity = $this->calculateTotalComponentQuantity($packageQuantity);
        $currentStock = $this->componentProduct->getCurrentStock();
        
        return [
            'component_name' => $this->componentProduct->name,
            'required_quantity' => $requiredQuantity,
            'current_stock' => $currentStock,
            'is_sufficient' => $currentStock >= $requiredQuantity,
            'shortage' => max(0, $requiredQuantity - $currentStock),
            'unit' => $this->unit
        ];
    }
}
