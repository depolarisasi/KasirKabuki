<x-layouts.app>
    <x-slot name="title">Manajemen Stok Sate - KasirBraga</x-slot>

    <div class="container mx-auto py-2">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="breadcrumbs text-sm">
                <ul>
                    <li><a href="{{ route('staf.dashboard') }}">Dashboard</a></li>
                    <li>Stok Sate</li>
                </ul>
            </div>
        </div>

        <!-- Expenses Management Content -->
        <div class="bg-base-100">
            <div class="container mx-auto px-8 py-4">
                <div class="card bg-base-200 shadow-xl border border-base-300">
                    <div class="card-body">
                        <div class="p-6 text-base-content">
                            <div class="mb-6">
                                <h1 class="text-2xl font-bold text-base-content">Manajemen Stok Sate</h1>
                                <p class="text-base-content/70 mt-2">Kelola stok sate dan riwayat pergerakan</p>
                            </div>
                            
                            {{-- Expenses Management Component --}}
                          
                            <livewire:stock-sate-management />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app> 
 