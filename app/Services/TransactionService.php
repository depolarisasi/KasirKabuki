<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPartnerPrice;
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
     * Add item to cart with custom price (for partner pricing)
     */
    public function addToCartWithPrice($productId, $quantity = 1, $customPrice = null)
    {
        $cart = $this->getCart();
        
        $product = Product::with('category')->findOrFail($productId);
        
        // Use custom price if provided (for partner pricing), otherwise use product price
        $price = $customPrice !== null ? $customPrice : $product->price;
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
            // Update price in case partner pricing changed
            $cart[$productId]['price'] = $price;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'original_price' => $product->price, // Keep original for reference
                'category' => $product->category->name,
                'quantity' => $quantity,
            ];
        }
        
        Session::put('cart', $cart);
        return $cart;
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
     * Apply ad-hoc discount to cart
     */
    public function applyAdhocDiscount($type, $value, $orderType = 'dine_in')
    {
        // Check if order type is online - discounts not allowed
        if ($orderType === 'online') {
            throw new \Exception('Diskon tidak dapat diterapkan pada pesanan online.');
        }

        $cart = $this->getCart();
        if (empty($cart)) {
            throw new \Exception('Keranjang kosong, tidak dapat menerapkan diskon.');
        }

        $cartTotals = $this->getCartTotals();
        $subtotal = $cartTotals['subtotal'];

        // Validate discount amount
        if ($type === 'percentage') {
            if ($value <= 0 || $value > 100) {
                throw new \Exception('Persentase diskon harus antara 1-100%.');
            }
        } else if ($type === 'nominal') {
            if ($value <= 0) {
                throw new \Exception('Nominal diskon harus lebih dari 0.');
            }
            if ($value >= $subtotal) {
                throw new \Exception('Nominal diskon tidak boleh melebihi subtotal transaksi.');
            }
        } else {
            throw new \Exception('Tipe diskon tidak valid.');
        }

        $appliedDiscounts = Session::get('applied_discounts', []);
        
        // Remove any existing ad-hoc discount
        foreach ($appliedDiscounts as $discountId => $discountData) {
            if (strpos($discountId, 'adhoc_') === 0) {
                unset($appliedDiscounts[$discountId]);
            }
        }
        
        // Generate unique ID for ad-hoc discount
        $adhocId = 'adhoc_' . time();
        
        // Create ad-hoc discount data
        $discountName = $type === 'percentage' 
            ? "Diskon Cepat {$value}%" 
            : "Diskon Cepat Rp " . number_format($value, 0, ',', '.');
            
        $appliedDiscounts[$adhocId] = [
            'id' => $adhocId,
            'name' => $discountName,
            'type' => 'transaction',
            'value_type' => $type === 'percentage' ? 'percentage' : 'fixed',
            'value' => $value,
            'product_id' => null,
            'is_adhoc' => true
        ];
        
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
     * Save current cart as order dengan stock reduction untuk prevent overselling
     */
    public function saveOrder($orderName)
    {
        try {
            \DB::beginTransaction();
            
            $cart = $this->getCart();
            $cartTotals = $this->getCartTotals();
            
            if (empty($cart)) {
                throw new \Exception('Keranjang belanja kosong.');
            }
            
            $savedOrders = Session::get('saved_orders', []);
            
            if (isset($savedOrders[$orderName])) {
                throw new \Exception('Nama pesanan sudah digunakan.');
            }
            
            // Check stock availability untuk semua items dan reduce stock
            $stockService = app(\App\Services\StockService::class);
            $stockReservations = [];
            
            foreach ($cart as $productId => $item) {
                $product = \App\Models\Product::find($productId);
                if (!$product) {
                    throw new \Exception("Produk '{$item['name']}' tidak ditemukan.");
                }
                
                // Check stock availability with package support
                $stockCheck = $stockService->checkStockAvailability($productId, $item['quantity']);
                
                if (!$stockCheck['available']) {
                    throw new \Exception("Stok tidak mencukupi untuk {$product->name}. {$stockCheck['message']}");
                }
                
                // Reserve stock by logging as sale temporarily
                try {
                    $stockMovements = $stockService->logSale(
                        $product->id,
                        auth()->id(),
                        $item['quantity'],
                        null, // No transaction ID yet
                        "Saved order reservation - {$orderName}"
                    );
                    
                    $stockReservations[$productId] = [
                        'movements' => is_array($stockMovements) ? $stockMovements : [$stockMovements],
                        'quantity' => $item['quantity']
                    ];
                } catch (\Exception $stockError) {
                    throw new \Exception("Gagal reserve stok untuk {$product->name}: {$stockError->getMessage()}");
                }
            }
            
            // Save order dengan stock reservation info
            $savedOrders[$orderName] = [
                'cart' => $cart,
                'cart_totals' => $cartTotals,
                'created_at' => Carbon::now()->toISOString(),
                'stock_reservations' => $stockReservations,
                'user_id' => auth()->id()
            ];
            
            Session::put('saved_orders', $savedOrders);
            
            // Clear current cart
            $this->clearCart();
            
            \DB::commit();
            
            return $savedOrders[$orderName];
            
        } catch (\Exception $e) {
            \DB::rollBack();
            
            // If we have stock reservations that were made, we need to reverse them
            if (isset($stockReservations)) {
                $stockService = app(\App\Services\StockService::class);
                foreach ($stockReservations as $productId => $reservation) {
                    try {
                        $stockService->logCancellationReturn(
                            $productId,
                            auth()->id(),
                            $reservation['quantity'],
                            null,
                            "Rollback saved order reservation - {$orderName}"
                        );
                    } catch (\Exception $rollbackError) {
                        \Log::error('Failed to rollback stock reservation', [
                            'product_id' => $productId,
                            'order_name' => $orderName,
                            'error' => $rollbackError->getMessage()
                        ]);
                    }
                }
            }
            
            throw $e;
        }
    }

    /**
     * Load saved order to current cart - stock sudah di-reserve jadi tidak perlu adjust
     */
    public function loadSavedOrder($orderName)
    {
        $savedOrders = Session::get('saved_orders', []);
        
        if (!isset($savedOrders[$orderName])) {
            throw new \Exception('Pesanan tersimpan tidak ditemukan.');
        }
        
        $savedOrder = $savedOrders[$orderName];
        
        // Validate that saved order has required data
        if (!isset($savedOrder['cart']) || empty($savedOrder['cart'])) {
            throw new \Exception('Data pesanan tersimpan tidak valid.');
        }
        
        // Verify stock reservations are still valid (optional check)
        if (isset($savedOrder['stock_reservations'])) {
            $stockService = app(\App\Services\StockService::class);
            
            foreach ($savedOrder['stock_reservations'] as $productId => $reservation) {
                $currentStock = $stockService->getCurrentStock($productId);
                if ($currentStock < 0) {
                    \Log::warning('Saved order has negative stock', [
                        'order_name' => $orderName,
                        'product_id' => $productId,
                        'current_stock' => $currentStock,
                        'reserved_quantity' => $reservation['quantity']
                    ]);
                }
            }
        }
        
        // Clear current cart and load saved cart
        $this->clearCart();
        
        // Restore cart dengan preserved pricing
        Session::put('cart', $savedOrder['cart']);
        
        // Restore discounts if they exist
        if (isset($savedOrder['cart_totals']['applied_discounts'])) {
            Session::put('applied_discounts', $savedOrder['cart_totals']['applied_discounts']);
        }
        
        \Log::info('Saved order loaded successfully', [
            'order_name' => $orderName,
            'items_count' => count($savedOrder['cart']),
            'has_stock_reservations' => isset($savedOrder['stock_reservations']),
            'created_at' => $savedOrder['created_at'] ?? 'unknown'
        ]);
        
        return $savedOrder;
    }

    /**
     * Delete saved order dan return reserved stock
     */
    public function deleteSavedOrder($orderName)
    {
        try {
            \DB::beginTransaction();
            
            $savedOrders = Session::get('saved_orders', []);
            
            if (!isset($savedOrders[$orderName])) {
                throw new \Exception('Pesanan tersimpan tidak ditemukan.');
            }
            
            $savedOrder = $savedOrders[$orderName];
            
            // Return reserved stock if exists
            if (isset($savedOrder['stock_reservations'])) {
                $stockService = app(\App\Services\StockService::class);
                
                foreach ($savedOrder['stock_reservations'] as $productId => $reservation) {
                    try {
                        $stockService->logCancellationReturn(
                            $productId,
                            auth()->id(),
                            $reservation['quantity'],
                            null,
                            "Saved order deleted - return reservation - {$orderName}"
                        );
                        
                        \Log::info('Stock returned for deleted saved order', [
                            'order_name' => $orderName,
                            'product_id' => $productId,
                            'returned_quantity' => $reservation['quantity']
                        ]);
                    } catch (\Exception $stockError) {
                        \Log::error('Failed to return stock for deleted saved order', [
                            'order_name' => $orderName,
                            'product_id' => $productId,
                            'quantity' => $reservation['quantity'],
                            'error' => $stockError->getMessage()
                        ]);
                        
                        // Don't fail the deletion, just log the error
                    }
                }
            }
            
            // Remove from saved orders
            unset($savedOrders[$orderName]);
            Session::put('saved_orders', $savedOrders);
            
            \DB::commit();
            
            \Log::info('Saved order deleted successfully', [
                'order_name' => $orderName,
                'had_stock_reservations' => isset($savedOrder['stock_reservations'])
            ]);
            
            return $savedOrders;
            
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update existing saved order with current cart
     */
    public function updateSavedOrder($orderName)
    {
        try {
            \DB::beginTransaction();
            
            $savedOrders = Session::get('saved_orders', []);
            
            if (!isset($savedOrders[$orderName])) {
                throw new \Exception('Pesanan tersimpan tidak ditemukan.');
            }
            
            $cart = Session::get('cart', []);
            if (empty($cart)) {
                throw new \Exception('Keranjang kosong. Tidak ada yang bisa disimpan.');
            }
            
            $cartTotals = $this->getCartTotals();
            $oldOrder = $savedOrders[$orderName];
            $stockReservations = [];
            
            // Return stock from old order first
            if (isset($oldOrder['stock_reservations'])) {
                $stockService = app(\App\Services\StockService::class);
                
                foreach ($oldOrder['stock_reservations'] as $productId => $reservation) {
                    try {
                        $stockService->logCancellationReturn(
                            $productId,
                            auth()->id(),
                            $reservation['quantity'],
                            null,
                            "Update saved order - return old reservation - {$orderName}"
                        );
                    } catch (\Exception $stockError) {
                        \Log::warning('Failed to return old stock for order update', [
                            'order_name' => $orderName,
                            'product_id' => $productId,
                            'error' => $stockError->getMessage()
                        ]);
                    }
                }
            }
            
            // Reserve stock for new cart items
            $stockService = app(\App\Services\StockService::class);
            
            foreach ($cart as $productId => $item) {
                $product = \App\Models\Product::find($productId);
                if (!$product) {
                    throw new \Exception("Produk ID {$productId} tidak ditemukan.");
                }
                
                try {
                    $stockService->logSalesOut(
                        $productId,
                        auth()->id(),
                        $item['quantity'],
                        null,
                        "Update saved order reservation - {$orderName}"
                    );
                    
                    $stockReservations[$productId] = [
                        'quantity' => $item['quantity'],
                        'product_name' => $product->name,
                        'reserved_at' => Carbon::now()->toISOString()
                    ];
                    
                } catch (\Exception $stockError) {
                    throw new \Exception("Gagal reserve stok untuk {$product->name}: {$stockError->getMessage()}");
                }
            }
            
            // Update saved order dengan preserved creation time
            $savedOrders[$orderName] = [
                'cart' => $cart,
                'cart_totals' => $cartTotals,
                'created_at' => $oldOrder['created_at'], // Keep original creation time
                'updated_at' => Carbon::now()->toISOString(), // Add update timestamp
                'stock_reservations' => $stockReservations,
                'user_id' => auth()->id()
            ];
            
            Session::put('saved_orders', $savedOrders);
            
            // Clear current cart
            $this->clearCart();
            
            \DB::commit();
            
            \Log::info('Saved order updated successfully', [
                'order_name' => $orderName,
                'items_count' => count($cart),
                'old_reservations' => count($oldOrder['stock_reservations'] ?? []),
                'new_reservations' => count($stockReservations),
                'updated_by' => auth()->id()
            ]);
            
            return $savedOrders[$orderName];
            
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
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
     * Validate cart for checkout - UPDATED: No stock validation, cashier independent
     */
    public function validateCartForCheckout()
    {
        $cart = $this->getCart();
        
        if (empty($cart)) {
            throw new \Exception('Keranjang belanja kosong.');
        }
        
        $errors = [];
        
        // Check if products still exist (but NO stock validation)
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (!$product) {
                $errors[] = "Produk '{$item['name']}' tidak ditemukan.";
                continue;
            }
            
            // REMOVED: Stock validation - kasir independent dari stock management
            // Big Pappa requirement: Transaksi dapat dilakukan meskipun stok 0
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

                // Reduce stock using new StockService with package support dan transaction reference
                $stockService = app(\App\Services\StockService::class);
                try {
                    $stockService->logSale(
                        $product->id,
                        auth()->id(),
                        $item['quantity'],
                        $transaction->id,
                        "Sale - {$transaction->transaction_code}"
                    );
                } catch (\Exception $stockError) {
                    // Log stock reduction error but don't fail transaction
                    // Big Pappa requirement: Transaction should be independent from stock management
                    \Log::warning('Stock reduction failed during transaction', [
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'error' => $stockError->getMessage()
                    ]);
                }
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

            // Restore stock for each item menggunakan new system
            $stockService = app(\App\Services\StockService::class);
            foreach ($transaction->items as $item) {
                try {
                    $stockService->logCancellationReturn(
                        $item->product_id,
                        auth()->id(),
                        $item->quantity,
                        $transaction->id,
                        "Cancellation return - {$transaction->transaction_code}" . ($reason ? " - {$reason}" : "")
                    );
                } catch (\Exception $stockError) {
                    // Log error but don't fail cancellation
                    \Log::warning('Stock return failed during cancellation', [
                        'transaction_id' => $transaction->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'error' => $stockError->getMessage()
                    ]);
                }
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

    /**
     * Refresh cart prices based on order type and partner (for partner pricing)
     */
    public function refreshCartPrices($orderType, $partnerId = null)
    {
        $cart = $this->getCart();
        
        if (empty($cart)) {
            return $cart;
        }
        
        \Log::info('TransactionService: Refreshing cart prices', [
            'order_type' => $orderType,
            'partner_id' => $partnerId,
            'cart_items_count' => count($cart)
        ]);
        
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                $appropriatePrice = $product->getAppropriatePrice($orderType, $partnerId);
                $cart[$productId]['price'] = $appropriatePrice;
                
                \Log::info('TransactionService: Price updated for product', [
                    'product_id' => $productId,
                    'original_price' => $product->price,
                    'new_price' => $appropriatePrice,
                    'order_type' => $orderType,
                    'partner_id' => $partnerId
                ]);
            }
        }
        
        Session::put('cart', $cart);
        return $cart;
    }
} 