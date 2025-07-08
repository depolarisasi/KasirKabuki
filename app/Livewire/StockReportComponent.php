<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ReportService;
use App\Services\StockService;
use App\Models\Product;
use Carbon\Carbon;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class StockReportComponent extends Component
{
    use WithPagination;
    public $startDate;
    public $endDate;
    public $reportData = [];
    public $isLoading = false;
    public $selectedPeriod = 'week'; // today, week, month, custom
    
    protected $reportService;
    protected $stockService;

    public function boot(ReportService $reportService, StockService $stockService)
    {
        $this->reportService = $reportService;
        $this->stockService = $stockService;
    }

    public function mount()
    {
        // Initialize with current week data
        $this->setDatePeriod('week');
        $this->generateReportSilently(); // Silent on initial load
    }

    public function render()
    {
        return view('livewire.stock-report-component', [
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

            // Generate stock report for date range
            $this->reportData = $this->getStockReportForRange($this->startDate, $this->endDate);
            
            LivewireAlert::title('Berhasil!')
                ->text('Laporan stok berhasil dibuat.')
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

            // Generate stock report for date range (no alert)
            $this->reportData = $this->getStockReportForRange($this->startDate, $this->endDate);
            
        } catch (\Exception $e) {
            // Silent operation - log error but don't show alert
            \Log::error('Silent report generation failed: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    protected function getStockReportForRange($startDate, $endDate)
    {
        $period = Carbon::parse($startDate);
        $endPeriod = Carbon::parse($endDate);
        $dailyReports = [];
        $summary = [
            'total_days' => 0,
            'total_initial_stock' => 0,
            'total_final_stock' => 0,
            'total_sold' => 0,
            'total_differences' => 0,
            'days_with_stock' => 0
        ];

        while ($period <= $endPeriod) {
            $dailyReport = $this->stockService->getDailyReconciliation($period);
            
            if (!empty($dailyReport['reconciliation'])) {
                $dayData = [
                    'date' => $period->format('Y-m-d'),
                    'formatted_date' => $period->format('d/m/Y'),
                    'day_name' => $period->format('l'),
                    'reconciliation' => $dailyReport['reconciliation'],
                    'has_data' => true
                ];

                // Calculate day totals
                $dayData['day_totals'] = [
                    'initial_stock' => collect($dailyReport['reconciliation'])->sum('initial_stock'),
                    'final_stock' => collect($dailyReport['reconciliation'])->sum('final_stock'),
                    'sold' => collect($dailyReport['reconciliation'])->sum('sold'),
                    'difference' => collect($dailyReport['reconciliation'])->sum('difference')
                ];

                // Add to summary
                $summary['total_initial_stock'] += $dayData['day_totals']['initial_stock'];
                $summary['total_final_stock'] += $dayData['day_totals']['final_stock'];
                $summary['total_sold'] += $dayData['day_totals']['sold'];
                $summary['total_differences'] += $dayData['day_totals']['difference'];
                $summary['days_with_stock']++;
            } else {
                $dayData = [
                    'date' => $period->format('Y-m-d'),
                    'formatted_date' => $period->format('d/m/Y'),
                    'day_name' => $period->format('l'),
                    'reconciliation' => [],
                    'has_data' => false,
                    'day_totals' => [
                        'initial_stock' => 0,
                        'final_stock' => 0,
                        'sold' => 0,
                        'difference' => 0
                    ]
                ];
            }

            $dailyReports[] = $dayData;
            $summary['total_days']++;
            $period->addDay();
        }

        // Calculate averages
        $summary['avg_daily_stock'] = $summary['days_with_stock'] > 0 ? 
            $summary['total_initial_stock'] / $summary['days_with_stock'] : 0;
        $summary['avg_daily_sold'] = $summary['days_with_stock'] > 0 ? 
            $summary['total_sold'] / $summary['days_with_stock'] : 0;

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days_count' => $summary['total_days']
            ],
            'summary' => $summary,
            'daily_reports' => $dailyReports
        ];
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
                LivewireAlert::title('Error!')
                    ->text('Tidak ada data untuk diekspor. Buat laporan terlebih dahulu.')
                    ->error()
                    ->show();
                return;
            }

            // Generate filename
            $startDate = Carbon::parse($this->startDate)->format('d-m-Y');
            $endDate = Carbon::parse($this->endDate)->format('d-m-Y');
            $filename = "Laporan_Stok_{$startDate}_sampai_{$endDate}.xlsx";

            // Use Excel export
            $export = new \App\Exports\StockReportExport($this->reportData, $this->startDate, $this->endDate);
            
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
    public function formatDate($date)
    {
        return Carbon::parse($date)->format('d/m/Y');
    }

    public function getStockStatusClass($difference)
    {
        if ($difference > 0) {
            return 'text-success'; // Lebih banyak dari yang seharusnya
        } elseif ($difference < 0) {
            return 'text-error'; // Kurang dari yang seharusnya
        }
        return 'text-base-content'; // Sesuai
    }

    public function getStockStatusText($difference)
    {
        if ($difference > 0) {
            return 'Lebih ' . abs($difference);
        } elseif ($difference < 0) {
            return 'Kurang ' . abs($difference);
        }
        return 'Sesuai';
    }
} 