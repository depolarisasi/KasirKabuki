{{-- Remove @section directive as component uses layout in PHP class --}}
<div class="container mx-auto px-4 py-6">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-6">
<div>
                    <h1 class="text-2xl font-bold">Manajemen Produk</h1>
                    <p class="text-base-content/70">Kelola produk dan harga untuk sistem kasir</p>
                </div>
                <button wire:click="openCreateModal" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Produk
                </button>
            </div>

            <!-- Search and Filter Section -->
            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="form-control w-full sm:w-auto sm:flex-1">
                    <input wire:model.live="search" type="text" placeholder="Cari produk atau kategori..." 
                           class="input input-bordered w-full" />
                </div>
                
                <div class="form-control w-full sm:w-auto">
                    <select wire:model.live="categoryFilter" class="select select-bordered w-full sm:w-auto">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                @if($search || $categoryFilter)
                    <button wire:click="resetFilters" class="btn btn-ghost btn-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reset Filter
                    </button>
                @endif
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Deskripsi</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $index => $product)
                            <tr>
                                <td>{{ $products->firstItem() + $index }}</td>
                                <td>
                                    <div class="font-semibold">{{ $product->name }}</div>
                                </td>
                                <td>
                                    <div class="badge badge-outline">
                                        {{ $product->category->name }}
                                    </div>
                                </td>
                                <td>
                                    <div class="font-semibold text-primary">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="max-w-xs text-sm text-base-content/70">
                                        {{ Str::limit($product->description ?? '-', 50) }}
                                    </div>
                                </td>
                                <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <button wire:click="openEditModal({{ $product->id }})" 
                                                class="btn btn-sm btn-warning">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $product->id }})" 
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
                                <td colspan="7" class="text-center py-8">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-base-content/30 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        <p class="text-base-content/70">
                                            @if($search || $categoryFilter)
                                                Tidak ada produk yang sesuai dengan filter
                                            @else
                                                Belum ada produk. Tambahkan produk pertama Anda!
                                            @endif
                                        </p>
                                        @if($search || $categoryFilter)
                                            <button wire:click="resetFilters" class="btn btn-sm btn-ghost mt-2">
                                                Reset Filter
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Create/Edit -->
    @if($showModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-2xl">
                <h3 class="font-bold text-lg mb-4">
                    {{ $isEditMode ? 'Edit Produk' : 'Tambah Produk Baru' }}
                </h3>
                
                <form wire:submit="save">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Name Field -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Nama Produk <span class="text-error">*</span></span>
                            </label>
                            <input wire:model="name" type="text" placeholder="Masukkan nama produk" 
                                   class="input input-bordered w-full @error('name') input-error @enderror" />
                            @error('name')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Price Field -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Harga <span class="text-error">*</span></span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-3 text-base-content/70">Rp</span>
                                <input wire:model="price" type="number" step="1" min="0" placeholder="0" 
                                       class="input input-bordered w-full pl-8 @error('price') input-error @enderror" />
                            </div>
                            @error('price')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Category Field -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Kategori <span class="text-error">*</span></span>
                            </label>
                            <select wire:model="category_id" class="select select-bordered w-full @error('category_id') select-error @enderror">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Status Info (for edit mode) -->
                        @if($isEditMode)
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text">Status</span>
                                </label>
                                <div class="flex items-center">
                                    <div class="badge badge-success">Aktif</div>
                                    <span class="text-sm text-base-content/70 ml-2">Produk tersedia untuk dijual</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Description Field -->
                    <div class="form-control w-full mt-4">
                        <label class="label">
                            <span class="label-text">Deskripsi</span>
                        </label>
                        <textarea wire:model="description" placeholder="Deskripsi produk (opsional)" 
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
                                {{ $isEditMode ? 'Update Produk' : 'Simpan Produk' }}
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
// JavaScript for better UX
document.addEventListener('DOMContentLoaded', function() {
    Livewire.on('confirm-delete', (data) => {
        if (confirm(`Apakah Anda yakin ingin menghapus produk "${data.productName}"?`)) {
            Livewire.emit('delete', data.productId);
        }
    });
});
</script>
