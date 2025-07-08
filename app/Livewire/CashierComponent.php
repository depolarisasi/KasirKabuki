<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Partner;
use App\Models\Discount;
use App\Models\ProductPartnerPrice;
use App\Services\TransactionService;
use Livewire\Attributes\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class CashierComponent extends Component
{

    public $title = 'Kasir - KasirBraga';
    
    public $selectedCategory = 'all';
    public $searchProduct = '';
    public $orderType = 'dine_in'; // dine_in, take_away, online
    public $selectedPartner = null;
    
    // Order type labels for display
    public $orderTypeLabels = [
        'dine_in' => 'Makan di Tempat',
        'take_away' => 'Bawa Pulang',
        'online' => 'Online'
    ];
    
    // Saved order modal
    public $showSaveOrderModal = false;
    public $saveOrderName = '';
    public $orderName = '';
    public $showLoadOrderModal = false;
    public $currentLoadedOrder = null; // Track currently loaded order name for updates
    
    // Discount modal
    public $showDiscountModal = false;
    public $selectedDiscount = null;
    
    // Ad-hoc discount properties
    public $adhocDiscountPercentage = 0;
    public $adhocDiscountAmount = 0;
    
    // Checkout modal
    public $showCheckoutModal = false;
    public $paymentMethod = 'cash';
    public $paymentAmount = 0;
    public $checkoutNotes = '';
    public $checkoutSummary = [];
    
    // Receipt modal
    public $showReceiptModal = false;
    public $completedTransaction = null;
    
    protected $transactionService;

    public function boot(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function mount()
    {
        // Initialize any default values
    }

    public function render()
    {
        // Get products filtered by category and search
        $productsQuery = Product::with('category')
            ->when($this->selectedCategory !== 'all', function ($query) {
                $query->whereHas('category', function ($subQuery) {
                    $subQuery->where('id', $this->selectedCategory);
                });
            })
            ->when($this->searchProduct, function ($query) {
                $query->where('name', 'like', '%' . $this->searchProduct . '%');
            })
            ->orderBy('name');

        $products = $productsQuery->get();
        
        // Get categories for filter
        $categories = Category::orderBy('name')->get();
        
        // Get cart totals and items
        $cartData = $this->transactionService->getCartTotals();
        
        // Get available discounts
        $availableDiscounts = $this->transactionService->getAvailableDiscounts($this->orderType);
        
        // Get saved orders
        $savedOrders = $this->transactionService->getSavedOrders();
        
        // Get partners for online orders
        $partners = Partner::orderBy('name')->get();

        return view('livewire.cashier-component', [
            'products' => $products,
            'categories' => $categories,
            'cartData' => $cartData,
            'availableDiscounts' => $availableDiscounts,
            'savedOrders' => $savedOrders,
            'partners' => $partners,
            'orderTypeLabels' => $this->orderTypeLabels
        ]);
    }

    public function addToCart($productId)
    {
        try {
            $product = Product::with('category')->findOrFail($productId);
            
            // Get discounted price based on order type and selected partner (includes auto-discount)
            $price = $product->getDiscountedPrice($this->orderType, $this->selectedPartner);
            
            \Log::info('CashierComponent: Adding to cart with auto-discount pricing', [
                'product_id' => $productId,
                'order_type' => $this->orderType,
                'selected_partner' => $this->selectedPartner,
                'original_price' => $product->price,
                'appropriate_price' => $product->getAppropriatePrice($this->orderType, $this->selectedPartner),
                'discounted_price' => $price,
                'has_discount' => $product->hasActiveDiscount($this->orderType),
                'discount_amount' => $product->getDiscountAmount($this->orderType, $this->selectedPartner)
            ]);
            
            $this->transactionService->addToCartWithPrice($productId, 1, $price);
            
            $message = 'Produk ditambahkan ke keranjang.';
            if ($product->hasActiveDiscount($this->orderType)) {
                $discount = $product->getApplicableDiscount($this->orderType);
                $message .= " Diskon {$discount->formatted_value} otomatis diterapkan.";
            }
            
            LivewireAlert::title('Berhasil!')
            ->text($message)
            ->success()
            ->show();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menambahkan produk ke keranjang.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function updateCartQuantity($productId, $quantity)
    {
        try {
            $this->transactionService->updateCartQuantity($productId, $quantity);
            
            if ($quantity <= 0) {
                LivewireAlert::title('Berhasil!')
                ->text('Produk dihapus dari keranjang.')
                ->success() 
                ->show();
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat memperbarui keranjang.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()   
                ->show();
        }
    }

    public function removeFromCart($productId)
    {
        try {
            $this->transactionService->removeFromCart($productId);
            $this->info('Produk dihapus dari keranjang.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menghapus produk dari keranjang.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function clearCart()
    {
        try {
            $this->transactionService->clearCart();
            $this->currentLoadedOrder = null; // Reset loaded order when cart is cleared
            LivewireAlert::title('Berhasil!')
            ->text('Keranjang dikosongkan.')
            ->success()
            ->show();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat mengosongkan keranjang.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function updatedOrderType()
    {
        \Log::info('CashierComponent: Order type changed', [
            'new_order_type' => $this->orderType,
            'selected_partner' => $this->selectedPartner
        ]);
        
        // Clear applied discounts if switching to online
        if ($this->orderType === 'online') {
            $appliedDiscounts = $this->transactionService->getCartTotals()['applied_discounts'];
            foreach ($appliedDiscounts as $discountId => $discountData) {
                $this->transactionService->removeDiscount($discountId);
            }
            if (!empty($appliedDiscounts)) {
                LivewireAlert::title('Info')
                ->text('Diskon dihapus karena pesanan online tidak dapat menggunakan diskon.')
                ->warning()
                ->show();
            }
        } else {
            // Remove discounts when switching away from online
            $appliedDiscounts = $this->transactionService->getCartTotals()['applied_discounts'];
            foreach ($appliedDiscounts as $discountId => $discountData) {
                $this->transactionService->removeDiscount($discountId);
            }
            
            if (!empty($appliedDiscounts)) {
                LivewireAlert::title('Info')
                ->text('Diskon dihapus karena jenis pesanan berubah.')
                ->warning()
                ->show();
            }
        }
        
        // Reset partner selection when switching away from online
        if ($this->orderType !== 'online') {
            $this->selectedPartner = null;
        }
        
        // Reset ad-hoc discount inputs
        $this->adhocDiscountPercentage = 0;
        $this->adhocDiscountAmount = 0;
        
        // Refresh cart prices based on new order type
        $this->transactionService->refreshCartPrices($this->orderType, $this->selectedPartner);
        
        \Log::info('CashierComponent: Cart prices refreshed after order type change');
    }

    public function updatedSelectedPartner()
    {
        \Log::info('CashierComponent: Partner selection changed', [
            'order_type' => $this->orderType,
            'new_partner' => $this->selectedPartner
        ]);
        
        // Only refresh if order type is online
        if ($this->orderType === 'online') {
            // Refresh cart prices for partner pricing
            $this->transactionService->refreshCartPrices($this->orderType, $this->selectedPartner);
            
            if ($this->selectedPartner) {
                $partner = Partner::find($this->selectedPartner);
                
                LivewireAlert::title('Partner Dipilih')
                ->text("Harga partner untuk {$partner->name} telah diterapkan ke keranjang.")
                ->info()
                ->show();
            }
            
            \Log::info('CashierComponent: Cart prices refreshed after partner change');
        }
    }

    public function openDiscountModal()
    {
        if ($this->orderType === 'online') {
            LivewireAlert::title('Error!')
                ->text('Diskon tidak dapat diterapkan pada pesanan online.')
                ->error()
                ->show();
            return;
        }
        
        $this->showDiscountModal = true;
    }

    public function closeDiscountModal()
    {
        $this->showDiscountModal = false;
        $this->selectedDiscount = null;
    }

    public function applyDiscount($discountId)
    {
        try {
            $this->transactionService->applyDiscount($discountId, $this->orderType);
            LivewireAlert::title('Berhasil!')
            ->text('Diskon berhasil diterapkan.')
            ->success()
            ->show();
            $this->closeDiscountModal();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menerapkan diskon.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function removeDiscount($discountId)
    {
        try {
            $this->transactionService->removeDiscount($discountId);
            LivewireAlert::title('Berhasil!')
            ->text('Diskon dihapus.')
            ->success()
            ->show();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menghapus diskon.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function applyAdhocDiscount()
    {
        try {
            // Validate inputs
            if ((!$this->adhocDiscountPercentage && !$this->adhocDiscountAmount) || 
                ($this->adhocDiscountPercentage && $this->adhocDiscountAmount)) {
                LivewireAlert::title('Error!')
                ->text('Pilih salah satu: diskon % atau diskon nominal.')
                ->error()
                ->show();
                return;
            }

            if ($this->orderType === 'online') {
                LivewireAlert::title('Error!')
                ->text('Diskon tidak dapat diterapkan pada pesanan online.')
                ->error()
                ->show();
                return;
            }

            // Apply the ad-hoc discount
            if ($this->adhocDiscountPercentage > 0) {
                $this->transactionService->applyAdhocDiscount('percentage', $this->adhocDiscountPercentage, $this->orderType);
                LivewireAlert::title('Berhasil!')
                ->text("Diskon {$this->adhocDiscountPercentage}% berhasil diterapkan.")
                ->success()
                ->show();
            } else if ($this->adhocDiscountAmount > 0) {
                $this->transactionService->applyAdhocDiscount('nominal', $this->adhocDiscountAmount, $this->orderType);
                LivewireAlert::title('Berhasil!')
                ->text("Diskon Rp " . number_format($this->adhocDiscountAmount, 0, ',', '.') . " berhasil diterapkan.")
                ->success()
                ->show();
            }

            // Reset inputs
            $this->adhocDiscountPercentage = 0;
            $this->adhocDiscountAmount = 0;

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menerapkan diskon.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function openSaveOrderModal()
    {
        $cart = $this->transactionService->getCart();
        if (empty($cart)) {
            LivewireAlert::title('Error!')
            ->text('Keranjang kosong, tidak ada yang disimpan.')
            ->error()
            ->show();
            return;
        }
        
        $this->showSaveOrderModal = true;
        $this->orderName = ''; // Reset orderName for new save
    }

    public function closeSaveOrderModal()
    {
        $this->showSaveOrderModal = false;
        $this->orderName = ''; // Clear orderName when closing
    }

    public function saveOrder()
    {
        // Validate orderName is not empty
        if (empty(trim($this->orderName))) {
            LivewireAlert::title('Error!')
            ->text('Nama pesanan tidak boleh kosong.')
            ->error()
            ->show();
            return;
        }
        
        try {
            $this->transactionService->saveOrder($this->orderName);
            LivewireAlert::title('Berhasil!')
            ->text('Pesanan berhasil disimpan dengan nama "' . $this->orderName . '".')
            ->success()
            ->show();
            $this->closeSaveOrderModal();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menyimpan pesanan.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function openLoadOrderModal()
    {
        $this->showLoadOrderModal = true;
    }

    public function closeLoadOrderModal()
    {
        $this->showLoadOrderModal = false;
    }

    public function loadSavedOrder($orderName)
    {
        try {
            $this->transactionService->loadSavedOrder($orderName);
            $this->currentLoadedOrder = $orderName; // Track the loaded order
            LivewireAlert::title('Berhasil!')
            ->text('Pesanan "' . $orderName . '" berhasil dimuat.')
            ->success()
            ->show();
            $this->closeLoadOrderModal();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat memuat pesanan.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }
    
    public function updateSavedOrder()
    {
        try {
            if (!$this->currentLoadedOrder) {
                LivewireAlert::title('Error!')
                ->text('Tidak ada pesanan yang dimuat untuk diupdate.')
                ->error()
                ->show();
                return;
            }
            
            $this->transactionService->updateSavedOrder($this->currentLoadedOrder);
            LivewireAlert::title('Berhasil!')
            ->text('Pesanan "' . $this->currentLoadedOrder . '" berhasil diperbarui dengan perubahan terbaru.')
            ->success()
            ->show();
            $this->closeSaveOrderModal();
            $this->currentLoadedOrder = null; // Reset after update
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat memperbarui pesanan.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function deleteSavedOrder($orderName)
    {
        try {
            $this->transactionService->deleteSavedOrder($orderName);
                LivewireAlert::title('Berhasil!')
            ->text('Pesanan "' . $orderName . '" berhasil dihapus.')
            ->success()
            ->show();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menghapus pesanan.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function proceedToCheckout()
    {
        try {
            $this->transactionService->validateCartForCheckout();
            
            // Generate checkout summary
            $this->checkoutSummary = $this->transactionService->getCheckoutSummary($this->orderType, $this->selectedPartner);
            $this->paymentMethod = 'cash'; // Reset to default
            $this->checkoutNotes = '';
            $this->showCheckoutModal = true;
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat memproses checkout.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function openCheckoutModal()
    {
        try {
            $cart = $this->transactionService->getCart();
            if (empty($cart)) {
                LivewireAlert::title('Error!')
                ->text('Keranjang kosong, tidak ada yang di-checkout.')
                ->error()
                ->show();
                return;
            }
            
            \Log::info('CashierComponent: openCheckoutModal called', [
                'cart_count' => count($cart),
                'order_type' => $this->orderType,
                'selected_partner' => $this->selectedPartner
            ]);
            
            // Use getCheckoutSummary for consistent data structure with proceedToCheckout
            $this->checkoutSummary = $this->transactionService->getCheckoutSummary($this->orderType, $this->selectedPartner);
            
            \Log::info('CashierComponent: getCheckoutSummary result', [
                'checkout_summary_structure' => array_keys($this->checkoutSummary),
                'cart_totals_exists' => isset($this->checkoutSummary['cart_totals']),
                'cart_totals_keys' => isset($this->checkoutSummary['cart_totals']) ? array_keys($this->checkoutSummary['cart_totals']) : null,
                'subtotal_exists' => isset($this->checkoutSummary['cart_totals']['subtotal']),
                'final_total_exists' => isset($this->checkoutSummary['cart_totals']['final_total'])
            ]);
            
            $this->paymentMethod = 'cash'; // Reset to default
            $this->paymentAmount = 0;
            $this->checkoutNotes = '';
            $this->showCheckoutModal = true;
            
            \Log::info('CashierComponent: openCheckoutModal completed successfully');
            
        } catch (\Exception $e) {
            \Log::error('CashierComponent: openCheckoutModal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat membuka modal checkout.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function closeCheckoutModal()
    {
        $this->showCheckoutModal = false;
        $this->paymentMethod = 'cash';
        $this->paymentAmount = 0;
        $this->checkoutNotes = '';
        $this->checkoutSummary = [];
    }

    public function updatedPaymentMethod()
    {
        \Log::info('CashierComponent: Payment method updated', [
            'new_method' => $this->paymentMethod,
            'current_amount' => $this->paymentAmount,
            'final_total' => $this->checkoutSummary['cart_totals']['final_total'] ?? 0
        ]);

        // Reset payment amount when payment method changes
        if (in_array($this->paymentMethod, ['qris', 'aplikasi'])) {
            // For QRIS and Aplikasi, payment amount equals final total (exact payment)
            $this->paymentAmount = $this->checkoutSummary['cart_totals']['final_total'] ?? 0;
            \Log::info('CashierComponent: QRIS/Aplikasi selected - amount set to final total', [
                'method' => $this->paymentMethod,
                'amount' => $this->paymentAmount
            ]);
        } else {
            // For cash, reset to 0 so user can input
            $this->paymentAmount = 0;
            \Log::info('CashierComponent: Cash selected - amount reset to 0');
        }

        // Force UI refresh to ensure immediate update
        $this->dispatch('paymentMethodChanged', [
            'method' => $this->paymentMethod,
            'amount' => $this->paymentAmount
        ]);
    }

    public function getKembalianProperty()
    {
        if (in_array($this->paymentMethod, ['qris', 'aplikasi'])) {
            return 0; // QRIS and Aplikasi always exact payment
        }
        
        $finalTotal = $this->checkoutSummary['cart_totals']['final_total'] ?? 0;
        return max(0, $this->paymentAmount - $finalTotal);
    }

    public function completeTransaction()
    {
        try {
            // Validate payment method
            if (!in_array($this->paymentMethod, ['cash', 'qris', 'aplikasi'])) {
                LivewireAlert::title('Error!')
                ->text('Metode pembayaran tidak valid.')
                ->error()
                ->show();
                return;
            }

            // Validate payment amount for cash transactions
            if ($this->paymentMethod === 'cash') {
                $finalTotal = $this->checkoutSummary['cart_totals']['final_total'] ?? 0;
                
                if ($this->paymentAmount <= 0) {
                    LivewireAlert::title('Error!')
                    ->text('Jumlah uang yang diterima harus diisi.')
                    ->error()
                    ->show();
                    return;
                }
                
                if ($this->paymentAmount < $finalTotal) {
                    LivewireAlert::title('Error!')
                    ->text('Jumlah uang yang diterima kurang dari total pembayaran.')
                    ->error()
                    ->show();
                    return;
                }
            }

            // Complete the transaction
            $transaction = $this->transactionService->completeTransaction(
                $this->orderType,
                $this->selectedPartner,
                $this->paymentMethod,
                $this->checkoutNotes
            );
            
            // Broadcast real-time event for sales report updates
            $this->dispatch('transaction-completed', [
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code,
                'final_total' => $transaction->final_total,
                'created_at' => $transaction->created_at->toISOString(),
                'order_type' => $transaction->order_type,
                'payment_method' => $transaction->payment_method
            ])->to(SalesReportComponent::class);
            
            // Show success notification and receipt modal
            $this->completedTransaction = $transaction;
            $this->showReceiptModal = true;
            $this->closeCheckoutModal();
            $this->currentLoadedOrder = null; // Reset loaded order when transaction is completed
            
            LivewireAlert::title('Berhasil!')
                ->text('Transaksi berhasil diselesaikan dengan kode: ' . $transaction->transaction_code)
                ->success()
                ->show();
            
        } catch (\Exception $e) {
            \Log::error('CashierComponent: Error in completeTransaction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menyelesaikan transaksi.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function closeReceiptModal()
    {
        $this->showReceiptModal = false;
        $this->completedTransaction = null;
    }

    public function printReceipt()
    {
        if ($this->completedTransaction) {
            // Open receipt in new window for printing with payment amount for kembalian calculation
            $receiptUrl = route('staf.receipt.print', [
                'transaction' => $this->completedTransaction->id,
                'payment_amount' => $this->paymentAmount
            ]);
            $this->dispatch('open-receipt-window', ['url' => $receiptUrl]);
        }
    }

    public function printAndClose()
    {
        $this->printReceipt();
        $this->closeReceiptModal();
    }

    public function updatedSearchProduct()
    {
        // No action needed, search will be handled in render()
    }

    public function setCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
    }

    public function getCartItemsProperty()
    {
        return $this->transactionService->getCart();
    }

    public function getCartTotalProperty()
    {
        return $this->transactionService->getCartTotals()['final_total'];
    }
}
