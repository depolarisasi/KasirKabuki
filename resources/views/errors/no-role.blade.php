@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-base-200">
    <div class="card w-96 bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex items-center justify-center mb-4">
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Akses Ditolak</span>
                </div>
            </div>
            
            <h2 class="card-title text-center justify-center mb-4">Role Tidak Valid</h2>
            
            <div class="text-center space-y-4">
                <p class="text-base-content/70">{{ $message ?? 'Role pengguna tidak dikenali.' }}</p>
                
                @if(isset($user))
                    <div class="bg-base-200 p-4 rounded-lg text-left">
                        <h3 class="font-semibold mb-2">Debug Info:</h3>
                        <p><strong>Nama:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Role Column:</strong> {{ $user->role ?? 'NULL' }}</p>
                        <p><strong>Spatie Roles:</strong> {{ $user->roles ? $user->roles->pluck('name')->join(', ') : 'No roles assigned' }}</p>
                        <p><strong>Has Admin Role:</strong> {{ $user->hasRole('admin') ? 'YES' : 'NO' }}</p>
                        <p><strong>Has Staff Role:</strong> {{ $user->hasRole('staf') ? 'YES' : 'NO' }}</p>
                    </div>
                @endif
                
                <div class="card-actions justify-center space-x-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-error">Logout</button>
                    </form>
                    
                    <a href="{{ route('login') }}" class="btn btn-primary">Login Ulang</a>
                </div>
                
                <p class="text-sm text-base-content/50">
                    Jika masalah berlanjut, silakan hubungi administrator sistem.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection 