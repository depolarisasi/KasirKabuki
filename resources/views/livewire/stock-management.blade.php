<div class="container mx-auto px-4 py-6">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <!-- Header Section -->
            <div class="flex justify-between items-center mb-6">
<div>
                    <h1 class="text-2xl font-bold">Manajemen Stok Harian</h1>
                    <p class="text-base-content/70">Kelola stok awal, akhir, dan rekonsiliasi harian</p>
                    <div class="text-sm text-base-content/60 mt-1">
                        Tanggal: {{ Carbon\Carbon::today()->format('d F Y') }}
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="tabs tabs-boxed mb-6">
                <button wire:click="switchTab('input-awal')" 
                        class="tab {{ $activeTab === 'input-awal' ? 'tab-active' : '' }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Input Stok Awal
                </button>
                <button wire:click="switchTab('input-akhir')" 
                        class="tab {{ $activeTab === 'input-akhir' ? 'tab-active' : '' }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Input Stok Akhir
                </button>
                <button wire:click="switchTab('laporan')" 
                        class="tab {{ $activeTab === 'laporan' ? 'tab-active' : '' }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Laporan Rekonsiliasi
                </button>
            </div>

            <!-- Tab Content: Input Stok Awal -->
            @if($activeTab === 'input-awal')
                <div class="space-y-6">
                    <div class="alert alert-info">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Input stok awal untuk setiap produk di awal hari. Stok ini akan menjadi dasar perhitungan stok tersedia.</span>
                    </div>

                    <form wire:submit="inputStokAwal">
                        <div class="overflow-x-auto">
                            <table class="table table-zebra w-full">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Stok Saat Ini</th>
                                        <th>Kuantitas Awal</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productStatus as $status)
                                        <tr class="{{ $status['needs_stock_in'] ? '' : 'bg-success/10' }}">
                                            <td>
                                                <div class="flex items-center space-x-3">
                                                    <div class="avatar placeholder">
                                                        <div class="bg-primary text-primary-content rounded-full w-8">
                                                            <span class="text-xs">{{ substr($status['product']->name, 0, 1) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="font-semibold">{{ $status['product']->name }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge badge-outline">{{ $status['product']->category->name }}</div>
                                            </td>
                                            <td>
                                                @if($status['needs_stock_in'])
                                                    <div class="badge badge-warning">Perlu Input</div>
                                                @else
                                                    <div class="badge badge-success">Sudah Input</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="font-semibold">{{ $status['current_stock'] }} unit</div>
                                            </td>
                                            <td>
                                                @if($status['needs_stock_in'])
                                                    <input wire:model="stockQuantities.{{ $status['product']->id }}" 
                                                           type="number" min="0" step="1" 
                                                           placeholder="0" 
                                                           class="input input-bordered input-sm w-24" />
                                                @else
                                                    <span class="text-success font-semibold">✓ Selesai</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($status['needs_stock_in'])
                                                    <input wire:model="notes.{{ $status['product']->id }}" 
                                                           type="text" 
                                                           placeholder="Catatan..." 
                                                           class="input input-bordered input-sm w-32" />
                                                @else
                                                    <span class="text-base-content/50">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <button type="button" wire:click="resetForm" class="btn btn-ghost">
                                Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="inputStokAwal">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Simpan Stok Awal
                                </span>
                                <span wire:loading wire:target="inputStokAwal">
                                    <span class="loading loading-spinner loading-sm"></span>
                                    Menyimpan...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Tab Content: Input Stok Akhir -->
            @if($activeTab === 'input-akhir')
                <div class="space-y-6">
                    <div class="alert alert-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.33 0L3.5 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <span>Input stok fisik yang tersisa di akhir hari. Sistem akan menghitung selisih otomatis.</span>
                    </div>

                    <form wire:submit="inputStokAkhir">
                        <div class="overflow-x-auto">
                            <table class="table table-zebra w-full">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Kategori</th>
                                        <th>Stok Awal</th>
                                        <th>Terjual</th>
                                        <th>Expected</th>
                                        <th>Stok Fisik</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        @php
                                            $currentStock = $this->getProductCurrentStock($product->id);
                                            $expectedStock = $this->getProductExpectedStock($product->id);
                                            $stockIn = \App\Models\StockLog::forProduct($product->id)->stockIn()->today()->sum('quantity');
                                            $stockOut = \App\Models\StockLog::forProduct($product->id)->stockOut()->today()->sum('quantity');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="flex items-center space-x-3">
                                                    <div class="avatar placeholder">
                                                        <div class="bg-secondary text-secondary-content rounded-full w-8">
                                                            <span class="text-xs">{{ substr($product->name, 0, 1) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="font-semibold">{{ $product->name }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge badge-outline">{{ $product->category->name }}</div>
                                            </td>
                                            <td>
                                                <div class="font-semibold text-success">{{ $stockIn }} unit</div>
                                            </td>
                                            <td>
                                                <div class="font-semibold text-error">{{ $stockOut }} unit</div>
                                            </td>
                                            <td>
                                                <div class="font-semibold text-info">{{ $expectedStock }} unit</div>
                                            </td>
                                            <td>
                                                <input wire:model="stockQuantities.{{ $product->id }}" 
                                                       type="number" min="0" step="1" 
                                                       placeholder="0" 
                                                       class="input input-bordered input-sm w-24" />
                                            </td>
                                            <td>
                                                <input wire:model="notes.{{ $product->id }}" 
                                                       type="text" 
                                                       placeholder="Catatan..." 
                                                       class="input input-bordered input-sm w-32" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <button type="button" wire:click="resetForm" class="btn btn-ghost">
                                Reset Form
                            </button>
                            <button type="submit" class="btn btn-warning">
                                <span wire:loading.remove wire:target="inputStokAkhir">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Simpan Stok Akhir
                                </span>
                                <span wire:loading wire:target="inputStokAkhir">
                                    <span class="loading loading-spinner loading-sm"></span>
                                    Menyimpan...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Tab Content: Laporan Rekonsiliasi -->
            @if($activeTab === 'laporan')
                <div class="space-y-6">
                    <!-- Date Filter -->
                    <div class="flex justify-between items-center">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Pilih Tanggal</span>
                            </label>
                            <input wire:model.live="reportDate" type="date" 
                                   class="input input-bordered w-full max-w-xs" />
                        </div>
                        <button wire:click="exportReconciliation" class="btn btn-outline btn-success">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                            </svg>
                            Export Laporan
                        </button>
                    </div>

                    <!-- Reconciliation Table -->
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Kategori</th>
                                    <th>Stok Masuk</th>
                                    <th>Stok Keluar</th>
                                    <th>Expected</th>
                                    <th>Aktual</th>
                                    <th>Selisih</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reconciliation as $item)
                                    <tr>
                                        <td>
                                            <div class="flex items-center space-x-3">
                                                <div class="avatar placeholder">
                                                    <div class="bg-accent text-accent-content rounded-full w-8">
                                                        <span class="text-xs">{{ substr($item['product']->name, 0, 1) }}</span>
                                                    </div>
                                                </div>
                                                <div class="font-semibold">{{ $item['product']->name }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="badge badge-outline">{{ $item['product']->category->name }}</div>
                                        </td>
                                        <td>
                                            <div class="font-semibold text-success">{{ $item['stock_in'] }}</div>
                                        </td>
                                        <td>
                                            <div class="font-semibold text-error">{{ $item['stock_out'] }}</div>
                                        </td>
                                        <td>
                                            <div class="font-semibold text-info">{{ $item['expected_stock'] }}</div>
                                        </td>
                                        <td>
                                            @if($item['actual_stock'] !== null)
                                                <div class="font-semibold">{{ $item['actual_stock'] }}</div>
                                            @else
                                                <span class="text-base-content/50">Belum input</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item['difference'] !== null)
                                                <div class="font-semibold {{ $item['difference'] == 0 ? 'text-success' : ($item['difference'] > 0 ? 'text-warning' : 'text-error') }}">
                                                    {{ $item['difference'] > 0 ? '+' : '' }}{{ $item['difference'] }}
                                                </div>
                                            @else
                                                <span class="text-base-content/50">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item['actual_stock'] !== null)
                                                @if($item['difference'] == 0)
                                                    <div class="badge badge-success">Sesuai</div>
                                                @elseif($item['difference'] > 0)
                                                    <div class="badge badge-warning">Lebih</div>
                                                @else
                                                    <div class="badge badge-error">Kurang</div>
                                                @endif
                                            @else
                                                <div class="badge badge-ghost">Pending</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-8">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-base-content/30 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                                <p class="text-base-content/70">Tidak ada data rekonsiliasi untuk tanggal yang dipilih</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
