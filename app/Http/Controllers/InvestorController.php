<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvestorController extends Controller
{
    /**
     * Display the investor dashboard.
     */
    public function dashboard()
    {
        return view('investor.dashboard');
    }

    /**
     * Display sales report for investor (read-only).
     */
    public function salesReport()
    {
        return view('investor.reports.sales');
    }

    /**
     * Display expenses report for investor (read-only).
     */
    public function expensesReport()
    {
        return view('investor.reports.expenses');
    }
}
