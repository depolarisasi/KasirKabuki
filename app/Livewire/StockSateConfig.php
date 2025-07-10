<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\StockSate;
use App\Services\StockSateService;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Carbon\Carbon;

class StockSateConfig extends Component
{
    use WithPagination;

    // Date management
    public $selectedDate;
    public $showDatePicker = false;
    
    // Stock entry modal
    public $showStockModal = false;
    public $modalJenisSate = '';
    public $modalStokAwal = 0;
    public $modalStokTerjual = 0;
    public $modalNote = '';
    public $isEditMode = false;
    public $editingStockId = null;
    
    // Bulk operations
    public $showBulkModal = false;
    public $bulkDate = '';
    public $bulkStockEntries = [];
    
    // Filters
    public $searchJenis = '';
    public $filterStatus = 'all'; // all, available, sold_out, not_set
    
    // Loading states
    public $isLoading = false;

    protected $stockSateService;

    protected $rules = [
        'modalJenisSate' => 'required|string|max:255',
        'modalStokAwal' => 'required|integer|min:0',
        'modalStokTerjual' => 'required|integer|min:0',
        'modalNote' => 'nullable|string|max:500',
    ];

    public function boot(StockSateService $stockSateService)
    {
        $this->stockSateService = $stockSateService;
    }

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->bulkDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $stockSateData = $this->getStockSateData();
        $sateProducts = $this->getSateProducts();
        $dailyStats = $this->getDailyStats();
        
