<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Discount;
use App\Models\Product;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use RealRashid\SweetAlert\Facades\Alert;

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
    public $is_active = true;

    // Component state
    public $discountId = null;
    public $isEditMode = false;
    public $showModal = false;

    // Search functionality
    public $search = '';
    public $filterType = '';
    public $filterStatus = '';

    protected $paginationTheme = 'bootstrap';

    public function rules()
    {
        $rules = [
            'name' => 'required|min:2|max:100',
            'type' => 'required|in:product,transaction',
            'value_type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
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
                'is_active' => $this->is_active,
            ];

            if ($this->isEditMode) {
                $discount = Discount::findOrFail($this->discountId);
                $discount->update($data);
                
                Alert::success('Berhasil!', 'Diskon berhasil diperbarui.');
            } else {
                Discount::create($data);
                
                Alert::success('Berhasil!', 'Diskon berhasil ditambahkan.');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat menyimpan diskon: ' . $e->getMessage());
        }
    }

    public function toggleStatus($discountId)
    {
        try {
            $discount = Discount::findOrFail($discountId);
            $discount->update(['is_active' => !$discount->is_active]);
            
            $status = $discount->is_active ? 'diaktifkan' : 'dinonaktifkan';
            Alert::success('Berhasil!', 'Diskon "' . $discount->name . '" berhasil ' . $status . '.');
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat mengubah status diskon.');
        }
    }

    public function confirmDelete($discountId)
    {
        $discount = Discount::findOrFail($discountId);
        
        $this->dispatch('confirm-delete', [
            'discountId' => $discountId,
            'discountName' => $discount->name
        ]);
    }

    public function delete($discountId)
    {
        try {
            $discount = Discount::findOrFail($discountId);
            $discountName = $discount->name;
            $discount->delete();
            
            Alert::success('Berhasil!', 'Diskon "' . $discountName . '" berhasil dihapus.');
            $this->resetPage();
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat menghapus diskon.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['name', 'type', 'value_type', 'value', 'product_id', 'is_active', 'discountId', 'isEditMode']);
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

    // Reset product_id when type changes to transaction
    public function updatedType()
    {
        if ($this->type === 'transaction') {
            $this->product_id = null;
        }
    }
}
