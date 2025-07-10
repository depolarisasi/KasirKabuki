<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class StafController extends Controller
{
    /**
     * Display the cashier interface.
     */
    public function cashier()
    {
        return view('staf.cashier.index');
    }
    
    /**
     * Display stock management page.
     */
    public function stock()
    {
        return view('staf.stock.index');
    }
    
    /**
     * Display stock sate management page.
     */
    public function stockSate()
    {
        return view('staf.stock-sate.index');
    }
    
    /**
     * Display expenses management page.
     */
    public function expenses()
    {
        return view('staf.expenses.index');
    }
    
    /**
     * Display transaction detail page.
     */
    public function transactionDetail(Transaction $transaction)
    {
        // Ensure user can only view their own transactions or admin can view all
        if (!auth()->user()->hasRole('admin') && $transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to transaction.');
        }
        
        // Load transaction with relationships
        $transaction->load(['user', 'partner', 'items.product']);
        
        return view('staf.transactions.show', compact('transaction'));
    }
    
    /**
     * Display receipt print page.
     */
    public function receiptPrint(Transaction $transaction)
    { 
        
        // Load transaction with relationships
        $transaction->load(['user', 'partner', 'items.product']);
        
        return view('receipt.print', compact('transaction'));
    }

    /**
     * Android Bluetooth Print Response - Return JSON format for Bluetooth Print app
     */
    public function androidPrintResponse(Transaction $transaction)
    {
        try {
            // Log request details
            \Log::info('Android Print Request Started', [
                'transaction_id' => $transaction->id,
                'request_url' => request()->fullUrl(),
                'user_agent' => request()->header('User-Agent'),
                'request_time' => now()
            ]);

            // Load transaction with relationships
            $transaction->load(['user', 'partner', 'items.product']);
            
            // Get store settings
            $storeSettings = \App\Models\StoreSetting::current();
            
            // Validate required data
            if (!$storeSettings) {
                \Log::error('Store settings not found');
                return response()->json(['error' => 'Store settings not configured'], 500);
            }
            
            if (!$transaction->user) {
                \Log::error('Transaction user not found', ['transaction_id' => $transaction->id]);
                return response()->json(['error' => 'Transaction user not found'], 500);
            }
            
            // Get payment amount from request
            $paymentAmount = request()->input('payment_amount', $transaction->final_total);
            $kembalian = $transaction->payment_method === 'qris' ? 0 : max(0, $paymentAmount - $transaction->final_total);
            
            // Build array for Bluetooth Print app (following the exact format from instructions)
            $a = array();
            
            // Store Logo (if enabled and exists) - Type 1 (image)
            if ($storeSettings->show_receipt_logo && $storeSettings->receipt_logo_path) {
                $objLogo = new \stdClass();
                $objLogo->type = 1; // image
                $objLogo->path = url($storeSettings->receipt_logo_path); // Full URL to image
                $objLogo->align = 1; // center
                array_push($a, $objLogo);
            }
            
            // Header - Store Name (Center, Bold, Large)
            $obj1 = new \stdClass();
            $obj1->type = 0; // text
            $obj1->content = $storeSettings->store_name;
            $obj1->bold = 1;
            $obj1->align = 1; // center
            $obj1->format = 2; // double Height + Width
            array_push($a, $obj1);
            
            // Store Address (Center)
            if ($storeSettings->store_address) {
                $obj2 = new \stdClass();
                $obj2->type = 0; // text
                $obj2->content = $storeSettings->store_address;
                $obj2->bold = 0;
                $obj2->align = 1; // center
                $obj2->format = 0; // normal
                array_push($a, $obj2);
            }
            
            // Store Phone (Center)
            if ($storeSettings->store_phone) {
                $obj3 = new \stdClass();
                $obj3->type = 0; // text
                $obj3->content = $storeSettings->store_phone;
                $obj3->bold = 0;
                $obj3->align = 1; // center
                $obj3->format = 0; // normal
                array_push($a, $obj3);
            }
            
            // Receipt Header (if set)
            if ($storeSettings->receipt_header) {
                $objHeader = new \stdClass();
                $objHeader->type = 0; // text
                $objHeader->content = $storeSettings->receipt_header;
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
            $objTrans->content = 'No: ' . $transaction->transaction_code;
            $objTrans->bold = 0;
            $objTrans->align = 0; // left
            $objTrans->format = 0; // normal
            array_push($a, $objTrans);
            
            $objDate = new \stdClass();
            $objDate->type = 0; // text
            $dateToUse = $transaction->transaction_date ?: $transaction->created_at;
            $objDate->content = 'Waktu: ' . $dateToUse->locale('id')->isoFormat('D MMM Y, HH:mm');
            $objDate->bold = 0;
            $objDate->align = 0; // left
            $objDate->format = 0; // normal
            array_push($a, $objDate);
            
            $objCashier = new \stdClass();
            $objCashier->type = 0; // text
            $objCashier->content = 'Kasir: ' . $transaction->user->name;
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
            
            // Items
            foreach ($transaction->items as $item) {
                // Item name
                $objItem = new \stdClass();
                $objItem->type = 0; // text
                $objItem->content = $item->product_name;
                $objItem->bold = 0;
                $objItem->align = 0; // left
                $objItem->format = 0; // normal
                array_push($a, $objItem);
                
                // Quantity and price in one line
                $pricePerItem = $item->total / $item->quantity;
                $qtyPrice = $item->quantity . ' x ' . number_format($pricePerItem, 0, ',', '.');
                $totalFormatted = 'Rp ' . number_format($item->total, 0, ',', '.');
                
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
            if ($transaction->subtotal != $transaction->final_total) {
                $subtotalLine = 'Subtotal:' . str_repeat(' ', max(1, 32 - 9 - strlen('Rp ' . number_format($transaction->subtotal, 0, ',', '.')))) . 'Rp ' . number_format($transaction->subtotal, 0, ',', '.');
                $objSubtotal = new \stdClass();
                $objSubtotal->type = 0; // text
                $objSubtotal->content = $subtotalLine;
                $objSubtotal->bold = 0;
                $objSubtotal->align = 0; // left
                $objSubtotal->format = 0; // normal
                array_push($a, $objSubtotal);
            }
            
            // Discount (if any)
            if ($transaction->total_discount > 0) {
                $discountLine = 'Diskon:' . str_repeat(' ', max(1, 32 - 7 - strlen('-Rp ' . number_format($transaction->total_discount, 0, ',', '.')))) . '-Rp ' . number_format($transaction->total_discount, 0, ',', '.');
                $objDiscount = new \stdClass();
                $objDiscount->type = 0; // text
                $objDiscount->content = $discountLine;
                $objDiscount->bold = 0;
                $objDiscount->align = 0; // left
                $objDiscount->format = 0; // normal
                array_push($a, $objDiscount);
            }
            
            // Total (Bold)
            $totalLine = 'TOTAL:' . str_repeat(' ', max(1, 32 - 6 - strlen('Rp ' . number_format($transaction->final_total, 0, ',', '.')))) . 'Rp ' . number_format($transaction->final_total, 0, ',', '.');
            $objTotal = new \stdClass();
            $objTotal->type = 0; // text
            $objTotal->content = $totalLine;
            $objTotal->bold = 1;
            $objTotal->align = 0; // left
            $objTotal->format = 0; // normal
            array_push($a, $objTotal);
            
            // Payment Method
            $paymentLine = 'Bayar (' . strtoupper($transaction->payment_method) . '):' . str_repeat(' ', max(1, 32 - strlen('Bayar (' . strtoupper($transaction->payment_method) . '):') - strlen('Rp ' . number_format($paymentAmount, 0, ',', '.')))) . 'Rp ' . number_format($paymentAmount, 0, ',', '.');
            $objPayment = new \stdClass();
            $objPayment->type = 0; // text
            $objPayment->content = $paymentLine;
            $objPayment->bold = 0;
            $objPayment->align = 0; // left
            $objPayment->format = 0; // normal
            array_push($a, $objPayment);
            
            // Change (only for cash)
            if ($transaction->payment_method === 'cash' && $kembalian > 0) {
                $changeLine = 'Kembalian:' . str_repeat(' ', max(1, 32 - 10 - strlen('Rp ' . number_format($kembalian, 0, ',', '.')))) . 'Rp ' . number_format($kembalian, 0, ',', '.');
                $objChange = new \stdClass();
                $objChange->type = 0; // text
                $objChange->content = $changeLine;
                $objChange->bold = 0;
                $objChange->align = 0; // left
                $objChange->format = 0; // normal
                array_push($a, $objChange);
            }
            
            // Notes (if any)
            if ($transaction->notes) {
                $objNotes = new \stdClass();
                $objNotes->type = 0; // text
                $objNotes->content = 'Catatan: ' . $transaction->notes;
                $objNotes->bold = 0;
                $objNotes->align = 0; // left
                $objNotes->format = 0; // normal
                array_push($a, $objNotes);
            }
            
            // Empty line
            $objEmpty1 = new \stdClass();
            $objEmpty1->type = 0; // text
            $objEmpty1->content = ' ';
            $objEmpty1->bold = 0;
            $objEmpty1->align = 0; // left
            $objEmpty1->format = 0; // normal
            array_push($a, $objEmpty1);
            
            // Receipt Footer (if set)
            if ($storeSettings->receipt_footer) {
                $objFooter = new \stdClass();
                $objFooter->type = 0; // text
                $objFooter->content = $storeSettings->receipt_footer;
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
            \Log::info('Android Print JSON Response', [
                'transaction_id' => $transaction->id,
                'json_length' => count($a),
                'sample_structure' => array_slice($a, 0, 3) // Log first 3 items for verification
            ]);
            
            // Return JSON response exactly as specified in the instructions
            // Following the exact format: echo json_encode($a,JSON_FORCE_OBJECT);
            $jsonContent = json_encode($a, JSON_FORCE_OBJECT);
            
            return response($jsonContent, 200)
                ->header('Content-Type', 'application/json')
                ->header('Content-Length', strlen($jsonContent));
        } catch (\Exception $e) {
            \Log::error('Android Print Response Error', [
                'transaction_id' => $transaction->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to generate receipt print'], 500);
        }
    }
} 