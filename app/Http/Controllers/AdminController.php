<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Carbon\Carbon;

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
     * Display store configuration management page.
     */
    public function storeConfig()
    {
        return view('admin.config.store');
    }
    
    /**
     * Display test receipt for print testing.
     */
    public function testReceipt()
    {
        // Get test data from request or use current store settings
        $testData = request()->all();
        
        // Generate dummy transaction data
        $dummyTransaction = (object) [
            'transaction_code' => 'TEST-' . strtoupper(substr(md5(time()), 0, 8)),
            'created_at' => Carbon::now(),
            'subtotal' => 25000,
            'total_discount' => 2500,
            'partner_commission' => 0,
            'final_total' => 22500,
            'payment_method' => 'cash',
            'notes' => 'Test Order - Sample Receipt',
            'user' => (object) [
                'name' => 'Test User (Admin)'
            ],
            'partner' => null,
            'items' => [
                (object) [
                    'product_name' => 'Sate Ayam (5 tusuk)',
                    'quantity' => 1,
                    'price' => 15000,
                    'total' => 15000
                ],
                (object) [
                    'product_name' => 'Nasi Putih',
                    'quantity' => 2,
                    'price' => 5000,
                    'total' => 10000
                ]
            ]
        ];
        
        // Generate test payment amount and kembalian
        $paymentAmount = 25000;
        $kembalian = max(0, $paymentAmount - $dummyTransaction->final_total);
        
        return view('receipt.test-print', [
            'transaction' => $dummyTransaction,
            'testData' => $testData,
            'paymentAmount' => $paymentAmount,
            'kembalian' => $kembalian
        ]);
    }
    
    /**
     * Display reports index/cards page.
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
