<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'product_price',
        'quantity',
        'subtotal',
        'discount_amount',
        'total',
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Relationship with Transaction
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relationship with Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted amounts for display
     */
    public function getFormattedProductPriceAttribute()
    {
        return 'Rp ' . number_format($this->product_price, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedDiscountAmountAttribute()
    {
        return 'Rp ' . number_format($this->discount_amount, 0, ',', '.');
    }

    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Calculate subtotal (quantity * product_price)
     */
    public function calculateSubtotal()
    {
        $this->subtotal = $this->quantity * $this->product_price;
        return $this->subtotal;
    }

    /**
     * Calculate total (subtotal - discount_amount)
     */
    public function calculateTotal()
    {
        $this->total = $this->subtotal - $this->discount_amount;
        return $this->total;
    }

    /**
     * Set discount amount and recalculate total
     */
    public function applyDiscount($discountAmount)
    {
        $this->discount_amount = $discountAmount;
        $this->calculateTotal();
        return $this;
    }
}
