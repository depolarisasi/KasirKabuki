<x-layouts.app>
    <x-slot name="title">Point of Sales - KasirBraga</x-slot>

    <div class="container mx-auto py-2">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="breadcrumbs text-sm">
                <ul>
                    <li><a href="{{ route('staf.dashboard') }}">Dashboard</a></li>
                    <li>Kasir</li>
                </ul>
            </div>
        </div>

        <!-- Cashier Content -->
        <div class="bg-base-100">
            <div class="max-w-7xl mx-auto">
                <div class="card bg-base-200 shadow-xl border border-base-300">
                    <div class="card-body">
                        <div class="p-4 text-base-content">
                            <div class="mb-6">
                                <h1 class="text-xl font-bold text-base-content">Point of Sales</h1>
                                <p class="text-base-content/70 text-sm">Kasir: {{ auth()->user()->name }}</p>
                            </div>
                            
                            {{-- POS System Component --}}
                            @livewire('cashier-component')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app> 