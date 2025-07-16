{{-- Traditional Blade Navigation Partial --}}
{{-- Mobile: Fixed bottom dock with DaisyUI dock classes --}}
{{-- Desktop: Sticky top header with navbar and menu-horizontal --}}

<!-- Mobile Navigation: Bottom Dock (Hidden on large screens) -->
<nav class="lg:hidden">
    <!-- Sticky Top Header for Mobile -->
    <header class="sticky top-0 z-40 w-full">
        <div class="navbar bg-base-100 shadow-sm px-4 border-b border-base-200">
            <div class="navbar-start">
                <img src="{{ asset('assets/logo-150x75.png') }}" alt="KasirBraga" class="h-8 w-auto">
            </div>
            <div class="navbar-end">
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle avatar">
                        <div class="w-10 rounded-full bg-primary text-primary-content flex items-center justify-center">
                            <span class="text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                    </label>
                    <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                        <li class="menu-title">
                            <span>{{ auth()->user()->name }}</span>
                            <span class="text-xs opacity-60">{{ ucfirst(auth()->user()->role) }}</span>
                        </li>
                        <li><a href="{{ route('profile') }}">Profile</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Bottom Dock Navigation (Fixed at bottom) -->
    <div class="fixed bottom-0 left-0 right-0 z-50">
        <div class="dock dock-md bg-base-200 border-t border-base-300">
            @auth
                @if(auth()->user()->hasRole('admin'))
                    <!-- Admin Navigation Items -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="@if(request()->routeIs('admin.dashboard')) dock-active @endif">
                        <svg class="size-[1.2em]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                        </svg>
                        <span class="dock-label">Dashboard</span>
                    </a>

                    <!-- Configuration Direct Link (No Dropdown) -->
                    <a href="{{ route('admin.config') }}" 
                       class="@if(request()->routeIs('admin.config*') || request()->routeIs('admin.categories*') || request()->routeIs('admin.products*') || request()->routeIs('admin.partners*') || request()->routeIs('admin.discounts*') || request()->routeIs('admin.store-config*') || request()->routeIs('admin.users*') || request()->routeIs('admin.backdating-sales*')) dock-active @endif">
                        <svg class="size-[1.2em]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="dock-label">Config</span>
                    </a>

                    <!-- Reports Direct Link (No Dropdown) -->
                    <a href="{{ route('admin.reports') }}" 
                       class="@if(request()->routeIs('admin.reports*')) dock-active @endif">
                        <svg class="size-[1.2em]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span class="dock-label">Laporan</span>
                    </a>

                    
                @endif

                @if(auth()->user()->hasRole('staf') || auth()->user()->hasRole('admin'))
                    <!-- Staff Navigation Items -->
                    <a href="{{ route('staf.cashier') }}" 
                       class="@if(request()->routeIs('staf.cashier*')) dock-active @endif">
                        <svg class="size-[1.2em]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5 0a2 2 0 100-4 2 2 0 000 4zm6 0a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                        <span class="dock-label">Kasir</span>
                    </a>

                    <a href="{{ route('staf.expenses') }}" 
                       class="@if(request()->routeIs('staf.expenses*')) dock-active @endif">
                        <svg class="size-[1.2em]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        <span class="dock-label">Pengeluaran</span>
                    </a>
                @endif

                <!-- Transaction Page - Available for all roles with permission -->
                @if(auth()->user()->hasRole('staf') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('investor'))
                    <a href="{{ route('staf.transactions') }}" 
                       class="@if(request()->routeIs('staf.transactions*')) dock-active @endif">
                        <svg class="size-[1.2em]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="dock-label">Transaksi</span>
                    </a>
                @endif
            @endauth
        </div>
    </div>
</nav>

