{{-- Title handled by component layout --}}
<div>
    <!-- Header -->
    <div class="text-center mb-6">
        <div class="flex justify-center mb-4">
            <img src="{{ asset('assets/logo-150x75.png') }}" alt="KasirBraga" class="h-12 w-auto">
        </div>
        <h2 class="text-2xl font-bold text-primary">Selamat Datang</h2>
        <p class="text-base-content/70 mt-2">Silakan masuk dengan akun Anda</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-info mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-4">
        <!-- Email Address -->
        <div class="form-control">
            <label class="label" for="email">
                <span class="label-text font-semibold">Email</span>
            </label>
            <input wire:model="form.email" 
                   id="email" 
                   type="email" 
                   name="email" 
                   class="input input-bordered w-full @error('form.email') input-error @enderror" 
                   placeholder="Masukkan email Anda"
                   required 
                   autofocus 
                   autocomplete="username" />
            @error('form.email')
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-control">
            <label class="label" for="password">
                <span class="label-text font-semibold">Password</span>
            </label>
            <input wire:model="form.password" 
                   id="password" 
                   type="password" 
                   name="password" 
                   class="input input-bordered w-full @error('form.password') input-error @enderror" 
                   placeholder="Masukkan password Anda"
                   required 
                   autocomplete="current-password" />
            @error('form.password')
                <label class="label">
                    <span class="label-text-alt text-error">{{ $message }}</span>
                </label>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3">
                <input wire:model="form.remember" 
                       id="remember" 
                       type="checkbox" 
                       name="remember" 
                       class="checkbox checkbox-primary" />
                <span class="label-text">Ingat saya</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="form-control mt-6">
            <button type="submit" class="btn btn-primary w-full">
                <svg wire:loading wire:target="login" class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span wire:loading.remove wire:target="login">Masuk</span>
                <span wire:loading wire:target="login">Sedang masuk...</span>
            </button>
        </div>

        <!-- Forgot Password Link -->
        @if (Route::has('password.request'))
            <div class="text-center mt-4">
                <a href="{{ route('password.request') }}" 
                   wire:navigate 
                   class="link link-primary text-sm">
                    Lupa password?
                </a>
            </div>
        @endif
    </form>

    <!-- Alternative Login Method -->
    <div class="divider">atau</div>
    
    <div class="text-center mb-4">
        <a href="{{ route('pin-login') }}" 
           class="btn btn-outline btn-block">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            Login dengan PIN
        </a>
        <p class="text-xs text-base-content/60 mt-2">
            Login cepat menggunakan 6 digit PIN
        </p>
    </div>
 
</div> 