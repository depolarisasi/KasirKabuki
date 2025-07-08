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
        'quantity_change',
        'stock_after_change',
        'reference_transaction_id',
        'notes',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'stock_after_change' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Stock movement types
     */
    const TYPE_IN = 'in';
    const TYPE_OUT = 'out';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_SALE = 'sale';
    const TYPE_CANCELLATION_RETURN = 'cancellation_return';
    const TYPE_INITIAL_STOCK = 'initial_stock';

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
     * Relationship with Transaction (when applicable)
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reference_transaction_id');
    }

    /**
     * Scope for stock in movements
     */
    public function scopeStockIn($query)
    {
        return $query->whereIn('type', [self::TYPE_IN, self::TYPE_INITIAL_STOCK, self::TYPE_CANCELLATION_RETURN]);
    }

    /**
     * Scope for stock out movements
     */
    public function scopeStockOut($query)
    {
        return $query->whereIn('type', [self::TYPE_OUT, self::TYPE_SALE]);
    }

    /**
     * Scope for stock adjustments
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', self::TYPE_ADJUSTMENT);
    }

    /**
     * Scope for sales movements
     */
    public function scopeSales($query)
    {
        return $query->where('type', self::TYPE_SALE);
    }

    /**
     * Scope for cancellation returns
     */
    public function scopeCancellationReturns($query)
    {
        return $query->where('type', self::TYPE_CANCELLATION_RETURN);
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
     * Scope for transaction-related movements
     */
    public function scopeWithTransaction($query)
    {
        return $query->whereNotNull('reference_transaction_id');
    }

    /**
     * Get type label for display
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            self::TYPE_IN => 'Stok Masuk',
            self::TYPE_OUT => 'Stok Keluar',
            self::TYPE_ADJUSTMENT => 'Penyesuaian',
            self::TYPE_SALE => 'Penjualan',
            self::TYPE_CANCELLATION_RETURN => 'Return Pembatalan',
            self::TYPE_INITIAL_STOCK => 'Stok Awal',
            default => 'Unknown'
        };
    }

    /**
     * Get type badge class for display
     */
    public function getTypeBadgeAttribute()
    {
        return match($this->type) {
            self::TYPE_IN, self::TYPE_INITIAL_STOCK, self::TYPE_CANCELLATION_RETURN => 'badge-success',
            self::TYPE_OUT, self::TYPE_SALE => 'badge-error',
            self::TYPE_ADJUSTMENT => 'badge-warning',
            default => 'badge-ghost'
        };
    }

    /**
     * Get formatted quantity change dengan sign
     */
    public function getFormattedQuantityChangeAttribute()
    {
        $isIncrease = in_array($this->type, [self::TYPE_IN, self::TYPE_INITIAL_STOCK, self::TYPE_CANCELLATION_RETURN]);
        $sign = $isIncrease ? '+' : '-';
        return $sign . number_format(abs($this->quantity_change));
    }

    /**
     * Check if this is a stock increase movement
     */
    public function isStockIncrease()
    {
        return in_array($this->type, [self::TYPE_IN, self::TYPE_INITIAL_STOCK, self::TYPE_CANCELLATION_RETURN]);
    }

    /**
     * Check if this is a stock decrease movement
     */
    public function isStockDecrease()
    {
        return in_array($this->type, [self::TYPE_OUT, self::TYPE_SALE]);
    }

    /**
     * Calculate current stock untuk product - SINGLE SOURCE OF TRUTH
     */
    public static function getCurrentStock($productId)
    {
        // Get the latest stock_after_change value
        $latestLog = static::forProduct($productId)
            ->whereNotNull('stock_after_change')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestLog) {
            return $latestLog->stock_after_change;
        }

        // Fallback: Calculate dari all movements jika tidak ada stock_after_change
        $stockIn = static::forProduct($productId)->stockIn()->sum('quantity_change');
        $stockOut = static::forProduct($productId)->stockOut()->sum('quantity_change');
        
        return $stockIn - $stockOut;
    }

    /**
     * Get stock movements untuk specific date dan product
     */
    public static function getDailyMovements($productId, $date)
    {
        return static::forProduct($productId)
            ->forDate($date)
            ->with(['user', 'product', 'transaction'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Log stock movement dengan automatic stock_after_change calculation
     */
    public static function logMovement($productId, $userId, $type, $quantityChange, $notes = null, $transactionId = null)
    {
        // Get current stock before this movement
        $currentStock = static::getCurrentStock($productId);
        
        // Calculate stock after this movement
        $isIncrease = in_array($type, [self::TYPE_IN, self::TYPE_INITIAL_STOCK, self::TYPE_CANCELLATION_RETURN]);
        $stockAfter = $isIncrease ? $currentStock + abs($quantityChange) : $currentStock - abs($quantityChange);
        
        return static::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'type' => $type,
            'quantity_change' => abs($quantityChange), // Always store positive
            'stock_after_change' => $stockAfter,
            'reference_transaction_id' => $transactionId,
            'notes' => $notes,
        ]);
    }

    /**
     * Log sale movement untuk transaction
     */
    public static function logSale($productId, $userId, $quantity, $transactionId, $notes = null)
    {
        return static::logMovement(
            $productId,
            $userId,
            self::TYPE_SALE,
            $quantity,
            $notes ?: "Penjualan - Transaction #{$transactionId}",
            $transactionId
        );
    }

    /**
     * Log cancellation return untuk cancelled transaction
     */
    public static function logCancellationReturn($productId, $userId, $quantity, $transactionId, $notes = null)
    {
        return static::logMovement(
            $productId,
            $userId,
            self::TYPE_CANCELLATION_RETURN,
            $quantity,
            $notes ?: "Return pembatalan - Transaction #{$transactionId}",
            $transactionId
        );
    }

    /**
     * Log initial stock
     */
    public static function logInitialStock($productId, $userId, $quantity, $notes = null)
    {
        return static::logMovement(
            $productId,
            $userId,
            self::TYPE_INITIAL_STOCK,
            $quantity,
            $notes ?: "Stok awal"
        );
    }

    /**
     * Log stock adjustment
     */
    public static function logAdjustment($productId, $userId, $quantityChange, $notes = null)
    {
        return static::logMovement(
            $productId,
            $userId,
            self::TYPE_ADJUSTMENT,
            $quantityChange,
            $notes ?: "Penyesuaian stok"
        );
    }

    /**
     * DEPRECATED - Untuk backward compatibility
     * @deprecated Use getCurrentStock() instead
     */
    public static function calculateStockBalance($productId, $date = null)
    {
        if ($date) {
            // For historical stock calculation
            $query = static::forProduct($productId)->whereDate('created_at', '<=', $date);
            
            $stockIn = $query->clone()->stockIn()->sum('quantity_change');
            $stockOut = $query->clone()->stockOut()->sum('quantity_change');
            
            return $stockIn - $stockOut;
        }
        
        return static::getCurrentStock($productId);
    }
}
