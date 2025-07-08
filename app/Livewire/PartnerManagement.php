<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Partner;
use Livewire\WithPagination;
use Livewire\Attributes\Rule; 
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

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

    protected $paginationView = 'vendor.pagination.daisyui';

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
                
                LivewireAlert::title('Berhasil!')
                ->text("Partner \"{$partner->name}\" berhasil diperbarui.")
                ->success()
                ->show();
            } else {
                $partner = Partner::create($data);
                
                LivewireAlert::title('Berhasil!')
                ->text("Partner \"{$partner->name}\" berhasil ditambahkan.")
                ->success()
                ->show();
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            LivewireAlert::title('Terjadi kesalahan!')
                ->text('Terjadi kesalahan saat menyimpan partner.')
                ->error()
                ->show();
        }
    }

    public function confirmDelete($partnerId)
    {
        \Log::info('PartnerManagement: confirmDelete called with LivewireAlert', [
            'partner_id' => $partnerId,
            'user_id' => auth()->id()
        ]);
        
        try {
            $partner = Partner::findOrFail($partnerId);

            \Log::info('PartnerManagement: Showing delete confirmation with LivewireAlert', [
                'partner_id' => $partnerId,
                'partner_name' => $partner->name
            ]);

            // Use LivewireAlert for confirmation
            LivewireAlert::title('Konfirmasi Hapus')
                ->text("Apakah Anda yakin ingin menghapus partner \"{$partner->name}\"?")
                ->asConfirm()
                ->onConfirm('deletePartner', ['partnerId' => $partnerId])
                ->show();
            
        } catch (\Exception $e) {
            \Log::error('PartnerManagement: Error in confirmDelete', [
                'partner_id' => $partnerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            LivewireAlert::title('Error!')
                ->text('Terjadi kesalahan saat memproses partner.')
                ->error()
                ->show();
        }
    }

    public function deletePartner($data)
    {
        try {
            $partnerId = $data['partnerId'];
            $partner = Partner::findOrFail($partnerId);
            $partnerName = $partner->name;
            
            \Log::info('PartnerManagement: Executing delete', [
                'partner_id' => $partnerId,
                'partner_name' => $partnerName
            ]);
            
            $partner->delete();
            
            LivewireAlert::title('Berhasil!')
                ->text("Partner \"{$partnerName}\" berhasil dihapus.")
                ->success()
                ->show();
                
            $this->resetPage();
        } catch (\Exception $e) {
            \Log::error('PartnerManagement: Error in deletePartner', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            LivewireAlert::title('Error!')
                ->text('Terjadi kesalahan saat menghapus partner.')
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
        $this->reset(['name', 'commission_rate', 'partnerId', 'isEditMode']);
        $this->resetValidation();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}
