{{-- Remove @section directive as component uses layout in PHP class --}}
<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header with Breadcrumb -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Manajemen Produk</h1>
            <p class="text-white">Kelola produk dan kategori untuk sistem kasir</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <button wire:click="openCreateModal" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Produk
            </button>
        </div>
    </div>

    <div class="card bg-base-300 shadow-lg">
        <div class="card-body">
            <!-- Search and Filter Section -->
            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="form-control w-full sm:w-auto sm:flex-1">
                    <input wire:model.live="search" type="text" placeholder="Cari produk atau kategori..."
                        class="input input-bordered w-full" />
                </div>

                <div class="form-control w-full sm:w-auto">
                    <select wire:model.live="categoryFilter" class="select select-bordered w-full sm:w-auto">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($search || $categoryFilter)
                    <button wire:click="resetFilters" class="btn btn-ghost btn-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
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
                            <th>Foto</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $index => $product)
                            <tr>
                                <td>{{ $products->firstItem() + $index }}</td>
                                <td>
                                    <div class="avatar">
                                        <div class="w-12 h-12 rounded-lg">
                                            @if($product->photo)
                                                <img src="{{ $product->photo_url }}" alt="{{ $product->name }}" 
                                                     class="w-full h-full object-cover" />
                                            @else
                                                <div class="w-full h-full bg-base-300 flex items-center justify-center">
                                                    <span class="text-base-content/50 text-sm font-semibold">
                                                        {{ strtoupper(substr($product->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-semibold">{{ $product->name }}</div>
                                    @if($product->description)
                                        <div class="text-sm text-base-content/60 max-w-xs truncate">{{ $product->description }}</div>
                                    @endif
                                    @if($product->partnerPrices->count() > 0)
                                        <div class="badge badge-warning badge-xs mt-1">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            {{ $product->partnerPrices->count() }} Partner Price(s)
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="badge badge-outline">
                                        {{ $product->category->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="font-semibold text-primary">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td>{{ $product->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <button wire:click="openEditModal({{ $product->id }})"
                                            class="btn btn-sm btn-warning">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $product->id }})"
                                            class="btn btn-sm btn-error">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-base-content/30 mb-2" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                                            </path>
                                        </svg>
                                        <p class="text-base-content/70">
                                            @if ($search || $categoryFilter)
                                                Tidak ada produk yang sesuai dengan filter
                                            @else
                                                Belum ada produk. Tambahkan produk pertama Anda!
                                            @endif
                                        </p>
                                        @if ($search || $categoryFilter)
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
    @if ($showModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-2xl bg-base-300">
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
                            <select wire:model="category_id"
                                class="select select-bordered w-full @error('category_id') select-error @enderror">
                                <option value="">Pilih Kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Photo Upload Field -->
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text">Foto Produk</span>
                            </label>
                            <input wire:model="photo" type="file" accept="image/*"
                                class="file-input file-input-bordered w-full @error('photo') file-input-error @enderror" />
                            @error('photo')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                            <label class="label">
                                <span class="label-text-alt">JPG, PNG, max 2MB</span>
                            </label>
                        </div>
                    </div>

                    <!-- Photo Preview Section -->
                    @if ($photo || $existingPhoto)
                        <div class="form-control w-full mt-4">
                            <label class="label">
                                <span class="label-text">Preview Foto</span>
                            </label>
                            <div class="flex gap-4">
                                @if ($photo)
                                    <div class="relative">
                                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" 
                                             class="w-20 h-20 object-cover rounded-lg border">
                                        <span class="text-sm text-base-content/70">Foto Baru</span>
                                    </div>
                                @endif
                                @if ($existingPhoto && !$photo)
                                    <div class="relative">
                                        <img src="{{ asset($existingPhoto) }}" alt="Current" 
                                             class="w-20 h-20 object-cover rounded-lg border">
                                        <span class="text-sm text-base-content/70">Foto Saat Ini</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Description Field -->
                    <div class="form-control w-full mt-4">
                        <label class="label">
                            <span class="label-text">Deskripsi</span>
                        </label>
                        <textarea wire:model="description" placeholder="Deskripsi produk (opsional)"
                            class="textarea w-full textarea-bordered h-24 @error('description') textarea-error @enderror"></textarea>
                        @error('description')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Partner Pricing Section -->
                    <div class="form-control w-full mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <label class="label cursor-pointer">
                                <span class="label-text font-semibold">Partner Pricing</span>
                                <input wire:model.live="enablePartnerPricing" 
                                       type="checkbox" 
                                       class="toggle toggle-primary ml-2" />
                            </label>
                        </div>
                        
                        @if($enablePartnerPricing)
                            <div class="bg-base-200 rounded-lg p-4">
                                <div class="text-sm text-base-content/70 mb-3">
                                    Set partner-specific prices for online orders. Leave blank to use default price.
                                </div>
                                
                                <div class="space-y-3">
                                    @foreach($partners as $partner)
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-center p-3 bg-base-100 rounded-lg">
                                            <div class="flex items-center gap-3">
                                                <input wire:model.live="partnerPrices.{{ $partner->id }}.is_active" 
                                                       type="checkbox" 
                                                       class="checkbox checkbox-primary checkbox-sm" />
                                                <span class="font-medium">{{ $partner->name }}</span>
                                            </div>
                                            
                                            <div class="form-control">
                                                <div class="relative">
                                                    <span class="absolute left-3 top-3 text-base-content/70 text-sm">Rp</span>
                                                    <input wire:model.live="partnerPrices.{{ $partner->id }}.price" 
                                                           type="number" 
                                                           step="100" 
                                                           min="0"
                                                           placeholder="0"
                                                           class="input input-bordered input-sm w-full pl-10 @if(!($partnerPrices[$partner->id]['is_active'] ?? false)) opacity-50 @endif"
                                                           @if(!($partnerPrices[$partner->id]['is_active'] ?? false)) disabled @endif />
                                                </div>
                                            </div>
                                            
                                            <div class="text-right">
                                                @if(($partnerPrices[$partner->id]['is_active'] ?? false) && !empty($partnerPrices[$partner->id]['price']))
                                                    @php $saving = $price - $partnerPrices[$partner->id]['price']; @endphp
                                                    @if($saving > 0)
                                                        <span class="text-success text-sm">
                                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                                            </svg>
                                                            Save Rp {{ number_format($saving, 0, ',', '.') }}
                                                        </span>
                                                    @elseif($saving < 0)
                                                        <span class="text-error text-sm">
                                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                                            </svg>
                                                            +Rp {{ number_format(abs($saving), 0, ',', '.') }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="text-xs text-base-content/60 mt-3 p-2 bg-info/10 rounded">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Partner prices will be used for online orders when the specific partner is selected. 
                                    Dine-in and take-away orders always use the default price.
                                </div>
                            </div>
                        @endif
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
                            <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
    // Simple alert system to replace SweetAlert
    Livewire.on('show-simple-alert', (event) => {
        const data = event[0];
        let icon = '';
        switch(data.type) {
            case 'success': icon = '✅'; break;
            case 'error': icon = '❌'; break;
            case 'warning': icon = '⚠️'; break;
            default: icon = 'ℹ️';
        }
        
        alert(`${icon} ${data.title}\n\n${data.message}`);
    });
    
    // Simple confirmation system
    Livewire.on('show-delete-confirmation', (event) => {
        const data = event[0];
        if (confirm(`🗑️ ${data.message}`)) {
            Livewire.find(@this.id).deleteProduct({'productId': data.id});
                }
        });
    });
</script>
