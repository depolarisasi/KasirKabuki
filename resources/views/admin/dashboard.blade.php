@extends('layouts.app')

@section('title', 'Admin Dashboard - KasirBraga')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <h1 class="text-3xl font-bold text-base-content mb-6">Dashboard Admin</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Quick Stats -->
                    <div class="stat bg-info/10 border border-info/20 rounded-xl">
                        <div class="stat-title text-info">Total Pengguna</div>
                        <div class="stat-value text-info">{{ \App\Models\User::count() }}</div>
                        <div class="stat-desc text-info/70">Pengguna sistem</div>
                    </div>
                    
                    <div class="stat bg-success/10 border border-success/20 rounded-xl">
                        <div class="stat-title text-success">Total Kategori</div>
                        <div class="stat-value text-success">{{ \App\Models\Category::count() }}</div>
                        <div class="stat-desc text-success/70">Kategori produk</div>
                    </div>
                    
                    <div class="stat bg-secondary/10 border border-secondary/20 rounded-xl">
                        <div class="stat-title text-secondary">Total Produk</div>
                        <div class="stat-value text-secondary">{{ \App\Models\Product::count() }}</div>
                        <div class="stat-desc text-secondary/70">Produk aktif</div>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="mt-8 bg-base-200 p-6 rounded-xl border border-base-300">
                    <h2 class="text-xl font-semibold text-base-content mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Informasi Pengguna
                    </h2>
                    <div class="space-y-2 text-base-content">
                        <p><strong class="text-primary">Nama:</strong> {{ auth()->user()->name ?? 'N/A' }}</p>
                        <p><strong class="text-primary">Email:</strong> {{ auth()->user()->email ?? 'N/A' }}</p>
                        @if(isset(auth()->user()->role))
                            <p><strong class="text-primary">Role Column:</strong> 
                               <span class="badge badge-secondary">{{ auth()->user()->role }}</span>
                            </p>
                        @endif
                        <p><strong class="text-primary">Spatie Roles:</strong> 
                           @if(auth()->user()->roles && auth()->user()->roles->count() > 0)
                               @foreach(auth()->user()->roles as $role)
                                   <span class="badge badge-accent mr-1">{{ $role->name }}</span>
                               @endforeach
                           @else
                               <span class="text-warning">No roles assigned</span>
                           @endif
                        </p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="card-actions justify-start mt-6">
                    <a href="{{ route('admin.config') }}" class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Konfigurasi
                    </a>
                    <button onclick="testAlert()" class="btn btn-success">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Test Alert
                    </button>
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