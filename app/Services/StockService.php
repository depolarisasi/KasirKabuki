<?php

namespace App\Services;

use App\Models\StockLog;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Input stok awal untuk produk tertentu
     */
    public function inputStockAwal($productId, $userId, $quantity, $notes = null)
    {
        try {
            DB::beginTransaction();

            // Check if there's already a stock input for today
            $existingStock = StockLog::forProduct($productId)
                ->stockIn()
                ->today()
                ->first();

            if ($existingStock) {
                throw new \Exception('Stok awal untuk produk ini sudah diinput hari ini.');
            }

            $stockLog = StockLog::logMovement(
                $productId, 
                $userId, 
                'in', 
                $quantity, 
                $notes ?: 'Stok awal harian'
            );

            DB::commit();
            return $stockLog;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Input stok akhir dan hitung selisih
     */
    public function inputStockAkhir($productId, $userId, $actualQuantity, $notes = null)
    {
        try {
            DB::beginTransaction();

            // Calculate expected stock based on movements
            $expectedStock = $this->calculateExpectedStock($productId);
            
            // Calculate difference
            $difference = $actualQuantity - $expectedStock;

            // Log the final stock count
            $finalStockLog = StockLog::create([
                'product_id' => $productId,
                'user_id' => $userId,
                'type' => 'adjustment',
                'quantity' => $actualQuantity,
                'notes' => $notes ?: 'Stok akhir harian - Fisik: ' . $actualQuantity . ', Expected: ' . $expectedStock . ', Selisih: ' . $difference,
            ]);

            // If there's a difference, log it as adjustment
            if ($difference != 0) {
                StockLog::create([
                    'product_id' => $productId,
                    'user_id' => $userId,
                    'type' => 'adjustment',
                    'quantity' => abs($difference),
                    'notes' => 'Penyesuaian stok akhir. Selisih: ' . ($difference > 0 ? '+' : '-') . abs($difference),
                ]);
            }

            DB::commit();

            return [
                'stock_log' => $finalStockLog,
                'expected_stock' => $expectedStock,
                'actual_stock' => $actualQuantity,
                'difference' => $difference
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Kurangi stok ketika produk terjual
     */
    public function reduceStock($productId, $userId, $quantity, $notes = null)
    {
        try {
            // Check if there's enough stock
            $currentStock = $this->getCurrentStock($productId);
            
            if ($currentStock < $quantity) {
                throw new \Exception('Stok tidak mencukupi. Stok tersedia: ' . $currentStock);
            }

            return StockLog::logMovement(
                $productId,
                $userId,
                'out',
                $quantity,
                $notes ?: 'Pengurangan stok - penjualan'
            );

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Dapatkan stok saat ini untuk produk
     */
    public function getCurrentStock($productId)
    {
        return StockLog::calculateStockBalance($productId);
    }

    /**
     * Calculate expected stock based on today's movements
     */
    public function calculateExpectedStock($productId)
    {
        $todayIn = StockLog::forProduct($productId)
            ->stockIn()
            ->today()
            ->sum('quantity');

        $todayOut = StockLog::forProduct($productId)
            ->stockOut()
            ->today()
            ->sum('quantity');

        return $todayIn - $todayOut;
    }

    /**
     * Dapatkan laporan rekonsiliasi stok harian
     */
    public function getDailyReconciliation($date = null)
    {
        $date = $date ?: Carbon::today();
        
        $products = Product::with('category')->get();
        $reconciliation = [];

        foreach ($products as $product) {
            $stockIn = StockLog::forProduct($product->id)
                ->stockIn()
                ->forDate($date)
                ->sum('quantity');

            $stockOut = StockLog::forProduct($product->id)
                ->stockOut()
                ->forDate($date)
                ->sum('quantity');

            $adjustments = StockLog::forProduct($product->id)
                ->adjustments()
                ->forDate($date)
                ->get();

            // Get final stock adjustment (stok akhir)
            $finalStock = $adjustments->where('notes', 'like', '%Stok akhir harian%')->first();
            $actualStock = $finalStock ? $finalStock->quantity : null;

            $expectedStock = $stockIn - $stockOut;
            $difference = $actualStock !== null ? $actualStock - $expectedStock : null;

            $reconciliation[] = [
                'product' => $product,
                'stock_in' => $stockIn,
                'stock_out' => $stockOut,
                'expected_stock' => $expectedStock,
                'actual_stock' => $actualStock,
                'difference' => $difference,
                'movements' => StockLog::getDailyMovements($product->id, $date)
            ];
        }

        return $reconciliation;
    }

    /**
     * Get stock movements for a date range
     */
    public function getStockMovements($startDate = null, $endDate = null)
    {
        $query = StockLog::query()
            ->with(['product.category', 'user'])
            ->orderBy('created_at', 'desc');

        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        } elseif ($startDate) {
            $query->forDate($startDate);
        } else {
            $query->today();
        }

        return $query->get();
    }

    /**
     * Check if stock input is already done for today
     */
    public function isStockInputDone($productId, $type = 'in')
    {
        return StockLog::forProduct($productId)
            ->where('type', $type)
            ->today()
            ->exists();
    }

    /**
     * Get products that need stock input
     */
    public function getProductsNeedingStockInput()
    {
        $products = Product::with('category')->get();
        $needingInput = [];

        foreach ($products as $product) {
            $hasStockIn = $this->isStockInputDone($product->id, 'in');
            $hasStockOut = $this->isStockInputDone($product->id, 'adjustment'); // For final stock

            $needingInput[] = [
                'product' => $product,
                'needs_stock_in' => !$hasStockIn,
                'needs_stock_out' => !$hasStockOut,
                'current_stock' => $this->getCurrentStock($product->id)
            ];
        }

        return $needingInput;
    }
} 