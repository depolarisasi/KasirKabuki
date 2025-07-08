<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ReportService;
use Carbon\Carbon;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Rule;

class ExpenseReportComponent extends Component
{

    public $startDate;
    public $endDate;
    public $reportData = [];
    public $isLoading = false;
    public $selectedPeriod = 'month'; // today, week, month, custom
    public $investorMode = false; // New property for investor mode
    
    protected $reportService;

    public function boot(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function mount($investorMode = false)
    {
        $this->investorMode = $investorMode;
        
        // Initialize with current month data
        $this->setDatePeriod('month');
        $this->generateReportSilently(); // Silent on initial load
    }

    public function render()
    {
        return view('livewire.expense-report-component', [
            'reportData' => $this->reportData
        ]);
    }

    public function setDatePeriod($period)
    {
        $this->selectedPeriod = $period;

        switch ($period) {
            case 'today':
                $this->startDate = Carbon::today()->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'week':
                $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'custom':
                // Keep current dates for custom period
                break;
        }

        if ($period !== 'custom') {
            $this->generateReportSilently(); // Silent on automatic period change
        }
    }

    public function generateReport()
    {
        $this->isLoading = true;

        try {
            // Validate dates
            if (!$this->startDate || !$this->endDate) {
                LivewireAlert::title('Error!')
                    ->text('Tanggal mulai dan akhir harus diisi.')
                    ->error()
                    ->show();
                $this->isLoading = false;
                return;
            }

            if (Carbon::parse($this->startDate) > Carbon::parse($this->endDate)) {
                LivewireAlert::title('Error!')
                    ->text('Tanggal mulai tidak boleh lebih besar dari tanggal akhir.')
                    ->error()
                    ->show();
                $this->isLoading = false;
                return;
            }

            // Generate expense report
            $this->reportData = $this->reportService->getExpenseReport($this->startDate, $this->endDate);
            
            LivewireAlert::title('Berhasil!')
                ->text('Laporan pengeluaran berhasil dibuat.')
                ->success()
                ->show();
            
        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal membuat laporan: ' . $e->getMessage())
                ->error()
                ->show();
        } finally {
            $this->isLoading = false;
        }
    }

    public function generateReportSilently()
    {
        $this->isLoading = true;

        try {
            // Validate dates
            if (!$this->startDate || !$this->endDate) {
                $this->isLoading = false;
                return;
            }

            if (Carbon::parse($this->startDate) > Carbon::parse($this->endDate)) {
                $this->isLoading = false;
                return;
            }

            // Generate expense report (no alert)
            $this->reportData = $this->reportService->getExpenseReport($this->startDate, $this->endDate);
            
        } catch (\Exception $e) {
            // Silent operation - log error but don't show alert
            \Log::error('Silent expense report generation failed: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedStartDate()
    {
        $this->selectedPeriod = 'custom';
    }

    public function updatedEndDate()
    {
        $this->selectedPeriod = 'custom';
    }

    public function exportToExcel()
    {
        // Prevent export in investor mode
        if ($this->investorMode) {
            LivewireAlert::title('Akses Terbatas!')
                ->text('Fitur export tidak tersedia untuk investor.')
                ->warning()
                ->show();
            return;
        }
        
        try {
            if (empty($this->reportData)) {
                LivewireAlert::title('Error!')
                    ->text('Tidak ada data untuk diekspor. Buat laporan terlebih dahulu.')
                    ->error()
                    ->show();
                return;
            }

            // Generate filename
            $startDate = Carbon::parse($this->startDate)->format('d-m-Y');
            $endDate = Carbon::parse($this->endDate)->format('d-m-Y');
            $filename = "Laporan_Pengeluaran_{$startDate}_sampai_{$endDate}.xlsx";

            // Use Excel export
            $export = new \App\Exports\ExpenseReportExport($this->reportData, $this->startDate, $this->endDate);
            
            return $export->download($filename);
            
        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal mengekspor laporan: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function refreshReport()
    {
        $this->generateReport();
    }

    // Helper methods for formatting
    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function formatDate($date)
    {
        return Carbon::parse($date)->format('d/m/Y');
    }
}