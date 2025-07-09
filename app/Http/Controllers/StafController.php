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
        // Ensure user can only view their own transactions or admin can view all
        if (!auth()->user()->hasRole('admin') && $transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to receipt.');
        }
        
        // Load transaction with relationships
        $transaction->load(['user', 'partner', 'items.product']);
        
        return view('receipt.print', compact('transaction'));
    }
} 