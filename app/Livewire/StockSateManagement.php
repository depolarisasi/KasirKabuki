<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\StockSateService;
use App\Models\StockSate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StockSateManagement extends Component
{
    public $selectedDate;
    public $stockEntries = [];
    public $isEditing = false;
    
    // Individual stock fields untuk real-time editing
    public $stokAwal = [];
    public $stokAkhir = [];
    public $keterangan = [];
    
    protected $stockSateService;
    
    public function boot(StockSateService $stockSateService)
    {
        $this->stockSateService = $stockSateService;
    }

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->loadStockEntries();
    }

    /**
     * Load stock entries untuk selected date
     */
    public function loadStockEntries()
    {
        try {
            // Ensure entries exist untuk tanggal ini
            $entries = $this->stockSateService->ensureDailyStockEntries($this->selectedDate);
            
            $this->stockEntries = $entries->toArray();
            
            // Initialize arrays untuk editing
            $this->initializeEditingArrays();
            
            Log::info("Stock entries loaded for date: {$this->selectedDate}");
            
        } catch (\Exception $e) {
            Log::error("Error loading stock entries: " . $e->getMessage());
            session()->flash('error', 'Gagal memuat data stok.');
        }
    }

    /**
     * Initialize arrays untuk editing mode
     */
    private function initializeEditingArrays()
    {
        foreach ($this->stockEntries as $entry) {
            $jenis = $entry['jenis_sate'];
            $this->stokAwal[$jenis] = $entry['stok_awal'] ?? 0;
            $this->stokAkhir[$jenis] = $entry['stok_akhir'] ?? 0;
            $this->keterangan[$jenis] = $entry['keterangan'] ?? '';
        }
    }

    /**
     * Handle date change dari date picker
     */
    public function updatedSelectedDate()
    {
        $this->validateDate();
        $this->loadStockEntries();
        
        // Dispatch event untuk backfill functionality
        $this->dispatch('date-changed', ['date' => $this->selectedDate]);
    }

    /**
     * Validate selected date
     */
    private function validateDate()
    {
        try {
            Carbon::parse($this->selectedDate);
        } catch (\Exception $e) {
            $this->selectedDate = now()->format('Y-m-d');
            session()->flash('error', 'Format tanggal tidak valid. Menggunakan tanggal hari ini.');
        }
    }

    /**
     * Update stok awal untuk jenis sate tertentu
     */
    public function updateStokAwal($jenisSate)
    {
        // Convert empty string to 0 and ensure integer type
        $value = $this->stokAwal[$jenisSate] ?? 0;
        $value = ($value === '' || $value === null) ? 0 : (int) $value;
        
        $this->updateStockField($jenisSate, 'stok_awal', $value);
    }

    /**
     * Update stok akhir untuk jenis sate tertentu
     */
    public function updateStokAkhir($jenisSate)
    {
        // Convert empty string to 0 and ensure integer type
        $value = $this->stokAkhir[$jenisSate] ?? 0;
        $value = ($value === '' || $value === null) ? 0 : (int) $value;
        
        $this->updateStockField($jenisSate, 'stok_akhir', $value);
    }

    /**
     * Update keterangan untuk jenis sate tertentu
     */
    public function updateKeterangan($jenisSate)
    {
        $this->updateStockField($jenisSate, 'keterangan', $this->keterangan[$jenisSate] ?? '');
    }

    /**
     * Update specific stock field
     */
    private function updateStockField($jenisSate, $field, $value)
    {
        try {
            $data = [$field => $value];
            $userId = Auth::id();
            
            $this->stockSateService->updateStockByStaff(
                $this->selectedDate,
                $jenisSate,
                $data,
                $userId
            );
            
            // Reload entries untuk update display
            $this->loadStockEntries();
            
            // Calculate real-time selisih
            $this->calculateSelisih($jenisSate);
            
            session()->flash('success', "Stok {$jenisSate} berhasil diupdate.");
            
        } catch (\Exception $e) {
            Log::error("Error updating stock field: " . $e->getMessage());
            session()->flash('error', 'Gagal mengupdate stok.');
        }
    }

    /**
     * Calculate selisih real-time
     */
    public function calculateSelisih($jenisSate)
    {
        $entry = collect($this->stockEntries)->where('jenis_sate', $jenisSate)->first();
        
        if ($entry) {
            // Ensure all values are integers and handle empty strings
            $stokAwal = (int) ($this->stokAwal[$jenisSate] ?? $entry['stok_awal'] ?? 0);
            $stokTerjual = (int) ($entry['stok_terjual'] ?? 0);
            $stokAkhir = (int) ($this->stokAkhir[$jenisSate] ?? $entry['stok_akhir'] ?? 0);
            
            // Convert empty strings to 0
            if ($stokAwal === 0 && ($this->stokAwal[$jenisSate] ?? $entry['stok_awal']) === '') {
                $stokAwal = 0;
            }
            if ($stokAkhir === 0 && ($this->stokAkhir[$jenisSate] ?? $entry['stok_akhir']) === '') {
                $stokAkhir = 0;
            }
            
            $selisih = $stokAwal - $stokTerjual - $stokAkhir;
            
            // Update dalam stockEntries untuk display
            foreach ($this->stockEntries as &$stockEntry) {
                if ($stockEntry['jenis_sate'] === $jenisSate) {
                    $stockEntry['selisih'] = $selisih;
                    break;
                }
            }
        }
    }

    /**
     * Get selisih untuk display (calculated on-the-fly)
     */
    public function getSelisih($jenisSate)
    {
        $entry = collect($this->stockEntries)->where('jenis_sate', $jenisSate)->first();
        
        if ($entry) {
            // Ensure all values are integers and handle empty strings
            $stokAwal = (int) ($this->stokAwal[$jenisSate] ?? $entry['stok_awal'] ?? 0);
            $stokTerjual = (int) ($entry['stok_terjual'] ?? 0);
            $stokAkhir = (int) ($this->stokAkhir[$jenisSate] ?? $entry['stok_akhir'] ?? 0);
            
            // Convert empty strings to 0
            if ($stokAwal === 0 && ($this->stokAwal[$jenisSate] ?? $entry['stok_awal']) === '') {
                $stokAwal = 0;
            }
            if ($stokAkhir === 0 && ($this->stokAkhir[$jenisSate] ?? $entry['stok_akhir']) === '') {
                $stokAkhir = 0;
            }
            
            return $stokAwal - $stokTerjual - $stokAkhir;
        }
        
        return 0;
    }

    /**
     * Toggle editing mode
     */
    public function toggleEditing()
    {
        $this->isEditing = !$this->isEditing;
        
        if ($this->isEditing) {
            session()->flash('info', 'Mode editing diaktifkan.');
        } else {
            session()->flash('info', 'Mode editing dinonaktifkan.');
        }
    }

    /**
     * Save all changes
     */
    public function saveAllChanges()
    {
        try {
            $userId = Auth::id();
            
            foreach (StockSate::getJenisSateOptions() as $jenisSate) {
                // Ensure proper type conversion for all numeric fields
                $stokAwal = $this->stokAwal[$jenisSate] ?? 0;
                $stokAwal = ($stokAwal === '' || $stokAwal === null) ? 0 : (int) $stokAwal;
                
                $stokAkhir = $this->stokAkhir[$jenisSate] ?? 0;
                $stokAkhir = ($stokAkhir === '' || $stokAkhir === null) ? 0 : (int) $stokAkhir;
                
                $data = [
                    'stok_awal' => $stokAwal,
                    'stok_akhir' => $stokAkhir,
                    'keterangan' => $this->keterangan[$jenisSate] ?? ''
                ];
                
                $this->stockSateService->updateStockByStaff(
                    $this->selectedDate,
                    $jenisSate,
                    $data,
                    $userId
                );
            }
            
            $this->loadStockEntries();
            $this->isEditing = false;
            
            session()->flash('success', 'Semua perubahan berhasil disimpan.');
            
        } catch (\Exception $e) {
            Log::error("Error saving all changes: " . $e->getMessage());
            session()->flash('error', 'Gagal menyimpan perubahan.');
        }
    }

    /**
     * Reset form
     */
    public function resetForm()
    {
        $this->initializeEditingArrays();
        $this->isEditing = false;
        session()->flash('info', 'Form direset.');
    }

    /**
     * Get total summary untuk display
     */
    public function getTotalSummary()
    {
        $summary = $this->stockSateService->getStockSummary($this->selectedDate);
        return $summary;
    }

    /**
     * Get formatted date untuk display
     */
    public function getFormattedDate()
    {
        return Carbon::parse($this->selectedDate)->translatedFormat('l, d F Y');
    }

    /**
     * Check if date is today
     */
    public function isToday()
    {
        return $this->selectedDate === now()->format('Y-m-d');
    }

    /**
     * Go to today
     */
    public function goToToday()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->loadStockEntries();
        session()->flash('info', 'Beralih ke tanggal hari ini.');
    }

    public function render()
    {
        return view('livewire.stock-sate-management', [
            'totalSummary' => $this->getTotalSummary(),
            'formattedDate' => $this->getFormattedDate(),
            'jenisSateOptions' => StockSate::getJenisSateOptions(),
        ]);
    }
}
