<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <h1 class="text-xl font-bold text-gray-800">KasirBraga</h1>
                </div>
                
                <!-- Navigation Menu untuk Admin -->
                @auth
                    @if(auth()->user()->hasRole('admin'))
                        <div class="hidden md:ml-6 md:flex md:items-center md:space-x-4">
                            <a href="{{ route('admin.dashboard') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                                Dashboard
                            </a>
                            
                            <!-- Dropdown Menu Konfigurasi -->
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-sm">
                                    Konfigurasi
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </label>
                                <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                    <li>
                                        <a href="{{ route('admin.categories') }}" 
                                           class="{{ request()->routeIs('admin.categories') ? 'active' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                            Kategori Produk
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.products') }}" 
                                           class="{{ request()->routeIs('admin.products') ? 'active' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                            Produk
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.partners') }}" 
                                           class="{{ request()->routeIs('admin.partners') ? 'active' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            Partner Online
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.discounts') }}" 
                                           class="{{ request()->routeIs('admin.discounts') ? 'active' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            Diskon
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.users') }}"
                                           class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                            </svg>
                                            Users
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Dropdown Menu Laporan -->
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-sm {{ request()->routeIs('admin.reports.*') ? 'btn-primary' : '' }}">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Laporan
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </label>
                                <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                    <li>
                                        <a href="{{ route('admin.reports.sales') }}" 
                                           class="{{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                            </svg>
                                            Laporan Penjualan
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.reports.expenses') }}" 
                                           class="{{ request()->routeIs('admin.reports.expenses') ? 'active' : '' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                            Laporan Pengeluaran
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Navigation Menu untuk Staf -->
                    @if(auth()->user()->hasRole('staf'))
                        <div class="hidden md:ml-6 md:flex md:items-center md:space-x-4">
                            <a href="{{ route('staf.cashier') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('staf.cashier') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5 0a2 2 0 100-4 2 2 0 000 4zm6 0a2 2 0 100-4 2 2 0 000 4z"></path>
                                </svg>
                                Kasir
                            </a>
                            <a href="{{ route('staf.expenses') }}" 
                               class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('staf.expenses') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                                Pengeluaran
                            </a>
                        </div>
                    @endif
                @endauth
            </div>
            
            <div class="flex items-center space-x-4">
                @auth
                    <div class="flex items-center space-x-3">
                        <div class="text-sm">
                            <div class="font-medium text-gray-700">{{ auth()->user()->name }}</div>
                            <div class="text-gray-500 text-xs">{{ ucfirst(auth()->user()->role) }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline btn-error">Logout</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                @endauth
            </div>
        </div>
        
        <!-- Mobile menu (hidden by default) -->
        @auth
            @if(auth()->user()->hasRole('admin'))
                <div class="md:hidden">
                    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.categories') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.categories') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                            Kategori Produk
                        </a>
                        <a href="{{ route('admin.products') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.products') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                            Produk
                        </a>
                        <a href="{{ route('admin.partners') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.partners') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                            Partner Online
                        </a>
                        <a href="{{ route('admin.discounts') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.discounts') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                            Diskon
                        </a>
                        <a href="{{ route('admin.reports.sales') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.reports.sales') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                            Laporan Penjualan
                        </a>
                        <a href="{{ route('admin.reports.expenses') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.reports.expenses') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                            Laporan Pengeluaran
                        </a>
                    </div>
                </div>
            @endif
            
            @if(auth()->user()->hasRole('staf'))
                <div class="md:hidden">
                    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 border-t">
                        <a href="{{ route('staf.cashier') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('staf.cashier') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                            Kasir
                        </a>
                        <a href="{{ route('staf.expenses') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('staf.expenses') ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:text-blue-600' }}">
                            Pengeluaran
                        </a>
                    </div>
                </div>
            @endif
        @endauth
    </div>
</nav> 