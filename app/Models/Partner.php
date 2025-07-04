<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partner extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'commission_rate',
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'commission_rate' => 'decimal:2',
    ];

    /**
     * Scope for search functionality
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }

    /**
     * Get formatted commission rate as percentage
     */
    public function getFormattedCommissionRateAttribute()
    {
        return number_format($this->commission_rate, 1) . '%';
    }

    /**
     * Get commission rate as decimal for calculations
     */
    public function getCommissionDecimalAttribute()
    {
        return $this->commission_rate / 100;
    }
} 