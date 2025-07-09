@extends('layouts.app')

@section('title', 'Manajemen User - KasirBraga')

@section('content')
<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Manajemen User</h1>
            <p class="text-white">Kelola pengguna dan hak akses sistem</p>
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

    <!-- User Management Card -->
    <div class="card bg-base-300 shadow-lg">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                Dashboard Manajemen User
            </h2>
            
            {{-- Embedded Livewire Component --}}
            <livewire:user-management />
        </div>
    </div>
</div>
@endsection 