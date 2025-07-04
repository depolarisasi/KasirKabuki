<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        return view('admin.dashboard');
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
     * Display store configuration page.
     */
    public function config()
    {
        return view('admin.config.index');
    }
    
    /**
     * Display sales report page.
     */
    public function salesReport()
    {
        return view('admin.reports.sales');
    }
    
    /**
     * Display stock report page.
     */
    public function stockReport()
    {
        return view('admin.reports.stock');
    }
    
    /**
     * Display expenses report page.
     */
    public function expensesReport()
    {
        return view('admin.reports.expenses');
    }
}
