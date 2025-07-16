<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Dashboard Investor</h1>
            <p class="text-white">Pantau performa bisnis KasirKabuki</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <div class="badge badge-primary">{{ auth()->user()->name }}</div>
            <div class="badge badge-secondary">Role: Investor</div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Today Revenue -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="stat-figure text-success">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="flex-1 ml-4">
                        <h3 class="font-semibold text-sm">Penjualan Hari Ini</h3>
                        <p class="text-2xl font-bold text-success">{{ 'Rp ' . number_format($todayRevenue, 0, ',', '.') }}</p>
                        <p class="text-xs text-base-content/60">{{ \Carbon\Carbon::today()->format('d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today Expenses -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="stat-figure text-error">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 ml-4">
                        <h3 class="font-semibold text-sm">Pengeluaran Hari Ini</h3>
                        <p class="text-2xl font-bold text-error">{{ 'Rp ' . number_format($todayExpenses, 0, ',', '.') }}</p>
                        <p class="text-xs text-base-content/60">{{ \Carbon\Carbon::today()->format('d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Month Revenue -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="stat-figure text-info">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 ml-4">
                        <h3 class="font-semibold text-sm">Penjualan Bulan Ini</h3>
                        <p class="text-2xl font-bold text-info">{{ 'Rp ' . number_format($monthRevenue, 0, ',', '.') }}</p>
                        <p class="text-xs text-base-content/60">{{ \Carbon\Carbon::now()->format('F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Month Profit -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="stat-figure {{ $monthProfit >= 0 ? 'text-success' : 'text-error' }}">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="flex-1 ml-4">
                        <h3 class="font-semibold text-sm">Keuntungan Bulan Ini</h3>
                        <p class="text-2xl font-bold {{ $monthProfit >= 0 ? 'text-success' : 'text-error' }}">
                            {{ 'Rp ' . number_format($monthProfit, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-base-content/60">Penjualan - Pengeluaran</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter and Quick Filter -->
    <div class="card bg-base-300 shadow-lg mb-6">
        <div class="card-body">
            <h3 class="card-title mb-4">Filter Laporan</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Date Range -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Tanggal Mulai</span>
                    </label>
                    <input wire:model.live="filterStartDate" type="date" class="input input-bordered" />
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Tanggal Akhir</span>
                    </label>
                    <input wire:model.live="filterEndDate" type="date" class="input input-bordered" />
                </div>

                <!-- Quick Filters -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Filter Cepat</span>
                    </label>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-outline w-full">
                            Pilih Periode
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </label>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                            <li><button wire:click="setQuickFilter('today')" class="w-full text-left">Hari Ini</button></li>
                            <li><button wire:click="setQuickFilter('yesterday')" class="w-full text-left">Kemarin</button></li>
                            <li><button wire:click="setQuickFilter('this_week')" class="w-full text-left">Minggu Ini</button></li>
                            <li><button wire:click="setQuickFilter('this_month')" class="w-full text-left">Bulan Ini</button></li>
                            <li><button wire:click="setQuickFilter('last_month')" class="w-full text-left">Bulan Lalu</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Sales Chart -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <h3 class="card-title mb-4">Grafik Penjualan</h3>
                <div class="w-full h-64">
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Expenses Chart -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <h3 class="card-title mb-4">Grafik Pengeluaran</h3>
                <div class="w-full h-64">
                    <canvas id="expensesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Data Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transactions -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="card-title">Transaksi Terbaru</h3>
                    <a href="{{ route('investor.reports.sales') }}" class="btn btn-outline btn-sm">
                        Lihat Semua
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                
                @if(count($recentTransactions) > 0)
                    <div class="space-y-3">
                        @foreach($recentTransactions as $transaction)
                            <div class="flex justify-between items-center p-3 bg-base-100 rounded-lg">
                                <div>
                                    <div class="font-semibold text-sm">{{ $transaction['transaction_code'] }}</div>
                                    <div class="text-xs text-base-content/60">
                                        {{ \Carbon\Carbon::parse($transaction['transaction_date'] ?: $transaction['created_at'])->format('d M Y, H:i') }}
                                    </div>
                                    <div class="text-xs">
                                        @if($transaction['partner'])
                                            <span class="badge badge-info badge-xs">{{ $transaction['partner']['name'] }}</span>
                                        @endif
                                        <span class="badge badge-outline badge-xs">{{ ucfirst($transaction['order_type']) }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-success">{{ 'Rp ' . number_format($transaction['final_total'], 0, ',', '.') }}</div>
                                    <div class="text-xs text-base-content/60">{{ $transaction['user']['name'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-base-content/70">Belum ada transaksi terbaru</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="card-title">Pengeluaran Terbaru</h3>
                    <a href="{{ route('investor.reports.expenses') }}" class="btn btn-outline btn-sm">
                        Lihat Semua
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                
                @if(count($recentExpenses) > 0)
                    <div class="space-y-3">
                        @foreach($recentExpenses as $expense)
                            <div class="flex justify-between items-center p-3 bg-base-100 rounded-lg">
                                <div>
                                    <div class="font-semibold text-sm">{{ Str::limit($expense['description'], 30) }}</div>
                                    <div class="text-xs text-base-content/60">
                                        {{ \Carbon\Carbon::parse($expense['date'])->format('d M Y') }}
                                    </div>
                                    @php $categoryLabels = App\Models\Expense::getCategoryLabels(); @endphp
                                    <div class="badge badge-primary badge-xs mt-1">
                                        {{ $categoryLabels[$expense['category']] ?? $expense['category'] }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-error">{{ 'Rp ' . number_format($expense['amount'], 0, ',', '.') }}</div>
                                    <div class="text-xs text-base-content/60">{{ $expense['user']['name'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-base-content/70">Belum ada pengeluaran terbaru</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        let salesChart = null;
        let expensesChart = null;
        
        function initCharts() {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            if (salesChart) salesChart.destroy();
            
            salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: @json(collect($salesChartData)->pluck('date')),
                    datasets: [{
                        label: 'Penjualan',
                        data: @json(collect($salesChartData)->pluck('value')),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            // Expenses Chart
            const expensesCtx = document.getElementById('expensesChart').getContext('2d');
            if (expensesChart) expensesChart.destroy();
            
            expensesChart = new Chart(expensesCtx, {
                type: 'line',
                data: {
                    labels: @json(collect($expensesChartData)->pluck('date')),
                    datasets: [{
                        label: 'Pengeluaran',
                        data: @json(collect($expensesChartData)->pluck('value')),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Initialize charts after component is loaded
        setTimeout(initCharts, 100);
        
        // Re-initialize charts when data changes
        Livewire.on('chartDataUpdated', () => {
            setTimeout(initCharts, 100);
        });
    });
</script>
@endpush
