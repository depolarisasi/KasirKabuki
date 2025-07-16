<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <title>Struk - {{ $transaction->transaction_code }}</title>
    
    <!-- Enhanced Print-specific CSS with mobile optimizations -->
    <style>
        /* Enhanced print media queries for better mobile compatibility */
        @media print, 
               only all and (pointer: fine), 
               only all and (pointer: coarse), 
               only all and (pointer: none),
               only all and (-webkit-min-device-pixel-ratio:0) and (min-color-index:0),
               only all and (min--moz-device-pixel-ratio:0) and (min-resolution: 3e1dpcm) {
            
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important; 
                line-height: 1.2 !important;
                background: white !important;
                width: 80mm !important;
                max-width: 80mm !important;
            }
            
            .receipt {
                max-width: 80mm !important;
                width: 80mm !important;
                margin: 0 auto !important;
                padding: 8px !important;
                background: white !important;
                box-shadow: none !important;
                border: none !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .bluetooth-guide {
                display: none !important;
            }
        }
        
        /* Mobile screen optimizations */
        @media screen and (max-width: 768px) {
            body {
                margin: 10px;
                padding: 10px; 
                line-height: 1.4;
                background: #f5f5f5;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
            }
            
            .receipt {
                max-width: 300px;
                margin: 0 auto;
                padding: 15px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
        }
        
        /* Desktop screen */
        @media screen and (min-width: 769px) {
            body {
                margin: 20px;
                padding: 20px; 
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
        
        .center {
            text-align: center;
        }
        
        .separator {
            text-align: center;
            margin: 8px 0;
            font-weight: bold;
        }
        
        .separator-line {
            text-align: center;
            margin: 5px 0;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 8px;
        }
        
        .logo img {
            max-height: 40px;
            max-width: 60px;
        }
        
        .store-name { 
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
        }
        
        .store-info { 
            text-align: center;
            margin-bottom: 2px;
        }
        
        .header-text { 
            text-align: center;
            margin: 8px 0;
        }
        
        .order-section {
            text-align: center;
            margin: 10px 0;
        }
        
        .order-title { 
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .order-name { 
            margin-bottom: 3px;
        }
        
        .order-date { 
            margin-bottom: 5px;
        }
        
        .items-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold; 
            margin: 8px 0 5px 0;
            padding-bottom: 2px;
            border-bottom: 1px solid #333;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3px; 
        }
        
        .item-name {
            flex: 1;
            text-align: left;
            padding-right: 5px;
        }
        
        .item-qty {
            width: 30px;
            text-align: center;
        }
        
        .item-total {
            width: 70px;
            text-align: right;
        }
        
        .totals-section {
            margin: 10px 0; 
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .total-row.final {
            font-weight: bold; 
            margin-top: 5px;
            padding-top: 3px;
            border-top: 1px solid #333;
        }
        
        .payment-section {
            margin: 8px 0; 
        }
        
        .footer {
            text-align: center;
            margin-top: 10px; 
        }
        
        .print-button {
            text-align: center;
            margin: 20px 0;
            position: fixed;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        @media screen and (min-width: 769px) {
            .print-button {
                position: static;
                transform: none;
                background: transparent;
                box-shadow: none;
            }
        }
        
        .print-button button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 5px;
            min-width: 100px;
            touch-action: manipulation;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            user-select: none;
        }
        
        .print-button button:hover,
        .print-button button:active {
            background: #0056b3;
            transform: scale(0.98);
        }
        
        .print-button button.secondary {
            background: #6c757d;
        }
        
        .print-button button.secondary:hover,
        .print-button button.secondary:active {
            background: #545b62;
        }
        
        .bluetooth-guide {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0; 
        }
        
        .bluetooth-guide h4 {
            margin: 0 0 10px 0;
            color: #1976d2; 
        }
        
        .bluetooth-guide ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .bluetooth-guide li {
            margin-bottom: 5px;
        }

        body { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 20px; }
        .receipt { max-width: 320px; margin: 0 auto; padding: 20px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .print-button-container { text-align: center; margin-top: 20px; }
        .print-button-container button { background: #007bff; color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-size: 16px; margin: 0 5px; }
        .print-button-container button.secondary { background: #6c757d; }
        .print-button-container button:disabled { background: #aaa; }
        .android-print-btn {
            display: inline-block;
            background: #4CAF50; /* A green color for Android */
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            margin: 0 5px;
            min-width: 100px;
            touch-action: manipulation;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            user-select: none;
        }
        .android-print-btn:hover,
        .android-print-btn:active {
            background: #388E3C;
            transform: scale(0.98);
        }
        .android-instructions {
            margin-top: 15px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    
    @php
        // 1. Kumpulkan semua data yang dibutuhkan oleh JavaScript
        $storeSettings = \App\Models\StoreSetting::current();
        $paymentAmount = request()->input('payment_amount', $transaction->final_total);
        $kembalian = $transaction->payment_method === 'qris' ? 0 : max(0, $paymentAmount - $transaction->final_total);

        $dataForJs = [
            'store' => [
                'name' => $storeSettings->store_name,
                'logo' => $storeSettings->receipt_logo,
                'address' => $storeSettings->store_address,
                'phone' => $storeSettings->store_phone,
                'header' => $storeSettings->receipt_header,
                'footer' => $storeSettings->receipt_footer,
            ],
            'transaction' => [
                'code' => $transaction->transaction_code,
                'notes' => $transaction->notes,
                'date' => ($transaction->transaction_date ?: $transaction->created_at)->locale('id')->isoFormat('D MMM Y, HH:mm'),
                'cashier' => $transaction->user->name ?? 'N/A',
            ],
            'items' => $transaction->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                ];
            }),
            'totals' => [
                'subtotal' => $transaction->subtotal,
                'discount' => $transaction->total_discount,
                'tax_amount' => $transaction->tax_amount,
                'tax_rate' => $transaction->tax_rate,
                'service_charge_amount' => $transaction->service_charge_amount,
                'service_charge_rate' => $transaction->service_charge_rate,
                'final' => $transaction->final_total,
            ],
            'payment' => [
                'method' => strtoupper($transaction->payment_method),
                'amount' => $paymentAmount,
                'change' => $kembalian,
            ]
        ];
    @endphp
    
{{-- Bagian ini hanya untuk tampilan di browser --}}
<div class="receipt" id="receipt-preview">
    @if($storeSettings->show_receipt_logo && $storeSettings->receipt_logo_path)
        <div style="text-align:center; margin-bottom: 10px;">
            <img src="{{ asset($storeSettings->receipt_logo_path) }}" alt="Logo Toko" style="max-width: 80px; max-height: 60px;">
        </div>
    @endif
    <div style="text-align:center; font-weight:bold;">{{ $dataForJs['store']['name'] }}</div>
    <div style="text-align:center;">{{ $dataForJs['store']['address'] }}</div>
    @if($storeSettings->store_phone)
        <div style="text-align:center;">{{ $storeSettings->store_phone }}</div>
    @endif
    @if($storeSettings->receipt_header)
        <div style="text-align:center; margin-top: 5px;">{{ $storeSettings->receipt_header }}</div>
    @endif
    <hr>
    <div>Transaksi: {{ $dataForJs['transaction']['code'] }}</div>
    <div>Tanggal: {{ $dataForJs['transaction']['date'] }}</div>
    <div>Kasir: {{ $dataForJs['transaction']['cashier'] }}</div>
    <hr>
    @foreach($dataForJs['items'] as $item)
        <div>{{ $item['name'] }}</div>
        <div style="display:flex; justify-content:space-between;">
            <span>{{ $item['quantity'] }} x {{ number_format($item['total'] / $item['quantity'], 0, ',', '.') }}</span>
            <span>{{ number_format($item['total'], 0, ',', '.') }}</span>
        </div>
    @endforeach
    <hr>
    <div style="display:flex; justify-content:space-between;">
        <span>Subtotal:</span>
        <span>Rp. {{ number_format($dataForJs['totals']['subtotal'], 0, ',', '.') }}</span>
    </div>
    @if($dataForJs['totals']['discount'] > 0)
        <div style="display:flex; justify-content:space-between;">
            <span>Diskon:</span>
            <span>-Rp. {{ number_format($dataForJs['totals']['discount'], 0, ',', '.') }}</span>
        </div>
    @endif
    @if($dataForJs['totals']['tax_amount'] > 0)
        <div style="display:flex; justify-content:space-between;">
            <span>Pajak ({{ $dataForJs['totals']['tax_rate'] }}%):</span>
            <span>Rp. {{ number_format($dataForJs['totals']['tax_amount'], 0, ',', '.') }}</span>
        </div>
    @endif
    @if($dataForJs['totals']['service_charge_amount'] > 0)
        <div style="display:flex; justify-content:space-between;">
            <span>Biaya Layanan ({{ $dataForJs['totals']['service_charge_rate'] }}%):</span>
            <span>Rp. {{ number_format($dataForJs['totals']['service_charge_amount'], 0, ',', '.') }}</span>
        </div>
    @endif
    <hr>
    <div style="display:flex; justify-content:space-between; font-weight:bold;">
        <span>Total:</span>
        <span>Rp. {{ number_format($dataForJs['totals']['final'], 0, ',', '.') }}</span>
    </div>
    <div style="display:flex; justify-content:space-between;">
        <span>Bayar ({{ $dataForJs['payment']['method'] }}):</span>
        <span>Rp. {{ number_format($dataForJs['payment']['amount'], 0, ',', '.') }}</span>
    </div>
    @if($dataForJs['payment']['change'] > 0)
        <div style="display:flex; justify-content:space-between;">
            <span>Kembalian:</span>
            <span>Rp. {{ number_format($dataForJs['payment']['change'], 0, ',', '.') }}</span>
        </div>
    @endif
    @if($storeSettings->receipt_footer)
        <hr>
        <div style="text-align:center; margin-top: 5px;">{{ $storeSettings->receipt_footer }}</div>
    @endif
    <div style="text-align:center; margin-top: 10px; font-weight:bold;">Terima Kasih</div>
    <div style="text-align:center;">---oOo---</div>
</div>

{{-- Tombol Cetak Baru --}}
<div class="print-button-container">
    @php
        // Build Android Print URL with payment amount parameter
        $paymentAmount = request()->input('payment_amount', $transaction->final_total);
        $androidPrintUrl = route('android.print.response', $transaction->id) . '?payment_amount=' . $paymentAmount;
        $bluetoothSchemeUrl = 'my.bluetoothprint.scheme://' . $androidPrintUrl;
    @endphp
    
    <!-- Android Bluetooth Print Button -->
    <a href="{{ $bluetoothSchemeUrl }}" class="android-print-btn">
        📱 Cetak via Android Bluetooth
    </a>
    
     
</div>
 
</body>
</html>