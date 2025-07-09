<?php

namespace App\Services;

use App\Models\StockSate;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class StockSateService
{
    /**
     * Session key prefix untuk stock entry tracking
     */
    const SESSION_PREFIX = 'stock_sate_entries_';

    /**
     * Ensure daily stock entries exist untuk tanggal tertentu
     * Menggunakan session/cookies untuk optimization
     * 
     * @param string $date Format Y-m-d
     * @return \Illuminate\Support\Collection
     */
    public function ensureDailyStockEntries($date)
    {
        $sessionKey = self::SESSION_PREFIX . $date;
        
        // Check session first untuk optimization
        if (Session::has($sessionKey)) {
            // Verify session data masih valid dengan database check
            if ($this->verifySessionEntries($date)) {
                return StockSate::getStockEntriesForDate($date);
            } else {
                // Session invalid, remove dan create fresh
                Session::forget($sessionKey);
            }
        }
        
        // Create entries dan set session
        $entries = $this->createDailyEntries($date);
        Session::put($sessionKey, true);
        
        return $entries;
    }

    /**
     * Verify apakah session entries masih valid di database
     * 
     * @param string $date
     * @return bool
     */
    private function verifySessionEntries($date)
    {
        $jenisSateOptions = StockSate::getJenisSateOptions();
        $existingCount = StockSate::byDate($date)->count();
        
        return $existingCount >= count($jenisSateOptions);
    }

    /**
     * Create daily entries untuk semua jenis sate
     * 
     * @param string $date
     * @return \Illuminate\Support\Collection
     */
    private function createDailyEntries($date)
    {
        $entries = collect();
        
        foreach (StockSate::getJenisSateOptions() as $jenis) {
            $stock = StockSate::createOrGetStock($date, $jenis);
            $entries->push($stock);
        }
        
        Log::info("Daily stock entries created for date: {$date}");
        
        return $entries;
    }

    /**
     * Update stock saat ada transaksi completed
     * Deteksi produk yang memiliki jenis_sate dan add ke stok_terjual
     * 
     * @param array $transactionItems Format: [['product_id' => 1, 'quantity' => 2], ...]
     * @param string $date Format Y-m-d (default today)
     * @return void
     */
    public function updateStockFromTransaction($transactionItems, $date = null)
    {
        if (!$date) {
            $date = now()->format('Y-m-d');
        }

        foreach ($transactionItems as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product && $product->jenis_sate && $product->quantity_effect) {
                // Calculate total sate effect
                $totalEffect = $item['quantity'] * $product->quantity_effect;
                
                // Get atau create stock entry untuk jenis sate ini
                $stockEntry = StockSate::createOrGetStock($date, $product->jenis_sate);
                
                // Add ke stok terjual
                $stockEntry->addStokTerjual($totalEffect);
                
                Log::info("Stock updated for {$product->jenis_sate}: +{$totalEffect} from transaction", [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'quantity_effect' => $product->quantity_effect,
                    'date' => $date
                ]);
            }
        }
    }

    /**
     * Update stock saat saved order di-save (reserve stock)
     * 
     * @param array $savedOrderItems
     * @param string $date
     * @return void
     */
    public function updateStockFromSavedOrder($savedOrderItems, $date = null)
    {
        if (!$date) {
            $date = now()->format('Y-m-d');
        }

        $this->updateStockFromTransaction($savedOrderItems, $date);
        
        Log::info("Stock updated from saved order for date: {$date}");
    }

    /**
     * Return stock saat saved order di-cancel
     * 
     * @param array $savedOrderItems
     * @param string $date
     * @return void
     */
    public function returnStockFromCancelledOrder($savedOrderItems, $date = null)
    {
        if (!$date) {
            $date = now()->format('Y-m-d');
        }

        foreach ($savedOrderItems as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product && $product->jenis_sate && $product->quantity_effect) {
                // Calculate total sate effect to return
                $totalEffect = $item['quantity'] * $product->quantity_effect;
                
                // Get stock entry untuk jenis sate ini
                $stockEntry = StockSate::getStockForDateAndJenis($date, $product->jenis_sate);
                
                if ($stockEntry) {
                    // Reduce dari stok terjual
                    $stockEntry->reduceStokTerjual($totalEffect);
                    
                    Log::info("Stock returned for {$product->jenis_sate}: -{$totalEffect} from cancelled order", [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'quantity_effect' => $product->quantity_effect,
                        'date' => $date
                    ]);
                }
            }
        }
    }

    /**
     * Update stok awal, akhir, atau keterangan oleh staff
     * Otomatis update staf_pengisi dan tanggalwaktu_pengisian
     * 
     * @param string $date
     * @param string $jenisSate
     * @param array $data ['stok_awal', 'stok_akhir', 'keterangan']
     * @param int $userId
     * @return StockSate
     */
    public function updateStockByStaff($date, $jenisSate, $data, $userId)
    {
        $stockEntry = StockSate::createOrGetStock($date, $jenisSate);
        
        // Update staf pengisi jika perlu
        $stockEntry->updateStafPengisi($userId);
        
        // Update fields yang diberikan
        if (isset($data['stok_awal'])) {
            $stockEntry->stok_awal = $data['stok_awal'];
        }
        
        if (isset($data['stok_akhir'])) {
            $stockEntry->stok_akhir = $data['stok_akhir'];
        }
        
        if (isset($data['keterangan'])) {
            $stockEntry->keterangan = $data['keterangan'];
        }
        
        // Update selisih otomatis
        $stockEntry->updateSelisih();
        
        Log::info("Stock updated by staff for {$jenisSate} on {$date}", [
            'user_id' => $userId,
            'data' => $data
        ]);
        
        return $stockEntry;
    }

    /**
     * Get stock report untuk date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getStockReport($startDate, $endDate)
    {
        return StockSate::whereBetween('tanggal_stok', [$startDate, $endDate])
                        ->with('staf')
                        ->orderBy('tanggal_stok', 'desc')
                        ->orderBy('jenis_sate')
                        ->get();
    }

    /**
     * Get stock summary untuk specific date
     * 
     * @param string $date
     * @return array
     */
    public function getStockSummary($date)
    {
        $entries = StockSate::byDate($date)->get();
        
        $summary = [
            'total_stok_awal' => $entries->sum('stok_awal'),
            'total_stok_terjual' => $entries->sum('stok_terjual'),
            'total_stok_akhir' => $entries->sum('stok_akhir'),
            'total_selisih' => $entries->sum('selisih'),
            'entries_by_jenis' => []
        ];
        
        foreach (StockSate::getJenisSateOptions() as $jenis) {
            $entry = $entries->where('jenis_sate', $jenis)->first();
            $summary['entries_by_jenis'][$jenis] = $entry ? $entry->toArray() : [
                'stok_awal' => 0,
                'stok_terjual' => 0,
                'stok_akhir' => 0,
                'selisih' => 0,
                'keterangan' => null
            ];
        }
        
        return $summary;
    }

    /**
     * Clear session cache untuk specific date atau all
     * 
     * @param string|null $date
     * @return void
     */
    public function clearSessionCache($date = null)
    {
        if ($date) {
            Session::forget(self::SESSION_PREFIX . $date);
        } else {
            // Clear all stock session cache
            $keys = array_keys(Session::all());
            foreach ($keys as $key) {
                if (str_starts_with($key, self::SESSION_PREFIX)) {
                    Session::forget($key);
                }
            }
        }
        
        Log::info("Stock session cache cleared", ['date' => $date]);
    }

    /**
     * Get produk yang memiliki jenis_sate (untuk validation/filtering)
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getSateProducts()
    {
        return Product::whereNotNull('jenis_sate')
                      ->whereNotNull('quantity_effect')
                      ->get();
    }
} 