<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')]
class PinLogin extends Component
{
    #[Rule('required|string|size:6')]
    public $pin = '';
    
    public $isLoading = false;
    public $showRegularLogin = false;
    
    // For user display
    public $selectedUser = null;
    public $availableUsers = [];

    public function mount()
    {
        // Load users that have PIN set for quick selection
        $this->loadUsersWithPin();
    }

    public function render()
    {
        return view('livewire.pin-login');
    }

    public function loadUsersWithPin()
    {
        $this->availableUsers = User::whereNotNull('pin')
            ->where('is_active', true)
            ->select('id', 'name', 'pin')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'masked_pin' => $user->masked_pin
                ];
            });
    }

    public function selectUser($userId)
    {
        $this->selectedUser = $this->availableUsers->firstWhere('id', $userId);
        $this->pin = '';
        $this->resetValidation();
    }

    public function clearSelectedUser()
    {
        $this->selectedUser = null;
        $this->pin = '';
        $this->resetValidation();
    }

    public function authenticateWithPin()
    {
        $this->validate();
        $this->isLoading = true;

        try {
            // Find user by PIN
            $user = User::where('pin', $this->pin)
                        ->where('is_active', true)
                        ->first();

            if (!$user) {
                $this->addError('pin', 'PIN tidak valid atau user tidak aktif.');
                $this->isLoading = false;
                return;
            }

            // Login the user
            Auth::login($user);

            // Clear form
            $this->pin = '';
            $this->selectedUser = null;

            // Success message
            LivewireAlert::title('Berhasil!')
                ->text("Selamat datang, {$user->name}!")
                ->success()
                ->show();

            // Redirect based on role
            $this->redirectBasedOnRole($user);

        } catch (\Exception $e) {
            \Log::error('PIN Login Error: ' . $e->getMessage());
            
            LivewireAlert::title('Error!')
                ->text('Terjadi kesalahan saat login. Silakan coba lagi.')
                ->error()
                ->show();
        } finally {
            $this->isLoading = false;
        }
    }

    private function redirectBasedOnRole($user)
    {
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('staf')) {
            return redirect()->route('staf.cashier');
        } elseif ($user->hasRole('investor')) {
            return redirect()->route('investor.dashboard');
        }

        // Fallback
        return redirect()->route('dashboard');
    }

    public function toggleLoginMethod()
    {
        $this->showRegularLogin = !$this->showRegularLogin;
        $this->pin = '';
        $this->selectedUser = null;
        $this->resetValidation();
    }

    // Handle number pad input
    public function addDigit($digit)
    {
        if (strlen($this->pin) < 6) {
            $this->pin .= $digit;
            $this->resetValidation('pin');
        }
    }

    public function removeDigit()
    {
        if (strlen($this->pin) > 0) {
            $this->pin = substr($this->pin, 0, -1);
            $this->resetValidation('pin');
        }
    }

    public function clearPin()
    {
        $this->pin = '';
        $this->resetValidation('pin');
    }

    // Helper methods for UI
    public function getPinDisplayProperty()
    {
        return str_repeat('●', strlen($this->pin)) . str_repeat('○', 6 - strlen($this->pin));
    }

    public function getIsPinCompleteProperty()
    {
        return strlen($this->pin) === 6;
    }
}
