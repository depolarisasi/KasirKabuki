<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class TransactionPageComponent extends Component
{
    use WithPagination;

    // Filter properties
    public $startDate;
    public $endDate;
    public $selectedOrderType = '';
    public $selectedStatus = '';
    public $selectedPaymentMethod = '';
    public $searchQuery = '';
    
    // UI properties
    public $isLoading = false;
    public $showFilters = true;
    public $selectedTransaction = null;
    public $showDetailModal = false;
    
    // Available filter options
    public $orderTypes = [
        '' => 'Semua Jenis',
        'dine_in' => 'Makan di Tempat',
        'take_away' => 'Bawa Pulang',
        'online' => 'Online'
    ];
    
    public $statuses = [
        '' => 'Semua Status',
        'pending' => 'Pending',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    
    public $paymentMethods = [
        '' => 'Semua Metode',
        'cash' => 'Tunai',
        'qris' => 'QRIS',
        'aplikasi' => 'Aplikasi'
    ];

    protected $listeners = [
        'transaction-completed' => 'refreshTransactions',
        'refresh-transactions' => 'refreshTransactions',
    ];

    public function mount()
    {
        // Default to today's date
        $this->startDate = Carbon::today()->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');
    }

    public function render()
    {
        $this->isLoading = true;
        
        try {
            $transactions = $this->getFilteredTransactions();
            
            return view('livewire.transaction-page-component', [
                'transactions' => $transactions,
                'totalTransactions' => $transactions->total(),
                'totalAmount' => $this->getTotalAmount()
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function getFilteredTransactions()
    {
        $query = Transaction::with(['user', 'partner', 'items.product'])
            ->orderBy('created_at', 'desc');

        // Date filtering
        if ($this->startDate && $this->endDate) {
            $startDate = Carbon::parse($this->startDate)->startOfDay();
            $endDate = Carbon::parse($this->endDate)->endOfDay();
            $query->betweenDates($startDate, $endDate);
        } elseif ($this->startDate) {
            $date = Carbon::parse($this->startDate);
            $query->forDate($date);
        }

        // Order type filtering
        if ($this->selectedOrderType) {
            $query->where('order_type', $this->selectedOrderType);
        }

        // Status filtering
        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        // Payment method filtering
        if ($this->selectedPaymentMethod) {
            $query->where('payment_method', $this->selectedPaymentMethod);
        }

        // Search filtering
        if ($this->searchQuery) {
            $query->where(function ($subQuery) {
                $subQuery->where('transaction_code', 'LIKE', '%' . $this->searchQuery . '%')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('name', 'LIKE', '%' . $this->searchQuery . '%');
                    })
                    ->orWhereHas('partner', function ($partnerQuery) {
                        $partnerQuery->where('name', 'LIKE', '%' . $this->searchQuery . '%');
                    });
            });
        }

        return $query->paginate(20);
    }

    public function getTotalAmount()
    {
        $query = Transaction::query();

        // Apply same filters for total calculation
        if ($this->startDate && $this->endDate) {
            $startDate = Carbon::parse($this->startDate)->startOfDay();
            $endDate = Carbon::parse($this->endDate)->endOfDay();
            $query->betweenDates($startDate, $endDate);
        } elseif ($this->startDate) {
            $date = Carbon::parse($this->startDate);
            $query->forDate($date);
        }

        if ($this->selectedOrderType) {
            $query->where('order_type', $this->selectedOrderType);
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        if ($this->selectedPaymentMethod) {
            $query->where('payment_method', $this->selectedPaymentMethod);
        }

        if ($this->searchQuery) {
            $query->where(function ($subQuery) {
                $subQuery->where('transaction_code', 'LIKE', '%' . $this->searchQuery . '%')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('name', 'LIKE', '%' . $this->searchQuery . '%');
                    })
                    ->orWhereHas('partner', function ($partnerQuery) {
                        $partnerQuery->where('name', 'LIKE', '%' . $this->searchQuery . '%');
                    });
            });
        }

        return $query->sum('final_total');
    }

    public function resetFilters()
    {
        $this->startDate = Carbon::today()->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');
        $this->selectedOrderType = '';
        $this->selectedStatus = '';
        $this->selectedPaymentMethod = '';
        $this->searchQuery = '';
        $this->resetPage();
        
        LivewireAlert::title('Filter Direset!')
            ->text('Semua filter telah direset ke hari ini.')
            ->info()
            ->show();
    }

    public function setDateRange($range)
    {
        switch ($range) {
            case 'today':
                $this->startDate = Carbon::today()->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = Carbon::yesterday()->format('Y-m-d');
                $this->endDate = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'week':
                $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
        }
        $this->resetPage();
    }

    public function viewTransactionDetail($transactionId)
    {
        $this->selectedTransaction = Transaction::with(['user', 'partner', 'items.product.category'])
            ->findOrFail($transactionId);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedTransaction = null;
    }

    public function refreshTransactions()
    {
        $this->resetPage();
        // Force re-render without showing notification
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    // Update methods to trigger refresh
    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function updatedSelectedOrderType()
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectedPaymentMethod()
    {
        $this->resetPage();
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    // Helper methods
    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function getStatusBadgeClass($status)
    {
        return match($status) {
            'pending' => 'badge-warning',
            'completed' => 'badge-success',
            'cancelled' => 'badge-error',
            default => 'badge-ghost'
        };
    }

    public function getOrderTypeLabel($orderType)
    {
        return match($orderType) {
            'dine_in' => 'Makan di Tempat',
            'take_away' => 'Bawa Pulang',
            'online' => 'Online',
            default => $orderType
        };
    }

    public function getPaymentMethodLabel($paymentMethod)
    {
        return match($paymentMethod) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'aplikasi' => 'Aplikasi',
            default => $paymentMethod
        };
    }
} 