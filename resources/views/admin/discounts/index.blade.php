@extends('layouts.app')

@section('title', 'Manajemen Diskon - KasirBraga')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Manajemen Diskon') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Manajemen Diskon</h1>
                    <p class="text-gray-600 mt-2">Kelola diskon produk dan transaksi</p>
                </div>
                
                {{-- Embedded Livewire Component --}}
                <livewire:discount-management />
            </div>
        </div>
    </div>
</div>
@endsection 