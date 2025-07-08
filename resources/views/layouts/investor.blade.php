<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'KasirBraga') }} - Investor Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- DaisyUI & Tailwind -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="font-sans antialiased bg-base-200">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-primary shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and Brand -->
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <a href="{{ route('investor.dashboard') }}" class="flex items-center">
                                <svg class="h-8 w-8 text-primary-content mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="text-xl font-bold text-primary-content">KasirBraga</span>
                                <span class="text-sm text-primary-content/70 ml-2">Investor</span>
                            </a>
                        </div>
                    </div>

                    <!-- Navigation Links - Limited for Investor -->
                    <div class="hidden md:flex md:items-center md:space-x-4">
                        <a href="{{ route('investor.dashboard') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium text-primary-content hover:bg-primary-focus {{ request()->routeIs('investor.dashboard') ? 'bg-primary-focus' : '' }}">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h6"></path>
                            </svg>
                            Dashboard
                        </a>
                        
                        <a href="{{ route('investor.reports.sales') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium text-primary-content hover:bg-primary-focus {{ request()->routeIs('investor.reports.sales') ? 'bg-primary-focus' : '' }}">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Laporan Penjualan
                        </a>
                        
                        <a href="{{ route('investor.reports.expenses') }}" 
                           class="px-3 py-2 rounded-md text-sm font-medium text-primary-content hover:bg-primary-focus {{ request()->routeIs('investor.reports.expenses') ? 'bg-primary-focus' : '' }}">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Laporan Pengeluaran
                        </a>
                    </div>

                    <!-- User dropdown -->
                    <div class="flex items-center">
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost text-primary-content">
                                <div class="avatar">
                                    <div class="w-8 rounded-full bg-primary-content text-primary flex items-center justify-center">
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
                                    <span class="text-xs opacity-60">Investor</span>
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
                
                <!-- Mobile navigation menu -->
                <div class="md:hidden">
                    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                        <a href="{{ route('investor.dashboard') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium text-primary-content hover:bg-primary-focus {{ request()->routeIs('investor.dashboard') ? 'bg-primary-focus' : '' }}">
                            Dashboard
                        </a>
                        
                        <a href="{{ route('investor.reports.sales') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium text-primary-content hover:bg-primary-focus {{ request()->routeIs('investor.reports.sales') ? 'bg-primary-focus' : '' }}">
                            Laporan Penjualan
                        </a>
                        
                        <a href="{{ route('investor.reports.expenses') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium text-primary-content hover:bg-primary-focus {{ request()->routeIs('investor.reports.expenses') ? 'bg-primary-focus' : '' }}">
                            Laporan Pengeluaran
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-6">
            {{ $slot }}
        </main>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Custom Scripts -->
    @stack('scripts')
</body>
</html> 