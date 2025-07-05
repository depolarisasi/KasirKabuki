<x-layouts.app>
    <x-slot name="title">Konfigurasi Toko - KasirBraga</x-slot>

    <div class="container mx-auto py-2">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="breadcrumbs text-sm">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.config') }}">Konfigurasi</a></li>
                    <li>Konfigurasi Toko</li>
                </ul>
            </div>
        </div>

        <!-- Store Config Management Component -->
        <div class="bg-base-100">
            @livewire('store-config-management')
        </div>
    </div>
</x-layouts.app> 