<x-layouts.app>
    <x-slot name="title">Manajemen Produk - KasirBraga</x-slot>

    <div class="container mx-auto py-2">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="breadcrumbs text-sm">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.config') }}">Konfigurasi</a></li>
                    <li>Produk</li>
                </ul>
            </div>
        </div>

        <!-- Products Management Component -->
        <div class="bg-base-100">
            @livewire('product-management')
        </div>
    </div>
</x-layouts.app> 