<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'admin_id',
        'field_changed',
        'old_value',
        'new_value',
        'reason',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    /**
     * Get the transaction that was audited.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the admin who made the change.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Scope to get recent audits.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get audits for a specific transaction.
     */
    public function scopeForTransaction($query, $transactionId)
    {
        return $query->where('transaction_id', $transactionId)
                    ->orderBy('changed_at', 'desc');
    }

    /**
     * Get formatted change summary.
     */
    public function getChangeSummaryAttribute(): string
    {
        return "{$this->field_changed}: {$this->old_value} → {$this->new_value}";
    }

    /**
     * Get formatted timestamp.
     */
    public function getFormattedChangedAtAttribute(): string
    {
        return $this->changed_at->format('d M Y H:i');
    }
}
