@extends('layouts.app')

@section('title', 'Admin Dashboard - KasirBraga')

@section('content')
<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Dashboard Admin</h1>
            <p class="text-white">Monitoring dan akses cepat ke semua fitur sistem</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <a href="{{ route('admin.config') }}" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Konfigurasi
            </a>
            <button onclick="testAlert()" class="btn btn-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Test Alert
            </button>
        </div>
    </div>

    <!-- Stats Overview Card -->
    <div class="card bg-base-300 shadow-lg mb-6">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Statistik Sistem
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Total Users -->
                <div class="stat bg-info/10 border border-info/20 rounded-xl">
                    <div class="stat-title text-info">Total Pengguna</div>
                    <div class="stat-value text-info">{{ \App\Models\User::count() }}</div>
                    <div class="stat-desc text-info/70">Pengguna sistem</div>
                </div>
                
                <!-- Total Categories -->
                <div class="stat bg-success/10 border border-success/20 rounded-xl">
                    <div class="stat-title text-success">Total Kategori</div>
                    <div class="stat-value text-success">{{ \App\Models\Category::count() }}</div>
                    <div class="stat-desc text-success/70">Kategori produk</div>
                </div>
                
                <!-- Total Products -->
                <div class="stat bg-secondary/10 border border-secondary/20 rounded-xl">
                    <div class="stat-title text-secondary">Total Produk</div>
                    <div class="stat-value text-secondary">{{ \App\Models\Product::count() }}</div>
                    <div class="stat-desc text-secondary/70">Produk aktif</div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Information Card -->
    <div class="card bg-base-300 shadow-lg">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Informasi Pengguna
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">Nama</span>
                    </label>
                    <input type="text" value="{{ auth()->user()->name ?? 'N/A' }}" class="input input-bordered" readonly>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">Email</span>
                    </label>
                    <input type="email" value="{{ auth()->user()->email ?? 'N/A' }}" class="input input-bordered" readonly>
                </div>
                
                @if(isset(auth()->user()->role))
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Role Column</span>
                        </label>
                        <span class="badge badge-secondary">{{ auth()->user()->role }}</span>
                    </div>
                @endif
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-semibold">Spatie Roles</span>
                    </label>
                    <div class="flex gap-1 flex-wrap">
                        @if(auth()->user()->roles && auth()->user()->roles->count() > 0)
                            @foreach(auth()->user()->roles as $role)
                                <span class="badge badge-accent">{{ $role->name }}</span>
                            @endforeach
                        @else
                            <span class="text-warning">No roles assigned</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function testAlert() {
    alert('JavaScript Alert works with SmartMonkey Theme! 🎨');
}
</script>
@endpush
@endsection 