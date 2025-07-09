<div>
    {{-- Alert messages --}}
    @if (session()->has('success'))
        <div class="alert alert-success mb-4">
            <svg class="w-6 h-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error mb-4">
            <svg class="w-6 h-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Date Selection Section --}}
    <div class="card bg-base-100 shadow-lg mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg text-primary mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6M8 7l4 4m0 0l4-4m4 4v6m0 0l-4-4m4 4l4-4"></path>
                </svg>
                Pilih Tanggal Transaksi
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Tanggal Transaksi</span>
                    </label>
                    <input type="date" 
                           wire:model.live="selectedDate" 
                           max="{{ $maxDate }}"
                           class="input input-bordered w-full @error('selectedDate') input-error @enderror" />
                    @error('selectedDate')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>
                
                <div class="flex items-end">
                    <div class="stats stats-vertical lg:stats-horizontal shadow w-full">
                        <div class="stat">
                            <div class="stat-title">Tanggal Dipilih</div>
                            <div class="stat-value text-lg">{{ $formattedDate }}</div>
                            <div class="stat-desc">
                                @if($selectedDate === $maxDate)
                                    <span class="badge badge-success">Hari ini</span>
                                @else
                                    <span class="badge badge-warning">Backdating</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Products Section --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Order Type & Search --}}
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex flex-col lg:flex-row gap-4 mb-4">
                        {{-- Order Type Selection --}}
                        <div class="form-control flex-1">
                            <label class="label">
                                <span class="label-text font-medium">Jenis Pesanan</span>
                            </label>
                            <select wire:model.live="orderType" class="select select-bordered w-full">
                                @foreach($orderTypeLabels as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Partner Selection (for online orders) --}}
                        @if($orderType === 'online')
                            <div class="form-control flex-1">
                                <label class="label">
                                    <span class="label-text font-medium">Partner</span>
                                </label>
                                <select wire:model.live="selectedPartner" class="select select-bordered w-full">
                                    <option value="">Pilih Partner</option>
                                    @foreach($partners as $partner)
                                        <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Search --}}
                        <div class="form-control flex-1">
                            <label class="label">
                                <span class="label-text font-medium">Cari Produk</span>
                            </label>
                            <input type="text" 
                                   wire:model.live.debounce.300ms="searchProduct" 
                                   placeholder="Nama produk..." 
                                   class="input input-bordered w-full" />
                        </div>
                    </div>

                    {{-- Category Filter --}}
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button wire:click="setCategory('all')" 
                                class="btn btn-sm {{ $selectedCategory === 'all' ? 'btn-primary' : 'btn-outline' }}">
                            Semua
                        </button>
                        @foreach($categories as $category)
                            <button wire:click="setCategory('{{ $category->id }}')" 
                                    class="btn btn-sm {{ $selectedCategory == $category->id ? 'btn-primary' : 'btn-outline' }}">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Products Grid --}}
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Daftar Produk</h3>
                    
                    @if($products->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($products as $product)
                                <div class="card card-compact bg-base-200 shadow-md hover:shadow-lg transition-shadow">
                                    @if($product->photo)
                                        <figure class="h-32">
                                            <img src="{{ asset($product->photo) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="h-full w-full object-cover" />
                                        </figure>
                                    @endif
                                    
                                    <div class="card-body">
                                        <h4 class="card-title text-sm">{{ $product->name }}</h4>
                                        <p class="text-xs text-base-content/70">{{ $product->category->name }}</p>
                                        
                                        {{-- Price Display --}}
                                        <div class="flex items-center justify-between mt-2">
                                            <div class="text-sm font-bold text-primary">
                                                Rp {{ number_format($product->getAppropriatePrice($orderType, $selectedPartner), 0, ',', '.') }}
                                            </div>
                                            
                                            @if($product->hasPartnerPrice($orderType, $selectedPartner))
                                                <div class="text-xs line-through text-base-content/50">
                                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="card-actions justify-end mt-3">
                                            <button wire:click="addToCart({{ $product->id }})" 
                                                    class="btn btn-primary btn-sm w-full">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <p class="text-base-content/50">Tidak ada produk ditemukan</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Cart Section --}}
        <div class="space-y-6">
            {{-- Cart Summary --}}
            <div class="card bg-base-100 shadow-lg sticky top-4">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13h10M9 19v2a2 2 0 11-4 0v-2m4 0H9m0 0h6v2a2 2 0 11-4 0v-2m-4 0h4"></path>
                        </svg>
                        Keranjang Belanja
                    </h3>

                    {{-- Cart Items --}}
                    @if(!empty($cartData['cart_items']))
                        <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                            @foreach($cartData['cart_items'] as $productId => $item)
                                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                    <div class="flex-1 min-w-0 mr-3">
                                        <p class="text-sm font-medium truncate">{{ $item['name'] }}</p>
                                        <p class="text-xs text-base-content/70">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <button wire:click="updateQuantity({{ $productId }}, {{ $item['quantity'] - 1 }})" 
                                                class="btn btn-circle btn-xs btn-outline">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </button>
                                        
                                        <span class="text-sm font-bold w-8 text-center">{{ $item['quantity'] }}</span>
                                        
                                        <button wire:click="updateQuantity({{ $productId }}, {{ $item['quantity'] + 1 }})" 
                                                class="btn btn-circle btn-xs btn-outline">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                        
                                        <button wire:click="removeFromCart({{ $productId }})" 
                                                class="btn btn-circle btn-xs btn-error btn-outline ml-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Cart Totals --}}
                        <div class="divider my-4"></div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal ({{ $cartData['total_items'] }} item):</span>
                                <span>Rp {{ number_format($cartData['subtotal'], 0, ',', '.') }}</span>
                            </div>
                            
                            @if($cartData['total_discount'] > 0)
                                <div class="flex justify-between text-sm text-success">
                                    <span>Total Diskon:</span>
                                    <span>-Rp {{ number_format($cartData['total_discount'], 0, ',', '.') }}</span>
                                </div>
                            @endif
                            
                            <div class="flex justify-between font-bold text-lg border-t pt-2">
                                <span>Total:</span>
                                <span class="text-primary">Rp {{ number_format($cartData['final_total'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="divider my-4"></div>
                        <div class="space-y-2">
                            <button wire:click="proceedToCheckout" 
                                    class="btn btn-primary w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Proses Pembayaran
                            </button>
                            
                            <button wire:click="clearCart" 
                                    class="btn btn-outline btn-error w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Kosongkan Keranjang
                            </button>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13h10M9 19v2a2 2 0 11-4 0v-2m4 0H9m0 0h6v2a2 2 0 11-4 0v-2m-4 0h4"></path>
                            </svg>
                            <p class="text-base-content/50 mb-4">Keranjang kosong</p>
                            <p class="text-xs text-base-content/40">Pilih produk untuk memulai transaksi backdating</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Checkout Modal --}}
    @if($showCheckoutModal)
        <div class="modal modal-open">
            <div class="modal-box w-11/12 max-w-2xl">
                <h3 class="font-bold text-lg mb-4">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Konfirmasi Pembayaran - Backdating
                </h3>

                {{-- Backdating Info Alert --}}
                <div class="alert alert-warning mb-4">
                    <svg class="w-6 h-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div>
                        <h4 class="font-bold">Transaksi Backdating</h4>
                        <p class="text-sm">Transaksi ini akan disimpan dengan tanggal: <strong>{{ $formattedDate }}</strong></p>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="bg-base-200 p-4 rounded-lg mb-4">
                    <h4 class="font-semibold mb-3">Ringkasan Pesanan</h4>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Jenis Pesanan:</span>
                            <span class="font-medium">{{ $orderTypeLabels[$orderType] }}</span>
                        </div>
                        
                        @if($orderType === 'online' && isset($checkoutSummary['partner']))
                            <div class="flex justify-between">
                                <span>Partner:</span>
                                <span class="font-medium">{{ $checkoutSummary['partner']->name }}</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($checkoutSummary['cart_totals']['subtotal'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        
                        @if(($checkoutSummary['cart_totals']['total_discount'] ?? 0) > 0)
                            <div class="flex justify-between text-success">
                                <span>Diskon:</span>
                                <span>-Rp {{ number_format($checkoutSummary['cart_totals']['total_discount'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                        
                        @if(($checkoutSummary['partner_commission'] ?? 0) > 0)
                            <div class="flex justify-between text-warning">
                                <span>Komisi Partner:</span>
                                <span>-Rp {{ number_format($checkoutSummary['partner_commission'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                        
                        <div class="divider my-2"></div>
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total Pembayaran:</span>
                            <span class="text-primary">Rp {{ number_format($checkoutSummary['cart_totals']['final_total'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Metode Pembayaran</span>
                    </label>
                    <select wire:model.live="paymentMethod" class="select select-bordered w-full">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="qris">QRIS</option>
                        <option value="aplikasi">Aplikasi (GoPay/OVO/Dana)</option>
                    </select>
                </div>

                {{-- Payment Amount (for cash) --}}
                @if($paymentMethod === 'cash')
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Uang Diterima</span>
                        </label>
                        <input type="number" 
                               wire:model.live="paymentAmount" 
                               placeholder="Masukkan jumlah uang..." 
                               class="input input-bordered w-full" 
                               min="0" />
                        
                        @if($paymentAmount > 0 && ($checkoutSummary['cart_totals']['final_total'] ?? 0) > 0)
                            <label class="label">
                                <span class="label-text-alt">
                                    Kembalian: 
                                    <span class="font-bold {{ $this->kembalian >= 0 ? 'text-success' : 'text-error' }}">
                                        Rp {{ number_format($this->kembalian, 0, ',', '.') }}
                                    </span>
                                </span>
                            </label>
                        @endif
                    </div>
                @else
                    <div class="alert alert-info mb-4">
                        <svg class="w-6 h-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Pembayaran {{ strtoupper($paymentMethod) }} - Pastikan pembayaran telah diterima sebelum melanjutkan</span>
                    </div>
                @endif

                {{-- Notes --}}
                <div class="form-control mb-6">
                    <label class="label">
                        <span class="label-text font-medium">Catatan (Opsional)</span>
                    </label>
                    <textarea wire:model="checkoutNotes" 
                              placeholder="Catatan tambahan..." 
                              class="textarea textarea-bordered" 
                              rows="3"></textarea>
                </div>

                {{-- Modal Actions --}}
                <div class="modal-action">
                    <button wire:click="closeCheckoutModal" class="btn btn-outline">
                        Batal
                    </button>
                    <button wire:click="completeTransaction" 
                            class="btn btn-primary"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Selesaikan Transaksi
                        </span>
                        <span wire:loading>
                            <span class="loading loading-spinner loading-sm mr-2"></span>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Receipt Modal --}}
    @if($showReceiptModal && $completedTransaction)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4 text-center">
                    <svg class="w-6 h-6 inline mr-2 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Transaksi Berhasil!
                </h3>
                
                <div class="text-center mb-6">
                    <div class="text-2xl font-bold text-primary mb-2">{{ $completedTransaction->transaction_code }}</div>
                    <div class="text-sm text-base-content/70">{{ $formattedDate }}</div>
                </div>

                <div class="bg-base-200 p-4 rounded-lg mb-6">
                    <div class="flex justify-between items-center text-lg font-bold">
                        <span>Total:</span>
                        <span class="text-primary">Rp {{ number_format($completedTransaction->final_total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="alert alert-success mb-4">
                    <svg class="w-6 h-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Transaksi backdating telah berhasil disimpan dengan tanggal {{ $formattedDate }}</span>
                </div>

                <div class="modal-action">
                    <button wire:click="closeReceiptModal" class="btn btn-outline">
                        Tutup
                    </button>
                    <button wire:click="printReceipt" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 00-1 1v4a1 1 0 001 1zm3-5h2m-2-3h2m-2-3h2"></path>
                        </svg>
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- JavaScript for Receipt Window --}}
    <script>
        document.addEventListener('livewire:loaded', () => {
            Livewire.on('open-receipt-window', (data) => {
                window.open(data.url, 'receipt-window', 'width=300,height=600,scrollbars=yes');
            });
        });
    </script>
</div> 