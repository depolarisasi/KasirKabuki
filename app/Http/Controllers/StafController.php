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
        // Load transaction with relationships
        $transaction->load(['user', 'partner', 'items.product']);
        
        // Get store settings
        $storeSettings = \App\Models\StoreSetting::current();
        
        // Get payment amount from request
        $paymentAmount = request()->input('payment_amount', $transaction->final_total);
        $kembalian = $transaction->payment_method === 'qris' ? 0 : max(0, $paymentAmount - $transaction->final_total);
        
        // Build JSON array for Bluetooth Print app
        $printData = [];
        
        // Header - Store Name (Center, Bold, Large)
        $obj1 = new \stdClass();
        $obj1->type = 0; // text
        $obj1->content = $storeSettings->store_name;
        $obj1->bold = 1;
        $obj1->align = 1; // center
        $obj1->format = 2; // double Height + Width
        $printData[] = $obj1;
        
        // Store Address (Center)
        if ($storeSettings->store_address) {
            $obj2 = new \stdClass();
            $obj2->type = 0; // text
            $obj2->content = $storeSettings->store_address;
            $obj2->bold = 0;
            $obj2->align = 1; // center
            $obj2->format = 0; // normal
            $printData[] = $obj2;
        }
        
        // Store Phone (Center)
        if ($storeSettings->store_phone) {
            $obj3 = new \stdClass();
            $obj3->type = 0; // text
            $obj3->content = $storeSettings->store_phone;
            $obj3->bold = 0;
            $obj3->align = 1; // center
            $obj3->format = 0; // normal
            $printData[] = $obj3;
        }
        
        // Receipt Header (if set)
        if ($storeSettings->receipt_header) {
            $objHeader = new \stdClass();
            $objHeader->type = 0; // text
            $objHeader->content = $storeSettings->receipt_header;
            $objHeader->bold = 0;
            $objHeader->align = 1; // center
            $objHeader->format = 0; // normal
            $printData[] = $objHeader;
        }
        
        // Separator line
        $objSep1 = new \stdClass();
        $objSep1->type = 0; // text
        $objSep1->content = '================================';
        $objSep1->bold = 0;
        $objSep1->align = 1; // center
        $objSep1->format = 0; // normal
        $printData[] = $objSep1;
        
        // Transaction Info
        $objTrans = new \stdClass();
        $objTrans->type = 0; // text
        $objTrans->content = 'No: ' . $transaction->transaction_code;
        $objTrans->bold = 0;
        $objTrans->align = 0; // left
        $objTrans->format = 0; // normal
        $printData[] = $objTrans;
        
        $objDate = new \stdClass();
        $objDate->type = 0; // text
        $objDate->content = 'Waktu: ' . $transaction->created_at->locale('id')->isoFormat('D MMM Y, HH:mm');
        $objDate->bold = 0;
        $objDate->align = 0; // left
        $objDate->format = 0; // normal
        $printData[] = $objDate;
        
        $objCashier = new \stdClass();
        $objCashier->type = 0; // text
        $objCashier->content = 'Kasir: ' . $transaction->user->name;
        $objCashier->bold = 0;
        $objCashier->align = 0; // left
        $objCashier->format = 0; // normal
        $printData[] = $objCashier;
        
        // Separator line
        $objSep2 = new \stdClass();
        $objSep2->type = 0; // text
        $objSep2->content = '================================';
        $objSep2->bold = 0;
        $objSep2->align = 1; // center
        $objSep2->format = 0; // normal
        $printData[] = $objSep2;
        
        // Items
        foreach ($transaction->items as $item) {
            // Item name
            $objItem = new \stdClass();
            $objItem->type = 0; // text
            $objItem->content = $item->product_name;
            $objItem->bold = 0;
            $objItem->align = 0; // left
            $objItem->format = 0; // normal
            $printData[] = $objItem;
            
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
            $printData[] = $objQtyPrice;
        }
        
        // Separator line
        $objSep3 = new \stdClass();
        $objSep3->type = 0; // text
        $objSep3->content = '================================';
        $objSep3->bold = 0;
        $objSep3->align = 1; // center
        $objSep3->format = 0; // normal
        $printData[] = $objSep3;
        
        // Subtotal
        if ($transaction->subtotal != $transaction->final_total) {
            $subtotalLine = 'Subtotal:' . str_repeat(' ', max(1, 32 - 9 - strlen('Rp ' . number_format($transaction->subtotal, 0, ',', '.')))) . 'Rp ' . number_format($transaction->subtotal, 0, ',', '.');
            $objSubtotal = new \stdClass();
            $objSubtotal->type = 0; // text
            $objSubtotal->content = $subtotalLine;
            $objSubtotal->bold = 0;
            $objSubtotal->align = 0; // left
            $objSubtotal->format = 0; // normal
            $printData[] = $objSubtotal;
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
            $printData[] = $objDiscount;
        }
        
        // Total (Bold)
        $totalLine = 'TOTAL:' . str_repeat(' ', max(1, 32 - 6 - strlen('Rp ' . number_format($transaction->final_total, 0, ',', '.')))) . 'Rp ' . number_format($transaction->final_total, 0, ',', '.');
        $objTotal = new \stdClass();
        $objTotal->type = 0; // text
        $objTotal->content = $totalLine;
        $objTotal->bold = 1;
        $objTotal->align = 0; // left
        $objTotal->format = 0; // normal
        $printData[] = $objTotal;
        
        // Payment Method
        $paymentLine = 'Bayar (' . strtoupper($transaction->payment_method) . '):' . str_repeat(' ', max(1, 32 - strlen('Bayar (' . strtoupper($transaction->payment_method) . '):') - strlen('Rp ' . number_format($paymentAmount, 0, ',', '.')))) . 'Rp ' . number_format($paymentAmount, 0, ',', '.');
        $objPayment = new \stdClass();
        $objPayment->type = 0; // text
        $objPayment->content = $paymentLine;
        $objPayment->bold = 0;
        $objPayment->align = 0; // left
        $objPayment->format = 0; // normal
        $printData[] = $objPayment;
        
        // Change (only for cash)
        if ($transaction->payment_method === 'cash' && $kembalian > 0) {
            $changeLine = 'Kembalian:' . str_repeat(' ', max(1, 32 - 10 - strlen('Rp ' . number_format($kembalian, 0, ',', '.')))) . 'Rp ' . number_format($kembalian, 0, ',', '.');
            $objChange = new \stdClass();
            $objChange->type = 0; // text
            $objChange->content = $changeLine;
            $objChange->bold = 0;
            $objChange->align = 0; // left
            $objChange->format = 0; // normal
            $printData[] = $objChange;
        }
        
        // Notes (if any)
        if ($transaction->notes) {
            $objNotes = new \stdClass();
            $objNotes->type = 0; // text
            $objNotes->content = 'Catatan: ' . $transaction->notes;
            $objNotes->bold = 0;
            $objNotes->align = 0; // left
            $objNotes->format = 0; // normal
            $printData[] = $objNotes;
        }
        
        // Empty line
        $objEmpty1 = new \stdClass();
        $objEmpty1->type = 0; // text
        $objEmpty1->content = ' ';
        $objEmpty1->bold = 0;
        $objEmpty1->align = 0; // left
        $objEmpty1->format = 0; // normal
        $printData[] = $objEmpty1;
        
        // Receipt Footer (if set)
        if ($storeSettings->receipt_footer) {
            $objFooter = new \stdClass();
            $objFooter->type = 0; // text
            $objFooter->content = $storeSettings->receipt_footer;
            $objFooter->bold = 0;
            $objFooter->align = 1; // center
            $objFooter->format = 0; // normal
            $printData[] = $objFooter;
        }
        
        // Thank you message
        $objThank = new \stdClass();
        $objThank->type = 0; // text
        $objThank->content = 'Terima Kasih';
        $objThank->bold = 1;
        $objThank->align = 1; // center
        $objThank->format = 0; // normal
        $printData[] = $objThank;
        
        // End separator
        $objEnd = new \stdClass();
        $objEnd->type = 0; // text
        $objEnd->content = '---oOo---';
        $objEnd->bold = 0;
        $objEnd->align = 1; // center
        $objEnd->format = 0; // normal
        $printData[] = $objEnd;
        
        // Empty lines for cutting
        $objEmpty2 = new \stdClass();
        $objEmpty2->type = 0; // text
        $objEmpty2->content = ' ';
        $objEmpty2->bold = 0;
        $objEmpty2->align = 0; // left
        $objEmpty2->format = 0; // normal
        $printData[] = $objEmpty2;
        
        $objEmpty3 = new \stdClass();
        $objEmpty3->type = 0; // text
        $objEmpty3->content = ' ';
        $objEmpty3->bold = 0;
        $objEmpty3->align = 0; // left
        $objEmpty3->format = 0; // normal
        $printData[] = $objEmpty3;
        
        // Return JSON response for Bluetooth Print app
        return response()->json($printData, 200, [], JSON_FORCE_OBJECT);
    }
} 