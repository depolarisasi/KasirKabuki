<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expense;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use Carbon\Carbon;

class ExpenseManagement extends Component
{
    use WithPagination;

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

    protected $paginationTheme = 'bootstrap';

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
            Alert::error('Error!', 'Anda tidak memiliki izin untuk mengedit pengeluaran ini.');
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
                
                // Authorization check
                if (auth()->user()->hasRole('admin') || $expense->user_id === auth()->id()) {
                    $expense->update($data);
                    Alert::success('Berhasil!', 'Pengeluaran berhasil diperbarui.');
                } else {
                    Alert::error('Error!', 'Anda tidak memiliki izin untuk mengedit pengeluaran ini.');
                    return;
                }
            } else {
                Expense::create($data);
                Alert::success('Berhasil!', 'Pengeluaran berhasil ditambahkan.');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat menyimpan pengeluaran: ' . $e->getMessage());
        }
    }

    public function confirmDelete($expenseId)
    {
        $expense = Expense::findOrFail($expenseId);
        
        // Authorization check
        if (auth()->user()->hasRole('admin') || $expense->user_id === auth()->id()) {
            $this->dispatch('confirm-delete', [
                'expenseId' => $expenseId,
                'expenseDescription' => $expense->description,
                'expenseAmount' => $expense->formatted_amount
            ]);
        } else {
            Alert::error('Error!', 'Anda tidak memiliki izin untuk menghapus pengeluaran ini.');
        }
    }

    public function delete($expenseId)
    {
        try {
            $expense = Expense::findOrFail($expenseId);
            
            // Authorization check
            if (auth()->user()->hasRole('admin') || $expense->user_id === auth()->id()) {
                $expenseDescription = $expense->description;
                $expense->delete();
                
                Alert::success('Berhasil!', 'Pengeluaran "' . $expenseDescription . '" berhasil dihapus.');
                $this->resetPage();
            } else {
                Alert::error('Error!', 'Anda tidak memiliki izin untuk menghapus pengeluaran ini.');
            }
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat menghapus pengeluaran.');
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
