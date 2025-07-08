<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Partner;
use App\Models\ProductPartnerPrice;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Support\Facades\Storage;

class ProductManagement extends Component
{
    use WithPagination, WithFileUploads;

    // Form properties
    #[Rule('required|min:2|max:100')]
    public $name = '';
    
    #[Rule('nullable|max:500')]
    public $description = '';
    
    #[Rule('required|numeric|min:0')]
    public $price = '';
    
    #[Rule('required|exists:categories,id')]
    public $category_id = '';

    #[Rule('nullable|image|max:2048')]
    public $photo;

    public $existingPhoto = null;

    // Component state
    public $productId = null;
    public $isEditMode = false;
    public $showModal = false;

    // Search functionality
    public $search = '';
    public $categoryFilter = '';

    // Partner pricing properties
    public $partnerPrices = [];
    public $enablePartnerPricing = false;
    
    protected $paginationView = 'vendor.pagination.daisyui';

    public function render()
    {
        $categories = Category::orderBy('name')->get();
        $partners = Partner::orderBy('name')->get();
        
        $products = Product::with(['category', 'partnerPrices'])
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
            'categories' => $categories,
            'partners' => $partners
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
        $this->initializePartnerPrices();
    }

    public function openEditModal($productId)
    {
        $product = Product::findOrFail($productId);
        
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->price = $product->price;
        $this->category_id = $product->category_id;
        $this->existingPhoto = $product->photo;
        $this->isEditMode = true;
        $this->showModal = true;
        
        // Load existing partner prices
        $this->loadPartnerPrices($product);
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

            // Handle photo upload
            if ($this->photo) {
                // Delete old photo if exists (for edit mode)
                if ($this->isEditMode && $this->existingPhoto) {
                    $oldPhotoPath = public_path($this->existingPhoto);
                    if (file_exists($oldPhotoPath)) {
                        unlink($oldPhotoPath);
                    }
                }

                // Generate unique filename
                $filename = time() . '_' . $this->photo->getClientOriginalName();
                
                // Store photo in public/uploads/products
                $this->photo->storeAs('', $filename, 'products');
                
                // Save relative path in database
                $data['photo'] = 'uploads/products/' . $filename;
            }

            if ($this->isEditMode) {
                $product = Product::findOrFail($this->productId);
                $product->update($data);
                
                // Update partner prices
                $this->savePartnerPrices($product);
                
                LivewireAlert::title('Berhasil!')
                ->text("Produk \"{$data['name']}\" berhasil diperbarui.")
                ->success()
                ->show();
            } else {
                $product = Product::create($data);
                
                // Save partner prices for new product
                $this->savePartnerPrices($product);
                
                LivewireAlert::title('Berhasil!')
                ->text("Produk \"{$data['name']}\" berhasil ditambahkan.")
                ->success()
                ->show();
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage() ?: 'Terjadi kesalahan saat menyimpan produk.';
            LivewireAlert::title('Error!')
            ->text('Terjadi kesalahan saat menyimpan produk: ' . $errorMessage)
            ->error()
            ->show();
        }
    }

    public function confirmDelete($productId)
    {
        \Log::info('ProductManagement: confirmDelete called with LivewireAlert', [
            'product_id' => $productId,
            'user_id' => auth()->id()
        ]);
        
        try {
            $product = Product::findOrFail($productId);

            \Log::info('ProductManagement: Showing delete confirmation with LivewireAlert', [
                'product_id' => $productId,
                'product_name' => $product->name
            ]);

            // Use LivewireAlert for confirmation
            LivewireAlert::title('Konfirmasi Hapus')
                ->text("Apakah Anda yakin ingin menghapus produk \"{$product->name}\"?")
                ->asConfirm()
                ->onConfirm('deleteProduct', ['productId' => $productId])
                ->show();
            
        } catch (\Exception $e) {
            \Log::error('ProductManagement: Error in confirmDelete', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            LivewireAlert::title('Terjadi kesalahan!')
                ->text('Terjadi kesalahan saat memproses produk.')
                ->error()
                ->show();
        }
    }

    public function deleteProduct($data)
    {
        try {
            $productId = $data['productId'];
            $product = Product::findOrFail($productId);
            $productName = $product->name;
            
            \Log::info('ProductManagement: Executing delete', [
                'product_id' => $productId,
                'product_name' => $productName
            ]);
            
            // Delete photo file if exists
            if ($product->photo) {
                $photoPath = public_path($product->photo);
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
            
            $product->delete();
            
            LivewireAlert::title('Berhasil!')
                ->text("Produk \"{$productName}\" berhasil dihapus.")
                ->success()
                ->show();
                
            $this->resetPage();
        } catch (\Exception $e) {
            \Log::error('ProductManagement: Error in deleteProduct', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            LivewireAlert::title('Terjadi kesalahan!')
                ->text('Terjadi kesalahan saat menghapus produk.')
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
        $this->reset(['name', 'description', 'price', 'category_id', 'photo', 'existingPhoto', 'productId', 'isEditMode', 'enablePartnerPricing']);
        $this->partnerPrices = [];
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

    private function initializePartnerPrices()
    {
        $partners = Partner::orderBy('name')->get();
        $this->partnerPrices = [];
        $this->enablePartnerPricing = false;
        
        foreach ($partners as $partner) {
            $this->partnerPrices[$partner->id] = [
                'partner_name' => $partner->name,
                'price' => '',
                'is_active' => false
            ];
        }
    }

    private function loadPartnerPrices($product)
    {
        $partners = Partner::orderBy('name')->get();
        $existingPrices = $product->partnerPrices()->get()->keyBy('partner_id');
        $this->partnerPrices = [];
        
        // Check if any partner prices exist
        $this->enablePartnerPricing = $existingPrices->count() > 0;
        
        foreach ($partners as $partner) {
            $existingPrice = $existingPrices->get($partner->id);
            $this->partnerPrices[$partner->id] = [
                'partner_name' => $partner->name,
                'price' => $existingPrice ? $existingPrice->price : '',
                'is_active' => $existingPrice ? $existingPrice->is_active : false
            ];
        }
    }

    private function savePartnerPrices($product)
    {
        if (!$this->enablePartnerPricing) {
            // If partner pricing is disabled, remove all existing partner prices
            $product->partnerPrices()->delete();
            return;
        }
        
        foreach ($this->partnerPrices as $partnerId => $priceData) {
            // Only save if price is set and active
            if (!empty($priceData['price']) && $priceData['is_active']) {
                ProductPartnerPrice::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'partner_id' => $partnerId
                    ],
                    [
                        'price' => $priceData['price'],
                        'is_active' => true
                    ]
                );
            } else {
                // Remove partner price if exists but not active or no price set
                ProductPartnerPrice::where('product_id', $product->id)
                    ->where('partner_id', $partnerId)
                    ->delete();
            }
        }
    }

    public function togglePartnerPricing()
    {
        $this->enablePartnerPricing = !$this->enablePartnerPricing;
        
        if (!$this->enablePartnerPricing) {
            // Reset all partner prices when disabled
            foreach ($this->partnerPrices as $partnerId => $priceData) {
                $this->partnerPrices[$partnerId]['price'] = '';
                $this->partnerPrices[$partnerId]['is_active'] = false;
            }
        }
    }
}
