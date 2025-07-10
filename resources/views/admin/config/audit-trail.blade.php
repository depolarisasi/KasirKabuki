<x-layouts.app>
    <x-slot name="title">Audit Trail Configuration - KasirBraga</x-slot>

    <div class="container mx-auto py-2">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="breadcrumbs text-sm">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.config') }}">Konfigurasi</a></li>
                    <li>Audit Trail</li>
                </ul>
            </div>
        </div>

        <!-- Audit Trail Management Component -->
        <div class="bg-base-100">
            @livewire('audit-trail-config')
        </div>
    </div>
</x-layouts.app> 