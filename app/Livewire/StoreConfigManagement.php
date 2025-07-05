<?php

namespace App\Livewire;

use App\Models\StoreSetting;
use Livewire\Component;
use Livewire\WithFileUploads;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Storage;

class StoreConfigManagement extends Component
{
    use WithFileUploads;

    public $store_name;
    public $store_address;
    public $store_phone;
    public $store_email;
    public $receipt_header;
    public $receipt_footer;
    public $show_receipt_logo;
    public $receipt_logo;

    protected $rules = [
        'store_name' => 'required|string|max:255',
        'store_address' => 'nullable|string|max:500',
        'store_phone' => 'nullable|string|max:20',
        'store_email' => 'nullable|email|max:255',
        'receipt_header' => 'nullable|string|max:500',
        'receipt_footer' => 'nullable|string|max:500',
        'show_receipt_logo' => 'boolean',
        'receipt_logo' => 'nullable|image|max:1024', // Max 1MB
    ];

    public function mount()
    {
        $settings = StoreSetting::current();
        
        $this->store_name = $settings->store_name;
        $this->store_address = $settings->store_address;
        $this->store_phone = $settings->store_phone;
        $this->store_email = $settings->store_email;
        $this->receipt_header = $settings->receipt_header;
        $this->receipt_footer = $settings->receipt_footer;
        $this->show_receipt_logo = $settings->show_receipt_logo;
    }

    public function updateSettings()
    {
        $this->validate();

        try {
            $data = [
                'store_name' => $this->store_name,
                'store_address' => $this->store_address,
                'store_phone' => $this->store_phone,
                'store_email' => $this->store_email,
                'receipt_header' => $this->receipt_header,
                'receipt_footer' => $this->receipt_footer,
                'show_receipt_logo' => $this->show_receipt_logo,
            ];

            // Handle logo upload if provided
            if ($this->receipt_logo) {
                // Delete old logo if exists
                $currentSettings = StoreSetting::current();
                if ($currentSettings->receipt_logo_path) {
                    $oldLogoPath = public_path($currentSettings->receipt_logo_path);
                    if (file_exists($oldLogoPath)) {
                        unlink($oldLogoPath);
                    }
                }

                // Generate unique filename
                $filename = time() . '_' . $this->receipt_logo->getClientOriginalName();
                
                // Store file in public/uploads/logos directory
                $this->receipt_logo->storeAs('', $filename, 'logos');
                
                // Save relative path in database
                $data['receipt_logo_path'] = 'uploads/logos/' . $filename;
            }

            StoreSetting::updateSettings($data);

            Alert::success('Berhasil!', 'Konfigurasi toko berhasil diperbarui.');
            
        } catch (\Exception $e) {
            Alert::error('Error!', 'Gagal memperbarui konfigurasi: ' . $e->getMessage());
        }
    }

    public function testPrint()
    {
        try {
            // Generate test receipt URL with current form data
            $testData = [
                'store_name' => $this->store_name ?: 'Nama Toko',
                'store_address' => $this->store_address,
                'store_phone' => $this->store_phone,
                'receipt_header' => $this->receipt_header,
                'receipt_footer' => $this->receipt_footer,
                'show_receipt_logo' => $this->show_receipt_logo,
            ];

            // Open test receipt in new window
            $this->dispatch('open-test-receipt', ['testData' => $testData]);
            
            Alert::info('Test Print', 'Membuka jendela test print. Pastikan printer Anda sudah terhubung.');
            
        } catch (\Exception $e) {
            Alert::error('Error!', 'Gagal membuka test print: ' . $e->getMessage());
        }
    }

    public function resetToDefault()
    {
        $this->store_name = 'Sate Braga';
        $this->store_address = 'Jl. Braga No. 123, Bandung';
        $this->store_phone = '022-1234567';
        $this->store_email = 'info@satebraga.com';
        $this->receipt_header = 'TERIMA KASIH ATAS KUNJUNGAN ANDA';
        $this->receipt_footer = 'Selamat menikmati & sampai jumpa lagi!';
        $this->show_receipt_logo = false;
        
        Alert::info('Reset', 'Form telah direset ke nilai default.');
    }

    public function render()
    {
        return view('livewire.store-config-management', [
            'currentSettings' => StoreSetting::current()
        ]);
    }
} 