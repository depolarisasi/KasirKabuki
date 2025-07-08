<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type', // 'product' or 'transaction'
        'value_type', // 'percentage' or 'fixed'
        'value',
        'product_id', // null for transaction discounts
        'order_type', // 'dine_in', 'take_away', 'online', or null for all
        'is_active',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship with Product (for product-specific discounts)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope for search functionality
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }

    /**
     * Scope for active discounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for product discounts
     */
    public function scopeProductDiscounts($query)
    {
        return $query->where('type', 'product');
    }

    /**
     * Scope for transaction discounts
     */
    public function scopeTransactionDiscounts($query)
    {
        return $query->where('type', 'transaction');
    }

    /**
     * Scope for order type filtering
     */
    public function scopeForOrderType($query, $orderType)
    {
        return $query->where(function ($subQuery) use ($orderType) {
            $subQuery->where('order_type', $orderType)
                     ->orWhereNull('order_type'); // Include discounts that apply to all order types
        });
    }

    /**
     * Get formatted discount value for display
     */
    public function getFormattedValueAttribute()
    {
        if ($this->value_type === 'percentage') {
            return number_format($this->value, 1) . '%';
        }
        
        return 'Rp ' . number_format($this->value, 0, ',', '.');
    }

    /**
     * Get discount type label
     */
    public function getTypeLabelAttribute()
    {
        return $this->type === 'product' ? 'Diskon Produk' : 'Diskon Transaksi';
    }

    /**
     * Get value type label
     */
    public function getValueTypeLabelAttribute()
    {
        return $this->value_type === 'percentage' ? 'Persentase' : 'Nominal';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'badge-success' : 'badge-error';
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    /**
     * Calculate discount amount for given price
     */
    public function calculateDiscount($price)
    {
        if ($this->value_type === 'percentage') {
            return $price * ($this->value / 100);
        }
        
        return min($this->value, $price); // Don't discount more than the price
    }

    /**
     * Get order type label
     */
    public function getOrderTypeLabelAttribute()
    {
        $orderTypeLabels = [
            'dine_in' => 'Makan di Tempat',
            'take_away' => 'Bawa Pulang', 
            'online' => 'Online',
        ];
        
        return $this->order_type ? $orderTypeLabels[$this->order_type] : 'Semua Jenis';
    }

    /**
     * Check if discount applies to given order type
     */
    public function appliesTo($orderType)
    {
        return is_null($this->order_type) || $this->order_type === $orderType;
    }
}
