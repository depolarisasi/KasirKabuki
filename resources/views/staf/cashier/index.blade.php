@extends('layouts.app')

@section('title', 'Point of Sales - KasirBraga')

@section('header')
    <h2 class="font-semibold text-xl text-base-content leading-tight">
        {{ __('Point of Sales') }}
    </h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="card bg-base-100 shadow-xl border border-base-300">
            <div class="card-body">
                <div class="p-4 text-base-content">
                    <div class="mb-6">
                        <h1 class="text-xl font-bold text-base-content">Point of Sales</h1>
                        <p class="text-base-content/70 text-sm">Kasir: {{ auth()->user()->name }}</p>
                    </div>
                    
                    {{-- POS System Component --}}
                    @livewire('cashier-component')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 