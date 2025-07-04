<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ReportService;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert;
use Livewire\Attributes\Rule;

class SalesReportComponent extends Component
{
    public $startDate;
    public $endDate;
    public $reportData = [];
    public $isLoading = false;
    public $selectedPeriod = 'today'; // today, yesterday, week, month, custom
    
    // Chart data for JavaScript
    public $chartData = [];
    
    protected $reportService;

    public function boot(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function mount()
    {
        // Initialize with today's data
        $this->setDatePeriod('today');
        $this->generateReport();
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

            // Generate report data
            $this->reportData = $this->reportService->getSalesReport($this->startDate, $this->endDate);
            
            // Prepare chart data
            $this->prepareChartData();
            
            Alert::success('Berhasil!', 'Laporan berhasil dibuat.');
            
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

    protected function prepareChartData()
    {
        if (empty($this->reportData)) {
            return;
        }

        // Daily sales trend chart data
        $dailySales = $this->reportData['daily_sales'] ?? [];
        $this->chartData = [
            'daily_sales' => [
                'labels' => collect($dailySales)->pluck('formatted_date')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Pendapatan Bersih',
                        'data' => collect($dailySales)->pluck('net_revenue')->toArray(),
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Jumlah Transaksi',
                        'data' => collect($dailySales)->pluck('transaction_count')->toArray(),
                        'borderColor' => 'rgb(16, 185, 129)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.1,
                        'yAxisID' => 'y1'
                    ]
                ]
            ],
            
            // Revenue by category pie chart
            'category_revenue' => [
                'labels' => collect($this->reportData['revenue_by_category'] ?? [])->pluck('category_name')->toArray(),
                'datasets' => [
                    [
                        'data' => collect($this->reportData['revenue_by_category'] ?? [])->pluck('total_revenue')->toArray(),
                        'backgroundColor' => [
                            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', 
                            '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280'
                        ]
                    ]
                ]
            ],
            
            // Order type distribution
            'order_type_distribution' => [
                'labels' => collect($this->reportData['revenue_by_order_type'] ?? [])->keys()->map(function($type) {
                    return match($type) {
                        'dine_in' => 'Makan di Tempat',
                        'take_away' => 'Bawa Pulang',
                        'online' => 'Online',
                        default => $type
                    };
                })->toArray(),
                'datasets' => [
                    [
                        'data' => collect($this->reportData['revenue_by_order_type'] ?? [])->pluck('net_revenue')->toArray(),
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
        try {
            if (empty($this->reportData)) {
                Alert::error('Error!', 'Tidak ada data untuk diekspor. Buat laporan terlebih dahulu.');
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
            Alert::error('Error!', 'Gagal mengekspor laporan: ' . $e->getMessage());
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
