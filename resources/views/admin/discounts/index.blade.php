<x-layouts.app>
    <x-slot name="title">Manajemen Diskon - KasirBraga</x-slot>

    <div class="container mx-auto py-2">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="breadcrumbs text-sm">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.config') }}">Konfigurasi</a></li>
                    <li>Diskon</li>
                </ul>
            </div>
        </div>

        <!-- Discounts Management Component -->
        <div class="bg-base-100">
            @livewire('discount-management')
        </div>
    </div>
</x-layouts.app> 