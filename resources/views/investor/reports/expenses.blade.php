<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Laporan Pengeluaran</h1>
            <p class="text-white">Laporan detail pengeluaran untuk investor</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <div class="badge badge-primary">{{ auth()->user()->name }}</div>
            <div class="badge badge-secondary">Role: Investor</div>
            <div class="badge badge-info">Read-Only</div>
        </div>
    </div>

    <!-- Expenses Report Component -->
    <livewire:expense-report-component :investor-mode="true" />
</div> 