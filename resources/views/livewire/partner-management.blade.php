<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header with Breadcrumb -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Manajemen Partner Online</h1>
            <p class="text-white">Kelola partner online dan komisi untuk sistem kasir</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <button wire:click="openCreateModal" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Partner
            </button>
        </div>
    </div>

    <div class="card bg-base-300 shadow-lg">
        <div class="card-body">
            <!-- Search Section -->
            <div class="mb-4">
                <div class="form-control w-full max-w-xs">
                    <input wire:model.live="search" type="text" placeholder="Cari partner..." 
                           class="input input-bordered w-full max-w-xs" />
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Partner</th>
                            <th>Komisi</th>
                            <th>Komisi (Decimal)</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $index => $partner)
                            <tr>
                                <td>{{ $partners->firstItem() + $index }}</td>
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-primary text-primary-content rounded-full w-8">
                                                <span class="text-xs">{{ substr($partner->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div class="font-semibold">{{ $partner->name }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="badge badge-primary font-semibold">
                                        {{ $partner->formatted_commission_rate }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-sm text-base-content/70">
                                        {{ number_format($partner->commission_decimal, 3) }}
                                    </div>
                                </td>
                                <td>{{ $partner->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="flex gap-2">
                                        <button wire:click="openEditModal({{ $partner->id }})" 
                                                class="btn btn-sm btn-warning">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="confirmDelete({{ $partner->id }})" 
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
                                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        <p class="text-base-content/70">
                                            @if($search)
                                                Tidak ada partner yang sesuai dengan pencarian
                                            @else
                                                Belum ada partner online. Tambahkan partner pertama Anda!
                                            @endif
                                        </p>
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
                {{ $partners->links() }}
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
            <div class="modal-box bg-base-300">
                <h3 class="font-bold text-lg mb-4">
                    {{ $isEditMode ? 'Edit Partner' : 'Tambah Partner Baru' }}
                </h3>
                
                <form wire:submit="save">
                    <!-- Name Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Nama Partner <span class="text-error">*</span></span>
                        </label>
                        <input wire:model="name" type="text" placeholder="Masukkan nama partner" 
                               class="input input-bordered w-full @error('name') input-error @enderror" />
                        @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Commission Rate Field -->
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text">Tingkat Komisi (%) <span class="text-error">*</span></span>
                        </label>
                        <div class="relative">
                            <input wire:model="commission_rate" type="number" step="0.1" min="0" max="100" 
                                   placeholder="15.5" 
                                   class="input input-bordered w-full pr-8 @error('commission_rate') input-error @enderror" />
                            <span class="absolute right-3 top-3 text-base-content/70">%</span>
                        </div>
                        <label class="label">
                            <span class="label-text-alt">Masukkan nilai antara 0-100. Contoh: 15.5 untuk 15.5%</span>
                        </label>
                        @error('commission_rate')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Preview Calculation -->
                    @if($commission_rate && is_numeric($commission_rate))
                        <div class="alert alert-info mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>
                                Untuk pesanan Rp 100.000, komisi yang dibayarkan: 
                                <strong>Rp {{ number_format(100000 * ($commission_rate / 100), 0, ',', '.') }}</strong>
                            </span>
                        </div>
                    @endif

                    <!-- Modal Actions -->
                    <div class="modal-action">
                        <button type="button" wire:click="closeModal" class="btn btn-ghost">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="save">
                                {{ $isEditMode ? 'Update Partner' : 'Simpan Partner' }}
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
            text: `Apakah Anda yakin ingin menghapus partner "${data[0].partnerName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('delete', data[0].partnerId);
            }
        });
    });
});
</script>
