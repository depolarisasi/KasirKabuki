<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\StockLog;
use App\Services\StockService;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon;

class StockManagement extends Component
{
    public $activeTab = 'input-awal'; // input-awal, input-akhir, laporan
    
    // Form properties for stock input
    public $selectedProducts = [];
    public $stockQuantities = [];
    public $notes = [];
    
    // Date selection for stock operations
    public $selectedDate = '';
    public $useSelectedDate = false;
    
    // Date filter for reports
    public $reportDate = '';

    protected $stockService;

    public function boot(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function mount()
    {
        $this->reportDate = Carbon::today()->format('Y-m-d');
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->initializeFormData();
    }

    public function render()
    {
        $products = Product::with('category')->get();
        $targetDate = $this->useSelectedDate ? Carbon::parse($this->selectedDate) : Carbon::today();
        
        // Get product status based on selected date
        $productStatus = $this->getProductsNeedingStockInputForDate($targetDate);
        
        // Get reconciliation data if on report tab
        $reconciliation = [];
        if ($this->activeTab === 'laporan') {
            $reconciliation = $this->stockService->getDailyReconciliation(
                $this->reportDate ? Carbon::parse($this->reportDate) : null
            );
        }

        return view('livewire.stock-management', [
            'products' => $products,
            'productStatus' => $productStatus,
            'reconciliation' => $reconciliation,
            'targetDate' => $targetDate
        ]);
    }

    public function initializeFormData()
    {
        $products = Product::all();
        
        foreach ($products as $product) {
            $this->stockQuantities[$product->id] = '';
            $this->notes[$product->id] = '';
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        
        if ($tab === 'laporan') {
            $this->reportDate = Carbon::today()->format('Y-m-d');
        }
    }

    public function inputStokAwal()
    {
        // Validate that at least one product is selected with quantity
        $hasInput = false;
        foreach ($this->stockQuantities as $productId => $quantity) {
            if (!empty($quantity) && $quantity > 0) {
                $hasInput = true;
                break;
            }
        }

        if (!$hasInput) {
            Alert::error('Error!', 'Pilih minimal satu produk dan masukkan kuantitas.');
            return;
        }

        try {
            $successCount = 0;
            $errors = [];

            foreach ($this->stockQuantities as $productId => $quantity) {
                if (!empty($quantity) && $quantity > 0) {
                    try {
                        $this->stockService->inputStockAwal(
                            $productId,
                            auth()->id(),
                            $quantity,
                            $this->notes[$productId] ?: null
                        );
                        $successCount++;
                    } catch (\Exception $e) {
                        $product = Product::find($productId);
                        $errors[] = $product->name . ': ' . $e->getMessage();
                    }
                }
            }

            if ($successCount > 0) {
                Alert::success('Berhasil!', 'Stok awal berhasil diinput untuk ' . $successCount . ' produk.');
                $this->resetForm();
            }

            if (!empty($errors)) {
                Alert::warning('Perhatian!', 'Beberapa produk gagal diinput: ' . implode(', ', $errors));
            }

        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function inputStokAkhir()
    {
        // Debug logging
        \Log::info('inputStokAkhir called', [
            'stockQuantities' => $this->stockQuantities,
            'notes' => $this->notes,
            'user_id' => auth()->id()
        ]);

        // Validate that at least one product is selected with quantity
        $hasInput = false;
        foreach ($this->stockQuantities as $productId => $quantity) {
            if ($quantity !== '' && $quantity >= 0) {
                $hasInput = true;
                break;
            }
        }

        if (!$hasInput) {
            \Log::warning('inputStokAkhir: No input provided');
            Alert::error('Error!', 'Pilih minimal satu produk dan masukkan kuantitas stok akhir.');
            return;
        }

        try {
            $successCount = 0;
            $totalDifference = 0;
            $errors = [];

            foreach ($this->stockQuantities as $productId => $quantity) {
                if ($quantity !== '' && $quantity >= 0) {
                    try {
                        \Log::info('inputStokAkhir: Processing product', [
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'notes' => $this->notes[$productId] ?? null
                        ]);

                        $result = $this->stockService->inputStockAkhir(
                            $productId,
                            auth()->id(),
                            $quantity,
                            $this->notes[$productId] ?: null
                        );
                        
                        \Log::info('inputStokAkhir: Success for product', [
                            'product_id' => $productId,
                            'result' => $result
                        ]);
                        
                        $successCount++;
                        $totalDifference += abs($result['difference']);
                        
                    } catch (\Exception $e) {
                        $product = Product::find($productId);
                        $errors[] = $product->name . ': ' . $e->getMessage();
                        \Log::error('inputStokAkhir: Error for product', [
                            'product_id' => $productId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            }

            \Log::info('inputStokAkhir: Complete', [
                'success_count' => $successCount,
                'total_difference' => $totalDifference,
                'errors' => $errors
            ]);

            if ($successCount > 0) {
                $message = 'Stok akhir berhasil diinput untuk ' . $successCount . ' produk.';
                if ($totalDifference > 0) {
                    $message .= ' Total selisih: ' . $totalDifference . ' unit.';
                }
                Alert::success('Berhasil!', $message);
                $this->resetForm();
            }

            if (!empty($errors)) {
                Alert::warning('Perhatian!', 'Beberapa produk gagal diinput: ' . implode(', ', $errors));
            }

        } catch (\Exception $e) {
            \Log::error('inputStokAkhir: Fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Alert::error('Error!', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->stockQuantities = [];
        $this->notes = [];
        $this->initializeFormData();
    }

    public function updatedReportDate()
    {
        // Refresh data when date changes
        if ($this->activeTab === 'laporan') {
            $this->render();
        }
    }

    public function exportReconciliation()
    {
        try {
            $reconciliation = $this->stockService->getDailyReconciliation(
                $this->reportDate ? Carbon::parse($this->reportDate) : null
            );

            // For now, we'll just show success message
            // In future iterations, we can implement actual export
            Alert::success('Berhasil!', 'Laporan rekonsiliasi siap diexport.');
            
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    public function getProductCurrentStock($productId)
    {
        return $this->stockService->getCurrentStock($productId);
    }

    public function getProductExpectedStock($productId)
    {
        return $this->stockService->calculateExpectedStock($productId);
    }

    public function toggleDateSelection()
    {
        $this->useSelectedDate = !$this->useSelectedDate;
    }

    public function getProductsNeedingStockInputForDate($date)
    {
        $products = Product::with('category')->get();
        $needingInput = [];

        foreach ($products as $product) {
            $hasStockIn = StockLog::forProduct($product->id)
                ->where('type', 'in')
                ->whereDate('created_at', $date)
                ->exists();
                
            $hasStockOut = StockLog::forProduct($product->id)
                ->where('type', 'adjustment')
                ->whereDate('created_at', $date)
                ->exists();

            $needingInput[] = [
                'product' => $product,
                'needs_stock_in' => !$hasStockIn,
                'needs_stock_out' => !$hasStockOut,
                'current_stock' => $this->stockService->getCurrentStock($product->id)
            ];
        }

        return $needingInput;
    }
}
