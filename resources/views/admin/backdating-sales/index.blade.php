<x-layouts.app>
    <x-slot name="title">Backdating Penjualan - KasirBraga</x-slot>
    
    <div class="page-header">
        <div class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span>></span>
            <span>Backdating Penjualan</span>
        </div>
    </div>

    <div class="container mx-auto px-8 py-4 bg-base-200">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">Backdating Penjualan</h1>
                <p class="text-white">Input penjualan dengan tanggal custom untuk keperluan administrasi</p>
            </div>
            <div class="flex gap-2 mt-4 sm:mt-0">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Dashboard
                </a>
            </div>
        </div>

        <!-- Backdating Sales Card -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6M8 7l4 4m0 0l4-4m-4 4v6m0 0l-4-4m4 4l4-4"></path>
                    </svg>
                    Input Penjualan dengan Tanggal Custom
                </h2>
                
                {{-- Embedded Livewire Component --}}
                <livewire:backdating-sales-component />
            </div>
        </div>
    </div>
</x-layouts.app> 