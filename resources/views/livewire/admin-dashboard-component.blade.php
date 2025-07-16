<div class="bg-base-200 px-4 py-4">
    {{-- Page Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div class="bg-base-300 bg-opacity-10 rounded-lg p-4">
            <h1 class="text-lg font-bold text-white">Dashboard Admin</h1>
            <p class="text-white">Monitoring dan analisis komprehensif sistem KasirBraga</p>
            <div class="text-sm text-white/70 mt-1">
                Periode: {{ $this->getPeriodLabel() }} | Terakhir diperbarui: {{ $lastRefresh }}
            </div>
        </div>
        
        {{-- Controls --}}
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Period Selection --}}
            <div class="dropdown dropdown-bottom dropdown-end">
                <div tabindex="0" role="button" class="btn btn-outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Periode
                </div>
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                    <li><a wire:click="setDatePeriod('today')" class="@if($selectedPeriod === 'today') active @endif">Hari Ini</a></li>
                    <li><a wire:click="setDatePeriod('yesterday')" class="@if($selectedPeriod === 'yesterday') active @endif">Kemarin</a></li>
                    <li><a wire:click="setDatePeriod('week')" class="@if($selectedPeriod === 'week') active @endif">Minggu Ini</a></li>
                    <li><a wire:click="setDatePeriod('month')" class="@if($selectedPeriod === 'month') active @endif">Bulan Ini</a></li>
                    <li><a wire:click="setDatePeriod('custom')" class="@if($selectedPeriod === 'custom') active @endif">Custom</a></li>
                </ul>
            </div>

            {{-- Refresh Button --}}
            <button wire:click="refreshStats" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </span>
                <span wire:loading>
                    <span class="loading loading-spinner loading-sm mr-2"></span>
                    Loading...
                </span>
            </button>

            {{-- Auto Refresh Toggle --}}
            <button wire:click="toggleAutoRefresh" class="btn btn-ghost">
                @if($autoRefresh)
                    <svg class="w-4 h-4 mr-2 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Auto Refresh
                @else
                    <svg class="w-4 h-4 mr-2 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Manual
                @endif
            </button>
        </div>
    </div>

    {{-- Custom Date Range --}}
    @if($customDateRange)
        <div class="card bg-base-300 shadow-lg mb-6">
            <div class="card-body p-4">
                <h3 class="font-semibold mb-3">Pilih Rentang Tanggal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Tanggal Mulai</span></label>
                        <input type="date" wire:model.live="startDate" class="input input-bordered">
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Tanggal Akhir</span></label>
                        <input type="date" wire:model.live="endDate" class="input input-bordered">
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading Indicator --}}
    @if($isLoading)
        <div class="flex justify-center py-8">
            <span class="loading loading-spinner loading-lg"></span>
        </div>
    @endif

    {{-- System Alerts --}}
    @if(!empty($dashboardStats['alerts']))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach($dashboardStats['alerts'] as $alert)
                <div class="alert {{ $this->getAlertClass($alert['type']) }}">
                    <svg class="w-6 h-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $this->getAlertIcon($alert['icon']) }}"></path>
                    </svg>
                    <div>
                        <h4 class="font-bold">{{ $alert['title'] }}</h4>
                        <div class="text-sm">{{ $alert['message'] }}</div>
                        @if(isset($alert['action_url']))
                            <div class="mt-2">
                                <a href="{{ $alert['action_url'] }}" class="btn btn-sm btn-outline">
                                    {{ $alert['action_text'] }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Overview Statistics --}}
    @if(!empty($dashboardStats['overview']))
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
            {{-- Total Sales --}}
            <div class="stat bg-gradient-to-br from-success/20 to-success/5 border border-success/20 rounded-xl">
                <div class="stat-figure text-success">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="stat-title text-success">Total Penjualan</div>
                <div class="stat-value text-success">{{ $this->formatCurrency($dashboardStats['overview']['total_sales']) }}</div>
                <div class="stat-desc">
                    <span class="{{ $this->getChangeClass($dashboardStats['overview']['comparison']['sales_change']) }}">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $this->getChangeIcon($dashboardStats['overview']['comparison']['sales_change']) }}"></path>
                        </svg>
                        {{ $this->formatPercentage(abs($dashboardStats['overview']['comparison']['sales_change'])) }}
                    </span>
                    dari periode sebelumnya
                </div>
            </div>

            {{-- Total Expenses --}}
            <div class="stat bg-gradient-to-br from-error/20 to-error/5 border border-error/20 rounded-xl">
                <div class="stat-figure text-error">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                </div>
                <div class="stat-title text-error">Total Pengeluaran</div>
                <div class="stat-value text-error">{{ $this->formatCurrency($dashboardStats['overview']['total_expenses']) }}</div>
                <div class="stat-desc">
                    <span class="{{ $this->getChangeClass($dashboardStats['overview']['comparison']['expenses_change']) }}">
                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $this->getChangeIcon($dashboardStats['overview']['comparison']['expenses_change']) }}"></path>
                        </svg>
                        {{ $this->formatPercentage(abs($dashboardStats['overview']['comparison']['expenses_change'])) }}
                    </span>
                    dari periode sebelumnya
                </div>
            </div>

            {{-- Net Profit --}}
            <div class="stat bg-gradient-to-br from-info/20 to-info/5 border border-info/20 rounded-xl">
                <div class="stat-figure text-info">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="stat-title text-info">Keuntungan Bersih</div>
                <div class="stat-value text-info">{{ $this->formatCurrency($dashboardStats['overview']['net_profit']) }}</div>
                <div class="stat-desc">
                    Margin: {{ $this->formatPercentage($dashboardStats['overview']['profit_margin']) }}
                </div>
            </div>

            {{-- Total Transactions --}}
            <div class="stat bg-gradient-to-br from-warning/20 to-warning/5 border border-warning/20 rounded-xl">
                <div class="stat-figure text-warning">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="stat-title text-warning">Total Transaksi</div>
                <div class="stat-value text-warning">{{ number_format($dashboardStats['overview']['total_transactions']) }}</div>
                <div class="stat-desc">
                    Rata-rata: {{ $this->formatCurrency($dashboardStats['overview']['avg_transaction']) }}
                </div>
            </div>
        </div>
    @endif

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        {{-- Daily Sales Chart --}}
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Tren Penjualan Harian
                </h3>
                <div class="h-80">
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Hourly Pattern Chart --}}
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Pola Penjualan per Jam
                </h3>
                <div class="h-80">
                    <canvas id="hourlyPatternChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Pie Charts Section --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
        {{-- Sales by Order Type --}}
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 011-1h1m0 0h2m0 0h1a1 1 0 011 1v2M7 7h10"></path>
                    </svg>
                    Penjualan per Jenis Pesanan
                </h3>
                <div class="h-64">
                    <canvas id="orderTypeChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Sales by Payment Method --}}
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    Metode Pembayaran
                </h3>
                <div class="h-64">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Expenses by Category --}}
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                    Pengeluaran per Kategori
                </h3>
                <div class="h-64">
                    <canvas id="expensesCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Details Section --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Top Products --}}
        @if(!empty($dashboardStats['sales']['top_products']))
            <div class="card bg-base-300 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        Produk Terlaris
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dashboardStats['sales']['top_products']->take(5) as $product)
                                    <tr>
                                        <td class="font-medium">{{ $product->name }}</td>
                                        <td>{{ $product->total_quantity }}</td>
                                        <td class="font-semibold">{{ $this->formatCurrency($product->total_amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent Transactions --}}
        <div class="card bg-base-300 shadow-lg">