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

    {{-- Transaction Info Summary --}}
    <div class="card bg-base-200 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="font-semibold">Kode:</span><br>
                    <span class="font-mono">{{ $transaction->transaction_code }}</span>
                </div>
                <div>
                    <span class="font-semibold">Tanggal:</span><br>
                    <span>{{ $transaction->formatted_date }}</span>
                </div>
                <div>
                    <span class="font-semibold">Kasir:</span><br>
                    <span>{{ $transaction->user->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="font-semibold">Waktu Edit Tersisa:</span><br>
                    <span class="badge badge-warning">{{ $this->formattedTimeRemaining }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Form --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Basic Transaction Fields --}}
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Detail Transaksi
                </h3>

                {{-- Order Type --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Jenis Pesanan</span>
                    </label>
                    <select wire:model.live="orderType" class="select select-bordered w-full @error('orderType') select-error @enderror">
                        @foreach($orderTypeLabels as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('orderType')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                {{-- Partner Selection (for online orders) --}}
                @if($orderType === 'online')
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Partner</span>
                        </label>
                        <select wire:model.live="partnerId" class="select select-bordered w-full">
                            <option value="">Pilih Partner</option>
                            @foreach($partners as $partner)
                                <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Payment Method --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Metode Pembayaran</span>
                    </label>
                    <select wire:model.live="paymentMethod" class="select select-bordered w-full @error('paymentMethod') select-error @enderror">
                        @foreach($paymentMethodLabels as $method => $label)
                            <option value="{{ $method }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('paymentMethod')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                {{-- Transaction Date --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Tanggal Transaksi <span class="text-error">*</span></span>
                    </label>
                    <input type="date" 
                           wire:model.live="transactionDate" 
                           max="{{ now()->format('Y-m-d') }}"
                           class="input input-bordered w-full @error('transactionDate') input-error @enderror" />
                    @error('transactionDate')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                    <label class="label">
                        <span class="label-text-alt text-base-content/70">
                            Tanggal tidak boleh di masa depan. Format otomatis akan menyesuaikan jam transaksi asli.
                        </span>
                    </label>
                </div>

                {{-- Notes --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Catatan</span>
                    </label>
                    <textarea wire:model.live="notes" 
                              placeholder="Catatan transaksi..." 
                              class="textarea textarea-bordered w-full @error('notes') textarea-error @enderror" 
                              rows="3"></textarea>
                    @error('notes')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                {{-- Edit Reason --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Alasan Edit <span class="text-error">*</span></span>
                    </label>
                    <textarea wire:model.live="editReason" 
                              placeholder="Jelaskan alasan mengapa transaksi ini perlu diedit..." 
                              class="textarea textarea-bordered w-full @error('editReason') textarea-error @enderror" 
                              rows="3"></textarea>
                    @error('editReason')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Transaction Items --}}
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 011-1h1m0 0h2m0 0h1a1 1 0 011 1v2M7 7h10"></path>
                    </svg>
                    Item Transaksi
                    @if($itemsChanged)
                        <span class="badge badge-warning badge-sm">Diubah</span>
                    @endif
                </h3>

                <div class="space-y-3">
                    @foreach($editableItems as $index => $item)
                        <div class="card bg-base-200 shadow-sm">
                            <div class="card-body p-4">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                    {{-- Product Info --}}
                                    <div class="md:col-span-2">
                                        <div class="font-semibold">{{ $item['product_name'] }}</div>
                                        <div class="text-sm text-base-content/70">
                                            Rp {{ number_format($item['product_price'], 0, ',', '.') }}
                                        </div>
                                        @if($item['discount_amount'] > 0)
                                            <div class="text-xs text-warning">
                                                Diskon: Rp {{ number_format($item['discount_amount'], 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Quantity Controls --}}
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text text-xs">Kuantitas</span>
                                        </label>
                                        <div class="join">
                                            <button type="button" 
                                                    wire:click="updateItemQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                                    class="btn btn-outline btn-sm join-item"
                                                    {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                </svg>
                                            </button>
                                            <input type="number" 
                                                   value="{{ $item['quantity'] }}"
                                                   wire:change="updateItemQuantity({{ $index }}, $event.target.value)"
                                                   class="input input-bordered input-sm w-16 join-item text-center"
                                                   min="1">
                                            <button type="button" 
                                                    wire:click="updateItemQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                                    class="btn btn-outline btn-sm join-item">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        @if($item['quantity'] !== $item['original_quantity'])
                                            <div class="text-xs text-warning mt-1">
                                                Asli: {{ $item['original_quantity'] }}
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Total --}}
                                    <div class="text-right">
                                        <div class="font-bold text-primary">
                                            Rp {{ number_format($item['total'], 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-base-content/70">
                                            Subtotal: Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error("editableItems.{$index}.quantity")
                            <div class="text-error text-xs mt-1">{{ $message }}</div>
                        @enderror
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row gap-4 justify-end mt-6">
        <button wire:click="$dispatch('closeEditModal')" 
                class="btn btn-outline order-2 sm:order-1">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Batal
        </button>
        
        <button wire:click="previewChanges" 
                class="btn btn-primary order-1 sm:order-2"
                wire:loading.attr="disabled">
            <span wire:loading.remove>
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Preview Perubahan
            </span>
            <span wire:loading>
                <span class="loading loading-spinner loading-sm mr-2"></span>
                Memproses...
            </span>
        </button>
    </div>

    {{-- Confirmation Modal --}}
    @if($showConfirmModal)
        <div class="modal modal-open">
            <div class="modal-box w-11/12 max-w-2xl">
                <h3 class="font-bold text-lg mb-4">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Konfirmasi Perubahan
                </h3>

                <div class="alert alert-info mb-4">
                    <svg class="w-6 h-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="font-bold">Ringkasan Perubahan</h4>
                        <p class="text-sm">Berikut adalah perubahan yang akan diterapkan pada transaksi ini:</p>
                    </div>
                </div>

                {{-- Changes Summary --}}
                <div class="bg-base-200 rounded-lg p-4 mb-4">
                    @if(empty($changesSummary))
                        <div class="text-center text-base-content/70">Tidak ada perubahan terdeteksi</div>
                    @else
                        <div class="space-y-3">
                            @foreach($changesSummary as $change)
                                <div class="flex items-center justify-between border-b border-base-300 pb-2">
                                    <span class="font-medium">{{ $change['field'] }}:</span>
                                    <div class="text-right">
                                        <div class="text-sm text-base-content/70 line-through">{{ $change['old'] }}</div>
                                        <div class="text-sm text-success font-semibold">{{ $change['new'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Edit Reason Display --}}
                <div class="bg-warning/10 border border-warning/20 rounded-lg p-4 mb-4">
                    <h5 class="font-semibold text-warning mb-2">Alasan Edit:</h5>
                    <p class="text-sm">{{ $editReason }}</p>
                </div>

                <div class="modal-action">
                    <button wire:click="closeConfirmModal" class="btn btn-outline">
                        Batal
                    </button>
                    <button wire:click="confirmChanges" 
                            class="btn btn-primary"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Konfirmasi Perubahan
                        </span>
                        <span wire:loading>
                            <span class="loading loading-spinner loading-sm mr-2"></span>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
