@extends('layouts.app')

@section('title', 'Konfigurasi Toko - KasirBraga')

@section('header')
    <h2 class="font-semibold text-xl text-base-content leading-tight">
        {{ __('Konfigurasi Toko') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <div class="p-6 text-base-content">
                    {{-- Content akan ditampilkan oleh Livewire component --}}
                    <h1 class="text-2xl font-bold text-base-content">Konfigurasi Toko</h1>
                    <p class="text-base-content/70 mt-2">Pengaturan informasi toko dan struk</p>
                    
                    {{-- Placeholder untuk Livewire component yang akan menggantikan ini --}}
                    <div class="mt-6">
                        @livewire('store-config-management')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 