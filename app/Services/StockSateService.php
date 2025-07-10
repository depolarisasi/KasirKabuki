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
     * Menggunakan auto-creation logic untuk semua jenis sate
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
        
        // Use auto-creation logic untuk ensure all entries
        $entries = $this->ensureAllJenisSateEntries($date);
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
            try {
                $stock = StockSate::createOrGetStock($date, $jenis);
                
                if ($stock) {
                    $entries->push($stock);
                } else {
                    Log::error("Failed to create stock entry for jenis: {$jenis} on date: {$date}");
                    // Continue dengan jenis lain, jangan stop semua process
                }
            } catch (\Exception $e) {
                Log::error("Error creating stock entry", [
                    'date' => $date,
                    'jenis_sate' => $jenis,
                    'error' => $e->getMessage()
                ]);
                // Continue dengan jenis lain
            }
        }
        
        Log::info("Daily stock entries created for date: {$date}", [
            'total_entries' => $entries->count(),
            'expected_entries' => count(StockSate::getJenisSateOptions())
        ]);
        
        return $entries;
    }

    /**
     * Determine correct stock date berdasarkan business SOP
     * Gunakan previous day stock jika previous day stok_akhir belum diisi (0 atau null)
     * 
     * @param string|null $transactionDate Format Y-m-d (default today)
     * @return string Format Y-m-d
     */
    public function determineStockDate($transactionDate = null)
    {
        if (!$transactionDate) {
            $transactionDate = now()->format('Y-m-d');
        }
        
        // Check previous day completion
        $previousDay = Carbon::parse($transactionDate)->subDay()->format('Y-m-d');
        
        // Check if previous day stok_akhir sudah diisi untuk all jenis sate
        $previousDayComplete = $this->isPreviousDayStockComplete($previousDay);
        
        if (!$previousDayComplete) {
            // Use previous day context karena belum complete
            Log::info("Using previous day stock context", [
                'transaction_date' => $transactionDate,
                'stock_date_used' => $previousDay,
                'reason' => 'Previous day stock not completed'
            ]);
            return $previousDay;
        }
        
        // Use current transaction date
        return $transactionDate;
    }

    /**
     * Check if previous day stock is complete (all stok_akhir filled)
     * 
     * @param string $date
     * @return bool
     */
    public function isPreviousDayStockComplete($date)
    {
        $entries = StockSate::byDate($date)->get();
        
        if ($entries->count() < count(StockSate::getJenisSateOptions())) {
            // Not all entries exist
            return false;
        }
        
        // Check if all stok_akhir filled (not 0 or null)
        foreach ($entries as $entry) {
            if (($entry->stok_akhir ?? 0) <= 0) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Update stock from transaction dengan intelligent date detection
     * Uses previous day logic sesuai business SOP
     * 
     * @param array $transactionItems
     * @param string|null $transactionDate
     * @return void
     */
    public function updateStockFromTransaction($transactionItems, $transactionDate = null)
    {
        // Determine correct stock date using business logic
        $stockDate = $this->determineStockDate($transactionDate);

        foreach ($transactionItems as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product && $product->jenis_sate && $product->quantity_effect) {
                // Calculate total sate effect
                $totalEffect = $item['quantity'] * $product->quantity_effect;
                
                // Get atau create stock entry untuk jenis sate ini
                $stockEntry = StockSate::createOrGetStock($stockDate, $product->jenis_sate);
                
                // Add ke stok terjual
                $stockEntry->addStokTerjual($totalEffect);
                
                Log::info("Stock updated for {$product->jenis_sate}: +{$totalEffect} from transaction", [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'quantity_effect' => $product->quantity_effect,
                    'transaction_date' => $transactionDate,
                    'stock_date_used' => $stockDate
                ]);
            }
        }
    }

    /**
     * Update stock saat saved order di-save (reserve stock)
     * Uses previous day logic sesuai business SOP
     * 
     * @param array $savedOrderItems
     * @param string|null $transactionDate
     * @return void
     */
    public function updateStockFromSavedOrder($savedOrderItems, $transactionDate = null)
    {
        // Use intelligent date detection
        $stockDate = $this->determineStockDate($transactionDate);

        $this->updateStockFromTransaction($savedOrderItems, $transactionDate);
        
        Log::info("Stock updated from saved order", [
            'transaction_date' => $transactionDate,
            'stock_date_used' => $stockDate
        ]);
    }

    /**
     * Return stock saat saved order di-cancel
     * Uses previous day logic sesuai business SOP
     * 
     * @param array $savedOrderItems
     * @param string|null $transactionDate
     * @return void
     */
    public function returnStockFromCancelledOrder($savedOrderItems, $transactionDate = null)
    {
        // Use intelligent date detection
        $stockDate = $this->determineStockDate($transactionDate);

        foreach ($savedOrderItems as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product && $product->jenis_sate && $product->quantity_effect) {
                // Calculate total sate effect to return
                $totalEffect = $item['quantity'] * $product->quantity_effect;
                
                // Get stock entry untuk jenis sate ini
                $stockEntry = StockSate::getStockForDateAndJenis($stockDate, $product->jenis_sate);
                
                if ($stockEntry) {
                    // Reduce dari stok terjual
                    $stockEntry->reduceStokTerjual($totalEffect);
                    
                    Log::info("Stock returned for {$product->jenis_sate}: -{$totalEffect} from cancelled order", [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'quantity_effect' => $product->quantity_effect,
                        'transaction_date' => $transactionDate,
                        'stock_date_used' => $stockDate
                    ]);
                }
            }
        }
    }

    /**
     * Ensure ALL jenis sate entries exist untuk specific date
     * Auto-create all entries saat staff mengisi any stock type
     * 
     * @param string $date
     * @return \Illuminate\Support\Collection
     */
    public function ensureAllJenisSateEntries($date)
    {
        $entries = collect();
        
        foreach (StockSate::getJenisSateOptions() as $jenis) {
            $stock = StockSate::createOrGetStock($date, $jenis);
            if ($stock) {
                $entries->push($stock);
            }
        }
        
        Log::info("All jenis sate entries ensured for date: {$date}", [
            'entries_created' => $entries->count(),
            'expected_entries' => count(StockSate::getJenisSateOptions())
        ]);
        
        return $entries;
    }

    /**
     * Update stock by staff dengan auto-creation all entries
     * Auto-create all jenis sate entries if not exist saat pertama kali input
     * 
     * @param string $date
     * @param string $jenisSate
     * @param array $data
     * @param int $userId
     * @return StockSate
     */
    public function updateStockByStaff($date, $jenisSate, $data, $userId)
    {
        // Ensure all jenis sate entries exist saat staff mulai input
        $this->ensureAllJenisSateEntries($date);
        
        $stockEntry = StockSate::createOrGetStock($date, $jenisSate);
        
        // Add null check untuk prevent error
        if (!$stockEntry) {
            Log::error("Failed to create or get stock entry", [
                'date' => $date,
                'jenis_sate' => $jenisSate,
                'user_id' => $userId
            ]);
            throw new \Exception("Gagal membuat atau mengambil data stok untuk {$jenisSate} pada tanggal {$date}");
        }
        
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
        
        // Update selisih otomatis dengan formula yang benar
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

    /**
     * Update stock from sale transaction (backward compatibility)
     * Maps to updateStockFromTransaction dengan correct parameter format
     * 
     * @param string $jenisSate
     * @param int $totalQuantityEffect
     * @param string $transactionDate
     * @return void
     */
    public function updateStockFromSale($jenisSate, $totalQuantityEffect, $transactionDate = null)
    {
        // Convert old parameter format to new updateStockFromTransaction format
        $transactionItems = [
            [
                'product_id' => null, // Not needed for direct stock update
                'quantity' => 1,
                'quantity_effect' => $totalQuantityEffect,
                'jenis_sate' => $jenisSate
            ]
        ];
        
        // Determine correct stock date using business logic
        $stockDate = $this->determineStockDate($transactionDate);
        
        // Update stock directly
        $stockEntry = StockSate::createOrGetStock($stockDate, $jenisSate);
        $stockEntry->addStokTerjual($totalQuantityEffect);
        
        Log::info("Stock updated from sale for {$jenisSate}: +{$totalQuantityEffect}", [
            'jenis_sate' => $jenisSate,
            'quantity_effect' => $totalQuantityEffect,
            'transaction_date' => $transactionDate,
            'stock_date_used' => $stockDate
        ]);
    }
} 