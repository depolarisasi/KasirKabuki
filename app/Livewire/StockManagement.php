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
use Masmerise\Toaster\Toastable;

class StockManagement extends Component
{
    use Toastable;

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
            \Log::warning('inputStokAwal: No input provided');
            $this->error('Pilih minimal satu produk dan masukkan kuantitas.');
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
                $this->success('Stok awal berhasil diinput untuk ' . $successCount . ' produk.');
            }
            
            if (!empty($errors)) {
                $this->warning('Beberapa produk gagal diinput: ' . implode(', ', $errors));
            }
            
        } catch (\Exception $e) {
            \Log::error('inputStokAwal: Exception occurred', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function inputStokAkhir()
    {
        // Debug logging
        \Log::info('inputStokAkhir called', [
            'stockQuantities' => $this->stockQuantities,
            'notes' => $this->notes,
            'user_id' => auth()->id(),
            'session_id' => session()->getId()
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
            $this->error('Pilih minimal satu produk dan masukkan kuantitas stok akhir.');
            return;
        }

        try {
            $successCount = 0;
            $totalDifference = 0;
            $errors = [];
            $successProducts = [];

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
                        
                        // Add product name to success list for better feedback
                        $product = Product::find($productId);
                        $successProducts[] = $product->name;
                        
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

            // Show result notification with emoji for better UX
            if ($successCount > 0) {
                $message = "✅ Stok akhir berhasil diinput untuk {$successCount} produk pada {$today}";
                if ($totalDifference > 0) {
                    $message .= " dengan total selisih {$totalDifference} unit";
                }
                $message .= '.<br><br>';
                $message .= '<strong>Produk yang berhasil:</strong><br>';
                $message .= '• ' . implode('<br>• ', $successProducts);
                
                $this->success($message);
                
                // Call comprehensive form reset
                $this->forceFormReset();
            }
            
            if (!empty($errors)) {
                $this->warning('Beberapa produk gagal diinput:<br>• ' . implode('<br>• ', $errors));
            }
            
            \Log::info('inputStokAkhir: Completed successfully', [
                'success_count' => $successCount,
                'total_difference' => $totalDifference,
                'error_count' => count($errors)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('inputStokAkhir: Exception occurred', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->error('Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        \Log::info('resetForm: Starting basic form reset');
        
        $this->stockQuantities = [];
        $this->notes = [];
        $this->initializeFormData();
        
        // Force re-render to ensure UI updates
        $this->dispatch('$refresh');
        
        \Log::info('resetForm: Basic reset completed');
    }

    public function resetFormAndRefresh()
    {
        \Log::info('resetFormAndRefresh: Starting form reset');
        
        // Clear arrays completely first
        $this->stockQuantities = [];
        $this->notes = [];
        
        // Re-initialize with clean data
        $this->initializeFormData();
        
        // Dispatch events to clear Alpine.js state
        $this->dispatch('stockInputCompleted');
        $this->dispatch('inputsCleared');
        
        // Force Livewire component refresh
        $this->dispatch('$refresh');
        
        \Log::info('resetFormAndRefresh: Form reset completed', [
            'stockQuantities_count' => count($this->stockQuantities),
            'notes_count' => count($this->notes),
            'sample_stockQuantities' => array_slice($this->stockQuantities, 0, 3, true)
        ]);
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

            $this->success('Laporan rekonsiliasi siap diexport.');
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat export: ' . $e->getMessage());
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

    // Add method to explicitly clear all input values
    public function clearAllInputs()
    {
        \Log::info('clearAllInputs: Clearing all form inputs');
        
        // Clear all arrays completely
        $this->stockQuantities = [];
        $this->notes = [];
        
        // Re-initialize with empty data 
        $this->initializeFormData();
        
        // Dispatch multiple events to ensure UI clears
        $this->dispatch('inputsCleared');
        $this->dispatch('stockInputCompleted');
        $this->dispatch('$refresh');
        
        \Log::info('clearAllInputs: All inputs cleared', [
            'stockQuantities_count' => count($this->stockQuantities),
            'notes_count' => count($this->notes)
        ]);
        
        // Show feedback to user
        $this->info('Semua input telah dikosongkan.');
    }

    public function forceFormReset()
    {
        \Log::info('forceFormReset: Starting comprehensive form reset');
        
        // Approach 1: Complete property reset
        $this->stockQuantities = [];
        $this->notes = [];
        
        // Approach 2: Re-initialize arrays
        $this->initializeFormData();
        
        // Approach 3: Force Livewire to forget component state
        $this->forgetComputed();
        
        // Approach 4: Dispatch multiple events for frontend clearing
        $this->dispatch('stockFormReset');
        $this->dispatch('stockInputCompleted');
        $this->dispatch('inputsCleared');
        $this->dispatch('formClearSuccess');
        
        // Approach 5: Force component refresh
        $this->dispatch('$refresh');
        
        // Approach 7: Success notification to user
        $this->success('Semua input telah dikosongkan.');
        
        \Log::info('forceFormReset: Comprehensive reset completed');
    }
}
