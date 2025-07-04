<div class="min-h-screen bg-base-200 p-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
        <!-- Products Section (Left Side) -->
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <!-- Header with Search and Actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
<div>
                            <h1 class="text-2xl font-bold">Point of Sales</h1>
                            <p class="text-base-content/70">Pilih produk untuk menambahkan ke keranjang</p>
                        </div>
                        
                        <div class="flex gap-2 w-full md:w-auto">
                            <button wire:click="openLoadOrderModal" class="btn btn-outline btn-info btn-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                </svg>
                                Muat Pesanan
                            </button>
                            
                            <button wire:click="openSaveOrderModal" class="btn btn-outline btn-warning btn-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan Pesanan
                            </button>
                        </div>
                    </div>

                    <!-- Search and Category Filter -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Cari Produk</span>
                            </label>
                            <input wire:model.live="searchProduct" type="text" 
                                   placeholder="Ketik nama produk..." 
                                   class="input input-bordered w-full" />
                        </div>
                        
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Filter Kategori</span>
                            </label>
                            <select wire:model.live="selectedCategory" class="select select-bordered w-full">
                                <option value="all">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @forelse($products as $product)
                            <div class="card bg-base-100 border border-base-300 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                                 wire:click="addToCart({{ $product->id }})">
                                <div class="card-body p-4">
                                    <div class="flex items-center justify-center bg-primary/10 rounded-lg h-16 mb-3">
                                        <span class="text-2xl font-bold text-primary">
                                            {{ strtoupper(substr($product->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    
                                    <h3 class="font-semibold text-sm text-center mb-2 line-clamp-2">
                                        {{ $product->name }}
                                    </h3>
                                    
                                    <div class="text-center">
                                        <div class="badge badge-outline badge-xs mb-2">{{ $product->category->name }}</div>
                                        <div class="text-lg font-bold text-primary">
                                            {{ 'Rp ' . number_format($product->price, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <svg class="w-16 h-16 text-base-content/30 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p class="text-base-content/70">
                                    @if($searchProduct || $selectedCategory !== 'all')
                                        Tidak ada produk yang sesuai dengan filter
                                    @else
                                        Belum ada produk tersedia
                                    @endif
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Shopping Cart Section (Right Side) -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-xl sticky top-4">
                <div class="card-body">
                    <!-- Cart Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Keranjang Belanja</h2>
                        @if(!empty($cartData['cart_items']))
                            <button wire:click="clearCart" class="btn btn-ghost btn-sm text-error">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Kosongkan
                            </button>
                        @endif
                    </div>

                    <!-- Order Type Selection -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Jenis Pesanan</span>
                        </label>
                        <select wire:model.live="orderType" class="select select-bordered w-full">
                            <option value="dine_in">Makan di Tempat</option>
                            <option value="take_away">Bawa Pulang</option>
                            <option value="online">Online</option>
                        </select>
                    </div>

                    <!-- Partner Selection (only for online orders) -->
                    @if($orderType === 'online')
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Partner Online</span>
                            </label>
                            <select wire:model="selectedPartner" class="select select-bordered w-full">
                                <option value="">Pilih Partner</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }} ({{ $partner->commission_rate }}%)</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Cart Items -->
                    <div class="flex-1 max-h-64 overflow-y-auto mb-4">
                        @forelse($cartData['cart_items'] as $productId => $item)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg mb-2">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-sm truncate">{{ $item['name'] }}</h4>
                                    <p class="text-xs text-base-content/60">{{ $item['category'] }}</p>
                                    <p class="text-sm font-bold text-primary">
                                        {{ 'Rp ' . number_format($item['price'], 0, ',', '.') }}
                                    </p>
                                </div>
                                
                                <div class="flex items-center gap-2 ml-2">
                                    <button wire:click="updateCartQuantity({{ $productId }}, {{ $item['quantity'] - 1 }})" 
                                            class="btn btn-xs btn-circle btn-outline">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    
                                    <span class="w-8 text-center font-semibold">{{ $item['quantity'] }}</span>
                                    
                                    <button wire:click="updateCartQuantity({{ $productId }}, {{ $item['quantity'] + 1 }})" 
                                            class="btn btn-xs btn-circle btn-outline">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                    
                                    <button wire:click="removeFromCart({{ $productId }})" 
                                            class="btn btn-xs btn-circle btn-error ml-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-base-content/30 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5 0a2 2 0 100-4 2 2 0 000 4zm6 0a2 2 0 100-4 2 2 0 000 4z"></path>
                                </svg>
                                <p class="text-base-content/70 text-sm">Keranjang kosong</p>
                                <p class="text-base-content/50 text-xs">Pilih produk untuk memulai</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Applied Discounts -->
                    @if(!empty($cartData['applied_discounts']))
                        <div class="mb-4">
                            <h4 class="font-semibold text-sm mb-2">Diskon Diterapkan</h4>
                            @foreach($cartData['applied_discounts'] as $discountId => $discount)
                                <div class="flex items-center justify-between p-2 bg-success/10 rounded-lg mb-1">
                                    <div class="flex-1">
                                        <span class="text-sm font-semibold text-success">{{ $discount['name'] }}</span>
                                        <span class="text-xs text-success/70 block">
                                            {{ $discount['type'] === 'product' ? 'Diskon Produk' : 'Diskon Transaksi' }}
                                        </span>
                                    </div>
                                    <button wire:click="removeDiscount({{ $discountId }})" 
                                            class="btn btn-xs btn-circle btn-ghost">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Discount Button -->
                    @if($orderType !== 'online' && !empty($cartData['cart_items']))
                        <button wire:click="openDiscountModal" class="btn btn-outline btn-success btn-sm w-full mb-4">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Tambah Diskon
                        </button>
                    @endif

                    <!-- Cart Summary -->
                    @if(!empty($cartData['cart_items']))
                        <div class="border-t pt-4">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Subtotal ({{ $cartData['total_items'] }} item)</span>
                                    <span>{{ 'Rp ' . number_format($cartData['subtotal'], 0, ',', '.') }}</span>
                                </div>
                                
                                @if($cartData['total_discount'] > 0)
                                    <div class="flex justify-between text-success">
                                        <span>Total Diskon</span>
                                        <span>-{{ 'Rp ' . number_format($cartData['total_discount'], 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                
                                <div class="flex justify-between text-lg font-bold border-t pt-2">
                                    <span>Total</span>
                                    <span class="text-primary">{{ 'Rp ' . number_format($cartData['final_total'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <button wire:click="proceedToCheckout" 
                                class="btn btn-primary w-full mt-4"
                                @if($orderType === 'online' && !$selectedPartner) disabled @endif>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Lanjut ke Pembayaran
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Discount Modal -->
    @if($showDiscountModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Pilih Diskon</h3>
                
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @forelse($availableDiscounts as $discount)
                        <div class="card bg-base-200 cursor-pointer hover:bg-base-300 transition-colors"
                             wire:click="applyDiscount({{ $discount->id }})">
                            <div class="card-body p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold">{{ $discount->name }}</h4>
                                        <p class="text-sm text-base-content/70">
                                            {{ $discount->type === 'product' ? 'Diskon Produk' : 'Diskon Transaksi' }}
                                            @if($discount->type === 'product' && $discount->product)
                                                - {{ $discount->product->name }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="badge badge-success">
                                            @if($discount->value_type === 'percentage')
                                                {{ $discount->value }}%
                                            @else
                                                Rp {{ number_format($discount->value, 0, ',', '.') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-base-content/70 py-8">Tidak ada diskon yang tersedia</p>
                    @endforelse
                </div>

                <div class="modal-action">
                    <button wire:click="closeDiscountModal" class="btn">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Save Order Modal -->
    @if($showSaveOrderModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Simpan Pesanan</h3>
                
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Nama Pesanan</span>
                    </label>
                    <input wire:model="saveOrderName" type="text" 
                           placeholder="Contoh: Meja 5, Pelanggan A, dll" 
                           class="input input-bordered w-full" />
                </div>

                <div class="modal-action">
                    <button wire:click="closeSaveOrderModal" class="btn btn-ghost">Batal</button>
                    <button wire:click="saveOrder" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Load Order Modal -->
    @if($showLoadOrderModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Muat Pesanan Tersimpan</h3>
                
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @forelse($savedOrders as $orderName => $order)
                        <div class="card bg-base-200">
                            <div class="card-body p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold">{{ $order['name'] }}</h4>
                                        <p class="text-sm text-base-content/70">
                                            {{ count($order['cart']) }} item - 
                                            {{ 'Rp ' . number_format($order['totals']['final_total'], 0, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-base-content/50">
                                            {{ $order['created_at']->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button wire:click="loadSavedOrder('{{ $orderName }}')" 
                                                class="btn btn-sm btn-primary">Muat</button>
                                        <button wire:click="deleteSavedOrder('{{ $orderName }}')" 
                                                class="btn btn-sm btn-error">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-base-content/70 py-8">Tidak ada pesanan tersimpan</p>
                    @endforelse
                </div>

                <div class="modal-action">
                    <button wire:click="closeLoadOrderModal" class="btn">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Checkout Modal -->
    @if($showCheckoutModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-2xl">
                <h3 class="font-bold text-lg mb-4">Konfirmasi Pembayaran</h3>
                
                <!-- Order Summary -->
                <div class="bg-base-200 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold mb-3">Ringkasan Pesanan</h4>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Jenis Pesanan</span>
                            <span class="font-semibold">
                                @if($orderType === 'dine_in') Makan di Tempat
                                @elseif($orderType === 'take_away') Bawa Pulang
                                @else Online
                                @endif
                            </span>
                        </div>
                        
                        @if(isset($checkoutSummary['partner']))
                            <div class="flex justify-between">
                                <span>Partner Online</span>
                                <span class="font-semibold">{{ $checkoutSummary['partner']->name }}</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between">
                            <span>Total Item</span>
                            <span>{{ $checkoutSummary['cart_totals']['total_items'] ?? 0 }} item</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span>{{ 'Rp ' . number_format($checkoutSummary['cart_totals']['subtotal'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        
                        @if(($checkoutSummary['cart_totals']['total_discount'] ?? 0) > 0)
                            <div class="flex justify-between text-success">
                                <span>Total Diskon</span>
                                <span>-{{ 'Rp ' . number_format($checkoutSummary['cart_totals']['total_discount'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                        
                        @if(($checkoutSummary['partner_commission'] ?? 0) > 0)
                            <div class="flex justify-between text-warning">
                                <span>Komisi Partner ({{ $checkoutSummary['partner']->commission_rate }}%)</span>
                                <span>-{{ 'Rp ' . number_format($checkoutSummary['partner_commission'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                        
                        <div class="border-t pt-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total Pembayaran</span>
                                <span class="text-primary">{{ 'Rp ' . number_format($checkoutSummary['cart_totals']['final_total'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        @if(($checkoutSummary['partner_commission'] ?? 0) > 0)
                            <div class="flex justify-between text-sm text-info">
                                <span>Pendapatan Bersih</span>
                                <span>{{ 'Rp ' . number_format($checkoutSummary['net_revenue'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">Metode Pembayaran</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="label cursor-pointer border rounded-lg p-3 {{ $paymentMethod === 'cash' ? 'border-primary bg-primary/10' : 'border-base-300' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="font-semibold">Tunai</span>
                            </div>
                            <input wire:model="paymentMethod" type="radio" value="cash" class="radio radio-primary" />
                        </label>
                        
                        <label class="label cursor-pointer border rounded-lg p-3 {{ $paymentMethod === 'qris' ? 'border-primary bg-primary/10' : 'border-base-300' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-semibold">QRIS</span>
                            </div>
                            <input wire:model="paymentMethod" type="radio" value="qris" class="radio radio-primary" />
                        </label>
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Catatan (Opsional)</span>
                    </label>
                    <textarea wire:model="checkoutNotes" 
                              placeholder="Catatan tambahan untuk transaksi ini..."
                              class="textarea textarea-bordered h-20"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="modal-action">
                    <button wire:click="closeCheckoutModal" class="btn btn-ghost">Batal</button>
                    <button wire:click="completeTransaction" class="btn btn-primary">
                        <span wire:loading.remove wire:target="completeTransaction">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Selesaikan Transaksi
                        </span>
                        <span wire:loading wire:target="completeTransaction">
                            <span class="loading loading-spinner loading-sm"></span>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Receipt Modal -->
    @if($showReceiptModal && $completedTransaction)
        <div class="modal modal-open">
            <div class="modal-box max-w-md">
                <h3 class="font-bold text-lg mb-4 text-center text-success">
                    🎉 Transaksi Berhasil!
                </h3>
                
                <!-- Transaction Success Info -->
                <div class="bg-success/10 rounded-lg p-4 mb-4 text-center">
                    <div class="text-success font-bold text-lg mb-2">
                        {{ $completedTransaction->transaction_code }}
                    </div>
                    <div class="text-sm text-success/70 mb-2">
                        {{ $completedTransaction->created_at->format('d F Y, H:i') }}
                    </div>
                    <div class="text-2xl font-bold text-success">
                        {{ $completedTransaction->formatted_final_total }}
                    </div>
                    <div class="text-xs text-success/70 mt-1">
                        {{ $completedTransaction->payment_method_label }} • {{ $completedTransaction->order_type_label }}
                    </div>
                </div>

                <!-- Transaction Items Summary -->
                <div class="bg-base-200 rounded-lg p-3 mb-4">
                    <h4 class="font-semibold text-sm mb-2">Ringkasan Item</h4>
                    <div class="space-y-1 max-h-32 overflow-y-auto">
                        @foreach($completedTransaction->items as $item)
                            <div class="flex justify-between text-xs">
                                <span>{{ $item->quantity }}x {{ $item->product_name }}</span>
                                <span>{{ $item->formatted_total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="modal-action">
                    <button wire:click="closeReceiptModal" class="btn btn-ghost btn-sm">
                        Tutup
                    </button>
                    <button wire:click="printReceipt" class="btn btn-outline btn-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Lihat Struk
                    </button>
                    <button wire:click="printAndClose" class="btn btn-primary btn-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H3a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H7a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                        </svg>
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    // Handle receipt window opening
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-receipt-window', (event) => {
            // Open receipt in new window optimized for printing
            const receiptWindow = window.open(
                event.url, 
                'receipt', 
                'width=400,height=600,scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no'
            );
            
            // Focus the new window
            if (receiptWindow) {
                receiptWindow.focus();
            }
        });
    });
</script>
