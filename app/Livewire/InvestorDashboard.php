<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Expense;
use Carbon\Carbon;

class InvestorDashboard extends Component
{
    public $filterStartDate;
    public $filterEndDate;
    
    // Quick stats
    public $todayRevenue = 0;
    public $monthRevenue = 0;
    public $todayExpenses = 0;
    public $monthExpenses = 0;
    public $monthProfit = 0;
    
    // Chart data
    public $salesChartData = [];
    public $expensesChartData = [];
    
    // Recent data
    public $recentTransactions = [];
    public $recentExpenses = [];
    
    public function mount()
    {
        // Set default filter to current month
        $this->filterStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->filterEndDate = Carbon::now()->format('Y-m-d');
        
        $this->loadDashboardData();
    }
    
    public function render()
    {
        return view('livewire.investor-dashboard')->layout('layouts.investor');
    }
    
    public function updatedFilterStartDate()
    {
        $this->loadDashboardData();
    }
    
    public function updatedFilterEndDate()
    {
        $this->loadDashboardData();
    }
    
    private function loadDashboardData()
    {
        $this->calculateQuickStats();
        $this->loadChartData();
        $this->loadRecentData();
    }
    
    private function calculateQuickStats()
    {
        // Today's revenue and expenses
        $this->todayRevenue = Transaction::whereDate('created_at', Carbon::today())
                                        ->where('status', 'completed')
                                        ->sum('final_total');
                                        
        $this->todayExpenses = Expense::whereDate('date', Carbon::today())
                                     ->sum('amount');
        
        // This month's revenue and expenses  
        $this->monthRevenue = Transaction::whereMonth('created_at', Carbon::now()->month)
                                        ->whereYear('created_at', Carbon::now()->year)
                                        ->where('status', 'completed')
                                        ->sum('final_total');
                                        
        $this->monthExpenses = Expense::whereMonth('date', Carbon::now()->month)
                                     ->whereYear('date', Carbon::now()->year)
                                     ->sum('amount');
        
        // Calculate month profit
        $this->monthProfit = $this->monthRevenue - $this->monthExpenses;
    }
    
    private function loadChartData()
    {
        $startDate = Carbon::parse($this->filterStartDate);
        $endDate = Carbon::parse($this->filterEndDate);
        
        // Sales chart data
        $salesData = Transaction::selectRaw('DATE(created_at) as date, SUM(final_total) as total')
                               ->whereBetween('created_at', [$startDate, $endDate])
                               ->where('status', 'completed')
                               ->groupBy('date')
                               ->orderBy('date')
                               ->get();
        
        $this->salesChartData = $salesData->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('d M'),
                'value' => (float) $item->total
            ];
        })->toArray();
        
        // Expenses chart data
        $expensesData = Expense::selectRaw('DATE(date) as date, SUM(amount) as total')
                              ->whereBetween('date', [$startDate, $endDate])
                              ->groupBy('date')
                              ->orderBy('date')
                              ->get();
        
        $this->expensesChartData = $expensesData->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('d M'),
                'value' => (float) $item->total
            ];
        })->toArray();
    }
    
    private function loadRecentData()
    {
        // Recent 5 transactions
        $this->recentTransactions = Transaction::with(['user', 'partner'])
                                              ->where('status', 'completed')
                                              ->latest()
                                              ->take(5)
                                              ->get()
                                              ->toArray();
        
        // Recent 5 expenses
        $this->recentExpenses = Expense::with(['user'])
                                      ->latest('date')
                                      ->latest('created_at')
                                      ->take(5)
                                      ->get()
                                      ->toArray();
    }
    
    public function setQuickFilter($period)
    {
        switch ($period) {
            case 'today':
                $this->filterStartDate = Carbon::today()->format('Y-m-d');
                $this->filterEndDate = Carbon::today()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->filterStartDate = Carbon::yesterday()->format('Y-m-d');
                $this->filterEndDate = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'this_week':
                $this->filterStartDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->filterEndDate = Carbon::now()->format('Y-m-d');
                break;
            case 'this_month':
                $this->filterStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->filterEndDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last_month':
                $this->filterStartDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->filterEndDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
        }
        
        $this->loadDashboardData();
    }
}
