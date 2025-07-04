<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Discount;
use App\Models\Partner;
use App\Models\Category;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class TransactionService
{
    /**
     * Get cart items from session
     */
    public function getCart()
    {
        return Session::get('cart', []);
    }

    /**
     * Add item to cart
     */
    public function addToCart($productId, $quantity = 1)
    {
        $cart = $this->getCart();
        
        $product = Product::with('category')->findOrFail($productId);
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category->name,
                'quantity' => $quantity,
            ];
        }
        
        Session::put('cart', $cart);
        return $cart;
    }

    /**
     * Update item quantity in cart
     */
    public function updateCartQuantity($productId, $quantity)
    {
        $cart = $this->getCart();
        
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $quantity;
            }
        }
        
        Session::put('cart', $cart);
        return $cart;
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart($productId)
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        
        Session::put('cart', $cart);
        return $cart;
    }

    /**
     * Clear entire cart
     */
    public function clearCart()
    {
        Session::forget('cart');
        Session::forget('applied_discounts');
        return [];
    }

    /**
     * Get cart totals with calculations
     */
    public function getCartTotals()
    {
        $cart = $this->getCart();
        $appliedDiscounts = Session::get('applied_discounts', []);
        
        $subtotal = 0;
        $totalItems = 0;
        
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $totalItems += $item['quantity'];
        }
        
        // Apply product-specific discounts first
        $productDiscountAmount = 0;
        foreach ($appliedDiscounts as $discountId => $discountData) {
            if ($discountData['type'] === 'product' && isset($cart[$discountData['product_id']])) {
                $item = $cart[$discountData['product_id']];
                $itemTotal = $item['price'] * $item['quantity'];
                
                if ($discountData['value_type'] === 'percentage') {
                    $productDiscountAmount += $itemTotal * ($discountData['value'] / 100);
                } else {
                    $productDiscountAmount += $discountData['value'];
                }
            }
        }
        
        $afterProductDiscount = $subtotal - $productDiscountAmount;
        
        // Apply transaction-level discounts
        $transactionDiscountAmount = 0;
        foreach ($appliedDiscounts as $discountId => $discountData) {
            if ($discountData['type'] === 'transaction') {
                if ($discountData['value_type'] === 'percentage') {
                    $transactionDiscountAmount += $afterProductDiscount * ($discountData['value'] / 100);
                } else {
                    $transactionDiscountAmount += $discountData['value'];
                }
            }
        }
        
        $totalDiscount = $productDiscountAmount + $transactionDiscountAmount;
        $finalTotal = $subtotal - $totalDiscount;
        
        return [
            'subtotal' => $subtotal,
            'total_items' => $totalItems,
            'product_discount' => $productDiscountAmount,
            'transaction_discount' => $transactionDiscountAmount,
            'total_discount' => $totalDiscount,
            'final_total' => max(0, $finalTotal), // Ensure total can't be negative
            'cart_items' => $cart,
            'applied_discounts' => $appliedDiscounts
        ];
    }

    /**
     * Apply discount to cart
     */
    public function applyDiscount($discountId, $orderType = 'dine_in')
    {
        // Check if order type is online - discounts not allowed
        if ($orderType === 'online') {
            throw new \Exception('Diskon tidak dapat diterapkan pada pesanan online.');
        }
        
        $discount = Discount::where('id', $discountId)
            ->where('is_active', true)
            ->firstOrFail();
        
        $appliedDiscounts = Session::get('applied_discounts', []);
        
        // Check if discount already applied
        if (isset($appliedDiscounts[$discountId])) {
            throw new \Exception('Diskon ini sudah diterapkan.');
        }
        
        $appliedDiscounts[$discountId] = [
            'id' => $discount->id,
            'name' => $discount->name,
            'type' => $discount->type,
            'value_type' => $discount->value_type,
            'value' => $discount->value,
            'product_id' => $discount->product_id,
        ];
        
        Session::put('applied_discounts', $appliedDiscounts);
        return $appliedDiscounts;
    }

    /**
     * Remove discount from cart
     */
    public function removeDiscount($discountId)
    {
        $appliedDiscounts = Session::get('applied_discounts', []);
        unset($appliedDiscounts[$discountId]);
        
        Session::put('applied_discounts', $appliedDiscounts);
        return $appliedDiscounts;
    }

    /**
     * Get available discounts for current cart
     */
    public function getAvailableDiscounts($orderType = 'dine_in')
    {
        if ($orderType === 'online') {
            return collect([]);
        }
        
        $cart = $this->getCart();
        $appliedDiscounts = Session::get('applied_discounts', []);
        $productIds = array_keys($cart);
        
        return Discount::where('is_active', true)
            ->where(function ($query) use ($productIds) {
                $query->where('type', 'transaction')
                    ->orWhere(function ($subQuery) use ($productIds) {
                        $subQuery->where('type', 'product')
                            ->whereIn('product_id', $productIds);
                    });
            })
            ->whereNotIn('id', array_keys($appliedDiscounts))
            ->with('product')
            ->get();
    }

    /**
     * Save current cart as a saved order
     */
    public function saveOrder($orderName)
    {
        $cart = $this->getCart();
        $appliedDiscounts = Session::get('applied_discounts', []);
        
        if (empty($cart)) {
            throw new \Exception('Keranjang kosong, tidak dapat menyimpan pesanan.');
        }
        
        $savedOrders = Session::get('saved_orders', []);
        
        // Check if order name already exists
        if (isset($savedOrders[$orderName])) {
            throw new \Exception('Nama pesanan sudah ada. Gunakan nama yang berbeda.');
        }
        
        $savedOrders[$orderName] = [
            'name' => $orderName,
            'cart' => $cart,
            'discounts' => $appliedDiscounts,
            'created_at' => now(),
            'totals' => $this->getCartTotals()
        ];
        
        Session::put('saved_orders', $savedOrders);
        return $savedOrders;
    }

    /**
     * Load saved order to cart
     */
    public function loadSavedOrder($orderName)
    {
        $savedOrders = Session::get('saved_orders', []);
        
        if (!isset($savedOrders[$orderName])) {
            throw new \Exception('Pesanan tersimpan tidak ditemukan.');
        }
        
        $savedOrder = $savedOrders[$orderName];
        
        Session::put('cart', $savedOrder['cart']);
        Session::put('applied_discounts', $savedOrder['discounts']);
        
        return $savedOrder;
    }

    /**
     * Delete saved order
     */
    public function deleteSavedOrder($orderName)
    {
        $savedOrders = Session::get('saved_orders', []);
        unset($savedOrders[$orderName]);
        
        Session::put('saved_orders', $savedOrders);
        return $savedOrders;
    }

    /**
     * Get all saved orders
     */
    public function getSavedOrders()
    {
        return Session::get('saved_orders', []);
    }

    /**
     * Get products grouped by category
     */
    public function getProductsByCategory()
    {
        return Category::with(['products' => function ($query) {
            $query->orderBy('name');
        }])
        ->orderBy('name')
        ->get()
        ->filter(function ($category) {
            return $category->products->count() > 0;
        });
    }

    /**
     * Validate cart for checkout
     */
    public function validateCartForCheckout()
    {
        $cart = $this->getCart();
        
        if (empty($cart)) {
            throw new \Exception('Keranjang belanja kosong.');
        }
        
        $errors = [];
        
        // Check if products still exist and have sufficient stock
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (!$product) {
                $errors[] = "Produk '{$item['name']}' tidak ditemukan.";
                continue;
            }
            
            // In future iterations, we can add stock validation here
            // For now, we'll assume stock is managed separately
        }
        
        if (!empty($errors)) {
            throw new \Exception('Error dalam keranjang: ' . implode(', ', $errors));
        }
        
        return true;
    }

    /**
     * Generate unique transaction code
     */
    public function generateTransactionCode()
    {
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        return "TRX{$date}{$random}";
    }

    /**
     * Complete transaction and create database records
     */
    public function completeTransaction($orderType, $partnerId = null, $paymentMethod = 'cash', $notes = null)
    {
        try {
            \DB::beginTransaction();

            // Validate cart first
            $this->validateCartForCheckout();
            
            $cart = $this->getCart();
            $cartTotals = $this->getCartTotals();
            
            if (empty($cart)) {
                throw new \Exception('Keranjang belanja kosong.');
            }

            // Validate online order requirements
            if ($orderType === 'online' && !$partnerId) {
                throw new \Exception('Partner harus dipilih untuk pesanan online.');
            }

            // Create transaction record
            $transaction = \App\Models\Transaction::create([
                'transaction_code' => $this->generateTransactionCode(),
                'user_id' => auth()->id(),
                'order_type' => $orderType,
                'partner_id' => $partnerId,
                'subtotal' => $cartTotals['subtotal'],
                'total_discount' => $cartTotals['total_discount'],
                'final_total' => $cartTotals['final_total'],
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'discount_details' => $cartTotals['applied_discounts'],
                'notes' => $notes,
            ]);

            // Calculate partner commission if online order
            if ($orderType === 'online' && $partnerId) {
                $partner = \App\Models\Partner::find($partnerId);
                if ($partner) {
                    $transaction->partner_commission = $cartTotals['subtotal'] * ($partner->commission_rate / 100);
                    $transaction->save();
                }
            }

            // Create transaction items
            foreach ($cart as $productId => $item) {
                $product = \App\Models\Product::find($productId);
                if (!$product) {
                    throw new \Exception("Produk '{$item['name']}' tidak ditemukan.");
                }

                // Calculate item discount if any
                $itemDiscountAmount = 0;
                foreach ($cartTotals['applied_discounts'] as $discountData) {
                    if ($discountData['type'] === 'product' && $discountData['product_id'] == $productId) {
                        $itemTotal = $item['price'] * $item['quantity'];
                        if ($discountData['value_type'] === 'percentage') {
                            $itemDiscountAmount = $itemTotal * ($discountData['value'] / 100);
                        } else {
                            $itemDiscountAmount = $discountData['value'];
                        }
                        break;
                    }
                }

                $subtotal = $item['price'] * $item['quantity'];
                $total = $subtotal - $itemDiscountAmount;

                $transactionItem = $transaction->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                    'discount_amount' => $itemDiscountAmount,
                    'total' => $total,
                ]);

                // Reduce stock using StockService
                $stockService = app(\App\Services\StockService::class);
                $stockService->reduceStock(
                    $product->id,
                    auth()->id(),
                    $item['quantity'],
                    'Penjualan - ' . $transaction->transaction_code
                );
            }

            // Mark transaction as completed
            $transaction->markAsCompleted();

            // Clear cart and session data
            $this->clearCart();

            \DB::commit();

            return $transaction;

        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate checkout summary
     */
    public function getCheckoutSummary($orderType, $partnerId = null)
    {
        $cartTotals = $this->getCartTotals();
        
        $summary = [
            'cart_totals' => $cartTotals,
            'order_type' => $orderType,
            'partner_commission' => 0,
            'net_revenue' => $cartTotals['final_total'],
        ];

        // Calculate partner commission for online orders
        if ($orderType === 'online' && $partnerId) {
            $partner = \App\Models\Partner::find($partnerId);
            if ($partner) {
                $summary['partner_commission'] = $cartTotals['subtotal'] * ($partner->commission_rate / 100);
                $summary['net_revenue'] = $cartTotals['final_total'] - $summary['partner_commission'];
                $summary['partner'] = $partner;
            }
        }

        return $summary;
    }

    /**
     * Get recent transactions for dashboard
     */
    public function getRecentTransactions($limit = 10)
    {
        return \App\Models\Transaction::with(['user', 'partner', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Find transaction by code
     */
    public function findTransactionByCode($transactionCode)
    {
        return \App\Models\Transaction::with(['user', 'partner', 'items.product'])
            ->where('transaction_code', $transactionCode)
            ->first();
    }

    /**
     * Cancel transaction (if still pending)
     */
    public function cancelTransaction($transactionId, $reason = null)
    {
        try {
            \DB::beginTransaction();

            $transaction = \App\Models\Transaction::findOrFail($transactionId);
            
            if ($transaction->status !== 'pending') {
                throw new \Exception('Hanya transaksi pending yang dapat dibatalkan.');
            }

            // Restore stock for each item
            $stockService = app(\App\Services\StockService::class);
            foreach ($transaction->items as $item) {
                $stockService->inputStockAwal(
                    $item->product_id,
                    auth()->id(),
                    $item->quantity,
                    'Pembatalan transaksi - ' . $transaction->transaction_code . ($reason ? ' - ' . $reason : '')
                );
            }

            // Mark transaction as cancelled
            $transaction->markAsCancelled();
            if ($reason) {
                $transaction->update(['notes' => ($transaction->notes ? $transaction->notes . ' | ' : '') . 'Dibatalkan: ' . $reason]);
            }

            \DB::commit();

            return $transaction;

        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }
} 