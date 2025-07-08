<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-primary">📦 Laporan Stok</h1>
                        <p class="text-base-content/70 mt-1">Analisis rekonsiliasi stok harian</p>
                    </div>
                    
                    <div class="flex gap-2">
                        <button wire:click="refreshReport" 
                                class="btn btn-outline btn-primary btn-sm"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="refreshReport">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh
                            </span>
                            <span wire:loading wire:target="refreshReport">
                                <span class="loading loading-spinner loading-sm"></span>
                                Loading...
                            </span>
                        </button>
                        
                        <button wire:click="exportToExcel" 
                                class="btn btn-success btn-sm"
                                @if(empty($reportData)) disabled @endif>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filters -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Filter Periode</h2>
                
                <!-- Quick Period Buttons -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <button wire:click="setDatePeriod('today')" 
                            class="btn btn-sm {{ $selectedPeriod === 'today' ? 'btn-primary' : 'btn-outline' }}">
                        Hari Ini
                    </button>
                    <button wire:click="setDatePeriod('week')" 
                            class="btn btn-sm {{ $selectedPeriod === 'week' ? 'btn-primary' : 'btn-outline' }}">
                        Minggu Ini
                    </button>
                    <button wire:click="setDatePeriod('month')" 
                            class="btn btn-sm {{ $selectedPeriod === 'month' ? 'btn-primary' : 'btn-outline' }}">
                        Bulan Ini
                    </button>
                    <button wire:click="setDatePeriod('custom')" 
                            class="btn btn-sm {{ $selectedPeriod === 'custom' ? 'btn-primary' : 'btn-outline' }}">
                        Custom
                    </button>
                </div>

                <!-- Custom Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Tanggal Mulai</span>
                        </label>
                        <input wire:model="startDate" type="date" class="input input-bordered" />
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Tanggal Akhir</span>
                        </label>
                        <input wire:model="endDate" type="date" class="input input-bordered" />
                    </div>
                    
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">&nbsp;</span>
                        </label>
                        <button wire:click="generateReport" 
                                class="btn btn-primary"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="generateReport">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Buat Laporan
                            </span>
                            <span wire:loading wire:target="generateReport">
                                <span class="loading loading-spinner loading-sm"></span>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($reportData))
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Initial Stock -->
                <div class="card bg-gradient-to-r from-info to-info text-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-info-content/70 text-sm">Total Stok Awal</p>
                                <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_initial_stock'] ?? 0) }}</p>
                            </div>
                            <div class="text-info-content/70">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Sold -->
                <div class="card bg-gradient-to-r from-success to-success text-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-success-content/70 text-sm">Total Terjual</p>
                                <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_sold'] ?? 0) }}</p>
                            </div>
                            <div class="text-success-content/70">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5 0a2 2 0 100-4 2 2 0 000 4zm6 0a2 2 0 100-4 2 2 0 000 4z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Final Stock -->
                <div class="card bg-gradient-to-r from-primary to-primary text-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-primary-content/70 text-sm">Total Stok Akhir</p>
                                <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_final_stock'] ?? 0) }}</p>
                            </div>
                            <div class="text-primary-content/70">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Differences -->
                <div class="card bg-gradient-to-r from-{{ $reportData['summary']['total_differences'] >= 0 ? 'success to-success' : 'error to-error' }} text-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm">Total Selisih</p>
                                <p class="text-2xl font-bold">{{ number_format($reportData['summary']['total_differences'] ?? 0) }}</p>
                            </div>
                            <div class="text-white/70">
                                @if(($reportData['summary']['total_differences'] ?? 0) >= 0)
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                @else
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Stock Reports -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Rekonsiliasi Stok Harian</h2>
                    <div class="space-y-4">
                        @forelse($reportData['daily_reports'] ?? [] as $day)
                            <div class="collapse collapse-arrow bg-base-200">
                                <input type="radio" name="stock-accordion" />
                                <div class="collapse-title text-lg font-medium">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-3">
                                            <span>{{ $day['formatted_date'] }}</span>
                                            <span class="text-sm text-base-content/70">({{ $day['day_name'] }})</span>
                                            @if(!$day['has_data'])
                                                <div class="badge badge-warning">Tidak ada data</div>
                                            @endif
                                        </div>
                                        <div class="flex gap-4">
                                            @if($day['has_data'])
                                                <span class="text-sm">
                                                    Awal: <span class="font-bold">{{ number_format($day['day_totals']['initial_stock']) }}</span>
                                                </span>
                                                <span class="text-sm">
                                                    Terjual: <span class="font-bold">{{ number_format($day['day_totals']['sold']) }}</span>
                                                </span>
                                                <span class="text-sm">
                                                    Akhir: <span class="font-bold">{{ number_format($day['day_totals']['final_stock']) }}</span>
                                                </span>
                                                <span class="text-sm {{ $this->getStockStatusClass($day['day_totals']['difference']) }}">
                                                    Selisih: <span class="font-bold">{{ $day['day_totals']['difference'] >= 0 ? '+' : '' }}{{ number_format($day['day_totals']['difference']) }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($day['has_data'])
                                    <div class="collapse-content">
                                        <div class="overflow-x-auto">
                                            <table class="table table-zebra">
                                                <thead>
                                                    <tr>
                                                        <th>Produk</th>
                                                        <th>Stok Awal</th>
                                                        <th>Terjual</th>
                                                        <th>Stok Akhir (Manual)</th>
                                                        <th>Stok Seharusnya</th>
                                                        <th>Selisih</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($day['reconciliation'] as $item)
                                                        <tr>
                                                            <td class="font-semibold">{{ $item['product']->name ?? 'N/A' }}</td>
                                                            <td>{{ number_format($item['initial_stock'] ?? 0) }}</td>
                                                            <td>{{ number_format($item['sold'] ?? 0) }}</td>
                                                            <td>{{ number_format($item['final_stock'] ?? 0) }}</td>
                                                            <td>{{ number_format(($item['initial_stock'] ?? 0) - ($item['sold'] ?? 0)) }}</td>
                                                            <td class="{{ $this->getStockStatusClass($item['difference'] ?? 0) }} font-semibold">
                                                                {{ ($item['difference'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($item['difference'] ?? 0) }}
                                                            </td>
                                                            <td>
                                                                <div class="badge {{ ($item['difference'] ?? 0) == 0 ? 'badge-success' : (($item['difference'] ?? 0) > 0 ? 'badge-info' : 'badge-error') }}">
                                                                    {{ $this->getStockStatusText($item['difference'] ?? 0) }}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-base-content/70 py-8">
                                <svg class="w-16 h-16 text-base-content/30 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p>Tidak ada data stok pada periode ini</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Summary Analysis -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Analisis Ringkasan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <h3 class="font-semibold text-lg">Informasi Periode</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Periode:</span>
                                    <span class="font-semibold">
                                        {{ $this->formatDate($reportData['period']['start_date']) }} - {{ $this->formatDate($reportData['period']['end_date']) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Total Hari:</span>
                                    <span class="font-semibold">{{ number_format($reportData['summary']['total_days']) }} hari</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Hari dengan Data Stok:</span>
                                    <span class="font-semibold">{{ number_format($reportData['summary']['days_with_stock']) }} hari</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Rata-rata Stok Harian:</span>
                                    <span class="font-semibold">{{ number_format($reportData['summary']['avg_daily_stock'], 1) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Rata-rata Terjual Harian:</span>
                                    <span class="font-semibold">{{ number_format($reportData['summary']['avg_daily_sold'], 1) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h3 class="font-semibold text-lg">Summary Total</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Total Stok Awal:</span>
                                    <span class="font-semibold text-info">{{ number_format($reportData['summary']['total_initial_stock']) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Total Terjual:</span>
                                    <span class="font-semibold text-success">{{ number_format($reportData['summary']['total_sold']) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Total Stok Akhir:</span>
                                    <span class="font-semibold text-primary">{{ number_format($reportData['summary']['total_final_stock']) }}</span>
                                </div>
                                <div class="border-t pt-2">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total Selisih:</span>
                                        <span class="{{ ($reportData['summary']['total_differences'] ?? 0) >= 0 ? 'text-success' : 'text-error' }}">
                                            {{ ($reportData['summary']['total_differences'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($reportData['summary']['total_differences']) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body text-center py-12">
                    <svg class="w-16 h-16 text-base-content/30 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-base-content/70 mb-2">Belum Ada Data Laporan</h3>
                    <p class="text-base-content/50 mb-4">Pilih periode dan klik "Buat Laporan" untuk melihat analisis stok</p>
                    <button wire:click="generateReport" class="btn btn-primary">
                        Buat Laporan Sekarang
                    </button>
                </div>
            </div>
        @endif
    </div>
</div> 