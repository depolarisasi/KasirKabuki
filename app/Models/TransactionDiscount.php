<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDiscount extends Model
{
    protected $fillable = [
        'transaction_id',
        'discount_id',
        'discount_name',
        'discount_type',
        'discount_value',
        'discount_value_type',
        'discount_amount',
        'product_id',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    /**
     * Relationship with Transaction
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relationship with Discount (untuk pre-defined discounts)
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Relationship with Product (untuk product-specific discounts)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted discount amount for display
     */
    public function getFormattedDiscountAmountAttribute()
    {
        return 'Rp ' . number_format($this->discount_amount, 0, ',', '.');
    }

    /**
     * Get formatted discount value for display
     */
    public function getFormattedDiscountValueAttribute()
    {
        if ($this->discount_value_type === 'percentage') {
            return $this->discount_value . '%';
        } else {
            return 'Rp ' . number_format($this->discount_value, 0, ',', '.');
        }
    }

    /**
     * Check if this is an adhoc discount
     */
    public function getIsAdhocAttribute()
    {
        return $this->discount_type === 'adhoc';
    }

    /**
     * Check if this is a product-specific discount
     */
    public function getIsProductDiscountAttribute()
    {
        return $this->discount_type === 'product';
    }
}
