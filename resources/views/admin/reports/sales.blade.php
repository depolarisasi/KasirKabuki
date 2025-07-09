@extends('layouts.app')

@section('title', 'Laporan Penjualan - KasirBraga')

@section('content')
<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Laporan Penjualan</h1>
            <p class="text-white">Analisis penjualan dengan grafik dan ekspor data</p>
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

    <!-- Sales Report Card -->
    <div class="card bg-base-300 shadow-lg">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Dashboard Laporan Penjualan
            </h2>
            
            {{-- Embedded Livewire Component --}}
            <livewire:sales-report-component />
        </div>
    </div>
</div>
@endsection 