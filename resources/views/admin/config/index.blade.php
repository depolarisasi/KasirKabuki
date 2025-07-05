<x-layouts.app>
    <x-slot name="title">Konfigurasi - KasirBraga</x-slot>

    <div class="container mx-auto py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Konfigurasi</h1>
            <p class="text-base-content/70">Kelola semua pengaturan sistem KasirBraga</p>
        </div>

        <!-- Config Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Store Config Card -->
            <a href="/admin/store-config" class="card bg-gradient-to-br from-blue-500/10 to-blue-600/10 border border-blue-200 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body items-center text-center">
                    <div class="w-16 h-16 bg-blue-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="card-title text-lg">Konfigurasi Toko</h3>
                    <p class="text-sm text-base-content/70">Informasi toko, logo, dan pengaturan struk</p>
                </div>
            </a>

            <!-- Categories Card -->
            <a href="{{ route('admin.categories') }}" class="card bg-gradient-to-br from-green-500/10 to-green-600/10 border border-green-200 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body items-center text-center">
                    <div class="w-16 h-16 bg-green-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h3 class="card-title text-lg">Kategori Produk</h3>
                    <p class="text-sm text-base-content/70">Manajemen kategori untuk mengorganisir produk</p>
                </div>
            </a>

            <!-- Products Card -->
            <a href="{{ route('admin.products') }}" class="card bg-gradient-to-br from-purple-500/10 to-purple-600/10 border border-purple-200 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body items-center text-center">
                    <div class="w-16 h-16 bg-purple-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h3 class="card-title text-lg">Produk</h3>
                    <p class="text-sm text-base-content/70">Kelola daftar produk dan harga jual</p>
                </div>
            </a>

            <!-- Partners Card -->
            <a href="{{ route('admin.partners') }}" class="card bg-gradient-to-br from-orange-500/10 to-orange-600/10 border border-orange-200 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body items-center text-center">
                    <div class="w-16 h-16 bg-orange-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="card-title text-lg">Partner Online</h3>
                    <p class="text-sm text-base-content/70">Manajemen partner delivery dan komisi</p>
                </div>
            </a>

            <!-- Discounts Card -->
            <a href="{{ route('admin.discounts') }}" class="card bg-gradient-to-br from-red-500/10 to-red-600/10 border border-red-200 hover:shadow-lg transition-all duration-300 group">
                <div class="card-body items-center text-center">
                    <div class="w-16 h-16 bg-red-500 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <h3 class="card-title text-lg">Diskon</h3>
                    <p class="text-sm text-base-content/70">Aturan diskon produk dan transaksi</p>
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