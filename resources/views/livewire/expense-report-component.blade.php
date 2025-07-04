{{-- Remove @section directive as component uses layout in PHP class --}}
<div class="container mx-auto px-4 py-6">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-primary">💰 Laporan Pengeluaran</h1>
                        <p class="text-base-content/70 mt-1">Analisis pengeluaran operasional harian</p>
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Total Expenses -->
                <div class="card bg-gradient-to-r from-error to-error text-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-error-content/70 text-sm">Total Pengeluaran</p>
                                <p class="text-2xl font-bold">{{ $this->formatCurrency($reportData['summary']['total_expenses'] ?? 0) }}</p>
                            </div>
                            <div class="text-error-content/70">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Count -->
                <div class="card bg-gradient-to-r from-info to-info text-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-info-content/70 text-sm">Jumlah Transaksi</p>
                                <p class="text-2xl font-bold">{{ number_format($reportData['summary']['expense_count'] ?? 0) }}</p>
                            </div>
                            <div class="text-info-content/70">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Expense -->
                <div class="card bg-gradient-to-r from-primary to-primary text-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-primary-content/70 text-sm">Rata-rata Pengeluaran</p>
                                <p class="text-xl font-bold">{{ $this->formatCurrency($reportData['summary']['average_expense'] ?? 0) }}</p>
                            </div>
                            <div class="text-primary-content/70">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses by Date -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Pengeluaran per Tanggal</h2>
                    <div class="space-y-4">
                        @forelse($reportData['expenses_by_date'] ?? [] as $date => $dayData)
                            <div class="collapse collapse-arrow bg-base-200">
                                <input type="radio" name="expense-accordion" />
                                <div class="collapse-title text-lg font-medium">
                                    <div class="flex justify-between items-center">
                                        <span>{{ $this->formatDate($date) }}</span>
                                        <div class="flex gap-4">
                                            <span class="badge badge-primary">{{ $dayData['count'] }} transaksi</span>
                                            <span class="font-bold">{{ $this->formatCurrency($dayData['total']) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="collapse-content">
                                    <div class="overflow-x-auto">
                                        <table class="table table-zebra">
                                            <thead>
                                                <tr>
                                                    <th>Waktu</th>
                                                    <th>Keterangan</th>
                                                    <th>Jumlah</th>
                                                    <th>Diinput oleh</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dayData['expenses'] as $expense)
                                                    <tr>
                                                        <td>{{ Carbon\Carbon::parse($expense['created_at'])->format('H:i') }}</td>
                                                        <td>{{ $expense['description'] }}</td>
                                                        <td class="font-semibold">{{ $this->formatCurrency($expense['amount']) }}</td>
                                                        <td>
                                                            <div class="badge badge-outline">{{ $expense['user']['name'] ?? 'Unknown' }}</div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-base-content/70 py-8">
                                <svg class="w-16 h-16 text-base-content/30 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM13 8.5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
                                </svg>
                                <p>Tidak ada pengeluaran pada periode ini</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- All Expenses Table -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Detail Semua Pengeluaran</h2>
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Keterangan</th>
                                    <th>Jumlah</th>
                                    <th>Diinput oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['all_expenses'] ?? [] as $expense)
                                    <tr>
                                        <td>{{ $this->formatDate($expense['date']) }}</td>
                                        <td>{{ Carbon\Carbon::parse($expense['created_at'])->format('H:i') }}</td>
                                        <td>{{ $expense['description'] }}</td>
                                        <td class="font-semibold">{{ $this->formatCurrency($expense['amount']) }}</td>
                                        <td>
                                            <div class="badge badge-outline">{{ $expense['user']['name'] ?? 'Unknown' }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-base-content/70 py-8">
                                            Tidak ada data pengeluaran untuk periode ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Period Summary -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Ringkasan Periode</h2>
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
                                    <span>Total Transaksi:</span>
                                    <span class="font-semibold">{{ number_format($reportData['summary']['expense_count']) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Rata-rata per Transaksi:</span>
                                    <span class="font-semibold">{{ $this->formatCurrency($reportData['summary']['average_expense']) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h3 class="font-semibold text-lg">Summary Total</h3>
                            <div class="space-y-2">
                                <div class="border-t pt-2">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total Pengeluaran:</span>
                                        <span class="text-error">{{ $this->formatCurrency($reportData['summary']['total_expenses']) }}</span>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM13 8.5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-base-content/70 mb-2">Belum Ada Data Laporan</h3>
                    <p class="text-base-content/50 mb-4">Pilih periode dan klik "Buat Laporan" untuk melihat analisis pengeluaran</p>
                    <button wire:click="generateReport" class="btn btn-primary">
                        Buat Laporan Sekarang
                    </button>
                </div>
            </div>
        @endif
    </div>
</div> 