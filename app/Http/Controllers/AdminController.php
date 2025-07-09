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
     * Display users management page.
     */
    public function users()
    {
        return view('admin.users.index');
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
     * Display test receipt for printing.
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
     * Android Test Print Response - Return JSON format for Bluetooth Print app
     */
    public function androidTestPrint()
    {
        // Get test data from request or use defaults
        $testData = request()->all();
        
        // Get store settings (use test data if provided)
        $storeSettings = \App\Models\StoreSetting::current();
        $storeName = $testData['store_name'] ?? $storeSettings->store_name;
        $storeAddress = $testData['store_address'] ?? $storeSettings->store_address;
        $storePhone = $testData['store_phone'] ?? $storeSettings->store_phone;
        $receiptHeader = $testData['receipt_header'] ?? $storeSettings->receipt_header;
        $receiptFooter = $testData['receipt_footer'] ?? $storeSettings->receipt_footer;
        $showReceiptLogo = isset($testData['show_receipt_logo']) ? filter_var($testData['show_receipt_logo'], FILTER_VALIDATE_BOOLEAN) : $storeSettings->show_receipt_logo;
        
        // Generate test data
        $paymentAmount = 25000;
        $finalTotal = 22500;
        $subtotal = 25000;
        $discount = 2500;
        $kembalian = max(0, $paymentAmount - $finalTotal);
        
        // Build JSON array for Bluetooth Print app (following the exact format from instructions)
        $a = array();
        
        // Store Logo (if enabled and exists) - Type 1 (image)
        if ($showReceiptLogo && $storeSettings->receipt_logo_path) {
            $objLogo = new \stdClass();
            $objLogo->type = 1; // image
            $objLogo->path = url($storeSettings->receipt_logo_path); // Full URL to image
            $objLogo->align = 1; // center
            array_push($a, $objLogo);
        }
        
        // Header - Store Name (Center, Bold, Large)
        $obj1 = new \stdClass();
        $obj1->type = 0; // text
        $obj1->content = $storeName;
        $obj1->bold = 1;
        $obj1->align = 1; // center
        $obj1->format = 2; // double Height + Width
        array_push($a, $obj1);
        
        // Store Address (Center)
        if ($storeAddress) {
            $obj2 = new \stdClass();
            $obj2->type = 0; // text
            $obj2->content = $storeAddress;
            $obj2->bold = 0;
            $obj2->align = 1; // center
            $obj2->format = 0; // normal
            array_push($a, $obj2);
        }
        
        // Store Phone (Center)
        if ($storePhone) {
            $obj3 = new \stdClass();
            $obj3->type = 0; // text
            $obj3->content = $storePhone;
            $obj3->bold = 0;
            $obj3->align = 1; // center
            $obj3->format = 0; // normal
            array_push($a, $obj3);
        }
        
        // Receipt Header (if set)
        if ($receiptHeader) {
            $objHeader = new \stdClass();
            $objHeader->type = 0; // text
            $objHeader->content = $receiptHeader;
            $objHeader->bold = 0;
            $objHeader->align = 1; // center
            $objHeader->format = 0; // normal
            array_push($a, $objHeader);
        }
        
        // Separator line
        $objSep1 = new \stdClass();
        $objSep1->type = 0; // text
        $objSep1->content = '================================';
        $objSep1->bold = 0;
        $objSep1->align = 1; // center
        $objSep1->format = 0; // normal
        array_push($a, $objSep1);
        
        // Transaction Info
        $objTrans = new \stdClass();
        $objTrans->type = 0; // text
        $objTrans->content = 'No: TEST-' . strtoupper(substr(md5(time()), 0, 8));
        $objTrans->bold = 0;
        $objTrans->align = 0; // left
        $objTrans->format = 0; // normal
        array_push($a, $objTrans);
        
        $objDate = new \stdClass();
        $objDate->type = 0; // text
        $objDate->content = 'Waktu: ' . now()->locale('id')->isoFormat('D MMM Y, HH:mm');
        $objDate->bold = 0;
        $objDate->align = 0; // left
        $objDate->format = 0; // normal
        array_push($a, $objDate);
        
        $objCashier = new \stdClass();
        $objCashier->type = 0; // text
        $objCashier->content = 'Kasir: Test User (Admin)';
        $objCashier->bold = 0;
        $objCashier->align = 0; // left
        $objCashier->format = 0; // normal
        array_push($a, $objCashier);
        
        // Separator line
        $objSep2 = new \stdClass();
        $objSep2->type = 0; // text
        $objSep2->content = '================================';
        $objSep2->bold = 0;
        $objSep2->align = 1; // center
        $objSep2->format = 0; // normal
        array_push($a, $objSep2);
        
        // Test Items
        $testItems = [
            ['name' => 'Sate Ayam (5 tusuk)', 'qty' => 1, 'price' => 15000],
            ['name' => 'Nasi Putih', 'qty' => 2, 'price' => 5000]
        ];
        
        foreach ($testItems as $item) {
            // Item name
            $objItem = new \stdClass();
            $objItem->type = 0; // text
            $objItem->content = $item['name'];
            $objItem->bold = 0;
            $objItem->align = 0; // left
            $objItem->format = 0; // normal
            array_push($a, $objItem);
            
            // Quantity and price in one line
            $itemTotal = $item['qty'] * $item['price'];
            $qtyPrice = $item['qty'] . ' x ' . number_format($item['price'], 0, ',', '.');
            $totalFormatted = 'Rp ' . number_format($itemTotal, 0, ',', '.');
            
            // Calculate spacing for alignment (assuming 32 char width)
            $lineContent = $qtyPrice . str_repeat(' ', max(1, 32 - strlen($qtyPrice) - strlen($totalFormatted))) . $totalFormatted;
            
            $objQtyPrice = new \stdClass();
            $objQtyPrice->type = 0; // text
            $objQtyPrice->content = $lineContent;
            $objQtyPrice->bold = 0;
            $objQtyPrice->align = 0; // left
            $objQtyPrice->format = 0; // normal
            array_push($a, $objQtyPrice);
        }
        
        // Separator line
        $objSep3 = new \stdClass();
        $objSep3->type = 0; // text
        $objSep3->content = '================================';
        $objSep3->bold = 0;
        $objSep3->align = 1; // center
        $objSep3->format = 0; // normal
        array_push($a, $objSep3);
        
        // Subtotal
        if ($subtotal != $finalTotal) {
            $subtotalLine = 'Subtotal:' . str_repeat(' ', max(1, 32 - 9 - strlen('Rp ' . number_format($subtotal, 0, ',', '.')))) . 'Rp ' . number_format($subtotal, 0, ',', '.');
            $objSubtotal = new \stdClass();
            $objSubtotal->type = 0; // text
            $objSubtotal->content = $subtotalLine;
            $objSubtotal->bold = 0;
            $objSubtotal->align = 0; // left
            $objSubtotal->format = 0; // normal
            array_push($a, $objSubtotal);
        }
        
        // Discount (if any)
        if ($discount > 0) {
            $discountLine = 'Diskon:' . str_repeat(' ', max(1, 32 - 7 - strlen('-Rp ' . number_format($discount, 0, ',', '.')))) . '-Rp ' . number_format($discount, 0, ',', '.');
            $objDiscount = new \stdClass();
            $objDiscount->type = 0; // text
            $objDiscount->content = $discountLine;
            $objDiscount->bold = 0;
            $objDiscount->align = 0; // left
            $objDiscount->format = 0; // normal
            array_push($a, $objDiscount);
        }
        
        // Total (Bold)
        $totalLine = 'TOTAL:' . str_repeat(' ', max(1, 32 - 6 - strlen('Rp ' . number_format($finalTotal, 0, ',', '.')))) . 'Rp ' . number_format($finalTotal, 0, ',', '.');
        $objTotal = new \stdClass();
        $objTotal->type = 0; // text
        $objTotal->content = $totalLine;
        $objTotal->bold = 1;
        $objTotal->align = 0; // left
        $objTotal->format = 0; // normal
        array_push($a, $objTotal);
        
        // Payment Method
        $paymentLine = 'Bayar (CASH):' . str_repeat(' ', max(1, 32 - 13 - strlen('Rp ' . number_format($paymentAmount, 0, ',', '.')))) . 'Rp ' . number_format($paymentAmount, 0, ',', '.');
        $objPayment = new \stdClass();
        $objPayment->type = 0; // text
        $objPayment->content = $paymentLine;
        $objPayment->bold = 0;
        $objPayment->align = 0; // left
        $objPayment->format = 0; // normal
        array_push($a, $objPayment);
        
        // Change
        if ($kembalian > 0) {
            $changeLine = 'Kembalian:' . str_repeat(' ', max(1, 32 - 10 - strlen('Rp ' . number_format($kembalian, 0, ',', '.')))) . 'Rp ' . number_format($kembalian, 0, ',', '.');
            $objChange = new \stdClass();
            $objChange->type = 0; // text
            $objChange->content = $changeLine;
            $objChange->bold = 0;
            $objChange->align = 0; // left
            $objChange->format = 0; // normal
            array_push($a, $objChange);
        }
        
        // Test message
        $objTest = new \stdClass();
        $objTest->type = 0; // text
        $objTest->content = 'Catatan: Test Print - Preview Only';
        $objTest->bold = 0;
        $objTest->align = 0; // left
        $objTest->format = 0; // normal
        array_push($a, $objTest);
        
        // Empty line
        $objEmpty1 = new \stdClass();
        $objEmpty1->type = 0; // text
        $objEmpty1->content = ' ';
        $objEmpty1->bold = 0;
        $objEmpty1->align = 0; // left
        $objEmpty1->format = 0; // normal
        array_push($a, $objEmpty1);
        
        // Receipt Footer (if set)
        if ($receiptFooter) {
            $objFooter = new \stdClass();
            $objFooter->type = 0; // text
            $objFooter->content = $receiptFooter;
            $objFooter->bold = 0;
            $objFooter->align = 1; // center
            $objFooter->format = 0; // normal
            array_push($a, $objFooter);
        }
        
        // Thank you message
        $objThank = new \stdClass();
        $objThank->type = 0; // text
        $objThank->content = 'Terima Kasih';
        $objThank->bold = 1;
        $objThank->align = 1; // center
        $objThank->format = 0; // normal
        array_push($a, $objThank);
        
        // End separator
        $objEnd = new \stdClass();
        $objEnd->type = 0; // text
        $objEnd->content = '---oOo---';
        $objEnd->bold = 0;
        $objEnd->align = 1; // center
        $objEnd->format = 0; // normal
        array_push($a, $objEnd);
        
        // Log the JSON output for debugging
        \Log::info('Android Test Print JSON Response', [
            'json_length' => count($a),
            'sample_structure' => array_slice($a, 0, 3) // Log first 3 items for verification
        ]);
        
        // Return JSON response exactly as specified in the instructions
        // Following the exact format: echo json_encode($a,JSON_FORCE_OBJECT);
        $jsonContent = json_encode($a, JSON_FORCE_OBJECT);
        
        return response($jsonContent, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Length', strlen($jsonContent));
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
