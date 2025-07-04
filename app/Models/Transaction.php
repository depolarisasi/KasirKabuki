<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'user_id',
        'order_type',
        'partner_id',
        'subtotal',
        'total_discount',
        'partner_commission',
        'final_total',
        'payment_method',
        'status',
        'discount_details',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'partner_commission' => 'decimal:2',
        'final_total' => 'decimal:2',
        'discount_details' => 'array',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with User (Kasir)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Partner (untuk online orders)
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Relationship with TransactionItems
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Scope for specific status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for specific order type
     */
    public function scopeOrderType($query, $orderType)
    {
        return $query->where('order_type', $orderType);
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
     * Scope for today's transactions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get formatted order type for display
     */
    public function getOrderTypeLabelAttribute()
    {
        return match($this->order_type) {
            'dine_in' => 'Makan di Tempat',
            'take_away' => 'Bawa Pulang',
            'online' => 'Online',
            default => 'Unknown'
        };
    }

    /**
     * Get formatted payment method for display
     */
    public function getPaymentMethodLabelAttribute()
    {
        return match($this->payment_method) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            default => 'Unknown'
        };
    }

    /**
     * Get formatted status for display
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'completed' => 'badge-success',
            'cancelled' => 'badge-error',
            default => 'badge-ghost'
        };
    }

    /**
     * Get formatted amounts for display
     */
    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedTotalDiscountAttribute()
    {
        return 'Rp ' . number_format($this->total_discount, 0, ',', '.');
    }

    public function getFormattedPartnerCommissionAttribute()
    {
        return 'Rp ' . number_format($this->partner_commission, 0, ',', '.');
    }

    public function getFormattedFinalTotalAttribute()
    {
        return 'Rp ' . number_format($this->final_total, 0, ',', '.');
    }

    /**
     * Get formatted date for display
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d F Y H:i');
    }

    /**
     * Get short formatted date
     */
    public function getShortDateAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Check if transaction is completed
     */
    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if transaction is online order
     */
    public function getIsOnlineOrderAttribute()
    {
        return $this->order_type === 'online';
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Calculate and set partner commission
     */
    public function calculatePartnerCommission()
    {
        if ($this->partner && $this->order_type === 'online') {
            $this->partner_commission = $this->subtotal * ($this->partner->commission_rate / 100);
        } else {
            $this->partner_commission = 0;
        }
        
        return $this->partner_commission;
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    /**
     * Mark transaction as cancelled
     */
    public function markAsCancelled()
    {
        $this->update([
            'status' => 'cancelled'
        ]);
    }

    /**
     * Get total sales for specific period
     */
    public static function getTotalSales($startDate = null, $endDate = null)
    {
        $query = static::completed();
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        } elseif ($startDate) {
            $query->forDate($startDate);
        } else {
            $query->today();
        }
        
        return $query->sum('final_total');
    }

    /**
     * Get sales summary for dashboard
     */
    public static function getSalesSummary($date = null)
    {
        $date = $date ?: Carbon::today();
        
        $transactions = static::completed()->forDate($date);
        
        return [
            'total_transactions' => $transactions->count(),
            'total_sales' => $transactions->sum('final_total'),
            'total_discount' => $transactions->sum('total_discount'),
            'total_commission' => $transactions->sum('partner_commission'),
            'by_order_type' => $transactions->get()->groupBy('order_type')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total' => $items->sum('final_total')
                ];
            }),
            'by_payment_method' => $transactions->get()->groupBy('payment_method')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total' => $items->sum('final_total')
                ];
            })
        ];
    }
}
