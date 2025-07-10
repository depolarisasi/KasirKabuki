<?php

namespace App\Livewire;

use App\Services\ReportService;
use App\Services\StockSateService;
use Livewire\Component;
use Carbon\Carbon;

class StockReportComponent extends Component
{
    // Date range properties
    public $startDate;
    public $endDate;
    public $reportPeriod = 'today';
    
    // Report data
    public $reportData;
    public $stockSummary;
    
    // Loading states
    public $isGenerating = false;
    public $isExporting = false;
    
    protected $reportService;
    protected $stockSateService;

    public function boot(ReportService $reportService, StockSateService $stockSateService)
    {
        $this->reportService = $reportService;
        $this->stockSateService = $stockSateService;
    }

    public function mount()
    {
        // Set default date to today
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        
        // Generate initial report
        $this->generateReport();
    }

    public function updatedReportPeriod()
    {
        switch ($this->reportPeriod) {
            case 'today':
                $this->startDate = now()->format('Y-m-d');
                $this->endDate = now()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = now()->subDay()->format('Y-m-d');
                $this->endDate = now()->subDay()->format('Y-m-d');
                break;
            case 'this_week':
                $this->startDate = now()->startOfWeek()->format('Y-m-d');
                $this->endDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'last_week':
                $this->startDate = now()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->endDate = now()->subWeek()->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
        }

        $this->generateReport();
    }

    public function updatedStartDate()
    {
        $this->reportPeriod = 'custom';
        $this->generateReport();
    }

    public function updatedEndDate()
    {
        $this->reportPeriod = 'custom';
        $this->generateReport();
    }

    public function generateReport()
    {
        $this->isGenerating = true;

        try {
            // Validate date range
            if (Carbon::parse($this->endDate)->lt(Carbon::parse($this->startDate))) {
                $this->addError('dateRange', 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.');
                $this->isGenerating = false;
                return;
            }

            // Generate stock reconciliation report for sate products only
            $this->reportData = $this->reportService->getDailyStockReconciliation($this->startDate);
            
            // Calculate summary
            $this->stockSummary = [
                'total_sate_products' => count($this->reportData['sate_products']),
                'products_with_stock' => collect($this->reportData['sate_products'])->where('initial_stock_sate', '>', 0)->count(),
                'products_sold_out' => collect($this->reportData['sate_products'])->where('remaining_stock_sate', '<=', 0)->count(),
                'total_initial_sate_stock' => collect($this->reportData['sate_products'])->sum('initial_stock_sate'),
                'total_sold_sate_stock' => collect($this->reportData['sate_products'])->sum('sold_stock_sate'),
                'total_remaining_sate_stock' => collect($this->reportData['sate_products'])->sum('remaining_stock_sate'),
            ];

            $this->clearValidation();
            
        } catch (\Exception $e) {
            \Log::error('Stock report generation failed', [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'error' => $e->getMessage()
            ]);
            
            $this->addError('generation', 'Gagal menghasilkan laporan: ' . $e->getMessage());
    }

        $this->isGenerating = false;
    }

    public function exportToExcel()
    {
        $this->isExporting = true;
        
        try {
            if (empty($this->reportData)) {
                $this->addError('export', 'Tidak ada data untuk diekspor. Silakan generate laporan terlebih dahulu.');
                $this->isExporting = false;
                return;
            }

            // Create simplified export for sate products
            $exportData = [
                'date' => $this->reportData['date'],
                'sate_products' => $this->reportData['sate_products'],
                'summary' => $this->stockSummary
            ];

            $fileName = 'stock-sate-report-' . $this->startDate . '.xlsx';

            // Use simple Excel export
            return response()->streamDownload(function () use ($exportData) {
                echo "Date,Product Name,Jenis Sate,Initial Stock (Sate),Sold Stock (Sate),Remaining Stock (Sate),Product Units Available\n";
                
                foreach ($exportData['sate_products'] as $item) {
                    echo sprintf(
                        "%s,%s,%s,%d,%d,%d,%d\n",
                        $exportData['date'],
                        $item['product']->name,
                        $item['jenis_sate'],
                        $item['initial_stock_sate'],
                        $item['sold_stock_sate'],
                        $item['remaining_stock_sate'],
                        $item['remaining_product_units']
                    );
                }
            }, $fileName, [
                'Content-Type' => 'text/csv',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Stock report export failed', [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'error' => $e->getMessage()
            ]);
            
            $this->addError('export', 'Gagal mengekspor laporan: ' . $e->getMessage());
        }
        
        $this->isExporting = false;
    }

    public function render()
    {
        return view('livewire.stock-report-component', [
            'reportData' => $this->reportData,
            'stockSummary' => $this->stockSummary,
        ]);
    }
} 