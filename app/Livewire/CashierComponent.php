<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Partner;
use App\Models\Discount;
use App\Services\TransactionService;
use Livewire\Attributes\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use Masmerise\Toaster\Toastable;

class CashierComponent extends Component
{
    use Toastable;

    public $title = 'Kasir - KasirBraga';
    
    public $selectedCategory = 'all';
    public $searchProduct = '';
    public $orderType = 'dine_in'; // dine_in, take_away, online
    public $selectedPartner = null;
    
    // Saved order modal
    public $showSaveOrderModal = false;
    public $saveOrderName = '';
    public $showLoadOrderModal = false;
    
    // Discount modal
    public $showDiscountModal = false;
    public $selectedDiscount = null;
    
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
            'partners' => $partners
        ]);
    }

    public function addToCart($productId)
    {
        try {
            $this->transactionService->addToCart($productId, 1);
            $this->success('Produk ditambahkan ke keranjang.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menambahkan produk ke keranjang.';
            $this->error($errorMessage);
        }
    }

    public function updateCartQuantity($productId, $quantity)
    {
        try {
            $this->transactionService->updateCartQuantity($productId, $quantity);
            
            if ($quantity <= 0) {
                $this->info('Produk dihapus dari keranjang.');
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat memperbarui keranjang.';
            $this->error($errorMessage);
        }
    }

    public function removeFromCart($productId)
    {
        try {
            $this->transactionService->removeFromCart($productId);
            $this->info('Produk dihapus dari keranjang.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menghapus produk dari keranjang.';
            $this->error($errorMessage);
        }
    }

    public function clearCart()
    {
        try {
            $this->transactionService->clearCart();
            $this->success('Keranjang dikosongkan.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat mengosongkan keranjang.';
            $this->error($errorMessage);
        }
    }

    public function updatedOrderType()
    {
        // Clear applied discounts if switching to online
        if ($this->orderType === 'online') {
            $appliedDiscounts = $this->transactionService->getCartTotals()['applied_discounts'];
            foreach ($appliedDiscounts as $discountId => $discountData) {
                $this->transactionService->removeDiscount($discountId);
            }
            $this->info('Diskon dihapus karena pesanan online tidak dapat menggunakan diskon.');
        }
        
        // Reset partner selection
        $this->selectedPartner = null;
    }

    public function openDiscountModal()
    {
        if ($this->orderType === 'online') {
            $this->error('Diskon tidak dapat diterapkan pada pesanan online.');
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
            $this->success('Diskon berhasil diterapkan.');
            $this->closeDiscountModal();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menerapkan diskon.';
            $this->error($errorMessage);
        }
    }

    public function removeDiscount($discountId)
    {
        try {
            $this->transactionService->removeDiscount($discountId);
            $this->info('Diskon dihapus dari keranjang.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menghapus diskon.';
            $this->error($errorMessage);
        }
    }

    public function openSaveOrderModal()
    {
        $cart = $this->transactionService->getCart();
        if (empty($cart)) {
            $this->error('Keranjang kosong, tidak ada yang disimpan.');
            return;
        }
        
        $this->showSaveOrderModal = true;
        $this->saveOrderName = '';
    }

    public function closeSaveOrderModal()
    {
        $this->showSaveOrderModal = false;
        $this->saveOrderName = '';
    }

    public function saveOrder()
    {
        if (empty(trim($this->saveOrderName))) {
            $this->error('Nama pesanan tidak boleh kosong.');
            return;
        }
        
        try {
            $this->transactionService->saveOrder($this->saveOrderName);
            $this->success('Pesanan berhasil disimpan dengan nama "' . $this->saveOrderName . '".');
            $this->closeSaveOrderModal();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menyimpan pesanan.';
            $this->error($errorMessage);
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
            $this->success('Pesanan "' . $orderName . '" berhasil dimuat.');
            $this->closeLoadOrderModal();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat memuat pesanan.';
            $this->error($errorMessage);
        }
    }

    public function deleteSavedOrder($orderName)
    {
        try {
            $this->transactionService->deleteSavedOrder($orderName);
            $this->success('Pesanan "' . $orderName . '" berhasil dihapus.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menghapus pesanan.';
            $this->error($errorMessage);
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
            $this->error($errorMessage);
        }
    }

    public function openCheckoutModal()
    {
        try {
            $cart = $this->transactionService->getCart();
            if (empty($cart)) {
                $this->error('Keranjang kosong, tidak ada yang di-checkout.');
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
            $this->error($errorMessage);
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
        // Reset payment amount when payment method changes
        if ($this->paymentMethod === 'qris') {
            // For QRIS, payment amount equals final total (exact payment)
            $this->paymentAmount = $this->checkoutSummary['cart_totals']['final_total'] ?? 0;
        } else {
            // For cash, reset to 0 so user can input
            $this->paymentAmount = 0;
        }
    }

    public function getKembalianProperty()
    {
        if ($this->paymentMethod === 'qris') {
            return 0; // QRIS always exact payment
        }
        
        $finalTotal = $this->checkoutSummary['cart_totals']['final_total'] ?? 0;
        return max(0, $this->paymentAmount - $finalTotal);
    }

    public function completeTransaction()
    {
        try {
            // Validate payment method
            if (!in_array($this->paymentMethod, ['cash', 'qris'])) {
                $this->error('Metode pembayaran tidak valid.');
                return;
            }

            // Validate payment amount for cash transactions
            if ($this->paymentMethod === 'cash') {
                $finalTotal = $this->checkoutSummary['cart_totals']['final_total'] ?? 0;
                
                if ($this->paymentAmount <= 0) {
                    $this->error('Jumlah uang yang diterima harus diisi.');
                    return;
                }
                
                if ($this->paymentAmount < $finalTotal) {
                    $this->error('Jumlah uang yang diterima kurang dari total pembayaran.');
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
            
            // Show success notification and receipt modal
            $this->completedTransaction = $transaction;
            $this->showReceiptModal = true;
            $this->closeCheckoutModal();
            
            $this->success('Transaksi berhasil diselesaikan dengan kode: ' . $transaction->transaction_code);
            
        } catch (\Exception $e) {
            \Log::error('CashierComponent: Error in completeTransaction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menyelesaikan transaksi.';
            $this->error($errorMessage);
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
