<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Expense;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Category;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get comprehensive dashboard statistics for admin
     */
    public function getDashboardStats($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();

        return [
            'overview' => $this->getOverviewStats($startDate, $endDate),
            'sales' => $this->getSalesStats($startDate, $endDate),
            'expenses' => $this->getExpenseStats($startDate, $endDate),
            'products' => $this->getProductStats($startDate, $endDate),
            'charts' => $this->getChartsData($startDate, $endDate),
            'alerts' => $this->getSystemAlerts(),
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'is_today' => $startDate->isToday() && $endDate->isToday(),
                'is_single_day' => $startDate->isSameDay($endDate),
            ]
        ];
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats($startDate, $endDate)
    {
        $totalSales = Transaction::completed()
            ->betweenDates($startDate, $endDate)
            ->sum('final_total');

        $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $totalTransactions = Transaction::completed()
            ->betweenDates($startDate, $endDate)
            ->count();

        $netProfit = $totalSales - $totalExpenses;

        // Compare with previous period
        $periodDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = $startDate->copy()->subDays($periodDays);
        $prevEndDate = $startDate->copy()->subDay();

        $prevSales = Transaction::completed()
            ->betweenDates($prevStartDate, $prevEndDate)
            ->sum('final_total');

        $prevExpenses = Expense::whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('amount');

        return [
            'total_sales' => $totalSales,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'total_transactions' => $totalTransactions,
            'profit_margin' => $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0,
            'avg_transaction' => $totalTransactions > 0 ? $totalSales / $totalTransactions : 0,
            'comparison' => [
                'sales_change' => $prevSales > 0 ? (($totalSales - $prevSales) / $prevSales) * 100 : 0,
                'expenses_change' => $prevExpenses > 0 ? (($totalExpenses - $prevExpenses) / $prevExpenses) * 100 : 0,
                'profit_change' => $prevSales > 0 && $prevExpenses > 0 ? 
                    ((($totalSales - $totalExpenses) - ($prevSales - $prevExpenses)) / ($prevSales - $prevExpenses)) * 100 : 0,
            ]
        ];
    }

    /**
     * Get detailed sales statistics
     */
    private function getSalesStats($startDate, $endDate)
    {
        // Sales by order type
        $salesByOrderType = Transaction::completed()
            ->betweenDates($startDate, $endDate)
            ->select('order_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(final_total) as total'))
            ->groupBy('order_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->order_type => [
                    'count' => $item->count,
                    'total' => $item->total,
                    'label' => match($item->order_type) {
                        'dine_in' => 'Makan di Tempat',
                        'take_away' => 'Bawa Pulang',
                        'online' => 'Online',
                        default => $item->order_type
                    }
                ]];
            });

        // Sales by payment method
        $salesByPayment = Transaction::completed()
            ->betweenDates($startDate, $endDate)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(final_total) as total'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->payment_method => [
                    'count' => $item->count,
                    'total' => $item->total,
                    'label' => match($item->payment_method) {
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'aplikasi' => 'Aplikasi',
                        default => $item->payment_method
                    }
                ]];
            });

        // Top selling products
        $topProducts = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->select(
                'products.name',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_items.total) as total_amount')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        // Partner commissions
        $partnerCommissions = Transaction::completed()
            ->betweenDates($startDate, $endDate)
            ->whereNotNull('partner_id')
            ->with('partner')
            ->get()
            ->groupBy('partner_id')
            ->map(function ($transactions) {
                $partner = $transactions->first()->partner;
                return [
                    'partner_name' => $partner->name,
                    'total_commission' => $transactions->sum('partner_commission'),
                    'transaction_count' => $transactions->count(),
                    'total_sales' => $transactions->sum('final_total'),
                ];
            })
            ->values();

        return [
            'by_order_type' => $salesByOrderType,
            'by_payment_method' => $salesByPayment,
            'top_products' => $topProducts,
            'partner_commissions' => $partnerCommissions,
            'total_discounts' => Transaction::completed()
                ->betweenDates($startDate, $endDate)
                ->sum('total_discount'),
        ];
    }

    /**
     * Get expense statistics
     */
    private function getExpenseStats($startDate, $endDate)
    {
        // Expenses by category
        $expensesByCategory = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->select('category', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'count' => $item->count,
                    'total' => $item->total,
                    'percentage' => 0 // Will be calculated after getting total
                ];
            });

        $totalExpenses = $expensesByCategory->sum('total');
        $expensesByCategory = $expensesByCategory->map(function ($item) use ($totalExpenses) {
            $item['percentage'] = $totalExpenses > 0 ? ($item['total'] / $totalExpenses) * 100 : 0;
            return $item;
        });

        // Recent expenses
        $recentExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'by_category' => $expensesByCategory,
            'recent_expenses' => $recentExpenses,
            'total_amount' => $totalExpenses,
            'avg_expense' => $expensesByCategory->sum('count') > 0 ? 
                $totalExpenses / $expensesByCategory->sum('count') : 0,
        ];
    }

    /**
     * Get product performance statistics
     */
    private function getProductStats($startDate, $endDate)
    {
        // Get all products first
        $allProducts = Product::with('category')->get();
        
        // Low stock alerts - using StockLog system
        $lowStockProducts = collect();
        $outOfStockProducts = collect();
        $activeProductsCount = 0;
        
        foreach ($allProducts as $product) {
            $currentStock = $product->getCurrentStock();
            $minStock = $product->min_stock ?? 0;
            
            // Check low stock
            if ($currentStock > 0 && $currentStock <= $minStock) {
                $lowStockProducts->push($product);
            }
            
            // Check out of stock (exclude sate products as they don't require stock)
            if ($currentStock <= 0 && $product->type !== 'sate') {
                $outOfStockProducts->push($product);
            }
            
            // Count active products
            if ($currentStock > 0 || $product->type === 'sate') {
                $activeProductsCount++;
            }
        }

        // Products with no sales
        $productsWithSales = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->pluck('transaction_items.product_id')
            ->unique();

        $productsWithoutSales = Product::whereNotIn('id', $productsWithSales)
            ->with('category')
            ->get();

        return [
            'low_stock' => $lowStockProducts,
            'out_of_stock' => $outOfStockProducts,
            'no_sales' => $productsWithoutSales,
            'total_products' => Product::count(),
            'active_products' => $activeProductsCount,
        ];
    }

    /**
     * Get charts data for visualization
     */
    private function getChartsData($startDate, $endDate)
    {
        // Daily sales chart (last 30 days or period)
        $days = max(30, $startDate->diffInDays($endDate) + 1);
        $chartStartDate = $endDate->copy()->subDays($days - 1)->startOfDay();
        
        $dailySales = Transaction::completed()
            ->whereBetween('created_at', [$chartStartDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(final_total) as total_sales'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill missing dates with zero values
        $salesChartData = [];
        for ($date = $chartStartDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $salesChartData[] = [
                'date' => $dateStr,
                'sales' => $dailySales->get($dateStr)?->total_sales ?? 0,
                'transactions' => $dailySales->get($dateStr)?->transaction_count ?? 0,
                'label' => $date->format('d M'),
            ];
        }

        // Hourly sales pattern (current period)
        $hourlySales = Transaction::completed()
            ->betweenDates($startDate, $endDate)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('SUM(final_total) as total_sales'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $hourlyChartData = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourlyChartData[] = [
                'hour' => $hour,
                'sales' => $hourlySales->get($hour)?->total_sales ?? 0,
                'transactions' => $hourlySales->get($hour)?->transaction_count ?? 0,
                'label' => sprintf('%02d:00', $hour),
            ];
        }

        return [
            'daily_sales' => $salesChartData,
            'hourly_pattern' => $hourlyChartData,
        ];
    }

    /**
     * Get system alerts and notifications
     */
    private function getSystemAlerts()
    {
        $alerts = [];

        // Count low stock and out of stock products using StockLog system
        $allProducts = Product::with('category')->get();
        $lowStockCount = 0;
        $outOfStockCount = 0;
        
        foreach ($allProducts as $product) {
            $currentStock = $product->getCurrentStock();
            $minStock = $product->min_stock ?? 0;
            
            // Count low stock
            if ($currentStock > 0 && $currentStock <= $minStock) {
                $lowStockCount++;
            }
            
            // Count out of stock (exclude sate products)
            if ($currentStock <= 0 && $product->type !== 'sate') {
                $outOfStockCount++;
            }
        }

        if ($lowStockCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'title' => 'Stok Menipis',
                'message' => "{$lowStockCount} produk memiliki stok di bawah minimum",
                'action_url' => route('staf.stock-sate'),
                'action_text' => 'Lihat Stok',
            ];
        }

        if ($outOfStockCount > 0) {
            $alerts[] = [
                'type' => 'error',
                'icon' => 'x-circle',
                'title' => 'Stok Habis',
                'message' => "{$outOfStockCount} produk kehabisan stok",
                'action_url' => route('staf.stock-sate'),
                'action_text' => 'Lihat Stok',
            ];
        }

        // Today's sales performance
        $todaySales = Transaction::completed()->today()->sum('final_total');
        $yesterdaySales = Transaction::completed()
            ->whereDate('created_at', Carbon::yesterday())
            ->sum('final_total');

        if ($yesterdaySales > 0) {
            $salesChange = (($todaySales - $yesterdaySales) / $yesterdaySales) * 100;
            
            if ($salesChange < -20) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'trending-down',
                    'title' => 'Penjualan Menurun',
                    'message' => sprintf('Penjualan hari ini turun %.1f%% dari kemarin', abs($salesChange)),
                    'action_url' => route('admin.reports.sales'),
                    'action_text' => 'Lihat Laporan',
                ];
            } elseif ($salesChange > 20) {
                $alerts[] = [
                    'type' => 'success',
                    'icon' => 'trending-up',
                    'title' => 'Penjualan Meningkat',
                    'message' => sprintf('Penjualan hari ini naik %.1f%% dari kemarin', $salesChange),
                    'action_url' => route('admin.reports.sales'),
                    'action_text' => 'Lihat Laporan',
                ];
            }
        }

        return $alerts;
    }

    /**
     * Get quick stats for specific period
     */
    public function getQuickStats($period = 'today')
    {
        switch ($period) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek()->startOfDay();
                $endDate = Carbon::now()->endOfWeek()->endOfDay();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth()->startOfDay();
                $endDate = Carbon::now()->endOfMonth()->endOfDay();
                break;
            default:
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
        }

        return $this->getDashboardStats($startDate, $endDate);
    }
} 