<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StockLog extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for stock in movements
     */
    public function scopeStockIn($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for stock out movements
     */
    public function scopeStockOut($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope for stock adjustments
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', 'adjustment');
    }

    /**
     * Scope for specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    /**
     * Scope for date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for today's records
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * Get type label for display
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'in' => 'Stok Masuk',
            'out' => 'Stok Keluar',
            'adjustment' => 'Penyesuaian',
            default => 'Unknown'
        };
    }

    /**
     * Get type badge class for display
     */
    public function getTypeBadgeAttribute()
    {
        return match($this->type) {
            'in' => 'badge-success',
            'out' => 'badge-error',
            'adjustment' => 'badge-warning',
            default => 'badge-ghost'
        };
    }

    /**
     * Get formatted quantity with sign
     */
    public function getFormattedQuantityAttribute()
    {
        $sign = $this->type === 'out' ? '-' : '+';
        return $sign . number_format($this->quantity);
    }

    /**
     * Calculate total stock for a product up to a specific date
     */
    public static function calculateStockBalance($productId, $date = null)
    {
        $query = static::forProduct($productId);
        
        if ($date) {
            $query->whereDate('created_at', '<=', $date);
        }
        
        $stockIn = $query->clone()->stockIn()->sum('quantity');
        $stockOut = $query->clone()->stockOut()->sum('quantity');
        $adjustments = $query->clone()->adjustments()->sum('quantity');
        
        return $stockIn - $stockOut + $adjustments;
    }

    /**
     * Get stock movements for a specific date and product
     */
    public static function getDailyMovements($productId, $date)
    {
        return static::forProduct($productId)
            ->forDate($date)
            ->with(['user', 'product'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Log stock movement
     */
    public static function logMovement($productId, $userId, $type, $quantity, $notes = null)
    {
        return static::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => abs($quantity), // Always store positive quantity
            'notes' => $notes,
        ]);
    }
}
