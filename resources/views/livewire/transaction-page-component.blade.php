{{-- Transaction Page Component --}}
<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-white">Transaksi</h1>
            <p class="text-white">Kelola dan monitor semua transaksi sistem</p>
        </div>
        
        <!-- Summary Cards (Mobile: Stack, Desktop: Row) -->
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="stat bg-base-200 rounded-box">
                <div class="stat-title">Total Transaksi</div>
                <div class="stat-value text-lg lg:text-2xl">{{ number_format($totalTransactions) }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box">
                <div class="stat-title">Total Nilai</div>
                <div class="stat-value text-lg lg:text-2xl text-success">{{ $this->formatCurrency($totalAmount) }}</div>
            </div>
            <div class="stat bg-base-200 rounded-box col-span-2 lg:col-span-1">
                <div class="stat-title">Periode</div>
                <div class="stat-value text-sm lg:text-lg">
                    @if($startDate === $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card bg-base-300 shadow-lg mb-6">
        <div class="card-header p-4 border-b border-base-200">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">Filter & Pencarian</h3>
                <button wire:click="toggleFilters" class="btn btn-ghost btn-sm">
                    @if($showFilters)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                        </svg>
                        Sembunyikan
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        Tampilkan
                    @endif
                </button>
            </div>
        </div>
        
        @if($showFilters)
            <div class="card-body p-4">
                <!-- Quick Date Range Buttons -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <button wire:click="setDateRange('today')" class="btn btn-outline btn-sm">Hari Ini</button>
                    <button wire:click="setDateRange('yesterday')" class="btn btn-outline btn-sm">Kemarin</button>
                    <button wire:click="setDateRange('week')" class="btn btn-outline btn-sm">Minggu Ini</button>
                    <button wire:click="setDateRange('month')" class="btn btn-outline btn-sm">Bulan Ini</button>
                    <button wire:click="resetFilters" class="btn btn-ghost btn-sm ml-auto">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset Filter
                    </button>
                </div>

                <!-- Filter Controls Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    <!-- Date Range -->
                    <div class="form-control">
                        <label class="label"><span class="label-text">Tanggal Mulai</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered input-sm">
                    </div>
                    
                    <div class="form-control">
                        <label class="label"><span class="label-text">Tanggal Akhir</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered input-sm">
                    </div>
                    
                    <!-- Order Type Filter -->
                    <div class="form-control">
                        <label class="label"><span class="label-text">Jenis Pesanan</span></label>
                        <select wire:model.live="selectedOrderType" class="select select-bordered select-sm">
                            @foreach($orderTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="form-control">
                        <label class="label"><span class="label-text">Status</span></label>
                        <select wire:model.live="selectedStatus" class="select select-bordered select-sm">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Payment Method Filter -->
                    <div class="form-control">
                        <label class="label"><span class="label-text">Metode Bayar</span></label>
                        <select wire:model.live="selectedPaymentMethod" class="select select-bordered select-sm">
                            @foreach($paymentMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Search -->
                    <div class="form-control">
                        <label class="label"><span class="label-text">Pencarian</span></label>
                        <input type="text" wire:model.live.debounce.500ms="searchQuery" 
                               placeholder="Kode, user, partner..." 
                               class="input input-bordered input-sm">
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Loading Indicator -->
    @if($isLoading)
        <div class="flex justify-center py-8">
            <span class="loading loading-spinner loading-lg"></span>
        </div>
    @endif

    <!-- Transactions Table -->
    <div class="card bg-base-300 shadow-lg">
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Jenis</th>
                        <th>Metode Bayar</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr class="hover">
                            <td>
                                <div class="font-mono font-semibold text-primary">
                                 <a href="{{ route('receipt.print', $transaction->id) }}">
                                       {{ $transaction->transaction_code }}
                                </a>
                                </div>
                                @if($transaction->partner)
                                    <div class="text-xs text-base-content/70">
                                        via {{ $transaction->partner->name }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $transaction->short_date }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar">
                                        <div class="w-8 rounded-full bg-primary text-primary-content flex items-center justify-center">
                                            <span class="text-xs font-semibold">{{ substr($transaction->user->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <span class="text-sm">{{ $transaction->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-outline badge-sm">
                                    {{ $this->getOrderTypeLabel($transaction->order_type) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-sm">{{ $this->getPaymentMethodLabel($transaction->payment_method) }}</span>
                            </td>
                            <td>
                                <div class="font-semibold">{{ $transaction->formatted_final_total }}</div>
                                @if($transaction->total_discount > 0)
                                    <div class="text-xs text-warning">Diskon: {{ $transaction->formatted_total_discount }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $this->getStatusBadgeClass($transaction->status) }} badge-sm">
                                    {{ $transaction->status_label }}
                                </span>
                            </td>
                            <td>
                                <button wire:click="viewTransactionDetail({{ $transaction->id }})" 
                                        class="btn btn-ghost btn-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="text-base-content/70">Tidak ada transaksi ditemukan</span>
                                    @if($searchQuery || $selectedOrderType || $selectedStatus || $selectedPaymentMethod)
                                        <button wire:click="resetFilters" class="btn btn-outline btn-sm">Reset Filter</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($transactions->hasPages())
            <div class="card-footer p-4 border-t border-base-200">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Transaction Detail Modal -->
    @if($showDetailModal && $selectedTransaction)
        <div class="modal modal-open">
            <div class="modal-box w-11/12 max-w-4xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">Detail Transaksi</h3>
                    <button wire:click="closeDetailModal" class="btn btn-ghost btn-sm btn-circle">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Transaction Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="space-y-2">
                        <div><strong>Kode:</strong> {{ $selectedTransaction->transaction_code }}</div>
                        <div><strong>Tanggal:</strong> {{ $selectedTransaction->formatted_date }}</div>
                        <div><strong>Kasir:</strong> {{ $selectedTransaction->user->name }}</div>
                        <div><strong>Status:</strong> 
                            <span class="badge {{ $this->getStatusBadgeClass($selectedTransaction->status) }}">
                                {{ $selectedTransaction->status_label }}
                            </span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div><strong>Jenis Pesanan:</strong> {{ $this->getOrderTypeLabel($selectedTransaction->order_type) }}</div>
                        <div><strong>Metode Bayar:</strong> {{ $this->getPaymentMethodLabel($selectedTransaction->payment_method) }}</div>
                        @if($selectedTransaction->partner)
                            <div><strong>Partner:</strong> {{ $selectedTransaction->partner->name }}</div>
                            <div><strong>Komisi:</strong> {{ $selectedTransaction->formatted_partner_commission }}</div>
                        @endif
                        @if($selectedTransaction->notes)
                            <div><strong>Catatan:</strong> {{ $selectedTransaction->notes }}</div>
                        @endif
                    </div>
                </div>

                <!-- Transaction Items -->
                <div class="divider">Item Transaksi</div>
                <div class="overflow-x-auto mb-4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Diskon</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedTransaction->items as $item)
                                <tr>
                                    <td>
                                        <div class="font-semibold">{{ $item->product_name }}</div>
                                        @if($item->product && $item->product->category)
                                            <div class="text-xs text-base-content/70">{{ $item->product->category->name }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $this->formatCurrency($item->product_price) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $this->formatCurrency($item->discount_amount) }}</td>
                                    <td class="font-semibold">{{ $this->formatCurrency($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Transaction Summary -->
                <div class="bg-base-200 rounded-box p-4">
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>Subtotal:</div>
                        <div class="text-right">{{ $selectedTransaction->formatted_subtotal }}</div>
                        
                        <div>Total Diskon:</div>
                        <div class="text-right text-warning">-{{ $selectedTransaction->formatted_total_discount }}</div>
                        
                        @if($selectedTransaction->partner_commission > 0)
                            <div>Komisi Partner:</div>
                            <div class="text-right text-info">-{{ $selectedTransaction->formatted_partner_commission }}</div>
                        @endif
                        
                        <div class="font-bold text-lg border-t pt-2">Total Akhir:</div>
                        <div class="text-right font-bold text-lg text-success border-t pt-2">{{ $selectedTransaction->formatted_final_total }}</div>
                    </div>
                </div>

                <div class="modal-action">
                    <button wire:click="closeDetailModal" class="btn">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div> 