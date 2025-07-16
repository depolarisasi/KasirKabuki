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
     * Get cart totals with conditional tax/service charge for partner orders
     */
    public function getCartTotals($orderType = 'dine_in', $partnerId = null)
    {
        $cart = $this->getCart();
        $appliedDiscounts = Session::get('applied_discounts', []);
        $storeSettings = \App\Models\StoreSetting::current();
        
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
        $afterDiscount = $subtotal - $totalDiscount;
        
        // Check if this is an online order with partner (no tax/service charge)
        $isPartnerOrder = ($orderType === 'online' && $partnerId !== null);
        
        // Calculate tax (applied after discount) - Skip for partner orders
        $taxAmount = $isPartnerOrder ? 0 : $storeSettings->calculateTaxAmount($afterDiscount);
        $subtotalWithTax = $afterDiscount + $taxAmount;
        
        // Calculate service charge (applied after tax) - Skip for partner orders
        $serviceChargeAmount = $isPartnerOrder ? 0 : $storeSettings->calculateServiceChargeAmount($subtotalWithTax);
        
        // Final total includes tax and service charge
        $finalTotal = $subtotalWithTax + $serviceChargeAmount;
        
        return [
            'subtotal' => round($subtotal, 2),
            'total_items' => $totalItems,
            'product_discount' => round($productDiscountAmount, 2),
            'transaction_discount' => round($transactionDiscountAmount, 2),
            'total_discount' => round($totalDiscount, 2),
            'after_discount' => round($afterDiscount, 2),
            'tax_amount' => round($taxAmount, 2),
            'tax_rate' => $storeSettings->tax_rate,
            'subtotal_with_tax' => round($subtotalWithTax, 2),
            'service_charge_amount' => round($serviceChargeAmount, 2),
            'service_charge_rate' => $storeSettings->service_charge_rate,
            'final_total' => round(max(0, $finalTotal), 2), // Ensure total can't be negative
            'cart_items' => $cart,
            'applied_discounts' => $appliedDiscounts,
            'is_partner_order' => $isPartnerOrder // Add flag for UI purposes
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
            throw \App\Exceptions\BusinessException::discountNotAllowed('Diskon tidak dapat diterapkan pada pesanan online');
        }

        $cart = $this->getCart();
        if (empty($cart)) {
            throw \App\Exceptions\BusinessException::cartEmpty();
        }

        $cartTotals = $this->getCartTotals();
        $subtotal = $cartTotals['subtotal'];

        // Validate discount amount
        if ($type === 'percentage') {
            if ($value <= 0 || $value > 100) {
                throw \App\Exceptions\BusinessException::invalidDiscount('Persentase diskon harus antara 1-100%');
            }
        } else if ($type === 'nominal') {
            if ($value <= 0) {
                throw \App\Exceptions\BusinessException::invalidDiscount('Nominal diskon harus lebih dari 0');
            }
            if ($value >= $subtotal) {
                throw \App\Exceptions\BusinessException::invalidDiscount('Nominal diskon tidak boleh melebihi subtotal transaksi');
            }
        } else {
            throw \App\Exceptions\BusinessException::invalidDiscount('Tipe diskon tidak valid');
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
     * Save current cart as order
     */
    public function saveOrder($orderName)
    {
        $cart = $this->getCart();
        
        if (empty($cart)) {
            throw new \Exception('Keranjang kosong');
        }

        // Save order to session
        $savedOrders = Session::get('saved_orders', []);
        $cartTotals = $this->getCartTotals();
        
            $savedOrders[$orderName] = [
            'items' => $cart,
            'totals' => $cartTotals,
            'order_type' => Session::get('order_type', 'dine_in'),
            'partner_id' => Session::get('partner_id'),
            'payment_method' => Session::get('payment_method', 'cash'),
            'created_at' => now()->toISOString(),
            'applied_discounts' => Session::get('applied_discounts', [])
            ];
            
            Session::put('saved_orders', $savedOrders);
            return $savedOrders[$orderName];
    }

    /**
     * Load saved order ke cart
     */
    public function loadSavedOrder($orderName)
    {
        $savedOrders = Session::get('saved_orders', []);
        
        if (!isset($savedOrders[$orderName])) {
            throw new \Exception('Pesanan tidak ditemukan');
        }
        
        $order = $savedOrders[$orderName];
        
        // KasirKabuki tidak menggunakan stock management
        // Semua produk dapat di-load tanpa validasi stock
        
        // Load order data to session
        Session::put('cart', $order['items']);
        Session::put('applied_discounts', $order['applied_discounts'] ?? []);
        Session::put('order_type', $order['order_type'] ?? 'dine_in');
        Session::put('partner_id', $order['partner_id'] ?? null);
        Session::put('payment_method', $order['payment_method'] ?? 'cash');
        
        return $order;
    }

    /**
     * Delete saved order
     */
    public function deleteSavedOrder($orderName)
    {
            $savedOrders = Session::get('saved_orders', []);
            
            if (!isset($savedOrders[$orderName])) {
            throw new \Exception('Pesanan tidak ditemukan');
        }

        // No need to return stock - savedOrders tidak reserve stock
            unset($savedOrders[$orderName]);
            Session::put('saved_orders', $savedOrders);
            
        return true;
                    }
                    
    /**
     * Update saved order with current cart
     */
    public function updateSavedOrder($orderName)
    {
        $cart = $this->getCart();
        
            if (empty($cart)) {
            throw new \Exception('Keranjang kosong');
            }
            
        $savedOrders = Session::get('saved_orders', []);
        
        if (!isset($savedOrders[$orderName])) {
            throw new \Exception('Pesanan tidak ditemukan');
            }
            
        // KasirKabuki tidak menggunakan stock management
        // Semua produk dapat di-update tanpa validasi stock

        // Update saved order
        $cartTotals = $this->getCartTotals();
        
            $savedOrders[$orderName] = [
            'items' => $cart,
            'totals' => $cartTotals,
            'order_type' => Session::get('order_type', 'dine_in'),
            'partner_id' => Session::get('partner_id'),
            'payment_method' => Session::get('payment_method', 'cash'),
            'updated_at' => now()->toISOString(),
            'created_at' => $savedOrders[$orderName]['created_at'] ?? now()->toISOString(),
            'applied_discounts' => Session::get('applied_discounts', [])
            ];
            
            Session::put('saved_orders', $savedOrders);
            return $savedOrders[$orderName];
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
            $query->with(['activeComponents.componentProduct', 'partnerPrices'])
                  ->orderBy('name');
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
            throw \App\Exceptions\BusinessException::cartEmpty();
        }
        
        $errors = [];
        
        // Check if products still exist (but NO stock validation)
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (!$product) {
                $errors[] = \App\Exceptions\BusinessException::productNotFound($item['name'])->getUserMessage();
                continue;
            }
            
            // REMOVED: Stock validation - kasir independent dari stock management
            // Big Pappa requirement: Transaksi dapat dilakukan meskipun stok 0
        }
        
        if (!empty($errors)) {
            throw \App\Exceptions\BusinessException::invalidDiscount('Error dalam keranjang: ' . implode(', ', $errors));
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
     * Complete transaction and save to database with conditional tax/service charge
     */
    public function completeTransaction($orderType, $partnerId = null, $paymentMethod = 'cash', $notes = null)
    {
        try {
            \DB::beginTransaction();
            
            $cart = $this->getCart();
            $appliedDiscounts = Session::get('applied_discounts', []);
            
            if (empty($cart)) {
                throw new \Exception('Keranjang kosong');
            }

            // Calculate totals with conditional tax/service charge for partner orders
            $totals = $this->getCartTotals($orderType, $partnerId);

            // Create transaction
            $transaction = \App\Models\Transaction::create([
                'transaction_code' => $this->generateTransactionCode(),
                'subtotal' => $totals['subtotal'],
                'total_discount' => $totals['total_discount'],
                'tax_amount' => $totals['tax_amount'], // Will be 0 for partner orders
                'tax_rate' => $totals['tax_rate'],
                'service_charge_amount' => $totals['service_charge_amount'], // Will be 0 for partner orders
                'service_charge_rate' => $totals['service_charge_rate'],
                'final_total' => $totals['final_total'],
                'order_type' => $orderType,
                'partner_id' => $partnerId,
                'payment_method' => $paymentMethod,
                'cashier_name' => auth()->user()->name ?? 'Unknown',
                'notes' => $notes,
                'transaction_date' => now(),
                'status' => 'completed',
                'user_id' => auth()->id(),
            ]);

            // Create transaction items - KasirKabuki tidak menggunakan stock management
            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);
                $appropriatePrice = $product->getAppropriatePrice($orderType, $partnerId);
                
                \App\Models\TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_price' => $appropriatePrice,
                    'quantity' => $item['quantity'],
                    'subtotal' => $appropriatePrice * $item['quantity'],
                    'discount_amount' => 0, // Default no discount per item
                    'total' => $appropriatePrice * $item['quantity'],
                ]);
            }

            // Create discount records
            foreach ($appliedDiscounts as $discountId => $discountData) {
                // Calculate actual discount amount based on type and value
                $discountAmount = 0;
                if ($discountData['type'] === 'product' && isset($cart[$discountData['product_id']])) {
                    $item = $cart[$discountData['product_id']];
                    $itemTotal = $item['price'] * $item['quantity'];
                    
                    if ($discountData['value_type'] === 'percentage') {
                        $discountAmount = $itemTotal * ($discountData['value'] / 100);
                    } else {
                        $discountAmount = $discountData['value'];
                    }
                } elseif ($discountData['type'] === 'transaction') {
                    $effectiveSubtotal = $totals['subtotal'] - ($totals['product_discount'] ?? 0);
                    
                    if ($discountData['value_type'] === 'percentage') {
                        $discountAmount = $effectiveSubtotal * ($discountData['value'] / 100);
                    } else {
                        $discountAmount = $discountData['value'];
                    }
                }
                
                \App\Models\TransactionDiscount::create([
                    'transaction_id' => $transaction->id,
                    'discount_id' => is_numeric($discountId) ? $discountId : null,
                    'discount_name' => $discountData['name'],
                    'discount_type' => $discountData['type'],
                    'discount_value' => $discountData['value'],
                    'discount_value_type' => $discountData['value_type'],
                    'discount_amount' => $discountAmount,
                    'product_id' => $discountData['product_id'] ?? null,
                ]);
            }

            // Clear cart and applied discounts
            $this->clearCart();

            \DB::commit();

            return $transaction->load(['items', 'discounts']);

        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Complete backdated transaction
     */
    public function completeBackdatedTransaction($orderType, $customDate, $partnerId = null, $paymentMethod = 'cash', $notes = null)
    {
        try {
            \DB::beginTransaction();
            
            $cart = $this->getCart();
            $appliedDiscounts = Session::get('applied_discounts', []);
            
            if (empty($cart)) {
                throw new \Exception('Keranjang kosong');
            }

            // Validate date range
            $targetDate = Carbon::parse($customDate);
            if ($targetDate->isAfter(now())) {
                throw new \Exception('Tanggal tidak boleh di masa depan');
            }

            if ($targetDate->isBefore(now()->subDays(30))) {
                throw new \Exception('Transaksi backdate maksimal 30 hari');
            }

            // Calculate totals
            $totals = $this->getCartTotals();

            // Create transaction dengan custom date
            $transaction = \App\Models\Transaction::create([
                'transaction_code' => $this->generateTransactionCode(),
                'subtotal' => $totals['subtotal'],
                'total_discount' => $totals['total_discount'],
                'tax_amount' => $totals['tax_amount'],
                'tax_rate' => $totals['tax_rate'],
                'service_charge_amount' => $totals['service_charge_amount'],
                'service_charge_rate' => $totals['service_charge_rate'],
                'final_total' => $totals['final_total'],
                'order_type' => $orderType,
                'partner_id' => $partnerId,
                'payment_method' => $paymentMethod,
                'cashier_name' => auth()->user()->name ?? 'Unknown',
                'notes' => $notes,
                'transaction_date' => $targetDate,
                'status' => 'completed',
                'user_id' => auth()->id(),
                'created_at' => $targetDate,
                'updated_at' => now(),
            ]);

            // Create transaction items - KasirKabuki tidak menggunakan stock management
            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);
                $appropriatePrice = $product->getAppropriatePrice($orderType, $partnerId);
                
                \App\Models\TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_price' => $appropriatePrice,
                    'quantity' => $item['quantity'],
                    'subtotal' => $appropriatePrice * $item['quantity'],
                    'discount_amount' => 0, // Default no discount per item
                    'total' => $appropriatePrice * $item['quantity'],
                    'created_at' => $targetDate,
                    'updated_at' => now(),
                ]);
            }

            // Create discount records
            foreach ($appliedDiscounts as $discountId => $discountData) {
                // Calculate actual discount amount based on type and value
                $discountAmount = 0;
                if ($discountData['type'] === 'product' && isset($cart[$discountData['product_id']])) {
                    $item = $cart[$discountData['product_id']];
                    $itemTotal = $item['price'] * $item['quantity'];
                    
                    if ($discountData['value_type'] === 'percentage') {
                        $discountAmount = $itemTotal * ($discountData['value'] / 100);
                    } else {
                        $discountAmount = $discountData['value'];
                    }
                } elseif ($discountData['type'] === 'transaction') {
                    $effectiveSubtotal = $totals['subtotal'] - ($totals['product_discount'] ?? 0);
                    
                    if ($discountData['value_type'] === 'percentage') {
                        $discountAmount = $effectiveSubtotal * ($discountData['value'] / 100);
                    } else {
                        $discountAmount = $discountData['value'];
                    }
                }
                
                \App\Models\TransactionDiscount::create([
                    'transaction_id' => $transaction->id,
                    'discount_id' => is_numeric($discountId) ? $discountId : null,
                    'discount_name' => $discountData['name'],
                    'discount_type' => $discountData['type'],
                    'discount_value' => $discountData['value'],
                    'discount_value_type' => $discountData['value_type'],
                    'discount_amount' => $discountAmount,
                    'product_id' => $discountData['product_id'] ?? null,
                    'created_at' => $targetDate,
                    'updated_at' => now(),
                ]);
            }

            // Clear cart and applied discounts
            $this->clearCart();

            \DB::commit();

            return $transaction->load(['items', 'discounts']);

        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get checkout summary with partner commission and tax-free calculations
     */
    public function getCheckoutSummary($orderType, $partnerId = null)
    {
        $cartTotals = $this->getCartTotals($orderType, $partnerId);
        
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
     * Cancel transaction
     */
    public function cancelTransaction($transactionId, $reason = null)
    {
        try {
            \DB::beginTransaction();

            $transaction = \App\Models\Transaction::findOrFail($transactionId);
            
            if ($transaction->status === 'cancelled') {
                throw new \Exception('Transaksi sudah dibatalkan');
            }

            // Update transaction status
            $transaction->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancellation_reason' => $reason
            ]);

            // KasirKabuki tidak menggunakan stock management
            // Tidak perlu return stock

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