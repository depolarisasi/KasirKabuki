<div class="container mx-auto px-4 py-6">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-6">
<div>
                    <h1 class="text-2xl font-bold">Pencatatan Pengeluaran</h1>
                    <p class="text-base-content/70">Kelola catatan pengeluaran harian dan bulanan</p>
                </div>
                <button wire:click="openCreateModal" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Pengeluaran
                </button>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="stat bg-success/10 rounded-lg">
                    <div class="stat-figure text-success">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Pengeluaran Hari Ini</div>
                    <div class="stat-value text-success">{{ 'Rp ' . number_format($stats['today'], 0, ',', '.') }}</div>
                    <div class="stat-desc">{{ Carbon\Carbon::today()->format('d F Y') }}</div>
                </div>

                <div class="stat bg-info/10 rounded-lg">
                    <div class="stat-figure text-info">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Pengeluaran Bulan Ini</div>
                    <div class="stat-value text-info">{{ 'Rp ' . number_format($stats['this_month'], 0, ',', '.') }}</div>
                    <div class="stat-desc">{{ Carbon\Carbon::now()->format('F Y') }}</div>
                </div>

                <div class="stat bg-warning/10 rounded-lg">
                    <div class="stat-figure text-warning">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Total Filtered</div>
                    <div class="stat-value text-warning">{{ 'Rp ' . number_format($totals['amount'], 0, ',', '.') }}</div>
                    <div class="stat-desc">{{ $totals['count'] }} transaksi</div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <!-- Search -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Cari Deskripsi</span>
                    </label>
                    <input wire:model.live="search" type="text" placeholder="Cari pengeluaran..." 
                           class="input input-bordered w-full" />
                </div>

                <!-- Filter by Specific Date -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Tanggal Spesifik</span>
                    </label>
                    <input wire:model.live="filterDate" type="date" 
                           class="input input-bordered w-full" />
                </div>

                <!-- Filter by Month -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Bulan</span>
                    </label>
                    <input wire:model.live="filterMonth" type="month" 
                           class="input input-bordered w-full" />
                </div>

                <!-- Quick Filter Buttons -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Quick Filter</span>
                    </label>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-outline w-full">
                            Quick Filter
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </label>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                            <li><button wire:click="setQuickFilter('today')" class="w-full text-left">Hari Ini</button></li>
                            <li><button wire:click="setQuickFilter('yesterday')" class="w-full text-left">Kemarin</button></li>
                            <li><button wire:click="setQuickFilter('this_month')" class="w-full text-left">Bulan Ini</button></li>
                        </ul>
                    </div>
                </div>

                <!-- Reset Filters -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">&nbsp;</span>
                    </label>
                    <button wire:click="resetFilters" class="btn btn-ghost">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
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
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Jumlah</th>
                            <th>Dicatat Oleh</th>
                            <th>Waktu Input</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $index => $expense)
                            <tr class="{{ $expense->is_today ? 'bg-success/5' : '' }}">
                                <td>{{ $expenses->firstItem() + $index }}</td>
                                <td>
                                    <div class="flex flex-col">
                                        <div class="font-semibold">{{ $expense->short_date }}</div>
                                        @if($expense->is_today)
                                            <div class="badge badge-success badge-xs">Hari Ini</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="max-w-xs">
                                        <div class="font-semibold">{{ Str::limit($expense->description, 50) }}</div>
                                        @if(strlen($expense->description) > 50)
                                            <div class="text-xs text-base-content/60" title="{{ $expense->description }}">
                                                ...
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="font-bold text-lg text-error">{{ $expense->formatted_amount }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center space-x-2">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                <span class="text-xs">{{ substr($expense->user->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <span class="text-sm">{{ $expense->user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-sm">{{ $expense->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        @if(auth()->user()->hasRole('admin') || $expense->user_id === auth()->id())
                                            <button wire:click="openEditModal({{ $expense->id }})" 
                                                    class="btn btn-sm btn-warning">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button wire:click="confirmDelete({{ $expense->id }})" 
                                                    class="btn btn-sm btn-error">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @else
                                            <span class="text-xs text-base-content/50">Tidak ada akses</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-base-content/30 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        <p class="text-base-content/70">
                                            @if($search || $filterDate || $filterMonth)
                                                Tidak ada pengeluaran yang sesuai dengan filter
                                            @else
                                                Belum ada pengeluaran. Tambahkan pengeluaran pertama Anda!
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
                {{ $expenses->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Create/Edit -->
    @if($showModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">
                    {{ $isEditMode ? 'Edit Pengeluaran' : 'Tambah Pengeluaran Baru' }}
                </h3>
                
                <form wire:submit="save">
                    <!-- Date Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Tanggal Pengeluaran <span class="text-error">*</span></span>
                        </label>
                        <input wire:model="date" type="date" 
                               class="input input-bordered w-full @error('date') input-error @enderror" />
                        @error('date')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Amount Field -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Jumlah Pengeluaran <span class="text-error">*</span></span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-base-content/70">Rp</span>
                            <input wire:model="amount" type="number" step="0.01" min="1" max="99999999.99"
                                   placeholder="150000" 
                                   class="input input-bordered w-full pl-12 @error('amount') input-error @enderror" />
                        </div>
                        <label class="label">
                            <span class="label-text-alt">Masukkan jumlah dalam rupiah. Contoh: 150000 untuk Rp 150.000</span>
                        </label>
                        @error('amount')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Description Field -->
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text">Deskripsi Pengeluaran <span class="text-error">*</span></span>
                        </label>
                        <textarea wire:model="description" 
                                  placeholder="Contoh: Beli gas untuk kompor dapur, Bayar listrik bulan ini, dll"
                                  class="textarea textarea-bordered h-24 @error('description') textarea-error @enderror"></textarea>
                        <label class="label">
                            <span class="label-text-alt">Jelaskan secara detail untuk apa pengeluaran ini</span>
                        </label>
                        @error('description')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Preview Calculation -->
                    @if($amount && is_numeric($amount))
                        <div class="alert alert-info mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>
                                Pengeluaran akan tercatat sebagai: 
                                <strong>{{ 'Rp ' . number_format($amount, 0, ',', '.') }}</strong>
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
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ $isEditMode ? 'Update Pengeluaran' : 'Simpan Pengeluaran' }}
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
        if (confirm(`Apakah Anda yakin ingin menghapus pengeluaran "${data.expenseDescription}" sebesar ${data.expenseAmount}?`)) {
            Livewire.emit('delete', data.expenseId);
        }
    });
});
</script>
