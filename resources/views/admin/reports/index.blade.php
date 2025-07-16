<x-layouts.app>
    <x-slot name="title">Laporan - KasirBraga</x-slot>

    <div class="container mx-auto py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Laporan</h1>
            <p class="text-base-content/70">Lihat semua laporan dan analisis data KasirBraga</p>
        </div>

        <!-- Reports Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Sales Report Card -->
            <a href="{{ route('admin.reports.sales') }}" class="card bg-gradient-to-br from-emerald-500/10 to-emerald-600/10 border border-emerald-200 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body items-center text-center">
                    <div class="w-16 h-16 bg-emerald-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5 0a2 2 0 100-4 2 2 0 000 4zm6 0a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                    </div>
                    <h3 class="card-title text-lg">Laporan Penjualan</h3>
                    <p class="text-sm text-base-content/70">Analisis penjualan, omzet, dan performa produk</p>
                </div>
            </a>

            <!-- Expenses Report Card -->
            <a href="{{ route('admin.reports.expenses') }}" class="card bg-gradient-to-br from-amber-500/10 to-amber-600/10 border border-amber-200 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body items-center text-center">
                    <div class="w-16 h-16 bg-amber-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <h3 class="card-title text-lg">Laporan Pengeluaran</h3>
                    <p class="text-sm text-base-content/70">Analisis pengeluaran dan kontrol biaya</p>
                </div>
            </a>

            <!-- Financial Summary Card -->
            <a href="#" class="card bg-gradient-to-br from-violet-500/10 to-violet-600/10 border border-violet-200 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body items-center text-center">
                    <div class="w-16 h-16 bg-violet-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="card-title text-lg">Ringkasan Keuangan</h3>
                    <p class="text-sm text-base-content/70">Dashboard keuangan dan analisis profit</p>
                </div>
            </a>

            <!-- Business Analytics Card -->
            <a href="#" class="card bg-gradient-to-br from-indigo-500/10 to-indigo-600/10 border border-indigo-200 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body items-center text-center">
                    <div class="w-16 h-16 bg-indigo-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="card-title text-lg">Analisis Bisnis</h3>
                    <p class="text-sm text-base-content/70">Insight bisnis dan trend penjualan</p>
                </div>
            </a>

            <!-- Back to Dashboard Card -->
            <a href="{{ route('admin.dashboard') }}" class="card bg-gradient-to-br from-gray-500/10 to-gray-600/10 border border-gray-200 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body items-center text-center">
                    <div class="w-16 h-16 bg-gray-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </div>
                    <h3 class="card-title text-lg">Kembali</h3>
                    <p class="text-sm text-base-content/70">Kembali ke dashboard utama</p>
                </div>
            </a>
        </div>
    </div>
</x-layouts.app> 