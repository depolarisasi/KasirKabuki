<div class="bg-base-100">
    <!-- Header Section -->
    <div class=" shadow-lg rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-4">
           
            <!-- Date Navigation -->
            <div class="flex items-center space-x-3 mt-4 md:mt-0">
                @if (!$this->isToday())
                    <button 
                        wire:click="goToToday" 
                        class="btn btn-outline btn-sm"
                        title="Kembali ke hari ini">
                        📅 Hari Ini
                    </button>
                @endif
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Pilih Tanggal:</span>
                    </label>
                    <input 
                        type="date" 
                        wire:model.live="selectedDate"
                        class="input input-bordered input-sm w-40"
                        max="{{ now()->format('Y-m-d') }}"
                    />
                </div>
            </div>
        </div>
        
        <!-- Date Display -->
        <div class="bg-base-300 bg-opacity-10 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-primary">{{ $formattedDate }}</h2>
                    <p class="text-sm text-white">
                        @if ($this->isToday())
                            <span class="badge badge-success badge-sm">Hari Ini</span>
                        @else
                            <span class="badge badge-info badge-sm">Data Historis</span>
                        @endif
                    </p>
                </div>
                
                <!-- Edit Toggle -->
                <div class="flex items-center space-x-2">
                    <button 
                        wire:click="toggleEditing" 
                        class="btn {{ $isEditing ? 'btn-warning' : 'btn-primary' }} btn-sm">
                        @if ($isEditing)
                            ✏️ Mode Edit Aktif
                        @else
                            📝 Edit Stok
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success mb-6">
            <span>✅ {{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error mb-6">
            <span>❌ {{ session('error') }}</span>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info mb-6">
            <span>ℹ️ {{ session('info') }}</span>
        </div>
    @endif

    <!-- Stock Table -->
    <div class=" shadow-lg rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-white">📋 Data Stok Harian</h3>
            
            @if ($isEditing)
                <div class="flex space-x-2">
                    <button 
                        wire:click="saveAllChanges" 
                        class="btn btn-success btn-sm">
                        💾 Simpan Semua
                    </button>
                    <button 
                        wire:click="resetForm" 
                        class="btn btn-outline btn-sm">
                        🔄 Reset
                    </button>
                </div>
            @endif
        </div>
        
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr class="bg-base-300 text-primary-content">
                        <th class="text-center">🍖 Jenis Sate</th>
                        <th class="text-center">📦 Stok Awal</th>
                        <th class="text-center">💰 Stok Terjual</th>
                        <th class="text-center">📦 Stok Akhir</th>
                        <th class="text-center">⚖️ Selisih</th>
                        <th class="text-center">📝 Catatan</th>
                        @if ($isEditing)
                            <th class="text-center">⚡ Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jenisSateOptions as $jenisSate)
                        @php
                            $entry = collect($stockEntries)->where('jenis_sate', $jenisSate)->first();
                            $selisih = $this->getSelisih($jenisSate);
                            $selisihClass = $selisih < 0 ? 'text-error font-bold' : ($selisih > 0 ? 'text-warning font-bold' : 'text-success');
                        @endphp
                        <tr class="hover">
                            <!-- Jenis Sate -->
                            <td class="font-medium text-center">
                                <div class="badge badge-outline badge-lg">{{ $jenisSate }}</div>
                            </td>
                            
                            <!-- Stok Awal -->
                            <td class="text-center">
                                @if ($isEditing)
                                    <input 
                                        type="number" 
                                        wire:model.lazy="stokAwal.{{ $jenisSate }}"
                                        wire:change="updateStokAwal('{{ $jenisSate }}')"
                                        class="input input-bordered input-sm w-20 text-center"
                                        min="0"
                                        placeholder="0"
                                    />
                                @else
                                    <span class="badge badge-primary">{{ $entry['stok_awal'] ?? 0 }}</span>
                                @endif
                            </td>
                            
                            <!-- Stok Terjual (Read-only) -->
                            <td class="text-center">
                                <span class="badge badge-info">{{ $entry['stok_terjual'] ?? 0 }}</span>
                                <div class="text-xs text-gray-500 mt-1">Auto dari transaksi</div>
                            </td>
                            
                            <!-- Stok Akhir -->
                            <td class="text-center">
                                @if ($isEditing)
                                    <input 
                                        type="number" 
                                        wire:model.lazy="stokAkhir.{{ $jenisSate }}"
                                        wire:change="updateStokAkhir('{{ $jenisSate }}')"
                                        class="input input-bordered input-sm w-20 text-center"
                                        min="0"
                                        placeholder="0"
                                    />
                                @else
                                    <span class="badge badge-primary">{{ $entry['stok_akhir'] ?? 0 }}</span>
                                @endif
                            </td>
                            
                            <!-- Selisih (Calculated) -->
                            <td class="text-center">
                                <span class="badge {{ $selisih < 0 ? 'badge-error' : ($selisih > 0 ? 'badge-warning' : 'badge-success') }}">
                                    {{ $selisih }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1">
                                    @if ($selisih < 0)
                                        Minus/Kurang
                                    @elseif ($selisih > 0)
                                        Lebih/Surplus
                                    @else
                                        Seimbang
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Keterangan -->
                            <td class="text-center max-w-xs">
                                @if ($isEditing)
                                    <textarea 
                                        wire:model.lazy="keterangan.{{ $jenisSate }}"
                                        wire:change="updateKeterangan('{{ $jenisSate }}')"
                                        class="textarea textarea-bordered textarea-sm w-full text-xs"
                                        rows="2"
                                        placeholder="Tambahkan catatan..."
                                    ></textarea>
                                @else
                                    <div class="text-xs text-white break-words">
                                        {{ $entry['keterangan'] ?? '-' }}
                                    </div>
                                @endif
                            </td>
                            
                            <!-- Aksi (Edit Mode) -->
                            @if ($isEditing)
                                <td class="text-center">
                                    <div class="flex flex-col space-y-1">
                                        <button 
                                            wire:click="calculateSelisih('{{ $jenisSate }}')"
                                            class="btn btn-ghost btn-xs"
                                            title="Hitung ulang selisih">
                                            🔄
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="bg-gradient-to-r from-primary to-secondary text-white shadow-lg rounded-lg p-6">
        <h3 class="text-xl font-semibold mb-4">📊 Ringkasan Stok</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class=" bg-opacity-20 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold">{{ $totalSummary['total_stok_awal'] }}</div>
                <div class="text-sm opacity-90">Total Stok Awal</div>
            </div>
            
            <div class=" bg-opacity-20 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold">{{ $totalSummary['total_stok_terjual'] }}</div>
                <div class="text-sm opacity-90">Total Terjual</div>
            </div>
            
            <div class=" bg-opacity-20 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold">{{ $totalSummary['total_stok_akhir'] }}</div>
                <div class="text-sm opacity-90">Total Stok Akhir</div>
            </div>
            
            <div class=" bg-opacity-20 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold {{ $totalSummary['total_selisih'] < 0 ? 'text-error' : ($totalSummary['total_selisih'] > 0 ? 'text-warning' : '') }}">
                    {{ $totalSummary['total_selisih'] }}
                </div>
                <div class="text-sm opacity-90">Total Selisih</div>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="bg-base-200 rounded-lg p-6 mt-6">
        <h4 class="font-semibold text-white mb-3">💡 Panduan Penggunaan</h4>
        <div class="grid md:grid-cols-2 gap-4 text-sm text-white">
            <div>
                <ul class="space-y-2">
                    <li>• <strong>Stok Awal:</strong> Jumlah sate di awal shift/hari</li>
                    <li>• <strong>Stok Terjual:</strong> Otomatis dari transaksi kasir</li>
                    <li>• <strong>Stok Akhir:</strong> Sisa stok setelah dihitung fisik</li>
                </ul>
            </div>
            <div>
                <ul class="space-y-2">
                    <li>• <strong>Selisih:</strong> Dihitung otomatis (Awal - Terjual - Akhir)</li>
                    <li>• <strong>Minus:</strong> Kemungkinan ada yang hilang/rusak</li>
                    <li>• <strong>Plus:</strong> Kemungkinan ada stok tidak tercatat</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Auto-refresh indicator -->
    <div wire:loading class="fixed bottom-4 right-4 bg-base-300 text-white px-4 py-2 rounded-lg shadow-lg">
        <span class="loading loading-spinner loading-sm"></span>
        Memuat data...
    </div>
</div>

<!-- Real-time calculation script -->
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('date-changed', (event) => {
            console.log('Date changed to:', event.date);
            // Additional handling if needed
        });
    });
</script>
