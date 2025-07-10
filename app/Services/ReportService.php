<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Expense;
use App\Models\StockLog;
use App\Models\Product;
use App\Models\Category;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get comprehensive sales report for date range
     */
    public function getSalesReport($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();

        // Base query for completed transactions in date range with optimized eager loading
        $transactions = Transaction::completed()
            ->betweenDates($startDate, $endDate)
            ->with(['user', 'partner', 'items.product.category']);

        $allTransactions = $transactions->get();

        // Basic metrics
        $totalTransactions = $allTransactions->count();
        $totalGrossRevenue = $allTransactions->sum('subtotal');
        $totalDiscounts = $allTransactions->sum('total_discount');
        $totalCommissions = $allTransactions->sum('partner_commission');
        $totalNetRevenue = $allTransactions->sum('final_total');

        // Get expenses for the same period
        $totalExpenses = Expense::betweenDates($startDate, $endDate)->sum('amount');
        
        // Net profit after expenses
        $netProfit = $totalNetRevenue - $totalExpenses;

        // Revenue by order type
        $revenueByOrderType = $allTransactions->groupBy('order_type')->map(function ($transactions) {
            return [
                'count' => $transactions->count(),
                'gross_revenue' => $transactions->sum('subtotal'),
                'net_revenue' => $transactions->sum('final_total'),
                'avg_order_value' => $transactions->count() > 0 ? $transactions->sum('final_total') / $transactions->count() : 0
            ];
        })->toArray(); // FIXED: Convert Collection to array to prevent modification issues

        // Revenue by payment method
        $revenueByPaymentMethod = $allTransactions->groupBy('payment_method')->map(function ($transactions) {
            return [
                'count' => $transactions->count(),
                'total_revenue' => $transactions->sum('final_total'),
                'percentage' => 0 // Will be calculated later
            ];
        })->toArray(); // FIXED: Convert Collection to array before modification

        // Calculate percentages for payment methods
        if ($totalNetRevenue > 0) {
            foreach ($revenueByPaymentMethod as $method => $data) {
                $revenueByPaymentMethod[$method]['percentage'] = ($data['total_revenue'] / $totalNetRevenue) * 100;
            }
        }

        // Top selling products
        $topProducts = $this->getTopSellingProducts($startDate, $endDate, 10);

        // Revenue by category
        $revenueByCategory = $this->getRevenueByCategory($startDate, $endDate);

        // Daily sales trend
        $dailySales = $this->getDailySalesTrend($startDate, $endDate);

        // Partner performance
        $partnerPerformance = $this->getPartnerPerformance($startDate, $endDate);

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days_count' => $startDate->diffInDays($endDate) + 1
            ],
            'summary' => [
                'total_transactions' => $totalTransactions,
                'total_gross_revenue' => $totalGrossRevenue,
                'total_discounts' => $totalDiscounts,
                'total_commissions' => $totalCommissions,
                'total_net_revenue' => $totalNetRevenue,
                'total_expenses' => $totalExpenses,
                'net_profit' => $netProfit,
                'avg_order_value' => $totalTransactions > 0 ? $totalNetRevenue / $totalTransactions : 0
            ],
            'revenue_by_order_type' => $revenueByOrderType,
            'revenue_by_payment_method' => $revenueByPaymentMethod,
            'top_products' => $topProducts,
            'revenue_by_category' => $revenueByCategory,
            'daily_sales' => $dailySales,
            'partner_performance' => $partnerPerformance
        ];
    }

    /**
     * Get top selling products for period
     */
    public function getTopSellingProducts($startDate, $endDate, $limit = 10)
    {
        return TransactionItem::select([
            'product_id',
            'product_name',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(total) as total_revenue'),
            DB::raw('COUNT(DISTINCT transaction_id) as order_count')
        ])
        ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
            $query->completed()->betweenDates($startDate, $endDate);
        })
        ->with('product.category')
        ->groupBy('product_id', 'product_name')
        ->orderBy('total_quantity', 'desc')
        ->limit($limit)
        ->get()
        ->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'category_name' => $item->product ? $item->product->category->name : 'Unknown',
                'total_quantity' => $item->total_quantity,
                'total_revenue' => $item->total_revenue,
                'order_count' => $item->order_count,
                'avg_quantity_per_order' => $item->order_count > 0 ? $item->total_quantity / $item->order_count : 0
            ];
        })->toArray(); // FIXED: Convert Collection to array
    }

    /**
     * Get revenue breakdown by category
     */
    public function getRevenueByCategory($startDate, $endDate)
    {
        $categoryRevenue = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->select([
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_items.total) as total_revenue'),
                DB::raw('COUNT(DISTINCT transactions.id) as order_count')
            ])
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        $totalRevenue = $categoryRevenue->sum('total_revenue');

        return $categoryRevenue->map(function ($category) use ($totalRevenue) {
            return [
                'category_id' => $category->category_id,
                'category_name' => $category->category_name,
                'total_quantity' => $category->total_quantity,
                'total_revenue' => $category->total_revenue,
                'order_count' => $category->order_count,
                'percentage' => $totalRevenue > 0 ? ($category->total_revenue / $totalRevenue) * 100 : 0
            ];
        })->toArray(); // FIXED: Convert Collection to array
    }

    /**
     * Get daily sales trend for chart
     */
    public function getDailySalesTrend($startDate, $endDate)
    {
        $dailySales = Transaction::completed()
            ->betweenDates($startDate, $endDate)
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(subtotal) as gross_revenue'),
                DB::raw('SUM(total_discount) as total_discount'),
                DB::raw('SUM(final_total) as net_revenue')
            ])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Fill missing dates with zero values
        $period = Carbon::parse($startDate)->startOfDay();
        $endPeriod = Carbon::parse($endDate)->startOfDay();
        $dateRange = [];
        
        while ($period <= $endPeriod) {
            $dateStr = $period->format('Y-m-d');
            $existingData = $dailySales->firstWhere('date', $dateStr);
            
            $dateRange[] = [
                'date' => $dateStr,
                'formatted_date' => $period->format('d/m'),
                'transaction_count' => $existingData ? $existingData->transaction_count : 0,
                'gross_revenue' => $existingData ? $existingData->gross_revenue : 0,
                'total_discount' => $existingData ? $existingData->total_discount : 0,
                'net_revenue' => $existingData ? $existingData->net_revenue : 0
            ];
            
            $period->addDay();
        }

        return $dateRange;
    }

    /**
     * Get partner performance analytics
     */
    public function getPartnerPerformance($startDate, $endDate)
    {
        return Transaction::completed()
            ->whereNotNull('partner_id')
            ->betweenDates($startDate, $endDate)
            ->with('partner')
            ->select([
                'partner_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(subtotal) as gross_revenue'),
                DB::raw('SUM(partner_commission) as total_commission'),
                DB::raw('SUM(final_total) as net_revenue')
            ])
            ->groupBy('partner_id')
            ->orderBy('gross_revenue', 'desc')
            ->get()
            ->map(function ($transaction) {
                $partner = $transaction->partner;
                return [
                    'partner_id' => $transaction->partner_id,
                    'partner_name' => $partner ? $partner->name : 'Unknown',
                    'commission_rate' => $partner ? $partner->commission_rate : 0,
                    'order_count' => $transaction->order_count,
                    'gross_revenue' => $transaction->gross_revenue,
                    'total_commission' => $transaction->total_commission,
                    'net_revenue' => $transaction->net_revenue,
                    'avg_order_value' => $transaction->order_count > 0 ? $transaction->gross_revenue / $transaction->order_count : 0
                ];
            })->toArray(); // FIXED: Convert Collection to array
    }

    /**
     * Get expense report for period
     */
    public function getExpenseReport($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();

        $expenses = Expense::betweenDates($startDate, $endDate)
            ->with('user')
            ->orderBy('date', 'desc')
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $expenseCount = $expenses->count();

        // Group by date
        $expensesByDate = $expenses->groupBy(function ($expense) {
            return $expense->date->format('Y-m-d');
        })->map(function ($dayExpenses) {
            return [
                'count' => $dayExpenses->count(),
                'total' => $dayExpenses->sum('amount'),
                'expenses' => $dayExpenses->toArray()
            ];
        });

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_expenses' => $totalExpenses,
                'expense_count' => $expenseCount,
                'average_expense' => $expenseCount > 0 ? $totalExpenses / $expenseCount : 0
            ],
            'expenses_by_date' => $expensesByDate,
            'all_expenses' => $expenses
        ];
    }

    /**
     * Get daily stock reconciliation - SIMPLIFIED for sate products only
     */
    public function getDailyStockReconciliation($date = null)
    {
        $date = $date ?: now()->format('Y-m-d');
        
        // Get all sate products only
        $sateProducts = Product::whereNotNull('jenis_sate')
                              ->whereNotNull('quantity_effect')
                              ->with('category')
                              ->get();
        
        $stockSateService = app(StockSateService::class);
        $reconciliation = [];
        
        foreach ($sateProducts as $product) {
            $stockSateEntry = $stockSateService->getStockForDate($product->jenis_sate, $date);
            
            $initialStock = $stockSateEntry ? $stockSateEntry->stok_awal : 0;
            $soldStock = $stockSateEntry ? $stockSateEntry->stok_terjual : 0;
            $remainingStock = $initialStock - $soldStock;
            
            // Convert to product units
            $initialProductUnits = floor($initialStock / $product->quantity_effect);
            $soldProductUnits = floor($soldStock / $product->quantity_effect);
            $remainingProductUnits = floor($remainingStock / $product->quantity_effect);
            
            $reconciliation[] = [
                'product' => $product,
                'jenis_sate' => $product->jenis_sate,
                'quantity_effect' => $product->quantity_effect,
                'initial_stock_sate' => $initialStock,
                'sold_stock_sate' => $soldStock,
                'remaining_stock_sate' => $remainingStock,
                'initial_product_units' => $initialProductUnits,
                'sold_product_units' => $soldProductUnits,
                'remaining_product_units' => $remainingProductUnits,
                'stock_entry' => $stockSateEntry
            ];
        }
        
        return [
            'date' => $date,
            'sate_products' => $reconciliation,
            'summary' => [
                'total_sate_products' => count($reconciliation),
                'products_with_stock' => collect($reconciliation)->where('initial_stock_sate', '>', 0)->count(),
                'products_sold_out' => collect($reconciliation)->where('remaining_stock_sate', '<=', 0)->count(),
            ]
        ];
    }

    /**
     * Format currency for display
     */
    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Get formatted summary for dashboard
     */
    public function getDashboardSummary($date = null)
    {
        $date = $date ?: Carbon::today();
        
        $salesReport = $this->getSalesReport($date, $date);
        $expenseReport = $this->getExpenseReport($date, $date);
        
        return [
            'today_sales' => [
                'transaction_count' => $salesReport['summary']['total_transactions'],
                'gross_revenue' => $salesReport['summary']['total_gross_revenue'],
                'net_revenue' => $salesReport['summary']['total_net_revenue'],
                'formatted_net_revenue' => $this->formatCurrency($salesReport['summary']['total_net_revenue'])
            ],
            'today_expenses' => [
                'expense_count' => $expenseReport['summary']['expense_count'],
                'total_expenses' => $expenseReport['summary']['total_expenses'],
                'formatted_total_expenses' => $this->formatCurrency($expenseReport['summary']['total_expenses'])
            ],
            'net_profit' => [
                'amount' => $salesReport['summary']['net_profit'],
                'formatted_amount' => $this->formatCurrency($salesReport['summary']['net_profit'])
            ]
        ];
    }
} 