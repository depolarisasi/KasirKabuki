@extends('layouts.app')

@section('title', 'Laporan Pengeluaran - KasirBraga')

@section('content')
<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Laporan Pengeluaran</h1>
            <p class="text-white">Analisis pengeluaran dan biaya operasional</p>
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

    <!-- Expense Report Card -->
    <div class="card bg-base-300 shadow-lg">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Dashboard Laporan Pengeluaran
            </h2>
            
            {{-- Embedded Livewire Component --}}
            <livewire:expense-report-component />
        </div>
    </div>
</div>
@endsection 