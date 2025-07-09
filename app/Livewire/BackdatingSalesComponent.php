<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Partner;
use App\Models\Discount;
use App\Services\TransactionService;
use Carbon\Carbon;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class BackdatingSalesComponent extends Component
{
    public $title = 'Backdating Sales - KasirBraga';
    
    // Date selection for backdating
    public $selectedDate;
    public $maxDate;
    
    // Cashier functionality (copied from CashierComponent)
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

    protected $rules = [
        'selectedDate' => 'required|date|before_or_equal:today',
    ];

    protected $messages = [
        'selectedDate.required' => 'Tanggal harus dipilih.',
        'selectedDate.date' => 'Format tanggal tidak valid.',
        'selectedDate.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
    ];

    public function boot(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function mount()
    {
        // Set default date to today
        $this->selectedDate = now()->format('Y-m-d');
        $this->maxDate = now()->format('Y-m-d');
    }

    public function updatedSelectedDate()
    {
        $this->validateDate();
    }

    private function validateDate()
    {
        $this->validate(['selectedDate' => 'required|date|before_or_equal:today']);
    }

    public function render()
    {
        // Get products filtered by category and search with eager loading
        $productsQuery = Product::with(['category', 'activeComponents.componentProduct', 'partnerPrices'])
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
        
        // Get available discounts with eager loading
        $availableDiscounts = $this->transactionService->getAvailableDiscounts($this->orderType);
        
        // Get partners for online orders
        $partners = Partner::orderBy('name')->get();

        return view('livewire.backdating-sales-component', [
            'products' => $products,
            'categories' => $categories,
            'cartData' => $cartData,
            'availableDiscounts' => $availableDiscounts,
            'partners' => $partners,
            'orderTypeLabels' => $this->orderTypeLabels,
            'formattedDate' => Carbon::parse($this->selectedDate)->translatedFormat('l, d F Y')
        ]);
    }

    public function addToCart($productId)
    {
        try {
            $this->transactionService->addToCart($productId);
            
            LivewireAlert::title('Berhasil!')
                ->text('Produk berhasil ditambahkan ke keranjang.')
                ->success()
                ->show();
                
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menambahkan produk.';
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
            
            LivewireAlert::title('Berhasil!')
                ->text('Produk berhasil dihapus dari keranjang.')
                ->success()
                ->show();
                
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menghapus produk.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function updateQuantity($productId, $quantity)
    {
        try {
            if ($quantity <= 0) {
                $this->transactionService->removeFromCart($productId);
            } else {
                $this->transactionService->updateCartQuantity($productId, $quantity);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat mengupdate kuantitas.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function clearCart()
    {
        $this->transactionService->clearCart();
        
        LivewireAlert::title('Berhasil!')
            ->text('Keranjang berhasil dikosongkan.')
            ->success()
            ->show();
    }

    public function updatedOrderType()
    {
        try {
            $this->transactionService->refreshCartPrices($this->orderType, $this->selectedPartner);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat mengupdate harga.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function updatedSelectedPartner()
    {
        try {
            $this->transactionService->refreshCartPrices($this->orderType, $this->selectedPartner);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat mengupdate harga partner.';
            LivewireAlert::title('Terjadi kesalahan!')
                ->text($errorMessage)
                ->error()
                ->show();
        }
    }

    public function proceedToCheckout()
    {
        try {
            // Validate date first
            $this->validateDate();
            
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
        if (in_array($this->paymentMethod, ['qris', 'aplikasi'])) {
            // For QRIS and Aplikasi, payment amount equals final total (exact payment)
            $this->paymentAmount = $this->checkoutSummary['cart_totals']['final_total'] ?? 0;
        } else {
            // For cash, reset to 0 so user can input
            $this->paymentAmount = 0;
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
            // Validate date first
            $this->validateDate();
            
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

            // Complete the backdated transaction with custom date
            // Parameters: ($orderType, $customDate, $partnerId, $paymentMethod, $notes)
            $transaction = $this->transactionService->completeBackdatedTransaction(
                $this->orderType,
                $this->selectedDate, // Custom date for backdating
                $this->selectedPartner,
                $this->paymentMethod,
                $this->checkoutNotes
            );
            
            // Store completed transaction for receipt
            $this->completedTransaction = $transaction;
            
            // Clear cart and close checkout modal
            $this->closeCheckoutModal();
            
            // Show success and receipt modal
            LivewireAlert::title('Transaksi Berhasil!')
                ->text("Transaksi backdating berhasil disimpan dengan tanggal {$this->selectedDate}.")
                ->success()
                ->show();
                
            $this->showReceiptModal = true;

        } catch (\Exception $e) {
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
            $receiptUrl = route('receipt.print', [
                'transaction' => $this->completedTransaction->id,
                'payment_amount' => $this->paymentAmount
            ]);
            
            // Dispatch event to open receipt window
            $this->dispatch('open-receipt-window', ['url' => $receiptUrl]);
        } else {
            LivewireAlert::title('Error!')
                ->text('Tidak ada transaksi yang dapat dicetak.')
                ->error()
                ->show();
        }
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