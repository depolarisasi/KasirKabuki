@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
<div class="container mx-auto px-8 py-4 bg-base-200">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Detail Transaksi</h1>
            <p class="text-white">{{ $transaction->transaction_code }}</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <a href="{{ route('staf.cashier') }}" class="btn btn-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Kasir
            </a>
            <a href="{{ route('receipt.print', $transaction->id) }}" 
               target="_blank" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H3a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-6a2 2 0 00-2-2H7a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
                Print Struk
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Transaction Info -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-success mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Transaksi Berhasil
                </h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Kode Transaksi:</span>
                        <span class="font-bold">{{ $transaction->transaction_code }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Tanggal & Waktu:</span>
                        <span>{{ $transaction->formatted_date }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Kasir:</span>
                        <span>{{ $transaction->user->name ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Jenis Pesanan:</span>
                        <span class="badge badge-info">{{ $transaction->order_type_label }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-base-content/70">Metode Pembayaran:</span>
                        <span class="badge badge-primary">{{ $transaction->payment_method_label }}</span>
                    </div>
                    
                    @if($transaction->partner)
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Partner Online:</span>
                            <span>{{ $transaction->partner->name ?? 'N/A' }} ({{ $transaction->partner->commission_rate ?? 0 }}%)</span>
                        </div>
                    @endif
                    
                    @if($transaction->notes)
                        <div class="flex justify-between">
                            <span class="text-base-content/70">Catatan:</span>
                            <span>{{ $transaction->notes }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction Items -->
        <div class="card bg-base-300 shadow-lg">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Item Transaksi
                </h2>
                
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach($transaction->items as $item)
                        <div class="flex justify-between items-center p-3 bg-base-200 rounded-lg">
                            <div class="flex-1">
                                <h4 class="font-semibold">{{ $item->product_name }}</h4>
                                <p class="text-sm text-base-content/70">
                                    {{ $item->quantity }} x {{ $item->formatted_price }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="font-bold">{{ $item->formatted_total }}</div>
                                @if($item->discount_amount > 0)
                                    <div class="text-sm text-success">
                                        -{{ $item->formatted_discount }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Summary -->
    <div class="card bg-base-300 shadow-lg mt-6">
        <div class="card-body">
            <h2 class="card-title mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Ringkasan Pembayaran
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>{{ $transaction->formatted_total_price }}</span>
                    </div>
                    
                    @if($transaction->total_discount > 0)
                        <div class="flex justify-between text-success">
                            <span>Total Diskon:</span>
                            <span>-{{ $transaction->formatted_total_discount }}</span>
                        </div>
                    @endif
                    
                    @if($transaction->partner && $transaction->partner_commission > 0)
                        <div class="flex justify-between text-warning">
                            <span>Komisi Partner:</span>
                            <span>-{{ $transaction->formatted_partner_commission }}</span>
                        </div>
                    @endif
                    
                    <div class="border-t pt-2">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total Pembayaran:</span>
                            <span class="text-primary">{{ $transaction->formatted_final_total }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-2">
                    @if($transaction->partner && $transaction->partner_commission > 0)
                        <div class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="font-bold">Pendapatan Bersih</div>
                                <div class="text-sm">{{ $transaction->formatted_net_revenue }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 