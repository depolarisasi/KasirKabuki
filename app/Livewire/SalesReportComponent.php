<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ReportService;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class SalesReportComponent extends Component
{

    public $startDate;
    public $endDate;
    public $reportData = [];
    public $isLoading = false;
    public $selectedPeriod = 'today'; // today, yesterday, week, month, custom
    public $investorMode = false; // New property for investor mode
    public $autoRefresh = true; // Auto refresh for real-time updates
    public $lastRefresh; // Track last refresh time
    
    // Chart data for JavaScript
    public $chartData = [];
    
    protected $reportService;

    protected $listeners = [
        'transaction-completed' => 'handleTransactionCompleted',
        'refresh-sales-report' => 'refreshReport',
    ];

    public function boot(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function mount($investorMode = false)
    {
        $this->investorMode = $investorMode;
        $this->lastRefresh = now()->format('H:i:s');
        
        // Initialize with today's data
        $this->setDatePeriod('today');
        $this->generateReportSilently();
    }

    public function render()
    {
        return view('livewire.sales-report-component', [
            'reportData' => $this->reportData,
            'chartData' => $this->chartData
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
            case 'yesterday':
                $this->startDate = Carbon::yesterday()->format('Y-m-d');
                $this->endDate = Carbon::yesterday()->format('Y-m-d');
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
            $this->generateReportSilently();
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

            // Clear any potential cache
            $this->clearQueryCache();

            // Generate report data
            $this->reportData = $this->reportService->getSalesReport($this->startDate, $this->endDate);
            
            // Prepare chart data
            $this->prepareChartData();
            
            // Update last refresh time
            $this->lastRefresh = now()->format('H:i:s');
            
            LivewireAlert::title('Berhasil!')
                ->text('Laporan berhasil dibuat.')
                ->success()
                ->show();
            
        } catch (\Exception $e) {
            LivewireAlert::title('Terjadi kesalahan!')
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

            // Clear any potential cache for real-time data
            $this->clearQueryCache();

            // Generate report data without alert
            $this->reportData = $this->reportService->getSalesReport($this->startDate, $this->endDate);
            
            // Update last refresh time
            $this->lastRefresh = now()->format('H:i:s');
            
            // Prepare chart data
            $this->prepareChartData();
            
        } catch (\Exception $e) {
            \Log::error('SalesReportComponent: generateReportSilently error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Silent failure for initial load
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Handle real-time transaction completion events
     */
    public function handleTransactionCompleted($transactionData = null)
    {
        // Only refresh if viewing today's data or if transaction falls within current date range
        $shouldRefresh = false;
        
        if ($this->selectedPeriod === 'today') {
            $shouldRefresh = true;
        } elseif ($transactionData && isset($transactionData['created_at'])) {
            $transactionDate = Carbon::parse($transactionData['created_at'])->format('Y-m-d');
            $startDate = Carbon::parse($this->startDate)->format('Y-m-d');
            $endDate = Carbon::parse($this->endDate)->format('Y-m-d');
            
            if ($transactionDate >= $startDate && $transactionDate <= $endDate) {
                $shouldRefresh = true;
            }
        }
        
        if ($shouldRefresh && $this->autoRefresh) {
            $this->generateReportSilently();
            
            // Show discrete notification for real-time update
            $this->dispatch('report-updated', [
                'message' => 'Laporan diperbarui dengan transaksi terbaru',
                'time' => $this->lastRefresh
            ]);
        }
    }

    /**
     * Clear potential query cache for real-time data
     */
    private function clearQueryCache()
    {
        // Clear model cache if any caching is implemented
        if (method_exists(\App\Models\Transaction::class, 'flushQueryCache')) {
            \App\Models\Transaction::flushQueryCache();
        }
        
        // Clear Laravel's query cache
        if (function_exists('cache')) {
            cache()->forget('sales_report_' . $this->startDate . '_' . $this->endDate);
            cache()->forget('transactions_today');
            cache()->forget('sales_summary_' . Carbon::today()->format('Y-m-d'));
        }
    }

    /**
     * Toggle auto refresh
     */
    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
        
        if ($this->autoRefresh) {
            LivewireAlert::title('Auto Refresh Aktif')
                ->text('Laporan akan otomatis ter-update saat ada transaksi baru.')
                ->success()
                ->show();
        } else {
            LivewireAlert::title('Auto Refresh Nonaktif')
                ->text('Laporan hanya akan ter-update manual.')
                ->info()
                ->show();
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

    protected function prepareChartData()
    {
        if (empty($this->reportData)) {
            $this->chartData = [];
            return;
        }

        // Safely get daily sales data as array
        $dailySales = collect($this->reportData['daily_sales'] ?? [])->toArray();
        $revenueByCategory = collect($this->reportData['revenue_by_category'] ?? [])->toArray();
        
        // FIXED: Handle revenueByOrderType properly - it's associative, not numerically indexed
        $revenueByOrderTypeRaw = $this->reportData['revenue_by_order_type'] ?? [];
        
        // Convert associative Collection to safe array format
        $revenueByOrderType = [];
        $orderTypeLabels = [];
        $orderTypeData = [];
        
        foreach ($revenueByOrderTypeRaw as $orderType => $data) {
            $revenueByOrderType[] = [
                'order_type' => $orderType,
                'net_revenue' => $data['net_revenue'] ?? 0,
                'count' => $data['count'] ?? 0
            ];
            
            // Prepare chart data
            $orderTypeLabels[] = match($orderType) {
                'dine_in' => 'Makan di Tempat',
                'take_away' => 'Bawa Pulang', 
                'online' => 'Online',
                default => $orderType
            };
            $orderTypeData[] = $data['net_revenue'] ?? 0;
        }
        
        // Daily sales trend chart data - using array operations
        $this->chartData = [
            'daily_sales' => [
                'labels' => array_column($dailySales, 'formatted_date'),
                'datasets' => [
                    [
                        'label' => 'Pendapatan Bersih',
                        'data' => array_column($dailySales, 'net_revenue'),
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Jumlah Transaksi',
                        'data' => array_column($dailySales, 'transaction_count'),
                        'borderColor' => 'rgb(16, 185, 129)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.1,
                        'yAxisID' => 'y1'
                    ]
                ]
            ],
            
            // Revenue by category pie chart - using array operations
            'category_revenue' => [
                'labels' => array_column($revenueByCategory, 'category_name'),
                'datasets' => [
                    [
                        'data' => array_column($revenueByCategory, 'total_revenue'),
                        'backgroundColor' => [
                            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', 
                            '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280'
                        ]
                    ]
                ]
            ],
            
            // FIXED: Order type distribution - using properly structured data
            'order_type_distribution' => [
                'labels' => $orderTypeLabels,
                'datasets' => [
                    [
                        'data' => $orderTypeData,
                        'backgroundColor' => ['#3B82F6', '#10B981', '#F59E0B']
                    ]
                ]
            ]
        ];

        // Dispatch chart data to frontend
        $this->dispatch('update-charts', $this->chartData);
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
            $filename = "Laporan_Penjualan_{$startDate}_sampai_{$endDate}.xlsx";

            // Use Excel export
            $export = new \App\Exports\SalesReportExport($this->reportData, $this->startDate, $this->endDate);
            
            return $export->download($filename);
            
        } catch (\Exception $e) {
            \Log::error('SalesReportComponent: Excel export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
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

    // Computed properties for easy access in view
    public function getTotalTransactionsProperty()
    {
        return $this->reportData['summary']['total_transactions'] ?? 0;
    }

    public function getTotalGrossRevenueProperty()
    {
        return $this->reportData['summary']['total_gross_revenue'] ?? 0;
    }

    public function getTotalNetRevenueProperty()
    {
        return $this->reportData['summary']['total_net_revenue'] ?? 0;
    }

    public function getTotalDiscountsProperty()
    {
        return $this->reportData['summary']['total_discounts'] ?? 0;
    }

    public function getTotalCommissionsProperty()
    {
        return $this->reportData['summary']['total_commissions'] ?? 0;
    }

    public function getNetProfitProperty()
    {
        return $this->reportData['summary']['net_profit'] ?? 0;
    }

    public function getAvgOrderValueProperty()
    {
        return $this->reportData['summary']['avg_order_value'] ?? 0;
    }

    // Helper methods for formatting
    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function formatPercentage($percentage)
    {
        return number_format($percentage, 1) . '%';
    }
}
