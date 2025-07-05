<x-layouts.app>
    <x-slot name="title">Laporan Pengeluaran - KasirBraga</x-slot>

    <div class="container mx-auto py-2">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="breadcrumbs text-sm">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.reports') }}">Laporan</a></li>
                    <li>Pengeluaran</li>
                </ul>
            </div>
        </div>

        <!-- Expenses Report Content -->
        <div class="bg-base-100">
            <div class="max-w-7xl mx-auto">
                <div class="card bg-base-200 shadow-xl border border-base-300">
                    <div class="card-body">
                        <div class="p-6 text-base-content">
                            <div class="mb-6">
                                <h1 class="text-2xl font-bold text-base-content">Laporan Pengeluaran</h1>
                                <p class="text-base-content/70 mt-2">Analisis pengeluaran dan biaya operasional</p>
                            </div>
                            
                            {{-- Embedded Livewire Component --}}
                            <livewire:expense-report-component />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app> 