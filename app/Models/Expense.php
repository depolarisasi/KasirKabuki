<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'description',
        'category',
        'date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Category labels mapping
     */
    public static function getCategoryLabels()
    {
        return [
            'gaji' => 'Gaji',
            'bahan_baku_sate' => 'Bahan Baku Sate',
            'bahan_baku_makanan_lain' => 'Bahan Baku Makanan Lain',
            'listrik' => 'Listrik',
            'air' => 'Air',
            'gas' => 'Gas',
            'promosi_marketing' => 'Promosi / Marketing',
            'pemeliharaan_alat' => 'Pemeliharaan Alat'
        ];
    }

    /**
     * Relationship with User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for search functionality
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('description', 'like', '%' . $search . '%');
    }

    /**
     * Scope for specific category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope for date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific month and year
     */
    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
                    ->whereMonth('date', $month);
    }

    /**
     * Scope for today's expenses
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Scope for this month's expenses
     */
    public function scopeThisMonth($query)
    {
        return $query->whereYear('date', Carbon::now()->year)
                    ->whereMonth('date', Carbon::now()->month);
    }

    /**
     * Get formatted amount for display
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get formatted date for display
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('d F Y');
    }

    /**
     * Get short formatted date
     */
    public function getShortDateAttribute()
    {
        return $this->date->format('d/m/Y');
    }

    /**
     * Get month name for grouping
     */
    public function getMonthNameAttribute()
    {
        return $this->date->format('F Y');
    }

    /**
     * Check if expense is from today
     */
    public function getIsTodayAttribute()
    {
        return $this->date->isToday();
    }

    /**
     * Get total expenses for a specific date range
     */
    public static function getTotalForPeriod($startDate, $endDate)
    {
        return static::betweenDates($startDate, $endDate)->sum('amount');
    }

    /**
     * Get total expenses for today
     */
    public static function getTotalToday()
    {
        return static::today()->sum('amount');
    }

    /**
     * Get total expenses for this month
     */
    public static function getTotalThisMonth()
    {
        return static::thisMonth()->sum('amount');
    }

    /**
     * Get expenses grouped by date
     */
    public static function getGroupedByDate($startDate = null, $endDate = null)
    {
        $query = static::query()->with('user');
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        } else {
            $query->thisMonth();
        }
        
        return $query->orderBy('date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->groupBy(function ($expense) {
                        return $expense->date->format('Y-m-d');
                    });
    }

    /**
     * Get monthly summary
     */
    public static function getMonthlySummary($year = null, $month = null)
    {
        $year = $year ?: Carbon::now()->year;
        $month = $month ?: Carbon::now()->month;
        
        $expenses = static::forMonth($year, $month)->get();
        
        return [
            'total_amount' => $expenses->sum('amount'),
            'total_count' => $expenses->count(),
            'daily_breakdown' => $expenses->groupBy(function ($expense) {
                return $expense->date->format('Y-m-d');
            })->map(function ($dailyExpenses) {
                return [
                    'count' => $dailyExpenses->count(),
                    'total' => $dailyExpenses->sum('amount'),
                    'formatted_total' => 'Rp ' . number_format($dailyExpenses->sum('amount'), 0, ',', '.')
                ];
            })
        ];
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute()
    {
        $labels = self::getCategoryLabels();
        return $labels[$this->category] ?? $this->category;
    }

    /**
     * Get category badge class based on category type
     */
    public function getCategoryBadgeAttribute()
    {
        $badges = [
            'gaji' => 'badge-primary',
            'bahan_baku_sate' => 'badge-secondary',
            'bahan_baku_makanan_lain' => 'badge-accent',
            'listrik' => 'badge-warning',
            'air' => 'badge-info',
            'gas' => 'badge-error',
            'promosi_marketing' => 'badge-success',
            'pemeliharaan_alat' => 'badge-neutral'
        ];
        
        return $badges[$this->category] ?? 'badge-ghost';
    }
}
