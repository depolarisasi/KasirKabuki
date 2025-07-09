<div class="min-h-screen bg-base-200 p-2">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6  mx-auto">
        <!-- Products Section (Left Side) -->
        <div class="lg:col-span-2">
            <div class="card shadow-xl">
                <div class="card-body">
                    <!-- Header with Search and Actions -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold">Point of Sales</h1>
                            <p class="text-white">Kasir: {{ auth()->user()->name }}</p>
                            @if ($currentLoadedOrder)
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="badge badge-info badge-sm">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        Pesanan dimuat: {{ $currentLoadedOrder }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Search and Category Filter -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Cari Produk</span>
                            </label>
                            <input wire:model.live="searchProduct" type="text" placeholder="Ketik nama produk..."
                                class="input input-bordered w-full" />
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Filter Kategori</span>
                            </label>
                            <select wire:model.live="selectedCategory" class="select select-bordered w-full">
                                <option value="all">Semua Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @forelse($products as $product)
                            <div class="card bg-base-300 border border-base-300 shadow-sm hover:shadow-md transition-shadow cursor-pointer"
                                wire:click="addToCart({{ $product->id }})">
                                <div class="card-body p-4">
                                    <div
                                        class="flex items-center justify-center bg-primary/10 rounded-lg mb-3 overflow-hidden">
                                        @if ($product->photo)
                                            <img src="{{ $product->photo_url }}" alt="{{ $product->name }}"
                                                class="w-full h-full object-cover rounded-lg" />
                                        @else
                                            <span class="text-2xl font-bold text-primary">
                                                {{ strtoupper(substr($product->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="font-semibold text-sm text-center mb-2 line-clamp-2">
                                        {{ $product->name }}
                                    </h3>

                                    <div class="text-center">
                                        <div class="badge badge-outline badge-xs mb-2">{{ $product->category->name }}
                                        </div>
                                        @php
                                            $basePrice = $product->getAppropriatePrice($orderType, $selectedPartner);
                                            $discountedPrice = $product->getDiscountedPrice(
                                                $orderType,
                                                $selectedPartner,
                                            );
                                            $hasDiscount = $product->hasActiveDiscount($orderType);
                                            $isPartnerPrice =
                                                $orderType === 'online' &&
                                                $selectedPartner &&
                                                $basePrice != $product->price;
                                        @endphp

                                        @if ($hasDiscount)
                                            <!-- Product has auto-discount -->
                                            <div class="text-lg font-bold text-success">
                                                {{ 'Rp ' . number_format($discountedPrice, 0, ',', '.') }}
                                            </div>
                                            <div class="text-sm text-base-content/60 line-through">
                                                {{ 'Rp ' . number_format($basePrice, 0, ',', '.') }}
                                            </div>
                                            <div class="badge badge-success badge-xs mt-1">
                                                @php $discount = $product->getApplicableDiscount($orderType); @endphp
                                                Diskon {{ $discount->formatted_value }}
                                            </div>
                                        @else
                                            <!-- No auto-discount, show normal price -->
                                            <div class="text-lg font-bold text-primary">
                                                {{ 'Rp ' . number_format($basePrice, 0, ',', '.') }}
                                            </div>
                                            @if ($isPartnerPrice)
                                                <div class="text-xs text-base-content/60 line-through">
                                                    {{ 'Rp ' . number_format($product->price, 0, ',', '.') }}
                                                </div>
                                                <div class="badge badge-warning badge-xs mt-1">
                                                    Partner Price
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <svg class="w-16 h-16 text-base-content/30 mx-auto mb-4" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p class="text-base-content/70">
                                    @if ($searchProduct || $selectedCategory !== 'all')
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
            <div class="card bg-base-300 shadow-xl sticky top-4">
                <div class="card-body">
                    <!-- Cart Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Keranjang Belanja</h2>
                        @if (!empty($cartData['cart_items']))
                            <button wire:click="clearCart" class="btn btn-ghost btn-sm text-error">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                                Kosongkan
                            </button>
                        @endif
                    </div>

                    <!-- Save/Load Order Buttons -->
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <button wire:click="openLoadOrderModal" class="btn btn-outline btn-info btn-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                            </svg>
                            Muat Pesanan
                        </button>

                        <button wire:click="openSaveOrderModal" class="btn btn-outline btn-warning btn-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if ($currentLoadedOrder)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                @endif
                            </svg>
                            @if ($currentLoadedOrder)
                                Update Pesanan
                            @else
                                Simpan Pesanan
                            @endif
                        </button>
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
                    @if ($orderType === 'online')
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Partner Online</span>
                            </label>
                            <select wire:model.live="selectedPartner" class="select select-bordered w-full">
                                <option value="">Pilih Partner</option>
                                @foreach ($partners as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }}
                                        ({{ $partner->commission_rate }}%)</option>
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
                                    <button
                                        wire:click="updateCartQuantity({{ $productId }}, {{ $item['quantity'] - 1 }})"
                                        class="btn btn-xs btn-circle btn-outline">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 12H4"></path>
                                        </svg>
                                    </button>

                                    <span class="w-8 text-center font-semibold">{{ $item['quantity'] }}</span>

                                    <button
                                        wire:click="updateCartQuantity({{ $productId }}, {{ $item['quantity'] + 1 }})"
                                        class="btn btn-xs btn-circle btn-outline">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>

                                    <!-- Add separator space before delete button -->
                                    <div class="w-2"></div>

                                    <button wire:click="removeFromCart({{ $productId }})"
                                        class="btn btn-xs btn-circle btn-error">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-base-content/30 mx-auto mb-2" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5 0a2 2 0 100-4 2 2 0 000 4zm6 0a2 2 0 100-4 2 2 0 000 4z">
                                    </path>
                                </svg>
                                <p class="text-base-content/70 text-sm">Keranjang kosong</p>
                                <p class="text-base-content/50 text-xs">Pilih produk untuk memulai</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Applied Discounts -->
                    @if (!empty($cartData['applied_discounts']))
                        <div class="mb-4">
                            <h4 class="font-semibold text-sm mb-2">Diskon Diterapkan</h4>
                            @foreach ($cartData['applied_discounts'] as $discountId => $discount)
                                <div class="flex justify-between items-center p-2 bg-warning/10 rounded-lg mb-2">
                                    <div class="flex-1">
                                        <span class="text-sm font-medium">{{ $discount['name'] }}</span>
                                        <span class="text-xs text-base-content/60 block">
                                            @if ($discount['type'] === 'product')
                                                Produk: {{ $discount['product_name'] ?? 'N/A' }}
                                            @else
                                                Diskon Total
                                            @endif
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-bold text-warning">
                                            @if ($discount['value_type'] === 'percentage')
                                                -{{ $discount['value'] }}%
                                            @else
                                                -Rp {{ number_format($discount['value'], 0, ',', '.') }}
                                            @endif
                                        </span>
                                        <button wire:click="removeDiscount({{ $discountId }})"
                                            class="btn btn-xs btn-circle btn-ghost ml-2">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Quick Discount Addition -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Tambah Diskon</span>
                        </label>
                        <div class="flex gap-2">
                            <select wire:model="selectedDiscount" class="select select-bordered flex-1">
                                <option value="">Pilih Diskon</option>
                                @foreach ($availableDiscounts as $discount)
                                    <option value="{{ $discount->id }}">
                                        {{ $discount->name }}
                                        (@if ($discount->value_type === 'percentage')
                                            {{ $discount->value }}%
                                        @else
                                            Rp {{ number_format($discount->value, 0, ',', '.') }}
                                        @endif)
                                    </option>
                                @endforeach
                            </select>
                            <button wire:click="addDiscount" class="btn btn-outline btn-sm"
                                @if (!$selectedDiscount) disabled @endif>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Ad-hoc Discount -->
                    @if ($orderType !== 'online')
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-semibold">Diskon Cepat</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text text-xs">Diskon %</span>
                                    </label>
                                    <input wire:model.live="adhocDiscountPercentage" type="number" step="0.1"
                                        min="0" max="100" placeholder="0"
                                        class="input input-bordered input-sm" />
                                </div>
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text text-xs">Diskon Rp</span>
                                    </label>
                                    <input wire:model.live="adhocDiscountAmount" type="number" step="1000"
                                        min="0" placeholder="0" class="input input-bordered input-sm" />
                                </div>
                            </div>
                            <button wire:click="applyAdhocDiscount" class="btn btn-warning btn-sm w-full"
                                @if ((!$adhocDiscountPercentage && !$adhocDiscountAmount) || ($adhocDiscountPercentage && $adhocDiscountAmount)) disabled @endif>
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                    </path>
                                </svg>
                                Terapkan Diskon Cepat
                            </button>
                            @if ($adhocDiscountPercentage && $adhocDiscountAmount)
                                <div class="text-xs text-error mt-1">
                                    Pilih salah satu: diskon % atau diskon nominal
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Cart Totals -->
                    @if (!empty($cartData['cart_items']))
                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal:</span>
                                <span>{{ 'Rp ' . number_format($cartData['subtotal'] ?? 0, 0, ',', '.') }}</span>
                            </div>

                            @if (($cartData['total_discount'] ?? 0) > 0)
                                <div class="flex justify-between text-sm text-warning">
                                    <span>Total Diskon:</span>
                                    <span>-{{ 'Rp ' . number_format($cartData['total_discount'], 0, ',', '.') }}</span>
                                </div>
                            @endif

                            @if ($orderType === 'online' && $selectedPartner && ($cartData['commission'] ?? 0) > 0)
                                <div class="flex justify-between text-sm text-info">
                                    <span>Komisi Partner:</span>
                                    <span>{{ 'Rp ' . number_format($cartData['commission'], 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span>Total:</span>
                                <span
                                    class="text-primary">{{ 'Rp ' . number_format($cartData['final_total'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <button wire:click="openCheckoutModal" class="btn btn-primary w-full mt-4">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            Checkout
                            ({{ isset($cartData['cart_items']) && is_array($cartData['cart_items']) ? array_sum(array_column($cartData['cart_items'], 'quantity')) : 0 }}
                            item)
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Load Order Modal -->
    @if ($showLoadOrderModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Muat Pesanan Tersimpan</h3>

                @if (count($savedOrders) > 0)
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach ($savedOrders as $orderName => $order)
                            <div class="border border-base-300 rounded-lg p-3">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-semibold">{{ $orderName }}</h4>
                                        <p class="text-sm text-base-content/60">
                                            {{ \Carbon\Carbon::parse($order['created_at'])->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold">Rp
                                            {{ number_format($order['cart_totals']['final_total'] ?? 0, 0, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-base-content/60">{{ count($order['cart']) }} item</p>
                                    </div>
                                </div>

                                <div class="flex gap-2 mt-3">
                                    <button wire:click="loadSavedOrder('{{ $orderName }}')"
                                        class="btn btn-primary btn-sm flex-1">
                                        Muat Pesanan
                                    </button>
                                    <button wire:click="deleteSavedOrder('{{ $orderName }}')"
                                        class="btn btn-error btn-outline btn-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-base-content/30 mx-auto mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <p class="text-base-content/70">Belum ada pesanan tersimpan</p>
                    </div>
                @endif

                <div class="modal-action">
                    <button wire:click="closeLoadOrderModal" class="btn btn-ghost">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Save Order Modal -->
    @if ($showSaveOrderModal)
        <div class="modal modal-open">
            <div class="modal-box">
                @if ($currentLoadedOrder)
                    <h3 class="font-bold text-lg mb-4">Update atau Simpan Pesanan</h3>

                    <!-- Current Loaded Order Info -->
                    <div class="bg-info/10 rounded-lg p-3 mb-4">
                        <div class="flex items-center gap-2 text-info">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm font-semibold">Pesanan dimuat: "{{ $currentLoadedOrder }}"</span>
                        </div>
                        <p class="text-xs text-info/70 mt-1">Anda dapat memperbarui pesanan ini atau menyimpan sebagai
                            pesanan baru.</p>
                    </div>
                @else
                    <h3 class="font-bold text-lg mb-4">Simpan Pesanan</h3>
                @endif

                @if (!$currentLoadedOrder)
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Nama Pesanan</span>
                        </label>
                        <input wire:model="orderName" type="text" placeholder="Masukkan nama untuk pesanan ini..."
                            class="input input-bordered" />
                        @error('orderName')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                @else
                    <!-- Show input for new order name when updating -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Nama Pesanan Baru (untuk simpan sebagai pesanan baru)</span>
                        </label>
                        <input wire:model="orderName" type="text"
                            placeholder="Masukkan nama untuk pesanan baru..." class="input input-bordered" />
                        @error('orderName')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                @endif

                <!-- Order Summary -->
                @if (!empty($cartData['cart_items']))
                    <div class="bg-base-200 rounded-lg p-3 mb-4">
                        <h4 class="font-semibold text-sm mb-2">Ringkasan Pesanan</h4>
                        <div class="space-y-1">
                            @foreach ($cartData['cart_items'] as $item)
                                <div class="flex justify-between text-xs">
                                    <span>{{ $item['quantity'] }}x {{ $item['name'] }}</span>
                                    <span>{{ 'Rp ' . number_format($item['quantity'] * $item['price'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t mt-2 pt-2 flex justify-between font-semibold text-sm">
                            <span>Total:</span>
                            <span>{{ 'Rp ' . number_format($cartData['final_total'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif

                <div class="modal-action">
                    <button wire:click="closeSaveOrderModal" class="btn btn-ghost">Batal</button>

                    @if ($currentLoadedOrder)
                        <!-- Update existing order button -->
                        <button wire:click="updateSavedOrder" class="btn btn-warning">
                            <span wire:loading.remove wire:target="updateSavedOrder">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Update "{{ $currentLoadedOrder }}"
                            </span>
                            <span wire:loading wire:target="updateSavedOrder">
                                <span class="loading loading-spinner loading-sm"></span>
                                Mengupdate...
                            </span>
                        </button>

                        <!-- Save as new order button (only if orderName is filled) -->
                        <button wire:click="saveOrder" class="btn btn-primary"
                            @if (empty(trim($orderName))) disabled @endif>
                            <span wire:loading.remove wire:target="saveOrder">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Simpan Sebagai Baru
                            </span>
                            <span wire:loading wire:target="saveOrder">
                                <span class="loading loading-spinner loading-sm"></span>
                                Menyimpan...
                            </span>
                        </button>
                    @else
                        <!-- Regular save button for new orders -->
                        <button wire:click="saveOrder" class="btn btn-primary">
                            <span wire:loading.remove wire:target="saveOrder">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan Pesanan
                            </span>
                            <span wire:loading wire:target="saveOrder">
                                <span class="loading loading-spinner loading-sm"></span>
                                Menyimpan...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Checkout Modal -->
    @if ($showCheckoutModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-2xl">
                <h3 class="font-bold text-lg mb-4">Checkout Transaksi</h3>

                <!-- Order Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left: Items List -->
                    <div>
                        <h4 class="font-semibold mb-3">Detail Pesanan</h4>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach ($checkoutSummary['cart_totals']['cart_items'] ?? [] as $item)
                                <div class="flex justify-between items-start p-3 bg-base-200 rounded-lg">
                                    <div class="flex-1">
                                        <h5 class="font-medium text-sm">{{ $item['name'] }}</h5>
                                        <p class="text-xs text-base-content/60">{{ $item['category'] }}</p>
                                        <p class="text-sm">
                                            {{ $item['quantity'] }}x
                                            {{ 'Rp ' . number_format($item['price'], 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        @php $itemSubtotal = $item['price'] * $item['quantity']; @endphp
                                        <p class="font-semibold">
                                            {{ 'Rp ' . number_format($itemSubtotal, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Right: Totals & Order Info -->
                    <div>
                        <div class="bg-base-200 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold mb-3">Informasi Pesanan</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Jenis Pesanan:</span>
                                    <span class="font-medium">{{ $orderTypeLabels[$orderType] ?? $orderType }}</span>
                                </div>

                                @if ($orderType === 'online' && $selectedPartner)
                                    @php $partnerData = $partners->find($selectedPartner); @endphp
                                    <div class="flex justify-between">
                                        <span>Partner:</span>
                                        <span class="font-medium">{{ $partnerData->name ?? 'N/A' }}</span>
                                    </div>
                                @endif

                                <div class="flex justify-between">
                                    <span>Total Item:</span>
                                    <span
                                        class="font-medium">{{ $checkoutSummary['cart_totals']['total_items'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Totals Breakdown -->
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>{{ 'Rp ' . number_format($checkoutSummary['cart_totals']['subtotal'] ?? 0, 0, ',', '.') }}</span>
                            </div>

                            @if (($checkoutSummary['cart_totals']['total_discount'] ?? 0) > 0)
                                <div class="flex justify-between text-warning">
                                    <span>Total Diskon:</span>
                                    <span>-{{ 'Rp ' . number_format($checkoutSummary['cart_totals']['total_discount'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            @if ($orderType === 'online' && ($checkoutSummary['partner_commission'] ?? 0) > 0)
                                <div class="flex justify-between text-info">
                                    <span>Komisi Partner:</span>
                                    <span>{{ 'Rp ' . number_format($checkoutSummary['partner_commission'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span>Total Akhir:</span>
                                <span
                                    class="text-primary">{{ 'Rp ' . number_format($checkoutSummary['cart_totals']['final_total'] ?? 0, 0, ',', '.') }}</span>
                            </div>

                            @if ($orderType === 'online' && $selectedPartner && ($checkoutSummary['partner_commission'] ?? 0) > 0)
                                <div class="flex justify-between text-sm text-base-content/70 mt-2">
                                    <span>Net Revenue Toko:</span>
                                    <span>{{ 'Rp ' . number_format($checkoutSummary['net_revenue'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-semibold">Metode Pembayaran</span>
                    </label>
                    <div class="grid {{ $orderType === 'online' ? 'grid-cols-3' : 'grid-cols-2' }} gap-3">
                        <label
                            class="label cursor-pointer border rounded-lg p-3 {{ $paymentMethod === 'cash' ? 'border-primary bg-primary/10' : 'border-base-300' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <span class="font-semibold">Tunai</span>
                            </div>
                            <input wire:model.live="paymentMethod" type="radio" value="cash"
                                class="radio radio-primary" />
                        </label>

                        <label
                            class="label cursor-pointer border rounded-lg p-3 {{ $paymentMethod === 'qris' ? 'border-primary bg-primary/10' : 'border-base-300' }}">
                            <div class="flex items-center space-x-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span class="font-semibold">QRIS</span>
                            </div>
                            <input wire:model.live="paymentMethod" type="radio" value="qris"
                                class="radio radio-primary" />
                        </label>

                        @if ($orderType === 'online')
                            <label
                                class="label cursor-pointer border rounded-lg p-3 {{ $paymentMethod === 'aplikasi' ? 'border-primary bg-primary/10' : 'border-base-300' }}">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span class="font-semibold">Aplikasi</span>
                                </div>
                                <input wire:model.live="paymentMethod" type="radio" value="aplikasi"
                                    class="radio radio-primary" />
                            </label>
                        @endif
                    </div>
                </div>

                <!-- Payment Amount Input (Cash Only) -->
                @if ($paymentMethod === 'cash')
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-semibold">Jumlah Uang Diterima</span>
                        </label>
                        <input wire:model.live="paymentAmount" type="number" step="1000"
                            min="{{ $checkoutSummary['cart_totals']['final_total'] ?? 0 }}"
                            placeholder="Masukkan jumlah uang yang diterima..."
                            class="input w-full input-bordered text-lg font-semibold">

                        <!-- Kembalian Display -->
                        @if ($paymentAmount > 0)
                            @php $kembalian = $this->kembalian; @endphp
                            <div class="mt-3 p-3 bg-base-200 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium">Kembalian:</span>
                                    <span
                                        class="text-lg font-bold {{ $kembalian >= 0 ? 'text-success' : 'text-error' }}">
                                        Rp {{ number_format($kembalian, 0, ',', '.') }}
                                    </span>
                                </div>
                                @if ($kembalian < 0)
                                    <div class="text-xs text-error mt-1">
                                        Uang tidak cukup! Kurang Rp {{ number_format(abs($kembalian), 0, ',', '.') }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Notes -->
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Catatan (Opsional)</span>
                    </label>
                    <textarea wire:model="checkoutNotes" placeholder="Catatan tambahan untuk transaksi ini..."
                        class="textarea textarea-bordered w-full h-20"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="modal-action">
                    <button wire:click="closeCheckoutModal" class="btn btn-secondary">Batal</button>
                    <button wire:click="completeTransaction" class="btn btn-primary"
                        @if (
                            $paymentMethod === 'cash' &&
                                ($paymentAmount <= 0 || $paymentAmount < ($checkoutSummary['cart_totals']['final_total'] ?? 0))) disabled @endif>
                        <span wire:loading.remove wire:target="completeTransaction"> SELESAIKAN PESANAN
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
    @if ($showReceiptModal && $completedTransaction)
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
                        {{ $completedTransaction->payment_method_label }} •
                        {{ $completedTransaction->order_type_label }}
                    </div>
                </div>

                <!-- Transaction Items Summary -->
                <div class="bg-base-200 rounded-lg p-3 mb-4">
                    <h4 class="font-semibold text-sm mb-2">Ringkasan Item</h4>
                    <div class="space-y-1 max-h-32 overflow-y-auto">
                        @foreach ($completedTransaction->items as $item)
                            <div class="flex justify-between text-xs">
                                <span>{{ $item->quantity }}x {{ $item->product_name }}</span>
                                <span>{{ $item->formatted_total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="modal-action flex-col sm:flex-row gap-2 mt-6">
                    <!-- Primary button group (stacked on mobile, row on desktop) -->
                    <div class="flex flex-col sm:flex-row w-full gap-2 order-2 sm:order-1">
                        <button wire:click="printReceipt" class="btn btn-primary btn-sm flex-1 sm:flex-initial">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H3a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H7a2 2 0 00-2 2v6a2 2 0 002 2z">
                                </path>
                            </svg>
                            Print Struk
                        </button>

                        <a href="{{ route('staf.transactions.show', $completedTransaction->id) }}"
                            class="btn btn-outline btn-sm flex-1 sm:flex-initial">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Detail Transaksi
                        </a>
                    </div>

                    <!-- Secondary button (close) -->
                    <div class="order-1 sm:order-2">
                        <button wire:click="closeReceiptModal" class="btn btn-ghost btn-sm w-full sm:w-auto">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Kembali ke Kasir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Floating Cart Info Element (Mobile Only) -->
    @if (!empty($cartData['cart_items']))
        <div class="fixed right-4 z-50 lg:hidden" style="bottom: 4.5rem;">
            <button wire:click="openCheckoutModal" class="bg-primary text-primary-content px-4 py-3 rounded-full shadow-lg hover:bg-primary-focus transition-colors duration-200">
                <div class="flex items-center gap-3 text-sm font-semibold">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5 0a2 2 0 100-4 2 2 0 000 4zm6 0a2 2 0 100-4 2 2 0 000 4z">
                            </path>
                        </svg>
                        <span>{{ array_sum(array_column($cartData['cart_items'], 'quantity')) }} Item</span>
                    </div>
                    <div class="border-l border-primary-content/30 pl-3">
                        <span>{{ 'Rp ' . number_format($cartData['final_total'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </button>
        </div>
    @endif

    <!-- JavaScript Section -->
    <script>
        // Handle receipt window opening - Livewire 3.x compatible
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-receipt-window', (event) => {
                console.log('Opening receipt window with event:', event);
                
                // Extract URL from event data (Livewire 3.x sends array format)
                const eventData = Array.isArray(event) ? event[0] : event;
                const receiptUrl = eventData.url || eventData;
                
                console.log('Receipt URL:', receiptUrl);
                
                if (!receiptUrl) {
                    console.error('No receipt URL provided');
                    alert('Error: Tidak dapat membuka struk - URL tidak tersedia');
                    return;
                }
                
                try {
                    // Open receipt in new window optimized for printing
                    const receiptWindow = window.open(
                        receiptUrl,
                        'receipt',
                        'width=400,height=600,scrollbars=yes,resizable=yes,menubar=no,toolbar=no,location=no'
                    );

                    // Check if window was successfully opened
                    if (receiptWindow) {
                        receiptWindow.focus();
                        console.log('Receipt window opened successfully');
                    } else {
                        console.error('Failed to open receipt window - popup may be blocked');
                        alert('Error: Tidak dapat membuka jendela struk. Pastikan popup blocker tidak aktif.');
                    }
                } catch (error) {
                    console.error('Error opening receipt window:', error);
                    alert('Error: Terjadi kesalahan saat membuka struk - ' + error.message);
                }
            });

            // Handle payment method changes for better UI feedback
            Livewire.on('paymentMethodChanged', (event) => {
                console.log('Payment method changed:', event);

                // Add visual feedback for payment method selection
                const cashLabel = document.querySelector('label:has(input[value="cash"])');
                const qrisLabel = document.querySelector('label:has(input[value="qris"])');

                if (event.method === 'cash') {
                    console.log('Switched to Cash - amount should be editable');
                    // Ensure payment amount field gets focus when switching to cash
                    setTimeout(() => {
                        const amountInput = document.querySelector(
                            'input[wire\\:model\\.live="paymentAmount"]');
                        if (amountInput) {
                            amountInput.focus();
                        }
                    }, 100);
                } else if (event.method === 'qris') {
                    console.log('Switched to QRIS - amount auto-set to:', event.amount);
                }
            });

            // Listen for transaction completed events
            Livewire.on('transaction-completed', (event) => {
                const transactionData = event[0];

                // Broadcast to other tabs for real-time updates
                if (typeof window.broadcastTransactionCompleted === 'function') {
                    window.broadcastTransactionCompleted(transactionData);
                } else {
                    // Fallback for cross-tab communication
                    localStorage.setItem('last-transaction', JSON.stringify({
                        ...transactionData,
                        timestamp: Date.now()
                    }));

                    // Dispatch custom event
                    const customEvent = new CustomEvent('transaction-completed', {
                        detail: transactionData
                    });
                    document.dispatchEvent(customEvent);
                }

                console.log('Transaction completed, broadcasted to other tabs:', transactionData);
            });
        });

        // Auto-focus search when page loads for better UX
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[wire\\:model\\.live="searchProduct"]');
            if (searchInput) {
                // Small delay to ensure component is ready
                setTimeout(() => searchInput.focus(), 500);
            }
        });
    </script>
</div>
