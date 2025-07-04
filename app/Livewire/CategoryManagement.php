<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class CategoryManagement extends Component
{
    use WithPagination;

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

    protected $paginationTheme = 'bootstrap';

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
                
                Alert::success('Berhasil!', 'Kategori berhasil diperbarui.');
            } else {
                Category::create([
                    'name' => $this->name,
                    'description' => $this->description,
                ]);
                
                Alert::success('Berhasil!', 'Kategori berhasil ditambahkan.');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat menyimpan kategori.');
        }
    }

    public function confirmDelete($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        
        // Check if category has products
        if ($category->products()->exists()) {
            Alert::warning('Tidak dapat dihapus!', 'Kategori ini masih memiliki produk terkait.');
            return;
        }

        Alert::question('Konfirmasi Hapus', 'Apakah Anda yakin ingin menghapus kategori "' . $category->name . '"?')
            ->showCancelButton('Batal', '#aaa')
            ->showConfirmButton('Ya, Hapus!', '#d33')
            ->then(function () use ($categoryId) {
                $this->delete($categoryId);
            });
    }

    public function delete($categoryId)
    {
        try {
            $category = Category::findOrFail($categoryId);
            $categoryName = $category->name;
            $category->delete();
            
            Alert::success('Berhasil!', 'Kategori "' . $categoryName . '" berhasil dihapus.');
            $this->resetPage();
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat menghapus kategori.');
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
