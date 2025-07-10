<div class="container mx-auto px-4 py-6 max-w-7xl">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-base-content">
                    <svg class="w-6 h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Audit Trail - Riwayat Perubahan Transaksi
                </h1>
                <p class="text-base-content/70 mt-1">Catatan lengkap semua perubahan yang dilakukan pada transaksi oleh admin</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="resetFilters" class="btn btn-outline btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset Filter
                </button>
                <button wire:click="exportAuditTrail" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100 shadow-lg mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Pencarian</span>
                    </label>
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchQuery" 
                           placeholder="Cari kode transaksi, alasan, atau field..." 
                           class="input input-bordered w-full">
                </div>

                {{-- Transaction Filter --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Transaksi</span>
                    </label>
                    <select wire:model.live="selectedTransaction" class="select select-bordered w-full">
                        <option value="">Semua Transaksi</option>
                        @foreach($recentTransactions as $transaction)
                            <option value="{{ $transaction->id }}">{{ $transaction->transaction_code }} - {{ $transaction->user->name ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Admin Filter --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Admin</span>
                    </label>
                    <select wire:model.live="selectedAdmin" class="select select-bordered w-full">
                        <option value="">Semua Admin</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Field Filter --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Field Diubah</span>
                    </label>
                    <select wire:model.live="selectedField" class="select select-bordered w-full">
                        <option value="">Semua Field</option>
                        @foreach($availableFields as $field)
                            <option value="{{ $field }}">{{ $field }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date Range --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Tanggal Mulai</span>
                    </label>
                    <input type="date" 
                           wire:model.live="startDate" 
                           class="input input-bordered w-full">
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Tanggal Akhir</span>
                    </label>
                    <input type="date" 
                           wire:model.live="endDate" 
                           class="input input-bordered w-full">
                </div>
            </div>
        </div>
    </div>

    {{-- Audit Trail Table --}}
    <div class="card bg-base-100 shadow-lg">
        <div class="card-header p-4 border-b border-base-200">
            <h3 class="text-lg font-semibold">Riwayat Perubahan</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Transaksi</th>
                        <th>Field</th>
                        <th>Perubahan</th>
                        <th>Admin</th>
                        <th>Alasan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditTrails as $audit)
                        <tr>
                            <td>
                                <div class="text-sm">
                                    <div class="font-medium">{{ $audit->changed_at->format('d/m/Y') }}</div>
                                    <div class="text-base-content/70">{{ $audit->changed_at->format('H:i:s') }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <div class="font-medium">{{ $audit->transaction->transaction_code ?? 'N/A' }}</div>
                                    <div class="text-base-content/70">{{ $audit->transaction->user->name ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $this->getStatusBadgeClass($audit->field_changed) }} badge-sm">
                                    {{ $audit->field_changed }}
                                </span>
                            </td>
                            <td>
                                <div class="text-sm max-w-xs">
                                    <div class="text-error">Lama: {{ Str::limit($audit->old_value, 30) }}</div>
                                    <div class="text-success">Baru: {{ Str::limit($audit->new_value, 30) }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="avatar">
                                        <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs">
                                            {{ strtoupper(substr($audit->admin->name ?? 'N/A', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="text-sm">{{ $audit->admin->name ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm max-w-xs">
                                    {{ Str::limit($audit->reason, 50) }}
                                </div>
                            </td>
                            <td>
                                <button wire:click="viewAuditDetail({{ $audit->id }})" 
                                        class="btn btn-ghost btn-xs"
                                        title="Lihat detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="text-base-content/70">Tidak ada audit trail ditemukan</span>
                                    @if($searchQuery || $selectedTransaction || $selectedAdmin || $selectedField)
                                        <button wire:click="resetFilters" class="btn btn-outline btn-sm">Reset Filter</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($auditTrails->hasPages())
            <div class="card-footer p-4 border-t border-base-200">
                {{ $auditTrails->links() }}
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedAudit)
        <div class="modal modal-open">
            <div class="modal-box w-11/12 max-w-2xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">Detail Audit Trail</h3>
                    <button wire:click="closeDetailModal" class="btn btn-ghost btn-sm btn-circle">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-base-200 rounded-box p-4">
                        <h4 class="font-semibold mb-2">Informasi Dasar</h4>
                        <div class="space-y-2 text-sm">
                            <div><strong>Waktu Perubahan:</strong> {{ $selectedAudit->changed_at->format('d F Y, H:i:s') }}</div>
                            <div><strong>Transaksi:</strong> {{ $selectedAudit->transaction->transaction_code ?? 'N/A' }}</div>
                            <div><strong>Kasir Asli:</strong> {{ $selectedAudit->transaction->user->name ?? 'N/A' }}</div>
                            <div><strong>Admin Editor:</strong> {{ $selectedAudit->admin->name ?? 'N/A' }}</div>
                            <div><strong>Field Diubah:</strong> 
                                <span class="badge {{ $this->getStatusBadgeClass($selectedAudit->field_changed) }} badge-sm ml-1">
                                    {{ $selectedAudit->field_changed }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-box p-4">
                        <h4 class="font-semibold mb-2">Detail Perubahan</h4>
                        <div class="space-y-3">
                            <div>
                                <div class="text-sm font-medium text-error mb-1">Nilai Lama:</div>
                                <div class="bg-error/10 border border-error/20 rounded p-2 text-sm">
                                    {{ $selectedAudit->old_value }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-success mb-1">Nilai Baru:</div>
                                <div class="bg-success/10 border border-success/20 rounded p-2 text-sm">
                                    {{ $selectedAudit->new_value }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-box p-4">
                        <h4 class="font-semibold mb-2">Alasan Perubahan</h4>
                        <div class="bg-base-100 rounded p-3 text-sm">
                            {{ $selectedAudit->reason }}
                        </div>
                    </div>
                </div>

                <div class="modal-action">
                    <button wire:click="closeDetailModal" class="btn">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div>
