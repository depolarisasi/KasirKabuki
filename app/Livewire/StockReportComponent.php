<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ReportService;
use App\Services\StockService;
use App\Models\Product;
use Carbon\Carbon;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class StockReportComponent extends Component
{
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
        $this->generateReport(false); // Don't show success alert on initial load
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
            $this->generateReport(false); // Don't show success alert on automatic period change
        }
    }

    public function generateReport($showSuccessAlert = true)
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

            // Generate stock report for date range
            $this->reportData = $this->getStockReportForRange($this->startDate, $this->endDate);
            
            // Only show success alert if explicitly requested
            if ($showSuccessAlert) {
                Alert::success('Berhasil!', 'Laporan stok berhasil dibuat.');
            }
            
        } catch (\Exception $e) {
            Alert::error('Error!', 'Gagal membuat laporan: ' . $e->getMessage());
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
                Alert::error('Error!', 'Tidak ada data untuk diekspor. Buat laporan terlebih dahulu.');
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
            Alert::error('Error!', 'Gagal mengekspor laporan: ' . $e->getMessage());
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