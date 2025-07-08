<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Rule as LivewireRule;

class UserManagement extends Component
{
    use WithPagination;

    // Form properties
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedRole = '';
    public $isActive = true;
    public $pin = '';
    public $generatePin = false; // Auto-generate PIN on create
    
    // Modal and state management
    public $showModal = false;
    public $editMode = false;
    public $editingUserId = null;
    
    // Search and filters
    public $search = '';
    public $roleFilter = '';
    public $statusFilter = '';
    
    // Roles for dropdown
    public $roles = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->loadRoles();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === '1');
            })
            ->with('roles')
            ->paginate(10);

        return view('livewire.user-management', [
            'users' => $users
        ]);
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'selectedRole' => 'required|exists:roles,name',
            'isActive' => 'boolean',
            'generatePin' => 'boolean',
        ];

        if ($this->editMode) {
            $rules['email'] = ['required', 'email', Rule::unique('users')->ignore($this->editingUserId)];
            $rules['password'] = 'nullable|min:8|confirmed';
            $rules['pin'] = ['nullable', 'string', 'size:6', 'regex:/^[0-9]{6}$/', Rule::unique('users')->ignore($this->editingUserId)];
        } else {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|min:8|confirmed';
            $rules['pin'] = 'nullable|string|size:6|regex:/^[0-9]{6}$/|unique:users,pin';
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'selectedRole.required' => 'Role harus dipilih.',
            'selectedRole.exists' => 'Role tidak valid.',
            'pin.size' => 'PIN harus 6 digit.',
            'pin.regex' => 'PIN hanya boleh berisi angka.',
            'pin.unique' => 'PIN sudah digunakan oleh user lain.',
        ];
    }

    public function loadRoles()
    {
        $this->roles = Role::all();
    }

    public function createUser()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->generatePin = true; // Default to auto-generate PIN for new users
        $this->showModal = true;
    }

    public function editUser($userId)
    {
        $user = User::with('roles')->findOrFail($userId);
        
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRole = $user->roles->first()?->name ?? '';
        $this->isActive = $user->is_active ?? true;
        $this->pin = ''; // Don't show existing PIN
        $this->generatePin = false;
        $this->password = '';
        $this->password_confirmation = '';
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function generateRandomPin()
    {
        $this->pin = User::generateRandomPin();
        $this->generatePin = false; // Switch to manual mode
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editMode) {
                $this->updateUser();
            } else {
                $this->storeUser();
            }
        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal menyimpan user: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    private function storeUser()
    {
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'is_active' => $this->isActive,
        ];

        // Handle PIN generation
        if ($this->generatePin) {
            $userData['pin'] = User::generateRandomPin();
        } elseif (!empty($this->pin)) {
            $userData['pin'] = $this->pin;
        }

        $user = User::create($userData);

        // Assign role
        $user->assignRole($this->selectedRole);

        $this->closeModal();

        $pinMessage = '';
        if (isset($userData['pin'])) {
            $pinMessage = " PIN: {$userData['pin']}";
        }

        LivewireAlert::title('Berhasil!')
            ->text("User berhasil ditambahkan.{$pinMessage}")
            ->success()
            ->show();
    }

    private function updateUser()
    {
        $user = User::findOrFail($this->editingUserId);
        
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->isActive,
        ];

        // Update password only if provided
        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        // Update PIN only if provided
        if (!empty($this->pin)) {
            $userData['pin'] = $this->pin;
        }

        $user->update($userData);

        // Update role
        $user->syncRoles([$this->selectedRole]);

        $this->closeModal();

        LivewireAlert::title('Berhasil!')
            ->text('User berhasil diperbarui.')
            ->success()
            ->show();
    }

    public function resetUserPin($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $newPin = User::generateRandomPin();
            
            $user->update(['pin' => $newPin]);

            LivewireAlert::title('Berhasil!')
                ->text("PIN berhasil direset. PIN baru: {$newPin}")
                ->success()
                ->show();

        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal mereset PIN: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function clearUserPin($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->update(['pin' => null]);

            LivewireAlert::title('Berhasil!')
                ->text('PIN berhasil dihapus.')
                ->success()
                ->show();

        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal menghapus PIN: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function confirmDelete($userId)
    {
        $this->dispatch('confirm-delete', ['userId' => $userId]);
    }

    public function deleteUser($userId)
    {
        try {
            // Prevent deleting current user
            if ($userId == auth()->id()) {
                LivewireAlert::title('Error!')
                    ->text('Anda tidak dapat menghapus akun sendiri.')
                    ->error()
                    ->show();
                return;
            }

            $user = User::findOrFail($userId);
            $user->delete();

            LivewireAlert::title('Berhasil!')
                ->text('User berhasil dihapus.')
                ->success()
                ->show();

        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal menghapus user: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function toggleUserStatus($userId)
    {
        try {
            // Prevent deactivating current user
            if ($userId == auth()->id()) {
                LivewireAlert::title('Error!')
                    ->text('Anda tidak dapat menonaktifkan akun sendiri.')
                    ->error()
                    ->show();
                return;
            }

            $user = User::findOrFail($userId);
            $user->update(['is_active' => !($user->is_active ?? true)]);

            $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            
            LivewireAlert::title('Berhasil!')
                ->text("User berhasil {$status}.")
                ->success()
                ->show();

        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal mengubah status user: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRole = '';
        $this->isActive = true;
        $this->pin = '';
        $this->generatePin = false;
        $this->editingUserId = null;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->roleFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }
}
