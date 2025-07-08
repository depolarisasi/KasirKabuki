{{-- Remove @section directive as component uses layout in PHP class --}}
<div class="container mx-auto px-4 py-6">
    <div class="min-h-screen bg-base-200 p-4">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-primary">📊 Laporan Penjualan</h1>
                            <p class="text-base-content/70 mt-1">Analisis komprehensif penjualan dan pendapatan</p>
                            @if($lastRefresh)
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-sm text-base-content/60">Terakhir diperbarui:</span>
                                    <span class="text-sm font-medium text-base-content">{{ $lastRefresh }}</span>
                                    @if($autoRefresh)
                                        <div class="badge badge-success badge-sm">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Real-time
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            <!-- Auto Refresh Toggle -->
                            <button wire:click="toggleAutoRefresh" 
                                    class="btn btn-sm {{ $autoRefresh ? 'btn-success' : 'btn-outline' }}"
                                    title="{{ $autoRefresh ? 'Nonaktifkan auto refresh' : 'Aktifkan auto refresh' }}">
                                @if($autoRefresh)
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path>
                                    </svg>
                                    Auto Update
                                @else
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3"></path>
                                    </svg>
                                    Manual
                                @endif
                            </button>
                            
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
                            
                            @if(!$investorMode)
                                <button wire:click="exportToExcel" 
                                        class="btn btn-success btn-sm"
                                        @if(empty($reportData)) disabled @endif>
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Export Excel
                                </button>
                            @endif
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
                        <button wire:click="setDatePeriod('yesterday')" 
                                class="btn btn-sm {{ $selectedPeriod === 'yesterday' ? 'btn-primary' : 'btn-outline' }}">
                            Kemarin
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
                    <!-- Total Transactions -->
                    <div class="card bg-gradient-to-r from-info to-info text-base-100 shadow-xl">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-info-content/70 text-sm">Total Transaksi</p>
                                    <p class="text-2xl font-bold">{{ number_format($this->totalTransactions) }}</p>
                                </div>
                                <div class="text-info-content/70">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gross Revenue -->
                    <div class="card bg-gradient-to-r from-success to-success text-base-100 shadow-xl">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-success-content/70 text-sm">Pendapatan Kotor</p>
                                    <p class="text-xl font-bold">{{ $this->formatCurrency($this->totalGrossRevenue) }}</p>
                                </div>
                                <div class="text-success-content/70">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Revenue -->
                    <div class="card bg-gradient-to-r from-primary to-primary text-base-100 shadow-xl">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-primary-content/70 text-sm">Pendapatan Bersih</p>
                                    <p class="text-xl font-bold">{{ $this->formatCurrency($this->totalNetRevenue) }}</p>
                                </div>
                                <div class="text-primary-content/70">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Profit -->
                    <div class="card bg-gradient-to-r from-{{ $this->netProfit >= 0 ? 'success to-success' : 'error to-error' }} text-base-100 shadow-xl">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-white/80 text-sm">Keuntungan Bersih</p>
                                    <p class="text-xl font-bold">{{ $this->formatCurrency($this->netProfit) }}</p>
                                </div>
                                <div class="text-white/70">
                                    @if($this->netProfit >= 0)
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

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Daily Sales Trend -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h2 class="card-title">Tren Penjualan Harian</h2>
                            <div class="h-64">
                                <canvas id="dailySalesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue by Category -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h2 class="card-title">Pendapatan per Kategori</h2>
                            <div class="h-64">
                                <canvas id="categoryRevenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Type Distribution -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">Distribusi Jenis Pesanan</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="h-64">
                                <canvas id="orderTypeChart"></canvas>
                            </div>
                            <div class="space-y-3">
                                @foreach($reportData['revenue_by_order_type'] ?? [] as $type => $data)
                                    <div class="flex justify-between items-center p-3 bg-base-200 rounded-lg">
                                        <div>
                                            <span class="font-semibold">
                                                @switch($type)
                                                    @case('dine_in') Makan di Tempat @break
                                                    @case('take_away') Bawa Pulang @break
                                                    @case('online') Online @break
                                                    @default {{ $type }}
                                                @endswitch
                                            </span>
                                            <p class="text-sm text-base-content/70">{{ $data['count'] }} transaksi</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold">{{ $this->formatCurrency($data['net_revenue']) }}</p>
                                            <p class="text-sm text-base-content/70">Rata-rata: {{ $this->formatCurrency($data['avg_order_value']) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">Produk Terlaris</h2>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Produk</th>
                                        <th>Kategori</th>
                                        <th>Terjual</th>
                                        <th>Pendapatan</th>
                                        <th>Rata-rata/Order</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData['top_products'] ?? [] as $index => $product)
                                        <tr>
                                            <td>
                                                <div class="font-bold text-primary">{{ $index + 1 }}</div>
                                            </td>
                                            <td>
                                                <div class="font-semibold">{{ $product['product_name'] }}</div>
                                            </td>
                                            <td>
                                                <div class="badge badge-outline">{{ $product['category_name'] }}</div>
                                            </td>
                                            <td>
                                                <div class="font-semibold">{{ number_format($product['total_quantity']) }} pcs</div>
                                                <div class="text-sm text-base-content/70">{{ $product['order_count'] }} order</div>
                                            </td>
                                            <td>
                                                <div class="font-bold">{{ $this->formatCurrency($product['total_revenue']) }}</div>
                                            </td>
                                            <td>
                                                <div>{{ number_format($product['avg_quantity_per_order'], 1) }} pcs</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-base-content/70 py-8">
                                                Tidak ada data produk untuk periode ini
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Partner Performance -->
                @if(!empty($reportData['partner_performance']))
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h2 class="card-title">Performa Partner Online</h2>
                            <div class="overflow-x-auto">
                                <table class="table table-zebra">
                                    <thead>
                                        <tr>
                                            <th>Partner</th>
                                            <th>Commission Rate</th>
                                            <th>Orders</th>
                                            <th>Gross Revenue</th>
                                            <th>Commission</th>
                                            <th>Net Revenue</th>
                                            <th>Avg Order</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData['partner_performance'] as $partner)
                                            <tr>
                                                <td>
                                                    <div class="font-semibold">{{ $partner['partner_name'] }}</div>
                                                </td>
                                                <td>
                                                    <div class="badge badge-warning">{{ $partner['commission_rate'] }}%</div>
                                                </td>
                                                <td>
                                                    <div class="font-semibold">{{ number_format($partner['order_count']) }}</div>
                                                </td>
                                                <td>
                                                    <div class="font-bold">{{ $this->formatCurrency($partner['gross_revenue']) }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-warning font-semibold">{{ $this->formatCurrency($partner['total_commission']) }}</div>
                                                </td>
                                                <td>
                                                    <div class="font-bold text-success">{{ $this->formatCurrency($partner['net_revenue']) }}</div>
                                                </td>
                                                <td>
                                                    <div>{{ $this->formatCurrency($partner['avg_order_value']) }}</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Detailed Summary -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title">Ringkasan Detail</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <h3 class="font-semibold text-lg">Pendapatan</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Pendapatan Kotor:</span>
                                        <span class="font-semibold">{{ $this->formatCurrency($this->totalGrossRevenue) }}</span>
                                    </div>
                                    <div class="flex justify-between text-error">
                                        <span>Total Diskon:</span>
                                        <span class="font-semibold">-{{ $this->formatCurrency($this->totalDiscounts) }}</span>
                                    </div>
                                    <div class="flex justify-between text-warning">
                                        <span>Komisi Partner:</span>
                                        <span class="font-semibold">-{{ $this->formatCurrency($this->totalCommissions) }}</span>
                                    </div>
                                    <div class="border-t pt-2">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span>Pendapatan Bersih:</span>
                                            <span class="text-primary">{{ $this->formatCurrency($this->totalNetRevenue) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h3 class="font-semibold text-lg">Operasional</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Pendapatan Bersih:</span>
                                        <span class="font-semibold">{{ $this->formatCurrency($this->totalNetRevenue) }}</span>
                                    </div>
                                    <div class="flex justify-between text-error">
                                        <span>Total Pengeluaran:</span>
                                        <span class="font-semibold">-{{ $this->formatCurrency($reportData['summary']['total_expenses'] ?? 0) }}</span>
                                    </div>
                                    <div class="border-t pt-2">
                                        <div class="flex justify-between text-lg font-bold">
                                            <span>Keuntungan Bersih:</span>
                                            <span class="text-{{ $this->netProfit >= 0 ? 'success' : 'error' }}">
                                                {{ $this->formatCurrency($this->netProfit) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-sm text-base-content/70 mt-2">
                                        <div>Rata-rata nilai order: {{ $this->formatCurrency($this->avgOrderValue) }}</div>
                                        <div>Periode: {{ Carbon\Carbon::parse($reportData['period']['start_date'])->format('d/m/Y') }} - {{ Carbon\Carbon::parse($reportData['period']['end_date'])->format('d/m/Y') }}</div>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-base-content/70 mb-2">Belum Ada Data Laporan</h3>
                        <p class="text-base-content/50 mb-4">Pilih periode dan klik "Buat Laporan" untuk melihat analisis penjualan</p>
                        <button wire:click="generateReport" class="btn btn-primary">
                            Buat Laporan Sekarang
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('livewire:init', () => {
    let dailySalesChart, categoryRevenueChart, orderTypeChart;
    
    // Initialize charts when data is updated
    Livewire.on('update-charts', (event) => {
        const chartData = event[0];
        
        // Destroy existing charts
        if (dailySalesChart) dailySalesChart.destroy();
        if (categoryRevenueChart) categoryRevenueChart.destroy();
        if (orderTypeChart) orderTypeChart.destroy();
        
        // Daily Sales Trend Chart
        const dailySalesCtx = document.getElementById('dailySalesChart');
        if (dailySalesCtx && chartData.daily_sales) {
            dailySalesChart = new Chart(dailySalesCtx, {
                type: 'line',
                data: chartData.daily_sales,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Pendapatan (Rp)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Jumlah Transaksi'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }
        
        // Category Revenue Pie Chart
        const categoryCtx = document.getElementById('categoryRevenueChart');
        if (categoryCtx && chartData.category_revenue) {
            categoryRevenueChart = new Chart(categoryCtx, {
                type: 'pie',
                data: chartData.category_revenue,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        // Order Type Distribution Chart
        const orderTypeCtx = document.getElementById('orderTypeChart');
        if (orderTypeCtx && chartData.order_type_distribution) {
            orderTypeChart = new Chart(orderTypeCtx, {
                type: 'doughnut',
                data: chartData.order_type_distribution,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    });
    
    // Handle real-time report updates
    Livewire.on('report-updated', (event) => {
        const data = event[0];
        showRealTimeNotification(data.message, data.time);
    });
    
    // Function to show discrete real-time notifications
    function showRealTimeNotification(message, time) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 z-50 transform translate-x-full transition-transform duration-300 ease-in-out';
        notification.innerHTML = `
            <div class="alert alert-info shadow-lg max-w-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <div class="font-semibold">${message}</div>
                    <div class="text-sm opacity-70">Pukul ${time}</div>
                </div>
                <button class="btn btn-sm btn-ghost" onclick="this.parentElement.parentElement.remove()">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    // Listen for global transaction events (for cross-tab communication)
    document.addEventListener('transaction-completed', (event) => {
        // This will trigger if the event comes from another tab/window
        if (event.detail) {
            @this.call('handleTransactionCompleted', event.detail);
        }
    });
});

// Global function to broadcast transaction events across tabs
window.broadcastTransactionCompleted = function(transactionData) {
    // Broadcast custom event for cross-tab communication
    const event = new CustomEvent('transaction-completed', {
        detail: transactionData
    });
    document.dispatchEvent(event);
    
    // Also use localStorage for cross-tab communication
    localStorage.setItem('last-transaction', JSON.stringify({
        ...transactionData,
        timestamp: Date.now()
    }));
};

// Listen for localStorage changes (cross-tab communication)
window.addEventListener('storage', function(e) {
    if (e.key === 'last-transaction' && e.newValue) {
        try {
            const transactionData = JSON.parse(e.newValue);
            // Only process if timestamp is within last 10 seconds
            if (Date.now() - transactionData.timestamp < 10000) {
                @this.call('handleTransactionCompleted', transactionData);
            }
        } catch (error) {
            console.log('Error parsing transaction data:', error);
        }
    }
});
</script>