<!-- Desktop Navigation: Sticky Header (Hidden on small screens) -->
<nav class="hidden lg:block">
    <header class="sticky top-0 z-40 w-full">
        <div class="navbar bg-base-100 shadow-sm border-b border-base-200">
            <div class="navbar-start">
                <img src="{{ asset('assets/logo-150x75.png') }}" alt="KasirBraga" class="h-10 w-auto">
            </div>
            
            <div class="navbar-center">
                <ul class="menu menu-horizontal px-1">
                    @auth
                        @if(auth()->user()->hasRole('admin'))
                            <!-- Admin Navigation -->
                            <li>
                                <a href="{{ route('admin.dashboard') }}" 
                                   class="@if(request()->routeIs('admin.dashboard')) active @endif">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                                    </svg>
                                    Dashboard
                                </a>
                            </li>
                            
                            <!-- Configuration Dropdown -->
                            <li>
                                <details>
                                    <summary class="@if(request()->routeIs('admin.categories*') || request()->routeIs('admin.products*') || request()->routeIs('admin.partners*') || request()->routeIs('admin.discounts*') || request()->routeIs('admin.config*') || request()->routeIs('admin.users*') || request()->routeIs('admin.backdating-sales*')) active @endif">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Konfigurasi
                                    </summary>
                                    <ul class="p-2 bg-base-100 rounded-box w-52">
                                        <li><a href="{{ route('admin.config') }}">Toko</a></li>
                                        <li><a href="{{ route('admin.categories') }}">Kategori</a></li>
                                        <li><a href="{{ route('admin.products') }}">Produk</a></li>
                                        <li><a href="{{ route('admin.partners') }}">Partner</a></li>
                                        <li><a href="{{ route('admin.discounts') }}">Diskon</a></li>
                                        <li><a href="{{ route('admin.users') }}">User Management</a></li>
                                        <li><a href="{{ route('admin.config.audit-trail') }}">Audit Trail</a></li>
                                        <li><a href="{{ route('admin.backdating-sales') }}">Backdating Sales</a></li>
                                    </ul>
                                </details>
                            </li>
                            
                            <!-- Reports Dropdown -->
                            <li>
                                <details>
                                    <summary class="@if(request()->routeIs('admin.reports*')) active @endif">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        Laporan
                                    </summary>
                                    <ul class="p-2 bg-base-100 rounded-box w-52">
                                        <li><a href="{{ route('admin.reports.sales') }}">Penjualan</a></li>
                                        <li><a href="{{ route('admin.reports.expenses') }}">Pengeluaran</a></li>
                                    </ul>
                                </details>
                            </li>
                        @endif

                        @if(auth()->user()->hasRole('staf') || auth()->user()->hasRole('admin'))
                            <!-- Staff Navigation -->
                            <li>
                                <a href="{{ route('staf.cashier') }}" 
                                   class="@if(request()->routeIs('staf.cashier*')) active @endif">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m4.5 0a2 2 0 100-4 2 2 0 000 4zm6 0a2 2 0 100-4 2 2 0 000 4z"></path>
                                    </svg>
                                    Kasir
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('staf.expenses') }}" 
                                   class="@if(request()->routeIs('staf.expenses*')) active @endif">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    Pengeluaran
                                </a>
                            </li>
                        @endif

                        <!-- Transaction Page - Available for all roles with permission -->
                        @if(auth()->user()->hasRole('staf') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('investor'))
                            <li>
                                <a href="{{ route('staf.transactions') }}" 
                                   class="@if(request()->routeIs('staf.transactions*')) active @endif">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Transaksi
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </div>

            <div class="navbar-end">
                @auth
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost">
                            <div class="avatar">
                                <div class="w-8 rounded-full bg-primary text-primary-content flex items-center justify-center">
                                    <span class="text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                            </div>
                            <span class="ml-2">{{ auth()->user()->name }}</span>
                            <svg class="fill-current w-4 h-4 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </label>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                            <li class="menu-title">
                                <span>{{ auth()->user()->name }}</span>
                                <span class="text-xs opacity-60">{{ ucfirst(auth()->user()->role) }}</span>
                            </li>
                            <li><a href="{{ route('profile') }}">Profile</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                @endauth
            </div>
        </div>
    </header>
</nav> 