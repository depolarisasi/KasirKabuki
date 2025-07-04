<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class ProductManagement extends Component
{
    use WithPagination;

    // Form properties
    #[Rule('required|min:2|max:100')]
    public $name = '';
    
    #[Rule('nullable|max:500')]
    public $description = '';
    
    #[Rule('required|numeric|min:0')]
    public $price = '';
    
    #[Rule('required|exists:categories,id')]
    public $category_id = '';

    // Component state
    public $productId = null;
    public $isEditMode = false;
    public $showModal = false;

    // Search functionality
    public $search = '';
    public $categoryFilter = '';

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $categories = Category::orderBy('name')->get();
        
        $products = Product::with('category')
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->categoryFilter, function ($query) {
                $query->byCategory($this->categoryFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.product-management', [
            'products' => $products,
            'categories' => $categories
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEditModal($productId)
    {
        $product = Product::findOrFail($productId);
        
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->price = $product->price;
        $this->category_id = $product->category_id;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'category_id' => $this->category_id,
            ];

            if ($this->isEditMode) {
                $product = Product::findOrFail($this->productId);
                $product->update($data);
                
                Alert::success('Berhasil!', 'Produk berhasil diperbarui.');
            } else {
                Product::create($data);
                
                Alert::success('Berhasil!', 'Produk berhasil ditambahkan.');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat menyimpan produk: ' . $e->getMessage());
        }
    }

    public function confirmDelete($productId)
    {
        $product = Product::findOrFail($productId);
        
        // Check if product has transactions (we'll implement this in future)
        // For now, allow delete
        
        $this->dispatch('confirm-delete', [
            'productId' => $productId,
            'productName' => $product->name
        ]);
    }

    public function delete($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $productName = $product->name;
            $product->delete();
            
            Alert::success('Berhasil!', 'Produk "' . $productName . '" berhasil dihapus.');
            $this->resetPage();
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat menghapus produk.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'price', 'category_id', 'productId', 'isEditMode']);
        $this->resetValidation();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'categoryFilter']);
        $this->resetPage();
    }
}
