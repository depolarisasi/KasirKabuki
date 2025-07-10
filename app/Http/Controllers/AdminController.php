<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use App\Services\DashboardService;
use Carbon\Carbon;

class AdminController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the enhanced admin dashboard with statistics.
     */
    public function dashboard()
    {
        // Get quick stats for today as default
        $quickStats = $this->dashboardService->getQuickStats('today');
        
        return view('admin.dashboard', [
            'quickStats' => $quickStats,
            'lastUpdated' => now()->format('H:i:s')
        ]);
    }
    
    /**
     * Display categories management page.
     */
    public function categories()
    {
        return view('admin.categories.index');
    }
    
    /**
     * Display products management page.
     */
    public function products()
    {
        return view('admin.products.index');
    }
    
    /**
     * Display partners management page.
     */
    public function partners()
    {
        return view('admin.partners.index');
    }
    
    /**
     * Display discounts management page.
     */
    public function discounts()
    {
        return view('admin.discounts.index');
    }
    
    /**
     * Display users management page.
     */
    public function users()
    {
        return view('admin.users.index');
    }
    
    /**
     * Display backdating sales page.
     */
    public function backdatingSales()
    {
        return view('admin.backdating-sales.index');
    }
    
    /**
     * Display store configuration page.
     */
    public function config()
    {
        return view('admin.config.index');
    }
    
    /**
     * Display store configuration management page.
     */
    public function storeConfig()
    {
        return view('admin.config.store');
    }

    /**
     * Display audit trail configuration page.
     */
    public function auditTrailConfig()
    {
        return view('admin.config.audit-trail');
    }

    /**
     * Display stock sate configuration page.
     */
    public function stockSateConfig()
    {
        return view('admin.config.stock-sate');
    }

    /**
     * Display reports index page.
     */
    public function reports()
    {
        return view('admin.reports.index');
    }

    /**
     * Display sales report page.
     */
    public function salesReport()
    {
        return view('admin.reports.sales');
    }

    /**
     * Display expenses report page.
     */
    public function expensesReport()
    {
        return view('admin.reports.expenses');
    }
    
    /**
     * Display test receipt for printing.
     */
    public function testReceipt()
    {
        return view('admin.test-receipt');
    }

    /**
     * API endpoint for dashboard statistics
     */
    public function getDashboardStats($period = 'today')
    {
        try {
            $stats = $this->dashboardService->getQuickStats($period);
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch dashboard stats'], 500);
        }
    }

    /**
     * API endpoint for custom date range statistics
     */
    public function getCustomStats($startDate, $endDate)
    {
        try {
            $stats = $this->dashboardService->getDashboardStats($startDate, $endDate);
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch custom stats'], 500);
        }
    }
}