        return view('livewire.stock-sate-config', [
            'stockSateData' => $stockSateData,
            'sateProducts' => $sateProducts,
            'dailyStats' => $dailyStats,
        ]);
    }

    public function getStockSateData()
    {
        $query = StockSate::where('tanggal_stok', $this->selectedDate);
        
        // Apply filters
        if ($this->searchJenis) {
            $query->where('jenis_sate', 'like', '%' . $this->searchJenis . '%');
        }
        
        if ($this->filterStatus !== 'all') {
            switch ($this->filterStatus) {
                case 'available':
                    $query->whereRaw('stok_awal > stok_terjual');
                    break;
                case 'sold_out':
                    $query->whereRaw('stok_awal <= stok_terjual');
                    break;
                case 'not_set':
                    $query->where('stok_awal', 0);
                    break;
            }
        }
        
        return $query->orderBy('jenis_sate')->paginate(20);
    }

    public function getSateProducts()
    {
        return Product::whereNotNull('jenis_sate')
                     ->whereNotNull('quantity_effect')
                     ->with('category')
                     ->orderBy('jenis_sate')
                     ->get();
    }

    public function getDailyStats()
    {
        $stockEntries = StockSate::where('tanggal_stok', $this->selectedDate)->get();
        
        return [
            'total_jenis' => $stockEntries->count(),
            'total_stok_awal' => $stockEntries->sum('stok_awal'),
            'total_stok_terjual' => $stockEntries->sum('stok_terjual'),
            'total_sisa' => $stockEntries->sum(function ($entry) {
                return $entry->stok_awal - $entry->stok_terjual;
            }),
            'jenis_habis' => $stockEntries->where(function ($entry) {
                return $entry->stok_awal <= $entry->stok_terjual;
            })->count(),
        ];
    }

    public function openStockModal($jenisSate = null, $stockId = null)
    {
        $this->resetModal();
        
        if ($stockId) {
            // Edit mode
            $stock = StockSate::find($stockId);
            if ($stock) {
                $this->isEditMode = true;
                $this->editingStockId = $stockId;
                $this->modalJenisSate = $stock->jenis_sate;
                $this->modalStokAwal = $stock->stok_awal;
                $this->modalStokTerjual = $stock->stok_terjual;
                $this->modalNote = $stock->note;
            }
        } else {
            // Add mode
            $this->modalJenisSate = $jenisSate ?? '';
        }
        
        $this->showStockModal = true;
    }

    public function saveStock()
    {
        $this->validate();
        
        try {
            if ($this->modalStokTerjual > $this->modalStokAwal) {
                $this->addError('modalStokTerjual', 'Stok terjual tidak boleh lebih dari stok awal.');
                return;
            }

            if ($this->isEditMode) {
                // Update existing stock
                $stock = StockSate::find($this->editingStockId);
                $stock->update([
                    'stok_awal' => $this->modalStokAwal,
                    'stok_terjual' => $this->modalStokTerjual,
                    'note' => $this->modalNote,
                ]);
                
                LivewireAlert::title('Berhasil!')
                    ->text('Stock sate berhasil diperbarui.')
                    ->success()
                    ->show();
            } else {
                // Create new stock entry
                StockSate::updateOrCreate([
                    'tanggal_stok' => $this->selectedDate,
                    'jenis_sate' => $this->modalJenisSate,
                ], [
                    'stok_awal' => $this->modalStokAwal,
                    'stok_terjual' => $this->modalStokTerjual,
                    'note' => $this->modalNote,
                ]);
                
                LivewireAlert::title('Berhasil!')
                    ->text('Stock sate berhasil ditambahkan.')
                    ->success()
                    ->show();
            }

            $this->closeStockModal();
            
        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal menyimpan stock: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function deleteStock($stockId)
    {
        try {
            StockSate::find($stockId)->delete();
            
            LivewireAlert::title('Berhasil!')
                ->text('Stock sate berhasil dihapus.')
                ->success()
                ->show();
            
        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal menghapus stock: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function openBulkModal()
    {
        $this->bulkStockEntries = [];
        $sateProducts = $this->getSateProducts();
        
        foreach ($sateProducts as $product) {
            $existingStock = StockSate::where('tanggal_stok', $this->bulkDate)
                                    ->where('jenis_sate', $product->jenis_sate)
                                    ->first();
            
            $this->bulkStockEntries[] = [
                'jenis_sate' => $product->jenis_sate,
                'product_name' => $product->name,
                'quantity_effect' => $product->quantity_effect,
                'stok_awal' => $existingStock ? $existingStock->stok_awal : 0,
                'stok_terjual' => $existingStock ? $existingStock->stok_terjual : 0,
                'note' => $existingStock ? $existingStock->note : '',
            ];
        }
        
        $this->showBulkModal = true;
    }

    public function saveBulkStock()
    {
        try {
            foreach ($this->bulkStockEntries as $entry) {
                if ($entry['stok_awal'] > 0 || $entry['stok_terjual'] > 0) {
                    StockSate::updateOrCreate([
                        'tanggal_stok' => $this->bulkDate,
                        'jenis_sate' => $entry['jenis_sate'],
                    ], [
                        'stok_awal' => $entry['stok_awal'],
                        'stok_terjual' => $entry['stok_terjual'],
                        'note' => $entry['note'],
                    ]);
                }
            }
            
            LivewireAlert::title('Berhasil!')
                ->text('Bulk stock sate berhasil disimpan.')
                ->success()
                ->show();
            
            $this->showBulkModal = false;
            
        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal menyimpan bulk stock: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function copyFromPreviousDay()
    {
        try {
            $previousDate = Carbon::parse($this->selectedDate)->subDay()->format('Y-m-d');
            $previousStocks = StockSate::where('tanggal_stok', $previousDate)->get();
            
            if ($previousStocks->isEmpty()) {
                LivewireAlert::title('Tidak Ada Data!')
                    ->text('Tidak ada data stock untuk tanggal sebelumnya.')
                    ->warning()
                    ->show();
                return;
            }

            foreach ($previousStocks as $stock) {
                StockSate::updateOrCreate([
                    'tanggal_stok' => $this->selectedDate,
                    'jenis_sate' => $stock->jenis_sate,
                ], [
                    'stok_awal' => max(0, $stock->stok_awal - $stock->stok_terjual), // Sisa stock kemarin jadi stock awal hari ini
                    'stok_terjual' => 0,
                    'note' => 'Copied from ' . $previousDate,
                ]);
            }
            
            LivewireAlert::title('Berhasil!')
                ->text('Data stock berhasil disalin dari hari sebelumnya.')
                ->success()
                ->show();
            
        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal menyalin data: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function resetModal()
    {
        $this->modalJenisSate = '';
        $this->modalStokAwal = 0;
        $this->modalStokTerjual = 0;
        $this->modalNote = '';
        $this->isEditMode = false;
        $this->editingStockId = null;
        $this->clearValidation();
    }

    public function closeStockModal()
    {
        $this->showStockModal = false;
        $this->resetModal();
    }

    public function clearFilters()
    {
        $this->searchJenis = '';
        $this->filterStatus = 'all';
        $this->resetPage();
    }

    public function updatedSelectedDate()
    {
        $this->resetPage();
    }

    public function updatedSearchJenis()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }
} 