<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expense;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Masmerise\Toaster\Toastable;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Carbon\Carbon;

class ExpenseManagement extends Component
{
    use WithPagination, Toastable;

    // Form properties
    #[Rule('required|numeric|min:1|max:99999999.99')]
    public $amount = '';
    
    #[Rule('required|min:3|max:500')]
    public $description = '';
    
    #[Rule('required|date|before_or_equal:today')]
    public $date = '';

    // Component state
    public $expenseId = null;
    public $isEditMode = false;
    public $showModal = false;

    // Filter properties
    public $search = '';
    public $filterDate = '';
    public $filterMonth = '';
    public $filterYear = '';

    protected $paginationView = 'vendor.pagination.daisyui';

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
        $this->filterMonth = Carbon::now()->format('Y-m');
    }

    public function render()
    {
        $expenses = Expense::query()
            ->with('user')
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->filterDate, function ($query) {
                $query->forDate($this->filterDate);
            })
            ->when($this->filterMonth && !$this->filterDate, function ($query) {
                $year = Carbon::parse($this->filterMonth)->year;
                $month = Carbon::parse($this->filterMonth)->month;
                $query->forMonth($year, $month);
            })
            ->latest('date')
            ->latest('created_at')
            ->paginate(10);

        // Calculate totals for current filter
        $totalQuery = Expense::query()
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->filterDate, function ($query) {
                $query->forDate($this->filterDate);
            })
            ->when($this->filterMonth && !$this->filterDate, function ($query) {
                $year = Carbon::parse($this->filterMonth)->year;
                $month = Carbon::parse($this->filterMonth)->month;
                $query->forMonth($year, $month);
            });

        $totals = [
            'count' => $totalQuery->count(),
            'amount' => $totalQuery->sum('amount')
        ];

        // Quick stats
        $stats = [
            'today' => Expense::getTotalToday(),
            'this_month' => Expense::getTotalThisMonth(),
        ];

        return view('livewire.expense-management', [
            'expenses' => $expenses,
            'totals' => $totals,
            'stats' => $stats
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
        $this->date = Carbon::today()->format('Y-m-d');
    }

    public function openEditModal($expenseId)
    {
        $expense = Expense::findOrFail($expenseId);
        
        // Authorization check - only allow editing own expenses or if admin
        if (auth()->user()->hasRole('admin') || $expense->user_id === auth()->id()) {
            $this->expenseId = $expense->id;
            $this->amount = $expense->amount;
            $this->description = $expense->description;
            $this->date = $expense->date->format('Y-m-d');
            $this->isEditMode = true;
            $this->showModal = true;
        } else {
            $this->error('Anda tidak memiliki izin untuk mengedit pengeluaran ini.');
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'user_id' => auth()->id(),
                'amount' => $this->amount,
                'description' => $this->description,
                'date' => $this->date,
            ];

            if ($this->isEditMode) {
                $expense = Expense::findOrFail($this->expenseId);
                
                // Check authorization for editing
                if (!auth()->user()->hasRole('admin') && $expense->user_id !== auth()->id()) {
                    $this->error('Anda tidak memiliki izin untuk mengedit pengeluaran ini.');
                    return;
                }
                
                $expense->update($data);
                $this->success('Pengeluaran berhasil diperbarui.');
            } else {
                Expense::create($data);
                $this->success('Pengeluaran berhasil ditambahkan.');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat menyimpan pengeluaran: ' . $e->getMessage());
        }
    }

    public function confirmDelete($expenseId)
    {
        \Log::info('ExpenseManagement: confirmDelete called with LivewireAlert', [
            'expense_id' => $expenseId,
            'user_id' => auth()->id()
        ]);
        
        try {
            $expense = Expense::findOrFail($expenseId);
            
            // Authorization check
            if (auth()->user()->hasRole('admin') || $expense->user_id === auth()->id()) {
                \Log::info('ExpenseManagement: Showing delete confirmation with LivewireAlert', [
                    'expense_id' => $expenseId,
                    'expense_description' => $expense->description
                ]);

                // Use LivewireAlert for confirmation
                LivewireAlert::title('Konfirmasi Hapus')
                    ->text("Apakah Anda yakin ingin menghapus pengeluaran \"{$expense->description}\" ({$expense->formatted_amount})?")
                    ->asConfirm()
                    ->onConfirm('deleteExpense', ['expenseId' => $expenseId])
                    ->show();
            } else {
                \Log::warning('ExpenseManagement: Unauthorized delete attempt', [
                    'expense_id' => $expenseId,
                    'user_id' => auth()->id(),
                    'expense_owner' => $expense->user_id
                ]);
                
                LivewireAlert::title('Error!')
                    ->text('Anda tidak memiliki izin untuk menghapus pengeluaran ini.')
                    ->error()
                    ->show();
            }
            
        } catch (\Exception $e) {
            \Log::error('ExpenseManagement: Error in confirmDelete', [
                'expense_id' => $expenseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            LivewireAlert::title('Error!')
                ->text('Terjadi kesalahan saat memproses pengeluaran.')
                ->error()
                ->show();
        }
    }

    public function deleteExpense($data)
    {
        try {
            $expenseId = $data['expenseId'];
            $expense = Expense::findOrFail($expenseId);
            
            // Authorization check
            if (auth()->user()->hasRole('admin') || $expense->user_id === auth()->id()) {
                $expenseDescription = $expense->description;
                
                \Log::info('ExpenseManagement: Executing delete', [
                    'expense_id' => $expenseId,
                    'expense_description' => $expenseDescription
                ]);
                
                $expense->delete();
                
                LivewireAlert::title('Berhasil!')
                    ->text("Pengeluaran \"{$expenseDescription}\" berhasil dihapus.")
                    ->success()
                    ->show();
                    
                $this->resetPage();
            } else {
                LivewireAlert::title('Error!')
                    ->text('Anda tidak memiliki izin untuk menghapus pengeluaran ini.')
                    ->error()
                    ->show();
            }
        } catch (\Exception $e) {
            \Log::error('ExpenseManagement: Error in deleteExpense', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            LivewireAlert::title('Error!')
                ->text('Terjadi kesalahan saat menghapus pengeluaran.')
                ->error()
                ->show();
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['amount', 'description', 'date', 'expenseId', 'isEditMode']);
        $this->resetValidation();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterDate', 'filterMonth']);
        $this->filterMonth = Carbon::now()->format('Y-m');
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterDate()
    {
        $this->resetPage();
    }

    public function updatedFilterMonth()
    {
        $this->resetPage();
    }

    public function setQuickFilter($filter)
    {
        switch ($filter) {
            case 'today':
                $this->filterDate = Carbon::today()->format('Y-m-d');
                $this->filterMonth = '';
                break;
            case 'yesterday':
                $this->filterDate = Carbon::yesterday()->format('Y-m-d');
                $this->filterMonth = '';
                break;
            case 'this_week':
                // Clear specific date, set to this month for approximation
                $this->filterDate = '';
                $this->filterMonth = Carbon::now()->format('Y-m');
                break;
            case 'this_month':
                $this->filterDate = '';
                $this->filterMonth = Carbon::now()->format('Y-m');
                break;
        }
        $this->resetPage();
    }
}
