{{-- PIN Login Component with Guest Layout Styling --}}
<div>
    <!-- Header -->
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-primary">
            {{ $showRegularLogin ? 'Login dengan Email' : 'Login dengan PIN' }}
        </h2>
        <p class="text-base-content/70 mt-2">
            {{ $showRegularLogin ? 'Silakan masuk dengan akun Anda' : 'Masukkan PIN 6 digit untuk login cepat' }}
        </p>
    </div>

    @if(!$showRegularLogin)
        <!-- PIN Login Interface -->
        <div class="space-y-6">
            <!-- User Selection (if any users have PIN) -->
            @if(count($availableUsers) > 0)
                <div class="space-y-3">
                    <h3 class="font-semibold text-sm text-base-content/70">Pilih User (Opsional)</h3>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach($availableUsers as $user)
                            <button 
                                wire:click="selectUser({{ $user['id'] }})"
                                class="btn btn-outline btn-sm justify-start {{ $selectedUser && $selectedUser['id'] == $user['id'] ? 'btn-primary' : '' }}"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                {{ $user['name'] }} ({{ $user['masked_pin'] }})
                            </button>
                        @endforeach
                    </div>
                    
                    @if($selectedUser)
                        <div class="text-center">
                            <button wire:click="clearSelectedUser" class="btn btn-ghost btn-xs">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Clear Selection
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Selected User Info -->
            @if($selectedUser)
                <div class="alert alert-info">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Login sebagai: <strong>{{ $selectedUser['name'] }}</strong></span>
                </div>
            @endif

            <!-- PIN Display -->
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-semibold">PIN (6 Digit)</span>
                </label>
                <div class="text-center">
                    <div class="text-4xl font-mono tracking-wider mb-4 text-primary">
                        {{ $this->pinDisplay }}
                    </div>
                    @error('pin') 
                        <div class="text-error text-sm mt-2">{{ $message }}</div> 
                    @enderror
                </div>
            </div>

            <!-- Number Pad -->
            <div class="grid grid-cols-3 gap-3 max-w-xs mx-auto mb-6">
                @for($i = 1; $i <= 9; $i++)
                    <button 
                        wire:click="addDigit('{{ $i }}')"
                        class="btn btn-outline btn-lg aspect-square text-xl font-bold hover:btn-primary"
                        {{ strlen($pin) >= 6 ? 'disabled' : '' }}
                    >
                        {{ $i }}
                    </button>
                @endfor
                
                <!-- Bottom row: Clear, 0, Backspace -->
                <button 
                    wire:click="clearPin"
                    class="btn btn-ghost btn-lg aspect-square"
                    {{ strlen($pin) == 0 ? 'disabled' : '' }}
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
                
                <button 
                    wire:click="addDigit('0')"
                    class="btn btn-outline btn-lg aspect-square text-xl font-bold hover:btn-primary"
                    {{ strlen($pin) >= 6 ? 'disabled' : '' }}
                >
                    0
                </button>
                
                <button 
                    wire:click="removeDigit"
                    class="btn btn-ghost btn-lg aspect-square"
                    {{ strlen($pin) == 0 ? 'disabled' : '' }}
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"></path>
                    </svg>
                </button>
            </div>

            <!-- Login Button -->
            <div class="form-control">
                <button 
                    wire:click="authenticateWithPin"
                    class="btn btn-primary w-full"
                    {{ !$this->isPinComplete || $isLoading ? 'disabled' : '' }}
                    wire:loading.attr="disabled"
                    wire:target="authenticateWithPin"
                >
                    @if($isLoading)
                        <span class="loading loading-spinner loading-sm mr-2"></span>
                        Sedang Login...
                    @else
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Login dengan PIN
                    @endif
                </button>
            </div>
        </div>
    @else
        <!-- Regular Login Interface (redirect to Breeze) -->
        <div class="text-center space-y-4">
            <p class="text-base-content/70">
                Anda akan diarahkan ke halaman login reguler...
            </p>
            <div class="form-control">
                <a href="{{ route('login') }}" class="btn btn-primary w-full">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                    Login dengan Email
                </a>
            </div>
        </div>
    @endif

    <!-- Toggle Login Method -->
    <div class="divider">atau</div>
    <button 
        wire:click="toggleLoginMethod"
        class="btn btn-ghost w-full"
    >
        @if($showRegularLogin)
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            Login dengan PIN
        @else
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
            </svg>
            Login dengan Email
        @endif
    </button>
</div>
