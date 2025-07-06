<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Masmerise\Toaster\Toastable;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class CategoryManagement extends Component
{
    use WithPagination, Toastable;

    public $title = 'Manajemen Kategori - KasirBraga';

    // Form properties
    #[Rule('required|min:2|max:100')]
    public $name = '';
    
    #[Rule('nullable|max:500')]
    public $description = '';

    // Component state
    public $categoryId = null;
    public $isEditMode = false;
    public $showModal = false;

    // Search functionality
    public $search = '';

    protected $paginationView = 'vendor.pagination.daisyui';

    public function render()
    {
        $categories = Category::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.category-management', [
            'categories' => $categories
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEditModal($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->isEditMode) {
                $category = Category::findOrFail($this->categoryId);
                $category->update([
                    'name' => $this->name,
                    'description' => $this->description,
                ]);
                
                $this->success('Kategori berhasil diperbarui.');
            } else {
                Category::create([
                    'name' => $this->name,
                    'description' => $this->description,
                ]);
                
                $this->success('Kategori berhasil ditambahkan.');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat menyimpan kategori.');
        }
    }

    public function confirmDelete($categoryId)
    {
        \Log::info('CategoryManagement: confirmDelete called with LivewireAlert', [
            'category_id' => $categoryId,
            'user_id' => auth()->id()
        ]);
        
        try {
            $category = Category::findOrFail($categoryId);
            
            // Check if category has products
            if ($category->products()->exists()) {
                \Log::warning('CategoryManagement: Cannot delete category with products', [
                    'category_id' => $categoryId,
                    'category_name' => $category->name,
                    'products_count' => $category->products()->count()
                ]);
                
                $this->warning('Kategori ini masih memiliki produk terkait dan tidak dapat dihapus.');
                return;
            }

            \Log::info('CategoryManagement: Showing delete confirmation with LivewireAlert', [
                'category_id' => $categoryId,
                'category_name' => $category->name
            ]);

            // Use LivewireAlert for confirmation
            LivewireAlert::title('Konfirmasi Hapus')
                ->text("Apakah Anda yakin ingin menghapus kategori \"{$category->name}\"?")
                ->asConfirm()
                ->onConfirm('deleteCategory', ['categoryId' => $categoryId])
                ->show();
            
        } catch (\Exception $e) {
            \Log::error('CategoryManagement: Error in confirmDelete', [
                'category_id' => $categoryId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error('Terjadi kesalahan saat memproses kategori.');
        }
    }

    public function deleteCategory($data)
    {
        try {
            $categoryId = $data['categoryId'];
            $category = Category::findOrFail($categoryId);
            $categoryName = $category->name;
            
            \Log::info('CategoryManagement: Executing delete', [
                'category_id' => $categoryId,
                'category_name' => $categoryName
            ]);
            
            $category->delete();
            
            $this->success("Kategori \"{$categoryName}\" berhasil dihapus.");
                
            $this->resetPage();
        } catch (\Exception $e) {
            \Log::error('CategoryManagement: Error in deleteCategory', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error('Terjadi kesalahan saat menghapus kategori.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'categoryId', 'isEditMode']);
        $this->resetValidation();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}
