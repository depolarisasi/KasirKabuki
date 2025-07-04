<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $transaction->transaction_code }}</title>
    
    <!-- Print-specific CSS -->
    <style>
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            body {
                margin: 0;
                padding: 0;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                line-height: 1.2;
                background: white;
            }
            
            .receipt {
                max-width: 80mm;
                margin: 0 auto;
                padding: 10px;
                background: white;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-after: always;
            }
        }
        
        @media screen {
            body {
                margin: 20px;
                padding: 20px;
                font-family: 'Courier New', monospace;
                font-size: 14px;
                line-height: 1.4;
                background: #f5f5f5;
            }
            
            .receipt {
                max-width: 300px;
                margin: 0 auto;
                padding: 20px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
        }
        
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .store-info {
            font-size: 11px;
            margin-bottom: 2px;
        }
        
        .transaction-info {
            margin: 15px 0;
            font-size: 11px;
        }
        
        .transaction-info div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .items-section {
            margin: 15px 0;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
        }
        
        .item {
            margin-bottom: 8px;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        
        .totals-section {
            margin: 15px 0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .total-row.final {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 8px;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 1px dashed #333;
            padding-top: 10px;
            font-size: 10px;
        }
        
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        
        .print-button button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header Toko -->
        <div class="header">
            <div class="store-name">SATE BRAGA</div>
            <div class="store-info">Jl. Braga No. 123, Bandung</div>
            <div class="store-info">Telp: (022) 123-4567</div>
            <div class="store-info">================================</div>
        </div>

        <!-- Informasi Transaksi -->
        <div class="transaction-info">
            <div>
                <span>No. Transaksi</span>
                <span>{{ $transaction->transaction_code }}</span>
            </div>
            <div>
                <span>Tanggal</span>
                <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span>Kasir</span>
                <span>{{ $transaction->user->name }}</span>
            </div>
            <div>
                <span>Jenis</span>
                <span>{{ $transaction->order_type_label }}</span>
            </div>
            @if($transaction->partner)
                <div>
                    <span>Partner</span>
                    <span>{{ $transaction->partner->name }}</span>
                </div>
            @endif
            <div>
                <span>Pembayaran</span>
                <span>{{ $transaction->payment_method_label }}</span>
            </div>
        </div>

        <!-- Daftar Item -->
        <div class="items-section">
            @foreach($transaction->items as $item)
                <div class="item">
                    <div class="item-name">{{ $item->product_name }}</div>
                    <div class="item-details">
                        <span>{{ $item->quantity }} x {{ number_format($item->product_price, 0, ',', '.') }}</span>
                        <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($item->discount_amount > 0)
                        <div class="item-details">
                            <span>  Diskon</span>
                            <span>-{{ number_format($item->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Total Perhitungan -->
        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal</span>
                <span>{{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
            </div>
            
            @if($transaction->total_discount > 0)
                <div class="total-row">
                    <span>Total Diskon</span>
                    <span>-{{ number_format($transaction->total_discount, 0, ',', '.') }}</span>
                </div>
            @endif
            
            @if($transaction->partner_commission > 0)
                <div class="total-row">
                    <span>Komisi Partner ({{ $transaction->partner->commission_rate }}%)</span>
                    <span>-{{ number_format($transaction->partner_commission, 0, ',', '.') }}</span>
                </div>
            @endif
            
            <div class="total-row final">
                <span>TOTAL</span>
                <span>Rp {{ number_format($transaction->final_total, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Applied Discounts Detail -->
        @if($transaction->discount_details && count($transaction->discount_details) > 0)
            <div style="margin: 10px 0; font-size: 10px;">
                <div style="text-align: center; margin-bottom: 5px;">--- DISKON DITERAPKAN ---</div>
                @foreach($transaction->discount_details as $discount)
                    <div style="display: flex; justify-content: space-between;">
                        <span>{{ $discount['name'] }}</span>
                        <span>
                            @if($discount['value_type'] === 'percentage')
                                {{ $discount['value'] }}%
                            @else
                                Rp {{ number_format($discount['value'], 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>================================</div>
            <div>Terima kasih atas kunjungan Anda</div>
            <div>Selamat menikmati!</div>
            <div style="margin-top: 5px;">Powered by KasirBraga</div>
        </div>
    </div>

    <!-- Print Button (hidden when printing) -->
    <div class="print-button no-print">
        <button onclick="window.print()">🖨️ Cetak Struk</button>
        <button onclick="window.close()" style="background: #6c757d; margin-left: 10px;">✖️ Tutup</button>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
        
        // Close window after printing
        window.onafterprint = function() {
            // Optional: close window after printing
            // window.close();
        };
    </script>
</body>
</html> 