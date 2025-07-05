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

class CashierComponent extends Component
{
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
            Alert::success('Berhasil!', 'Produk ditambahkan ke keranjang.');
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());
        }
    }

    public function updateCartQuantity($productId, $quantity)
    {
        try {
            $this->transactionService->updateCartQuantity($productId, $quantity);
            
            if ($quantity <= 0) {
                Alert::info('Info', 'Produk dihapus dari keranjang.');
            }
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());
        }
    }

    public function removeFromCart($productId)
    {
        try {
            $this->transactionService->removeFromCart($productId);
            Alert::info('Info', 'Produk dihapus dari keranjang.');
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());
        }
    }

    public function clearCart()
    {
        try {
            $this->transactionService->clearCart();
            Alert::success('Berhasil!', 'Keranjang dikosongkan.');
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());
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
            Alert::info('Info', 'Diskon dihapus karena pesanan online tidak dapat menggunakan diskon.');
        }
        
        // Reset partner selection
        $this->selectedPartner = null;
    }

    public function openDiscountModal()
    {
        if ($this->orderType === 'online') {
            Alert::error('Error!', 'Diskon tidak dapat diterapkan pada pesanan online.');
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
            Alert::success('Berhasil!', 'Diskon berhasil diterapkan.');
            $this->closeDiscountModal();
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());
        }
    }

    public function removeDiscount($discountId)
    {
        try {
            $this->transactionService->removeDiscount($discountId);
            Alert::info('Info', 'Diskon dihapus dari keranjang.');
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());
        }
    }

    public function openSaveOrderModal()
    {
        $cart = $this->transactionService->getCart();
        if (empty($cart)) {
            Alert::error('Error!', 'Keranjang kosong, tidak ada yang disimpan.');
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
            Alert::error('Error!', 'Nama pesanan tidak boleh kosong.');
            return;
        }
        
        try {
            $this->transactionService->saveOrder($this->saveOrderName);
            Alert::success('Berhasil!', 'Pesanan berhasil disimpan dengan nama "' . $this->saveOrderName . '".');
            $this->closeSaveOrderModal();
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());
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
            Alert::success('Berhasil!', 'Pesanan "' . $orderName . '" berhasil dimuat.');
            $this->closeLoadOrderModal();
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());
        }
    }

    public function deleteSavedOrder($orderName)
    {
        try {
            $this->transactionService->deleteSavedOrder($orderName);
            Alert::success('Berhasil!', 'Pesanan "' . $orderName . '" berhasil dihapus.');
        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());
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
            Alert::error('Error!', $e->getMessage());
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
                Alert::error('Error!', 'Metode pembayaran tidak valid.');
                return;
            }

            // Complete the transaction
            $transaction = $this->transactionService->completeTransaction(
                $this->orderType,
                $this->selectedPartner,
                $this->paymentMethod,
                $this->checkoutNotes
            );

            Alert::success('Berhasil!', 'Transaksi berhasil diselesaikan. Kode transaksi: ' . $transaction->transaction_code);
            
            // Store completed transaction for receipt
            $this->completedTransaction = $transaction->load(['user', 'partner', 'items.product']);
            
            // Reset form
            $this->closeCheckoutModal();
            $this->orderType = 'dine_in';
            $this->selectedPartner = null;
            
            // Show receipt modal
            $this->showReceiptModal = true;

        } catch (\Exception $e) {
            Alert::error('Error!', $e->getMessage());
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
