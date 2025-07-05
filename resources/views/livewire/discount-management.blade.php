<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header with Breadcrumb -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Manajemen Diskon</h1>
            <p class="text-white">Kelola aturan diskon produk dan transaksi untuk sistem kasir</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <button wire:click="openCreateModal" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Diskon
            </button>
        </div>
    </div>

    <div class="card bg-base-300 shadow-lg">
        <div class="card-body">
            <!-- Filter Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <!-- Search -->
                <div class="form-control">
                    <input wire:model.live="search" type="text" placeholder="Cari diskon..." 
                           class="input input-bordered w-full" />
                </div>

                <!-- Filter Type -->
                <div class="form-control">
                    <select wire:model.live="filterType" class="select select-bordered w-full">
                        <option value="">Semua Tipe</option>
                        <option value="product">Diskon Produk</option>
                        <option value="transaction">Diskon Transaksi</option>
                    </select>
                </div>

                <!-- Filter Status -->
                <div class="form-control">
                    <select wire:model.live="filterStatus" class="select select-bordered w-full">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>

                <!-- Reset Filters -->
                <div class="form-control">
                    <button wire:click="$set('search', ''); $set('filterType', ''); $set('filterStatus', '')" 
                            class="btn btn-ghost">
                        Reset Filter
                    </button>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Diskon</th>
                            <th>Tipe</th>
                            <th>Nilai</th>
                            <th>Produk</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($discounts as $index => $discount)
                            <tr>
                                <td>{{ $discounts->firstItem() + $index }}</td>
                                <td>
                                    <div class="font-semibold">{{ $discount->name }}</div>
                                </td>
                                <td>
                                    <div class="badge {{ $discount->type === 'product' ? 'badge-info' : 'badge-secondary' }}">
                                        {{ $discount->type === 'product' ? 'Produk' : 'Transaksi' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <div class="font-semibold text-lg">{{ $discount->formatted_value }}</div>
                                        <div class="text-xs text-base-content/70">
                                            {{ $discount->value_type_label }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($discount->product)
                                        <div class="flex items-center space-x-2">
                                            <div class="avatar placeholder">
                                                <div class="bg-accent text-accent-content rounded-full w-6">
                                                    <span class="text-xs">{{ substr($discount->product->name, 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <span class="text-sm">{{ $discount->product->name }}</span>
                                        </div>
                                    @else
                                        <div class="badge badge-outline">Semua Produk</div>
                                    @endif
                                </td>
                                <td>
                                    <button wire:click="toggleStatus({{ $discount->id }})" 
                                            class="badge {{ $discount->status_badge }} cursor-pointer hover:opacity-75">
                                        {{ $discount->status_text }}
                                    </button>
                                </td>
                                <td>{{ $discount->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <button wire:click="openEditModal({{ $discount->id }})" 
                                                class="btn btn-sm btn-warning">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $discount->id }})" 
                                                class="btn btn-sm btn-error">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-base-content/30 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        <p class="text-base-content/70">
                                            @if($search || $filterType || $filterStatus !== '')
                                                Tidak ada diskon yang sesuai dengan filter
                                            @else
                                                Belum ada diskon. Tambahkan diskon pertama Anda!
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $discounts->links() }}
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-end mt-6">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Modal Create/Edit -->
    @if($showModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-2xl bg-base-300">
                <h3 class="font-bold text-lg mb-4">
                    {{ $isEditMode ? 'Edit Diskon' : 'Tambah Diskon Baru' }}
                </h3>
                
                <form wire:submit="save">
                    <!-- Name Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Nama Diskon <span class="text-error">*</span></span>
                        </label>
                        <input wire:model="name" type="text" placeholder="Masukkan nama diskon" 
                               class="input input-bordered w-full @error('name') input-error @enderror" />
                        @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Type and Status Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Discount Type -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Tipe Diskon <span class="text-error">*</span></span>
                            </label>
                            <select wire:model.live="type" class="select select-bordered w-full @error('type') select-error @enderror">
                                <option value="product">Diskon Produk</option>
                                <option value="transaction">Diskon Transaksi</option>
                            </select>
                            @error('type')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Status Toggle -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Status</span>
                            </label>
                            <label class="label cursor-pointer justify-start gap-3">
                                <input wire:model="is_active" type="checkbox" class="toggle toggle-primary">
                                <span class="label-text">{{ $is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Product Selection (only for product discounts) -->
                    @if($type === 'product')
                        <div class="form-control w-full mb-4">
                            <label class="label">
                                <span class="label-text">Produk <span class="text-error">*</span></span>
                            </label>
                            <select wire:model="product_id" class="select select-bordered w-full @error('product_id') select-error @enderror">
                                <option value="">Pilih Produk</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} - Rp {{ number_format($product->price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    @endif

                    <!-- Value Type and Value Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Value Type -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Tipe Nilai <span class="text-error">*</span></span>
                            </label>
                            <select wire:model.live="value_type" class="select select-bordered w-full @error('value_type') select-error @enderror">
                                <option value="percentage">Persentase (%)</option>
                                <option value="fixed">Nominal (Rp)</option>
                            </select>
                            @error('value_type')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Discount Value -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Nilai Diskon <span class="text-error">*</span></span>
                            </label>
                            <div class="relative">
                                @if($value_type === 'percentage')
                                    <input wire:model="value" type="number" step="0.1" min="0" max="100" placeholder="10" 
                                           class="input input-bordered w-full pr-8 @error('value') input-error @enderror" />
                                    <span class="absolute right-3 top-3 text-base-content/70">%</span>
                                @else
                                    <span class="absolute left-3 top-3 text-base-content/70">Rp</span>
                                    <input wire:model="value" type="number" min="0" placeholder="50000" 
                                           class="input input-bordered w-full pl-12 @error('value') input-error @enderror" />
                                @endif
                            </div>
                            @error('value')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>

                    <!-- Description Field -->
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text">Deskripsi</span>
                        </label>
                        <textarea wire:model="description" placeholder="Deskripsi diskon (opsional)" 
                                  class="textarea textarea-bordered h-24 @error('description') textarea-error @enderror"></textarea>
                        @error('description')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Modal Actions -->
                    <div class="modal-action">
                        <button type="button" wire:click="closeModal" class="btn btn-ghost">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="save">
                                {{ $isEditMode ? 'Update Diskon' : 'Simpan Diskon' }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="loading loading-spinner loading-sm"></span>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
// SweetAlert2 for better delete confirmation UX
document.addEventListener('DOMContentLoaded', function() {
    Livewire.on('confirm-delete', (data) => {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Apakah Anda yakin ingin menghapus diskon "${data[0].discountName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', data[0].discountId);
            }
        });
    });
});
</script>
