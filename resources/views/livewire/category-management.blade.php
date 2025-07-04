<div class="container mx-auto px-4 py-8">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Manajemen Kategori</h1>
                    <p class="text-base-content/70">Kelola kategori produk untuk sistem kasir</p>
                </div>
                <button wire:click="openCreateModal" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Kategori
                </button>
            </div>

            <!-- Search Section -->
            <div class="mb-4">
                <div class="form-control w-full max-w-xs">
                    <input wire:model.live="search" type="text" placeholder="Cari kategori..." 
                           class="input input-bordered w-full max-w-xs" />
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Produk</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $index => $category)
                            <tr>
                                <td>{{ $categories->firstItem() + $index }}</td>
                                <td>
                                    <div class="font-semibold">{{ $category->name }}</div>
                                </td>
                                <td>
                                    <div class="max-w-xs">
                                        {{ $category->description ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="badge badge-info">
                                        {{ $category->products_count ?? $category->products->count() }} produk
                                    </div>
                                </td>
                                <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <button wire:click="openEditModal({{ $category->id }})" 
                                                class="btn btn-sm btn-warning">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $category->id }})" 
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
                                <td colspan="6" class="text-center py-8">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-base-content/30 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                            </svg>
                                        <p class="text-base-content/70">Tidak ada kategori ditemukan</p>
                                        @if($search)
                                            <button wire:click="$set('search', '')" class="btn btn-sm btn-ghost mt-2">
                                                Reset Pencarian
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
                {{ $categories->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Create/Edit -->
    @if($showModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">
                    {{ $isEditMode ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                </h3>
                
                <form wire:submit="save">
                    <!-- Name Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Nama Kategori <span class="text-error">*</span></span>
                        </label>
                        <input wire:model="name" type="text" placeholder="Masukkan nama kategori" 
                               class="input input-bordered w-full @error('name') input-error @enderror" />
                        @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Description Field -->
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text">Deskripsi</span>
                        </label>
                        <textarea wire:model="description" placeholder="Deskripsi kategori (opsional)" 
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
                                {{ $isEditMode ? 'Update' : 'Simpan' }}
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
// Simple JavaScript for better SweetAlert UX
document.addEventListener('DOMContentLoaded', function() {
    Livewire.on('confirm-delete', (data) => {
        if (confirm(`Apakah Anda yakin ingin menghapus kategori "${data.categoryName}"?`)) {
            Livewire.emit('delete', data.categoryId);
        }
    });
});
</script>
