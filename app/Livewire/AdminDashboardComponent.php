<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\DashboardService;
use Carbon\Carbon;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class AdminDashboardComponent extends Component
{
    public $title = 'Dashboard Admin - KasirBraga';
    
    // Date filtering
    public $startDate;
    public $endDate;
    public $selectedPeriod = 'today';
    public $customDateRange = false;
    
    // Dashboard data
    public $dashboardStats = [];
    public $isLoading = false;
    public $lastRefresh;
    public $autoRefresh = true;
    
    // Chart data for JavaScript
    public $chartData = [];
    
    protected $dashboardService;

    protected $listeners = [
        'transaction-completed' => 'refreshStats',
        'expense-added' => 'refreshStats',
        'refresh-dashboard' => 'refreshStats',
    ];

    public function boot(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function mount()
    {
        $this->lastRefresh = now()->format('H:i:s');
        $this->setDatePeriod('today');
        $this->loadDashboardStats();
    }

    public function render()
    {
        return view('livewire.admin-dashboard-component', [
            'dashboardStats' => $this->dashboardStats,
            'chartData' => $this->chartData
        ]);
    }

    public function setDatePeriod($period)
    {
        $this->selectedPeriod = $period;
        $this->customDateRange = false;

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
                $this->customDateRange = true;
                if (!$this->startDate) {
                    $this->startDate = Carbon::today()->format('Y-m-d');
                }
                if (!$this->endDate) {
                    $this->endDate = Carbon::today()->format('Y-m-d');
                }
                break;
        }

        $this->loadDashboardStats();
    }

    public function updatedStartDate()
    {
        if ($this->customDateRange) {
            $this->loadDashboardStats();
        }
    }

    public function updatedEndDate()
    {
        if ($this->customDateRange) {
            $this->loadDashboardStats();
        }
    }

    public function loadDashboardStats()
    {
        try {
            $this->isLoading = true;
            
            $this->dashboardStats = $this->dashboardService->getDashboardStats(
                $this->startDate, 
                $this->endDate
            );
            
            // Prepare chart data for JavaScript
            $this->chartData = [
                'dailySales' => $this->dashboardStats['charts']['daily_sales'],
                'hourlyPattern' => $this->dashboardStats['charts']['hourly_pattern'],
                'salesByOrderType' => $this->preparePieChartData($this->dashboardStats['sales']['by_order_type']),
                'salesByPayment' => $this->preparePieChartData($this->dashboardStats['sales']['by_payment_method']),
                'expensesByCategory' => $this->preparePieChartData($this->dashboardStats['expenses']['by_category']),
            ];
            
            $this->lastRefresh = now()->format('H:i:s');

        } catch (\Exception $e) {
            \Log::error('Dashboard stats loading error: ' . $e->getMessage());
            
            LivewireAlert::title('Error!')
                ->text('Gagal memuat statistik dashboard: ' . $e->getMessage())
                ->error()
                ->show();
        } finally {
            $this->isLoading = false;
        }
    }

    private function preparePieChartData($data)
    {
        $chartData = [
            'labels' => [],
            'data' => [],
            'backgroundColor' => [
                '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', 
                '#F7DC6F', '#BB8FCE', '#85C1E9', '#F8C471', '#82E0AA'
            ]
        ];

        $index = 0;
        foreach ($data as $key => $item) {
            if (is_array($item) && isset($item['total']) && $item['total'] > 0) {
                $chartData['labels'][] = $item['label'] ?? $key;
                $chartData['data'][] = $item['total'];
            }
            $index++;
        }

        return $chartData;
    }

    public function refreshStats()
    {
        $this->loadDashboardStats();
        
        LivewireAlert::title('Data Diperbarui!')
            ->text('Statistik dashboard telah diperbarui dengan data terbaru.')
            ->success()
            ->show();
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
        
        $status = $this->autoRefresh ? 'diaktifkan' : 'dinonaktifkan';
        LivewireAlert::title('Auto Refresh ' . ucfirst($status))
            ->text('Pembaruan otomatis dashboard telah ' . $status . '.')
            ->info()
            ->show();
    }

    public function exportData($type = 'overview')
    {
        try {
            // Logic untuk export data ke Excel/PDF
            // Untuk sekarang, hanya show notification
            LivewireAlert::title('Export Data')
                ->text('Fitur export akan segera tersedia.')
                ->info()
                ->show();
        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal mengekspor data: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    // Helper methods untuk formatting
    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function formatPercentage($value, $decimals = 1)
    {
        return number_format($value, $decimals) . '%';
    }

    public function getChangeClass($value)
    {
        if ($value > 0) {
            return 'text-success';
        } elseif ($value < 0) {
            return 'text-error';
        } else {
            return 'text-base-content';
        }
    }

    public function getChangeIcon($value)
    {
        if ($value > 0) {
            return 'trending-up';
        } elseif ($value < 0) {
            return 'trending-down';
        } else {
            return 'minus';
        }
    }

    public function getAlertClass($type)
    {
        return match($type) {
            'success' => 'alert-success',
            'warning' => 'alert-warning',
            'error' => 'alert-error',
            'info' => 'alert-info',
            default => 'alert-info'
        };
    }

    public function getAlertIcon($icon)
    {
        return match($icon) {
            'exclamation-triangle' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z',
            'x-circle' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            'trending-up' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            'trending-down' => 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        };
    }

    public function getPeriodLabel()
    {
        if (!isset($this->dashboardStats['period'])) {
            return 'Hari Ini';
        }

        $period = $this->dashboardStats['period'];
        
        if ($period['is_today']) {
            return 'Hari Ini';
        } elseif ($period['is_single_day']) {
            return Carbon::parse($period['start'])->format('d F Y');
        } else {
            return Carbon::parse($period['start'])->format('d M') . ' - ' . 
                   Carbon::parse($period['end'])->format('d M Y');
        }
    }
}
