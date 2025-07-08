<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Manajemen User</h1>
            <p class="text-white">Kelola user dan role sistem</p>
        </div>
        <button wire:click="createUser" class="btn btn-primary mt-4 sm:mt-0">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Tambah User
        </button>
    </div>

    <!-- Search and Filters -->
    <div class="card bg-base-300 shadow-lg mb-6">
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Cari User</span>
                    </label>
                    <input wire:model.live="search" type="text" placeholder="Nama atau email..." class="input input-bordered" />
                </div>

                <!-- Role Filter -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Filter Role</span>
                    </label>
                    <select wire:model.live="roleFilter" class="select select-bordered">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Filter Status</span>
                    </label>
                    <select wire:model.live="statusFilter" class="select select-bordered">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">&nbsp;</span>
                    </label>
                    <button wire:click="resetFilters" class="btn btn-outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card bg-base-300 shadow-lg">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr class="bg-base-200">
                            <th class="w-16">#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>PIN</th>
                            <th>Bergabung</th>
                            <th class="w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr class="hover">
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="avatar">
                                            <div class="mask mask-squircle w-12 h-12 bg-primary text-primary-content flex items-center justify-center">
                                                <span class="font-semibold">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ $user->name }}</div>
                                            @if($user->id == auth()->id())
                                                <div class="text-sm opacity-50">Anda</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            <div class="badge badge-outline badge-sm">{{ ucfirst($role->name) }}</div>
                                        @endforeach
                                    @else
                                        <div class="badge badge-error badge-sm">No Role</div>
                                    @endif
                                </td>
                                <td>
                                    @php $isActive = $user->is_active ?? true; @endphp
                                    <div class="flex items-center space-x-2">
                                        <div class="badge {{ $isActive ? 'badge-success' : 'badge-error' }} badge-sm">
                                            {{ $isActive ? 'Aktif' : 'Nonaktif' }}
                                        </div>
                                        @if($user->id != auth()->id())
                                            <button 
                                                wire:click="toggleUserStatus({{ $user->id }})"
                                                class="btn btn-ghost btn-xs"
                                                title="{{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }} User"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isActive ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center space-x-2">
                                        @if($user->hasPin())
                                            <div class="badge badge-success badge-sm">{{ $user->masked_pin }}</div>
                                            <div class="dropdown dropdown-end">
                                                <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                    </svg>
                                                </div>
                                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                                    <li><a wire:click="resetUserPin({{ $user->id }})">Reset PIN</a></li>
                                                    <li><a wire:click="clearUserPin({{ $user->id }})" class="text-error">Hapus PIN</a></li>
                                                </ul>
                                            </div>
                                        @else
                                            <div class="badge badge-ghost badge-sm">Belum ada</div>
                                            <button 
                                                wire:click="resetUserPin({{ $user->id }})"
                                                class="btn btn-ghost btn-xs"
                                                title="Generate PIN"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="flex space-x-1">
                                        <button 
                                            wire:click="editUser({{ $user->id }})" 
                                            class="btn btn-sm btn-ghost"
                                            title="Edit User"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        @if($user->id != auth()->id())
                                            <button 
                                                wire:click="confirmDelete({{ $user->id }})" 
                                                class="btn btn-sm btn-ghost text-error"
                                                title="Hapus User"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8">
                                    <div class="flex flex-col items-center space-y-2">
                                        <svg class="w-12 h-12 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                        <p class="text-base-content/70">Tidak ada user ditemukan</p>
                                        @if($search || $roleFilter || $statusFilter)
                                            <button wire:click="resetFilters" class="btn btn-sm btn-outline">Reset Filter</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal {{ $showModal ? 'modal-open' : '' }}">
        <div class="modal-box w-11/12 max-w-2xl">
            <h3 class="font-bold text-lg mb-4">
                {{ $editMode ? 'Edit User' : 'Tambah User' }}
            </h3>

            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Nama <span class="text-error">*</span></span>
                        </label>
                        <input wire:model="name" type="text" class="input input-bordered" placeholder="Nama lengkap" />
                        @error('name') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Email <span class="text-error">*</span></span>
                        </label>
                        <input wire:model="email" type="email" class="input input-bordered" placeholder="email@example.com" />
                        @error('email') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">
                                Password 
                                @if(!$editMode) <span class="text-error">*</span> @endif
                                @if($editMode) <span class="text-sm opacity-60">(kosongkan jika tidak diubah)</span> @endif
                            </span>
                        </label>
                        <input wire:model="password" type="password" class="input input-bordered" placeholder="Minimal 8 karakter" />
                        @error('password') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password Confirmation -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Konfirmasi Password</span>
                        </label>
                        <input wire:model="password_confirmation" type="password" class="input input-bordered" placeholder="Ulangi password" />
                        @error('password_confirmation') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Role -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Role <span class="text-error">*</span></span>
                        </label>
                        <select wire:model="selectedRole" class="select select-bordered">
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('selectedRole') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Status -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <div class="flex items-center space-x-4 pt-2">
                            <label class="label cursor-pointer">
                                <input wire:model="isActive" type="checkbox" class="toggle toggle-success" />
                                <span class="label-text ml-2">{{ $isActive ? 'Aktif' : 'Nonaktif' }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- PIN Management Section -->
                <div class="divider">PIN Management</div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if(!$editMode)
                        <!-- Auto Generate PIN for New Users -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Auto Generate PIN</span>
                            </label>
                            <div class="flex items-center space-x-4 pt-2">
                                <label class="label cursor-pointer">
                                    <input wire:model="generatePin" type="checkbox" class="toggle toggle-primary" />
                                    <span class="label-text ml-2">{{ $generatePin ? 'Ya, generate otomatis' : 'Manual' }}</span>
                                </label>
                            </div>
                        </div>
                    @endif

                    <!-- Manual PIN Input -->
                    @if(!$generatePin)
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">
                                    PIN (6 digit)
                                    @if($editMode) <span class="text-sm opacity-60">(kosongkan jika tidak diubah)</span> @endif
                                </span>
                            </label>
                            <div class="flex space-x-2">
                                <input wire:model="pin" type="text" maxlength="6" class="input input-bordered flex-1" placeholder="123456" />
                                <button type="button" wire:click="generateRandomPin" class="btn btn-outline">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Generate
                                </button>
                            </div>
                            @error('pin') <span class="text-error text-sm">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>

                <div class="modal-action">
                    <button type="button" wire:click="closeModal" class="btn btn-ghost">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        {{ $editMode ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function waitForSwal(callback) {
        if (typeof window.Swal !== 'undefined') {
            callback();
        } else {
            setTimeout(() => waitForSwal(callback), 100);
        }
    }

    waitForSwal(() => {
        document.addEventListener('livewire:init', () => {
            Livewire.on('confirm-delete', (event) => {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Apakah Anda yakin ingin menghapus user ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('deleteUser', { userId: event.userId });
                    }
                });
            });
        });
    });
</script>
@endpush
