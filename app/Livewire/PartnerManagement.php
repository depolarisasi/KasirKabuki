<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Partner;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class PartnerManagement extends Component
{
    use WithPagination;

    // Form properties
    #[Rule('required|min:2|max:100')]
    public $name = '';
    
    #[Rule('required|numeric|min:0|max:100')]
    public $commission_rate = '';

    // Component state
    public $partnerId = null;
    public $isEditMode = false;
    public $showModal = false;

    // Search functionality
    public $search = '';

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $partners = Partner::query()
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.partner-management', [
            'partners' => $partners
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEditModal($partnerId)
    {
        $partner = Partner::findOrFail($partnerId);
        
        $this->partnerId = $partner->id;
        $this->name = $partner->name;
        $this->commission_rate = $partner->commission_rate;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
                'commission_rate' => $this->commission_rate,
            ];

            if ($this->isEditMode) {
                $partner = Partner::findOrFail($this->partnerId);
                $partner->update($data);
                
                Alert::success('Berhasil!', 'Partner berhasil diperbarui.');
            } else {
                Partner::create($data);
                
                Alert::success('Berhasil!', 'Partner berhasil ditambahkan.');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat menyimpan partner: ' . $e->getMessage());
        }
    }

    public function confirmDelete($partnerId)
    {
        $partner = Partner::findOrFail($partnerId);
        
        // Check if partner has transactions (we'll implement this in future)
        // For now, allow delete
        
        $this->dispatch('confirm-delete', [
            'partnerId' => $partnerId,
            'partnerName' => $partner->name
        ]);
    }

    public function delete($partnerId)
    {
        try {
            $partner = Partner::findOrFail($partnerId);
            $partnerName = $partner->name;
            $partner->delete();
            
            Alert::success('Berhasil!', 'Partner "' . $partnerName . '" berhasil dihapus.');
            $this->resetPage();
        } catch (\Exception $e) {
            Alert::error('Error!', 'Terjadi kesalahan saat menghapus partner.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['name', 'commission_rate', 'partnerId', 'isEditMode']);
        $this->resetValidation();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}
