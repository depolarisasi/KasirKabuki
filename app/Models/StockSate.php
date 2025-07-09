<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class StockSate extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'tanggal_stok',
        'staf_pengisi',
        'jenis_sate',
        'stok_awal',
        'stok_terjual',
        'stok_akhir',
        'selisih',
        'keterangan',
        'tanggalwaktu_pengisian',
    ];

    protected $dates = [
        'tanggal_stok',
        'tanggalwaktu_pengisian',
        'deleted_at'
    ];

    protected $casts = [
        'tanggal_stok' => 'date',
        'tanggalwaktu_pengisian' => 'datetime',
        'stok_awal' => 'integer',
        'stok_terjual' => 'integer', 
        'stok_akhir' => 'integer',
        'selisih' => 'integer',
    ];

    /**
     * Get the staff/user who filled the stock
     */
    public function staf(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staf_pengisi');
    }

    /**
     * Enum values untuk jenis sate
     */
    public static function getJenisSateOptions(): array
    {
        return [
            'Sate Dada Asin',
            'Sate Dada Pedas', 
            'Sate Kulit',
            'Sate Paha'
        ];
    }

    /**
     * Calculate selisih berdasarkan stok awal, terjual, dan akhir
     */
    public function calculateSelisih(): int
    {
        return ($this->stok_awal ?? 0) - ($this->stok_terjual ?? 0) - ($this->stok_akhir ?? 0);
    }

    /**
     * Update selisih automatically
     */
    public function updateSelisih(): void
    {
        $this->selisih = $this->calculateSelisih();
        $this->save();
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('tanggal_stok', $date);
    }

    /**
     * Scope untuk filter berdasarkan jenis sate
     */
    public function scopeByJenisSate($query, $jenis)
    {
        return $query->where('jenis_sate', $jenis);
    }

    /**
     * Get stock data untuk specific date dan jenis sate
     */
    public static function getStockForDateAndJenis($date, $jenis)
    {
        return static::byDate($date)->byJenisSate($jenis)->first();
    }

    /**
     * Create or get stock entry untuk specific date dan jenis sate
     */
    public static function createOrGetStock($date, $jenis)
    {
        return static::firstOrCreate([
            'tanggal_stok' => $date,
            'jenis_sate' => $jenis,
        ], [
            'staf_pengisi' => 1, // Default admin
            'stok_awal' => 0,
            'stok_terjual' => 0,
            'stok_akhir' => 0,
            'selisih' => 0,
            'tanggalwaktu_pengisian' => now(),
        ]);
    }

    /**
     * Update staf pengisi jika belum diset atau masih default admin
     */
    public function updateStafPengisi($userId): void
    {
        if ($this->staf_pengisi === 1 || $this->staf_pengisi === null) {
            $this->staf_pengisi = $userId;
            $this->tanggalwaktu_pengisian = now();
            $this->save();
        }
    }

    /**
     * Add stok terjual (dari transaksi)
     */
    public function addStokTerjual($quantity): void
    {
        $this->stok_terjual = ($this->stok_terjual ?? 0) + $quantity;
        $this->updateSelisih();
    }

    /**
     * Reduce stok terjual (dari cancel saved order)
     */
    public function reduceStokTerjual($quantity): void
    {
        $this->stok_terjual = max(0, ($this->stok_terjual ?? 0) - $quantity);
        $this->updateSelisih();
    }

    /**
     * Get all stock entries untuk specific date
     */
    public static function getStockEntriesForDate($date)
    {
        $entries = collect();
        
        foreach (static::getJenisSateOptions() as $jenis) {
            $stock = static::createOrGetStock($date, $jenis);
            $entries->push($stock);
        }
        
        return $entries;
    }
}
