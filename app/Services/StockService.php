<?php

namespace App\Services;

use App\Models\StockLog;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Get current stock untuk product - SINGLE SOURCE OF TRUTH
     */
    public function getCurrentStock($productId)
    {
        return StockLog::getCurrentStock($productId);
    }

    /**
     * Input stok awal menggunakan new StockLog system
     */
    public function inputStockAwal($productId, $userId, $quantity, $notes = null)
    {
        try {
            DB::beginTransaction();

            // Check if there's already initial stock input for today
            $existingStock = StockLog::forProduct($productId)
                ->where('type', StockLog::TYPE_INITIAL_STOCK)
                ->today()
                ->first();

            if ($existingStock) {
                // Update existing entry
                $currentStock = $this->getCurrentStock($productId);
                // Calculate difference from existing entry
                $stockBefore = $currentStock - $existingStock->quantity_change;
                $stockAfter = $stockBefore + $quantity;
                
                $existingStock->update([
                    'quantity_change' => $quantity,
                    'stock_after_change' => $stockAfter,
                    'notes' => $notes ?: "Daily stock input - Initial: {$quantity}",
                    'updated_at' => now()
                ]);
                
                $stockLog = $existingStock;
            } else {
                // Create new stock entry using enhanced system
                $stockLog = StockLog::logInitialStock(
                    $productId,
                    $userId,
                    $quantity,
                    $notes ?: "Daily stock input - Initial: {$quantity}"
                );
            }

            DB::commit();
            return $stockLog;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Input stok akhir dengan reconciliation menggunakan new system
     */
    public function inputStockAkhir($productId, $userId, $actualQuantity, $notes = null)
    {
        try {
            DB::beginTransaction();

            // Get initial stock for today
            $initialStock = $this->getTodayInitialStock($productId);
            
            // Get sold quantity today
            $soldToday = $this->getTodaySoldQuantity($productId);
            
            // Calculate expected stock
            $expectedStock = $initialStock - $soldToday;
            
            // Calculate difference
            $difference = $actualQuantity - $expectedStock;

            // Create adjustment if there's a difference
            if ($difference != 0) {
                $adjustmentNotes = "Daily stock input - Final: {$actualQuantity}, Initial: {$initialStock}, Expected: {$expectedStock}, Difference: {$difference}";
                if ($notes) {
                    $adjustmentNotes .= ", Notes: {$notes}";
                }

                $stockLog = StockLog::logAdjustment(
                    $productId,
                    $userId,
                    $difference, // positive if stock is more than expected, negative if less
                    $adjustmentNotes
                );
            } else {
                // No difference - just log for tracking
                $stockLog = StockLog::create([
                    'product_id' => $productId,
                    'user_id' => $userId,
                    'type' => StockLog::TYPE_ADJUSTMENT,
                    'quantity_change' => 0,
                    'stock_after_change' => $actualQuantity,
                    'notes' => "Daily stock input - Final: {$actualQuantity}, No adjustment needed" . ($notes ? ", Notes: {$notes}" : "")
                ]);
            }

            DB::commit();
            
            return [
                'stock_log' => $stockLog,
                'initial_stock' => $initialStock,
                'sold_today' => $soldToday,
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
     * Get today's initial stock untuk product
     */
    public function getTodayInitialStock($productId)
    {
        $initialStock = StockLog::forProduct($productId)
            ->where('type', StockLog::TYPE_INITIAL_STOCK)
            ->today()
            ->sum('quantity_change');

        return $initialStock;
    }

    /**
     * Get today's sold quantity untuk product
     */
    public function getTodaySoldQuantity($productId)
    {
        return StockLog::forProduct($productId)
            ->where('type', StockLog::TYPE_SALE)
            ->today()
            ->sum('quantity_change');
    }

    /**
     * Get daily reconciliation dengan new data structure
     */
    public function getDailyReconciliation($date = null)
    {
        $date = $date ?: Carbon::today();
        $products = Product::with('category')->get();
        $reconciliation = [];

        foreach ($products as $product) {
            // Get initial stock untuk the date
            $initialStock = StockLog::forProduct($product->id)
                ->where('type', StockLog::TYPE_INITIAL_STOCK)
                ->forDate($date)
                ->sum('quantity_change');

            // Get sold quantity untuk the date
            $soldQuantity = StockLog::forProduct($product->id)
                ->where('type', StockLog::TYPE_SALE)
                ->forDate($date)
                ->sum('quantity_change');

            // Get final stock from adjustments/manual input
            $finalStockLog = StockLog::forProduct($product->id)
                ->where('type', StockLog::TYPE_ADJUSTMENT)
                ->where('notes', 'like', '%Daily stock input - Final%')
                ->forDate($date)
                ->latest()
                ->first();

            $finalStock = $finalStockLog ? $finalStockLog->stock_after_change : null;
            
            // Calculate expected vs actual difference
            $expectedStock = $initialStock - $soldQuantity;
            $difference = $finalStock !== null ? ($finalStock - $expectedStock) : 0;

            $reconciliation[] = [
                'product' => $product,
                'initial_stock' => $initialStock,
                'sold' => $soldQuantity,
                'final_stock' => $finalStock ?? $expectedStock,
                'expected_stock' => $expectedStock,
                'difference' => $difference,
                'movements' => StockLog::getDailyMovements($product->id, $date)
            ];
        }

        return ['reconciliation' => $reconciliation];
    }

    /**
     * Calculate expected stock untuk product
     */
    public function calculateExpectedStock($productId)
    {
        $initialStock = $this->getTodayInitialStock($productId);
        $soldToday = $this->getTodaySoldQuantity($productId);
        
        return $initialStock - $soldToday;
    }

    /**
     * Get stock movements for a date range menggunakan new system
     */
    public function getStockMovements($startDate = null, $endDate = null)
    {
        $query = StockLog::query()
            ->with(['product.category', 'user', 'transaction'])
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
     * Check if stock input is already done for today using new types
     */
    public function isStockInputDone($productId, $type = 'initial')
    {
        $stockType = $type === 'initial' ? StockLog::TYPE_INITIAL_STOCK : StockLog::TYPE_ADJUSTMENT;
        
        $query = StockLog::forProduct($productId)
            ->where('type', $stockType)
            ->today();

        if ($type === 'final') {
            $query->where('notes', 'like', '%Daily stock input - Final%');
        }

        return $query->exists();
    }

    /**
     * Get products that need stock input dengan enhanced information
     */
    public function getProductsNeedingStockInput()
    {
        $products = Product::with('category')->get();
        $needingInput = [];

        foreach ($products as $product) {
            $hasInitialStock = $this->isStockInputDone($product->id, 'initial');
            $hasFinalStock = $this->isStockInputDone($product->id, 'final');

            $needingInput[] = [
                'product' => $product,
                'needs_initial_stock' => !$hasInitialStock,
                'needs_final_stock' => !$hasFinalStock,
                'current_stock' => $this->getCurrentStock($product->id),
                'today_initial' => $this->getTodayInitialStock($product->id),
                'today_sold' => $this->getTodaySoldQuantity($product->id),
                'expected_stock' => $this->calculateExpectedStock($product->id)
            ];
        }

        return $needingInput;
    }

    /**
     * Log product sale dengan package support
     */
    public function logSale($productId, $userId, $quantity, $transactionId = null, $notes = null)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            throw new \Exception("Product not found");
        }

        return $product->reduceStockForSale($quantity, $userId, $transactionId, $notes);
    }

    /**
     * Log cancellation return dengan package support
     */
    public function logCancellationReturn($productId, $userId, $quantity, $transactionId = null, $notes = null)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            throw new \Exception("Product not found");
        }

        return $product->returnStockForCancellation($quantity, $userId, $transactionId, $notes);
    }

    /**
     * Check stock availability untuk sale with package support
     */
    public function checkStockAvailability($productId, $quantity = 1)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return ['available' => false, 'message' => 'Product not found'];
        }

        if ($product->isPackageProduct()) {
            $status = $product->getPackageStockStatus($quantity);
            
            return [
                'available' => $status['is_sufficient'],
                'is_package' => true,
                'max_available' => $status['can_make_quantity'],
                'components' => $status['components'] ?? [],
                'message' => $status['is_sufficient'] 
                    ? "Package tersedia" 
                    : "Stok component tidak mencukupi"
            ];
        }

        $currentStock = $this->getCurrentStock($productId);
        
        return [
            'available' => $currentStock >= $quantity,
            'is_package' => false,
            'current_stock' => $currentStock,
            'max_available' => $currentStock,
            'message' => $currentStock >= $quantity 
                ? "Stok tersedia" 
                : "Stok tidak mencukupi"
        ];
    }

    /**
     * Get comprehensive stock report untuk product
     */
    public function getProductStockReport($productId, $startDate = null, $endDate = null)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return null;
        }

        $query = StockLog::forProduct($productId);
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        } elseif ($startDate) {
            $query->forDate($startDate);
        }

        $movements = $query->with(['user', 'transaction'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'product' => $product,
            'current_stock' => $this->getCurrentStock($productId),
            'stock_info' => $product->getStockInfo(),
            'movements' => $movements,
            'summary' => [
                'total_in' => $movements->where('type', 'in')->sum('quantity_change'),
                'total_out' => $movements->where('type', 'out')->sum('quantity_change'),
                'total_sales' => $movements->where('type', StockLog::TYPE_SALE)->sum('quantity_change'),
                'total_adjustments' => $movements->where('type', StockLog::TYPE_ADJUSTMENT)->sum('quantity_change'),
                'total_returns' => $movements->where('type', StockLog::TYPE_CANCELLATION_RETURN)->sum('quantity_change'),
            ]
        ];
    }

    /**
     * Log stock adjustment untuk admin - bisa positive atau negative
     */
    public function logStockAdjustment($productId, $userId, $quantityChange, $reason = null)
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($productId);
            
            // Validate user permissions (admin only)
            $user = \App\Models\User::find($userId);
            if (!$user || $user->role !== 'admin') {
                throw new \Exception('Hanya admin yang dapat melakukan penyesuaian stok');
            }

            // Get current stock untuk calculate after-change value
            $currentStock = $this->getCurrentStock($productId);
            $stockAfterChange = $currentStock + $quantityChange;

            // Create log entry
            $stockLog = StockLog::create([
                'product_id' => $productId,
                'user_id' => $userId,
                'type' => StockLog::TYPE_ADJUSTMENT,
                'quantity_change' => $quantityChange,
                'stock_after_change' => $stockAfterChange,
                'notes' => $reason ? "Admin adjustment: {$reason}" : "Admin stock adjustment",
                'reference_transaction_id' => null,
                'created_at' => now()
            ]);

            DB::commit();

            \Log::info('Stock adjustment logged', [
                'product_id' => $productId,
                'product_name' => $product->name,
                'user_id' => $userId,
                'quantity_change' => $quantityChange,
                'stock_before' => $currentStock,
                'stock_after' => $stockAfterChange,
                'reason' => $reason
            ]);

            return $stockLog;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Stock adjustment failed', [
                'product_id' => $productId,
                'user_id' => $userId,
                'quantity_change' => $quantityChange,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Bulk stock adjustment untuk multiple products - admin only
     */
    public function bulkStockAdjustment(array $adjustments, $userId, $reason = null)
    {
        try {
            DB::beginTransaction();

            $user = \App\Models\User::find($userId);
            if (!$user || $user->role !== 'admin') {
                throw new \Exception('Hanya admin yang dapat melakukan penyesuaian stok bulk');
            }

            $results = [];
            $errors = [];

            foreach ($adjustments as $adjustment) {
                if (!isset($adjustment['product_id']) || !isset($adjustment['quantity_change'])) {
                    $errors[] = 'Missing product_id atau quantity_change dalam adjustment data';
                    continue;
                }

                try {
                    $stockLog = $this->logStockAdjustment(
                        $adjustment['product_id'],
                        $userId,
                        $adjustment['quantity_change'],
                        $reason ?: ($adjustment['reason'] ?? null)
                    );
                    
                    $results[] = [
                        'product_id' => $adjustment['product_id'],
                        'success' => true,
                        'stock_log_id' => $stockLog->id,
                        'quantity_change' => $adjustment['quantity_change']
                    ];
                } catch (\Exception $e) {
                    $errors[] = "Product ID {$adjustment['product_id']}: {$e->getMessage()}";
                    $results[] = [
                        'product_id' => $adjustment['product_id'],
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            if (!empty($errors) && empty(array_filter($results, fn($r) => $r['success']))) {
                // All failed
                throw new \Exception('Semua adjustment gagal: ' . implode(', ', $errors));
            }

            DB::commit();

            \Log::info('Bulk stock adjustment completed', [
                'user_id' => $userId,
                'total_adjustments' => count($adjustments),
                'successful' => count(array_filter($results, fn($r) => $r['success'])),
                'failed' => count($errors),
                'reason' => $reason
            ]);

            return [
                'success' => true,
                'results' => $results,
                'errors' => $errors,
                'summary' => [
                    'total' => count($adjustments),
                    'successful' => count(array_filter($results, fn($r) => $r['success'])),
                    'failed' => count($errors)
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get stock adjustment history untuk audit trail
     */
    public function getStockAdjustmentHistory($productId = null, $userId = null, $limit = 50)
    {
        $query = StockLog::with(['product', 'user'])
            ->where('type', StockLog::TYPE_ADJUSTMENT)
            ->orderBy('created_at', 'desc');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->limit($limit)->get();
    }
} 