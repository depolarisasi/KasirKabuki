@extends('layouts.app')

@section('title', 'Manajemen Kategori - KasirBraga')

@section('header')
    <h2 class="font-semibold text-xl text-base-content leading-tight">
        {{ __('Manajemen Kategori') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <div class="p-6 text-base-content">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-base-content">Manajemen Kategori</h1>
                        <p class="text-base-content/70 mt-2">Kelola kategori produk untuk sistem kasir</p>
                    </div>
                    
                    {{-- Categories Management Component --}}
                    @livewire('category-management')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 