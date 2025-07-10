<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Stock Sate Configuration</h1>
            <p class="text-white">Manajemen stok harian untuk produk sate</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <input type="date" wire:model.live="selectedDate" class="input input-bordered input-sm">
            <button wire:click="openBulkModal" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Bulk Entry
            </button>
        </div>
    </div>

    <!-- Daily Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="stat bg-base-300 rounded-xl">
            <div class="stat-figure text-primary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <div class="stat-title">Total Jenis</div>
            <div class="stat-value text-primary">{{ $dailyStats['total_jenis'] }}</div>
            <div class="stat-desc">Jenis sate</div>
        </div>

        <div class="stat bg-base-300 rounded-xl">
            <div class="stat-figure text-secondary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <div class="stat-title">Stok Awal</div>
            <div class="stat-value text-secondary">{{ number_format($dailyStats['total_stok_awal']) }}</div>
            <div class="stat-desc">Total tusuk</div>
        </div>

        <div class="stat bg-base-300 rounded-xl">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
            </div>
            <div class="stat-title">Terjual</div>
            <div class="stat-value text-warning">{{ number_format($dailyStats['total_stok_terjual']) }}</div>
            <div class="stat-desc">Total tusuk</div>
        </div>

        <div class="stat bg-base-300 rounded-xl">
            <div class="stat-figure text-accent">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div class="stat-title">Sisa</div>
            <div class="stat-value text-accent">{{ number_format($dailyStats['total_sisa']) }}</div>
            <div class="stat-desc">Total tusuk</div>
        </div>

        <div class="stat bg-base-300 rounded-xl">
            <div class="stat-figure text-error">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <div class="stat-title">Habis</div>
            <div class="stat-value text-error">{{ $dailyStats['jenis_habis'] }}</div>
            <div class="stat-desc">Jenis sate</div>
        </div>
    </div>

    <!-- Filters and Quick Actions -->
    <div class="card bg-base-300 shadow-lg mb-6">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
                <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                    <input type="text" wire:model.live="searchJenis" placeholder="Cari jenis sate..." class="input input-bordered input-sm w-full sm:w-auto">
                    <select wire:model.live="filterStatus" class="select select-bordered select-sm w-full sm:w-auto">
                        <option value="all">Semua Status</option>
                        <option value="available">Masih Tersedia</option>
                        <option value="sold_out">Habis Terjual</option>
                        <option value="not_set">Belum Diset</option>
                    </select>
                </div>
                
                <div class="flex gap-2">
                    <button wire:click="clearFilters" class="btn btn-outline btn-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear
                    </button>
                    <button wire:click="copyFromPreviousDay" class="btn btn-info btn-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Copy Kemarin
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Data Table -->
    <div class="card bg-base-300 shadow-lg">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Data Stock Sate - {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
            </h2>

            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Jenis Sate</th>
                            <th>Stok Awal</th>
                            <th>Stok Terjual</th>
                            <th>Sisa</th>
                            <th>Status</th>
                            <th>Note</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockSateData as $stock)
                            <tr>
                                <td class="font-semibold">{{ $stock->jenis_sate }}</td>
                                <td>
                                    <div class="badge badge-primary">{{ number_format($stock->stok_awal) }}</div>
                                </td>
                                <td>
                                    <div class="badge badge-warning">{{ number_format($stock->stok_terjual) }}</div>
                                </td>
                                <td>
                                    <div class="badge 
                                        {{ ($stock->stok_awal - $stock->stok_terjual) > 0 ? 'badge-success' : 'badge-error' }}">
                                        {{ number_format($stock->stok_awal - $stock->stok_terjual) }}
                                    </div>
                                </td>
                                <td>
                                    @if($stock->stok_awal <= $stock->stok_terjual)
                                        <span class="badge badge-error">Habis</span>
                                    @elseif($stock->stok_awal == 0)
                                        <span class="badge badge-ghost">Belum Set</span>
                                    @else
                                        <span class="badge badge-success">Tersedia</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-xs max-w-xs truncate">
                                        {{ $stock->note ?: '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="flex gap-1">
                                        <button wire:click="openStockModal('{{ $stock->jenis_sate }}', {{ $stock->id }})" 
                                                class="btn btn-ghost btn-xs">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="deleteStock({{ $stock->id }})" 
                                                wire:confirm="Yakin ingin menghapus data stock ini?"
                                                class="btn btn-ghost btn-xs text-error">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-base-content/70 py-8">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <p class="mb-2">Belum ada data stock untuk tanggal ini</p>
                                    <button wire:click="openBulkModal" class="btn btn-primary btn-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Setup Stock Hari Ini
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Quick Add Buttons for Missing Sate -->
            @if($sateProducts->count() > $stockSateData->count())
                <div class="mt-4">
                    <h3 class="text-sm font-semibold mb-2">Jenis Sate yang Belum Diset:</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($sateProducts as $product)
                            @unless($stockSateData->where('jenis_sate', $product->jenis_sate)->first())
                                <button wire:click="openStockModal('{{ $product->jenis_sate }}')" 
                                        class="btn btn-outline btn-xs">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ $product->jenis_sate }}
                                </button>
                            @endunless
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Pagination -->
            @if($stockSateData->hasPages())
                <div class="mt-4">
                    {{ $stockSateData->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Stock Entry Modal -->
    @if($showStockModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">
                    {{ $isEditMode ? 'Edit' : 'Tambah' }} Stock Sate
                </h3>
                
                <form wire:submit="saveStock">
                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Jenis Sate</span>
                            </label>
                            @if($isEditMode)
                                <input type="text" wire:model="modalJenisSate" class="input input-bordered" readonly>
                            @else
                                <select wire:model="modalJenisSate" class="select select-bordered">
                                    <option value="">Pilih Jenis Sate</option>
                                    @foreach($sateProducts as $product)
                                        <option value="{{ $product->jenis_sate }}">{{ $product->jenis_sate }}</option>
                                    @endforeach
                                </select>
                            @endif
                            @error('modalJenisSate') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Stok Awal</span>
                            </label>
                            <input type="number" wire:model="modalStokAwal" min="0" class="input input-bordered">
                            @error('modalStokAwal') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Stok Terjual</span>
                            </label>
                            <input type="number" wire:model="modalStokTerjual" min="0" max="{{ $modalStokAwal }}" class="input input-bordered">
                            @error('modalStokTerjual') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Catatan (Opsional)</span>
                            </label>
                            <textarea wire:model="modalNote" class="textarea textarea-bordered" rows="2"></textarea>
                            @error('modalNote') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="modal-action">
                        <button type="button" wire:click="closeStockModal" class="btn btn-ghost">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $isEditMode ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Bulk Entry Modal -->
    @if($showBulkModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-4xl">
                <h3 class="font-bold text-lg mb-4">Bulk Stock Entry</h3>
                
                <div class="mb-4">
                    <label class="label">
                        <span class="label-text">Tanggal</span>
                    </label>
                    <input type="date" wire:model.live="bulkDate" class="input input-bordered input-sm">
                </div>

                <div class="overflow-x-auto max-h-96">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Jenis Sate</th>
                                <th>Produk</th>
                                <th>Stok Awal</th>
                                <th>Stok Terjual</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bulkStockEntries as $index => $entry)
                                <tr>
                                    <td class="font-semibold">{{ $entry['jenis_sate'] }}</td>
                                    <td class="text-xs">{{ $entry['product_name'] }}</td>
                                    <td>
                                        <input type="number" 
                                               wire:model="bulkStockEntries.{{ $index }}.stok_awal" 
                                               min="0" 
                                               class="input input-bordered input-xs w-20">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               wire:model="bulkStockEntries.{{ $index }}.stok_terjual" 
                                               min="0" 
                                               max="{{ $entry['stok_awal'] }}"
                                               class="input input-bordered input-xs w-20">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               wire:model="bulkStockEntries.{{ $index }}.note" 
                                               class="input input-bordered input-xs w-32">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="modal-action">
                    <button type="button" wire:click="$set('showBulkModal', false)" class="btn btn-ghost">Batal</button>
                    <button wire:click="saveBulkStock" class="btn btn-primary">Simpan Semua</button>
                </div>
            </div>
        </div>
    @endif
</div> 