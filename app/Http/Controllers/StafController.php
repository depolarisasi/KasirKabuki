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
     * Display expenses management page.
     */
    public function expenses()
    {
        return view('staf.expenses.index');
    }
    
    /**
     * Display receipt print page.
     */
    public function receiptPrint(Transaction $transaction)
    {
        // Ensure user can only view their own transactions or admin can view all
        if (!auth()->user()->hasRole('admin') && $transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to receipt.');
        }
        
        // Load transaction with relationships
        $transaction->load(['user', 'partner', 'items.product']);
        
        return view('receipt.print', compact('transaction'));
    }
} 