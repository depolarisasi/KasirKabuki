<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Rule; 
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class DiscountManagement extends Component
{
    use WithPagination;

    // Form properties
    #[Rule('required|min:2|max:100')]
    public $name = '';
    
    #[Rule('required|in:product,transaction')]
    public $type = 'product';
    
    #[Rule('required|in:percentage,fixed')]
    public $value_type = 'percentage';
    
    #[Rule('required|numeric|min:0')]
    public $value = '';
    
    public $product_id = null;
    public $order_type = null;
    public $is_active = true;

    // Component state
    public $discountId = null;
    public $isEditMode = false;
    public $showModal = false;

    // Search functionality
    public $search = '';
    public $filterType = '';
    public $filterStatus = '';
    public $filterOrderType = '';

    // Order type options
    public $orderTypeOptions = [
        '' => 'Semua Jenis Pesanan',
        'dine_in' => 'Makan di Tempat',
        'take_away' => 'Bawa Pulang',
        'online' => 'Online'
    ];

    protected $paginationView = 'vendor.pagination.daisyui';

    public function rules()
    {
        $rules = [
            'name' => 'required|min:2|max:100',
            'type' => 'required|in:product,transaction',
            'value_type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'order_type' => 'nullable|in:dine_in,take_away,online',
            'is_active' => 'boolean',
        ];

        // Add value validation based on value_type
        if ($this->value_type === 'percentage') {
            $rules['value'] .= '|max:100';
        }

        // Add product_id validation for product discounts
        if ($this->type === 'product') {
            $rules['product_id'] = 'required|exists:products,id';
        } else {
            $rules['product_id'] = 'nullable';
        }

        return $rules;
    }

    public function render()
    {
        $discounts = Discount::query()
            ->with('product')
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->filterType, function ($query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('is_active', $this->filterStatus);
            })
            ->when($this->filterOrderType !== '', function ($query) {
                if ($this->filterOrderType === 'all') {
                    $query->whereNull('order_type');
                } else {
                    $query->where('order_type', $this->filterOrderType);
                }
            })
            ->latest()
            ->paginate(10);

        $products = Product::all();

        return view('livewire.discount-management', [
            'discounts' => $discounts,
            'products' => $products
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEditModal($discountId)
    {
        $discount = Discount::findOrFail($discountId);
        
        $this->discountId = $discount->id;
        $this->name = $discount->name;
        $this->type = $discount->type;
        $this->value_type = $discount->value_type;
        $this->value = $discount->value;
        $this->product_id = $discount->product_id;
        $this->order_type = $discount->order_type;
        $this->is_active = $discount->is_active;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'type' => $this->type,
                'value_type' => $this->value_type,
                'value' => $this->value,
                'product_id' => $this->type === 'product' ? $this->product_id : null,
                'order_type' => $this->order_type ?: null,
                'is_active' => $this->is_active,
            ];

            if ($this->isEditMode) {
                $discount = Discount::findOrFail($this->discountId);
                $discount->update($data);
                
                LivewireAlert::title('Berhasil!')
                ->text("Diskon \"{$this->name}\" berhasil diperbarui.")
                ->success()
                ->show();
            } else {
                Discount::create($data);
                
                LivewireAlert::title('Berhasil!')
                ->text("Diskon \"{$this->name}\" berhasil ditambahkan.")
                ->success()
                ->show();
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            LivewireAlert::title('Terjadi kesalahan!')
                ->text('Terjadi kesalahan saat menyimpan diskon.')
                ->error()
                ->show();
        }
    }

    public function toggleStatus($discountId)
    {
        try {
            $discount = Discount::findOrFail($discountId);
            $discount->update(['is_active' => !$discount->is_active]);
            
            $status = $discount->is_active ? 'diaktifkan' : 'dinonaktifkan';
            LivewireAlert::title('Berhasil!')
                ->text("Diskon \"{$discount->name}\" berhasil {$status}.")
                ->success()
                ->show();
        } catch (\Exception $e) {
            LivewireAlert::title('Terjadi kesalahan!')
                ->text('Terjadi kesalahan saat mengubah status diskon.')
                ->error()
                ->show();
        }
    }

    public function confirmDelete($discountId)
    {
        \Log::info('DiscountManagement: confirmDelete called with LivewireAlert', [
            'discount_id' => $discountId,
            'user_id' => auth()->id()
        ]);
        
        try {
            $discount = Discount::findOrFail($discountId);
            
            \Log::info('DiscountManagement: Showing delete confirmation with LivewireAlert', [
                'discount_id' => $discountId,
                'discount_name' => $discount->name
            ]);

            // Use LivewireAlert for confirmation
            LivewireAlert::title('Konfirmasi Hapus')
                ->text("Apakah Anda yakin ingin menghapus diskon \"{$discount->name}\"?")
                ->asConfirm()
                ->onConfirm('deleteDiscount', ['discountId' => $discountId])
                ->show();
            
        } catch (\Exception $e) {
            \Log::error('DiscountManagement: Error in confirmDelete', [
                'discount_id' => $discountId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            LivewireAlert::title('Error!')
                ->text('Terjadi kesalahan saat memproses diskon.')
                ->error()
                ->show();
        }
    }

    public function deleteDiscount($data)
    {
        try {
            $discountId = $data['discountId'];
            $discount = Discount::findOrFail($discountId);
            $discountName = $discount->name;
            
            \Log::info('DiscountManagement: Executing delete', [
                'discount_id' => $discountId,
                'discount_name' => $discountName
            ]);
            
            $discount->delete();
            
            LivewireAlert::title('Berhasil!')
                ->text("Diskon \"{$discountName}\" berhasil dihapus.")
                ->success()
                ->show();
                
            $this->resetPage();
        } catch (\Exception $e) {
            \Log::error('DiscountManagement: Error in deleteDiscount', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
                LivewireAlert::title('Terjadi kesalahan!')
                ->text('Terjadi kesalahan saat menghapus diskon.')
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
        $this->reset(['name', 'type', 'value_type', 'value', 'product_id', 'order_type', 'is_active', 'discountId', 'isEditMode']);
        $this->type = 'product'; // Default to product
        $this->value_type = 'percentage'; // Default to percentage
        $this->is_active = true; // Default to active
        $this->resetValidation();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterOrderType()
    {
        $this->resetPage();
    }

    // Reset product_id when type changes to transaction
    public function updatedType()
    {
        if ($this->type === 'transaction') {
            $this->product_id = null;
        }
    }
}
