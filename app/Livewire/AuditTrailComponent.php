<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransactionAudit;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class AuditTrailComponent extends Component
{
    use WithPagination;
    
    public $searchQuery = '';
    public $selectedTransaction = '';
    public $selectedAdmin = '';
    public $startDate = '';
    public $endDate = '';
    public $selectedField = '';
    
    public $showDetailModal = false;
    public $selectedAudit = null;

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'selectedTransaction' => ['except' => ''],
        'selectedAdmin' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
        'selectedField' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        // Set default date to last 30 days
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $auditTrails = $this->getFilteredAuditTrails();
        $admins = User::role('admin')->orderBy('name')->get();
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->take(50)
            ->get();
        $fields = TransactionAudit::select('field_changed')
            ->distinct()
            ->orderBy('field_changed')
            ->pluck('field_changed');

        return view('livewire.audit-trail-component', [
            'auditTrails' => $auditTrails,
            'admins' => $admins,
            'recentTransactions' => $recentTransactions,
            'availableFields' => $fields,
        ])->layout('layouts.app', ['title' => 'Audit Trail - Riwayat Perubahan Transaksi']);
    }

    public function getFilteredAuditTrails()
    {
        $query = TransactionAudit::with(['transaction.user', 'admin'])
            ->orderBy('changed_at', 'desc');

        // Search in transaction code or reason
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->whereHas('transaction', function ($tq) {
                    $tq->where('transaction_code', 'like', '%' . $this->searchQuery . '%');
                })->orWhere('reason', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('field_changed', 'like', '%' . $this->searchQuery . '%');
            });
        }

        // Filter by transaction
        if ($this->selectedTransaction) {
            $query->where('transaction_id', $this->selectedTransaction);
        }

        // Filter by admin
        if ($this->selectedAdmin) {
            $query->where('admin_id', $this->selectedAdmin);
        }

        // Filter by date range
        if ($this->startDate && $this->endDate) {
            $startDate = Carbon::parse($this->startDate)->startOfDay();
            $endDate = Carbon::parse($this->endDate)->endOfDay();
            $query->whereBetween('changed_at', [$startDate, $endDate]);
        }

        // Filter by field
        if ($this->selectedField) {
            $query->where('field_changed', $this->selectedField);
        }

        return $query->paginate(20);
    }

    public function viewAuditDetail($auditId)
    {
        $this->selectedAudit = TransactionAudit::with(['transaction.user', 'admin'])
            ->find($auditId);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedAudit = null;
    }

    public function resetFilters()
    {
        $this->searchQuery = '';
        $this->selectedTransaction = '';
        $this->selectedAdmin = '';
        $this->selectedField = '';
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function exportAuditTrail()
    {
        // Implement CSV export logic if needed
        session()->flash('info', 'Fitur export akan segera tersedia.');
    }

    public function getStatusBadgeClass($field)
    {
        $classes = [
            'Tanggal Transaksi' => 'badge-warning',
            'Catatan' => 'badge-info',
            'Jenis Pesanan' => 'badge-accent',
            'Partner' => 'badge-secondary',
            'Metode Pembayaran' => 'badge-primary',
            'default' => 'badge-neutral'
        ];

        return $classes[$field] ?? $classes['default'];
    }
}
