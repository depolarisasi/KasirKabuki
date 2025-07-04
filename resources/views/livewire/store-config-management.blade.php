<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Konfigurasi Toko</h1>
            <p class="text-gray-600">Kelola informasi toko dan pengaturan struk</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <button wire:click="resetToDefault" class="btn btn-ghost btn-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Reset Default
            </button>
        </div>
    </div>

    <form wire:submit="updateSettings" class="space-y-6">
        <!-- Store Information Card -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Informasi Toko
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Store Name -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Nama Toko *</span>
                        </label>
                        <input wire:model="store_name" 
                               type="text" 
                               class="input input-bordered w-full @error('store_name') input-error @enderror" 
                               placeholder="Masukkan nama toko">
                        @error('store_name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Store Phone -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Nomor Telepon</span>
                        </label>
                        <input wire:model="store_phone" 
                               type="text" 
                               class="input input-bordered w-full @error('store_phone') input-error @enderror" 
                               placeholder="022-1234567">
                        @error('store_phone')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Store Email -->
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">Email</span>
                        </label>
                        <input wire:model="store_email" 
                               type="email" 
                               class="input input-bordered w-full @error('store_email') input-error @enderror" 
                               placeholder="info@satebraga.com">
                        @error('store_email')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Store Address -->
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text font-semibold">Alamat Toko</span>
                        </label>
                        <textarea wire:model="store_address" 
                                  class="textarea textarea-bordered h-20 @error('store_address') textarea-error @enderror" 
                                  placeholder="Jl. Braga No. 123, Bandung"></textarea>
                        @error('store_address')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Configuration Card -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Pengaturan Struk
                </h2>

                <div class="space-y-4">
                    <!-- Receipt Header -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Header Struk</span>
                            <span class="label-text-alt">Teks yang tampil di bagian atas struk</span>
                        </label>
                        <textarea wire:model="receipt_header" 
                                  class="textarea textarea-bordered h-16 @error('receipt_header') textarea-error @enderror" 
                                  placeholder="TERIMA KASIH ATAS KUNJUNGAN ANDA"></textarea>
                        @error('receipt_header')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Receipt Footer -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Footer Struk</span>
                            <span class="label-text-alt">Teks yang tampil di bagian bawah struk</span>
                        </label>
                        <textarea wire:model="receipt_footer" 
                                  class="textarea textarea-bordered h-16 @error('receipt_footer') textarea-error @enderror" 
                                  placeholder="Selamat menikmati & sampai jumpa lagi!"></textarea>
                        @error('receipt_footer')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Logo Settings -->
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input wire:model="show_receipt_logo" 
                                   type="checkbox" 
                                   class="checkbox checkbox-primary">
                            <span class="label-text font-semibold">Tampilkan logo di struk</span>
                        </label>
                    </div>

                    @if($show_receipt_logo)
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-semibold">Upload Logo</span>
                                <span class="label-text-alt">Format: JPG, PNG. Maksimal 1MB</span>
                            </label>
                            <input wire:model="receipt_logo" 
                                   type="file" 
                                   accept="image/*"
                                   class="file-input file-input-bordered w-full @error('receipt_logo') file-input-error @enderror">
                            @error('receipt_logo')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                            
                            @if($currentSettings->receipt_logo_path)
                                <div class="mt-2">
                                    <span class="text-sm text-gray-600">Logo saat ini:</span>
                                    <img src="{{ asset('storage/' . $currentSettings->receipt_logo_path) }}" 
                                         alt="Current Logo" 
                                         class="max-w-32 max-h-16 object-contain mt-1">
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-end">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <svg wire:loading wire:target="updateSettings" class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <svg wire:loading.remove wire:target="updateSettings" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span wire:loading.remove wire:target="updateSettings">Simpan Konfigurasi</span>
                <span wire:loading wire:target="updateSettings">Menyimpan...</span>
            </button>
        </div>
    </form>

    <!-- Preview Section -->
    <div class="card bg-base-100 shadow-lg mt-6">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Preview Struk
            </h2>
            
            <div class="mockup-window border bg-base-300 max-w-md mx-auto">
                <div class="flex justify-center px-4 py-16 bg-white text-black text-sm">
                    <div class="text-center space-y-2">
                        @if($show_receipt_logo && $currentSettings->receipt_logo_path)
                            <img src="{{ asset('storage/' . $currentSettings->receipt_logo_path) }}" 
                                 alt="Logo" 
                                 class="max-h-12 mx-auto mb-2">
                        @endif
                        <div class="font-bold">{{ $store_name ?: 'Nama Toko' }}</div>
                        @if($store_address)
                            <div class="text-xs">{{ $store_address }}</div>
                        @endif
                        @if($store_phone)
                            <div class="text-xs">{{ $store_phone }}</div>
                        @endif
                        @if($receipt_header)
                            <div class="border-t pt-2 mt-2 text-xs">{{ $receipt_header }}</div>
                        @endif
                        <div class="border-t pt-2 mt-2">
                            <div>Sample Item x1 - Rp 15.000</div>
                            <div class="border-t pt-1 mt-1 font-bold">TOTAL: Rp 15.000</div>
                        </div>
                        @if($receipt_footer)
                            <div class="border-t pt-2 mt-2 text-xs">{{ $receipt_footer }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 