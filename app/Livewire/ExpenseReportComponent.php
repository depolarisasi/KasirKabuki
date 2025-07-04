<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ReportService;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert;
use Livewire\Attributes\Rule;

class ExpenseReportComponent extends Component
{
    public $startDate;
    public $endDate;
    public $reportData = [];
    public $isLoading = false;
    public $selectedPeriod = 'month'; // today, week, month, custom
    
    protected $reportService;

    public function boot(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function mount()
    {
        // Initialize with current month data
        $this->setDatePeriod('month');
        $this->generateReport();
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
            $this->generateReport();
        }
    }

    public function generateReport()
    {
        $this->isLoading = true;

        try {
            // Validate dates
            if (!$this->startDate || !$this->endDate) {
                Alert::error('Error!', 'Tanggal mulai dan akhir harus diisi.');
                $this->isLoading = false;
                return;
            }

            if (Carbon::parse($this->startDate) > Carbon::parse($this->endDate)) {
                Alert::error('Error!', 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
                $this->isLoading = false;
                return;
            }

            // Generate expense report
            $this->reportData = $this->reportService->getExpenseReport($this->startDate, $this->endDate);
            
            Alert::success('Berhasil!', 'Laporan pengeluaran berhasil dibuat.');
            
        } catch (\Exception $e) {
            Alert::error('Error!', 'Gagal membuat laporan: ' . $e->getMessage());
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
        try {
            if (empty($this->reportData)) {
                Alert::error('Error!', 'Tidak ada data untuk diekspor. Buat laporan terlebih dahulu.');
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
            Alert::error('Error!', 'Gagal mengekspor laporan: ' . $e->getMessage());
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