<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Audit Trail Configuration</h1>
            <p class="text-white">Monitor system activity dan kelola log audit</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <button wire:click="refreshLogs" class="btn btn-ghost btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
            <button wire:click="exportLogs" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export CSV
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-300 rounded-xl">
            <div class="stat-figure text-primary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="stat-title">Total Logs</div>
            <div class="stat-value text-primary">{{ number_format($stats['total_logs']) }}</div>
            <div class="stat-desc">Sejak {{ $stats['oldest_log_date'] ?? 'N/A' }}</div>
        </div>

        <div class="stat bg-base-300 rounded-xl">
            <div class="stat-figure text-secondary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Hari Ini</div>
            <div class="stat-value text-secondary">{{ number_format($stats['logs_today']) }}</div>
            <div class="stat-desc">Log entries</div>
        </div>

        <div class="stat bg-base-300 rounded-xl">
            <div class="stat-figure text-accent">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="stat-title">Minggu Ini</div>
            <div class="stat-value text-accent">{{ number_format($stats['logs_this_week']) }}</div>
            <div class="stat-desc">Log entries</div>
        </div>

        <div class="stat bg-base-300 rounded-xl">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <div class="stat-title">Estimasi Size</div>
            <div class="stat-value text-warning">{{ number_format($stats['size_estimate']) }}</div>
            <div class="stat-desc">KB database</div>
        </div>
    </div>

    <!-- Filters and Settings -->
    <div class="card bg-base-300 shadow-lg mb-6">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filter & Pengaturan
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <!-- Date Range -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Tanggal Mulai</span>
                    </label>
                    <input type="date" wire:model.live="startDate" class="input input-bordered input-sm">
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Tanggal Akhir</span>
                    </label>
                    <input type="date" wire:model.live="endDate" class="input input-bordered input-sm">
                </div>

                <!-- Table Filter -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Tabel</span>
                    </label>
                    <select wire:model.live="selectedTable" class="select select-bordered select-sm">
                        <option value="">Semua Tabel</option>
                        @foreach($availableTables as $table)
                            <option value="{{ $table['value'] }}">{{ $table['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- User Filter -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">User</span>
                    </label>
                    <select wire:model.live="selectedUser" class="select select-bordered select-sm">
                        <option value="">Semua User</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Aksi</span>
                    </label>
                    <select wire:model.live="selectedAction" class="select select-bordered select-sm">
                        <option value="">Semua Aksi</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>

                <!-- Retention Settings -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Retensi (Hari)</span>
                    </label>
                    <input type="number" wire:model="retentionDays" min="1" max="365" class="input input-bordered input-sm">
                </div>
            </div>

            <div class="flex justify-between items-center">
                <button wire:click="clearFilters" class="btn btn-outline btn-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Filters
                </button>

                <button wire:click="cleanupOldLogs" 
                        wire:loading.attr="disabled" 
                        wire:target="cleanupOldLogs"
                        class="btn btn-warning btn-sm" 
                        onclick="return confirm('Yakin ingin menghapus log lama? Aksi ini tidak dapat dibatalkan.')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span wire:loading.remove wire:target="cleanupOldLogs">Cleanup Old Logs</span>
                    <span wire:loading wire:target="cleanupOldLogs">Cleaning...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card bg-base-300 shadow-lg">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Log Audit Trail
            </h2>

            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Table</th>
                            <th>Record ID</th>
                            <th>IP Address</th>
                            <th>Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditLogs as $log)
                            <tr>
                                <td>
                                    <div class="text-xs">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-xs">
                                        {{ $log->user_id ? "User {$log->user_id}" : 'System' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="badge badge-sm 
                                        {{ $log->event === 'created' ? 'badge-success' : '' }}
                                        {{ $log->event === 'updated' ? 'badge-warning' : '' }}
                                        {{ $log->event === 'deleted' ? 'badge-error' : '' }}">
                                        {{ ucfirst($log->event) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-xs">
                                        {{ class_basename($log->auditable_type) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-xs">
                                        {{ $log->auditable_id }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-xs">
                                        {{ $log->ip_address }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-xs max-w-xs truncate">
                                        @if($log->new_values)
                                            <details class="dropdown">
                                                <summary class="btn btn-ghost btn-xs">View</summary>
                                                <div class="dropdown-content bg-base-100 rounded-box z-[1] w-64 p-2 shadow text-xs">
                                                    <pre>{{ json_encode(json_decode($log->new_values), JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </details>
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-base-content/70 py-8">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Tidak ada log audit ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($auditLogs->hasPages())
                <div class="mt-4">
                    {{ $auditLogs->links() }}
                </div>
            @endif
        </div>
    </div>
</div> 