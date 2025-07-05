<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Struk - {{ $transaction->transaction_code }}</title>
    
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
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
        }
        
        .store-info {
            font-size: 11px;
            text-align: center;
            margin-bottom: 2px;
        }
        
        .header-text {
            font-size: 11px;
            text-align: center;
            margin: 8px 0;
        }
        
        .order-section {
            text-align: left;
            margin: 10px 0;
        }
        
        .order-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .order-name {
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .order-date {
            font-size: 11px;
            margin-bottom: 5px;
        }
        
        .items-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 11px;
            margin: 8px 0 5px 0;
            padding-bottom: 2px;
            border-bottom: 1px solid #333;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3px;
            font-size: 11px;
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
            font-size: 11px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        
        .total-row.final {
            font-weight: bold;
            font-size: 12px;
            margin-top: 5px;
            padding-top: 3px;
            border-top: 1px solid #333;
        }
        
        .payment-section {
            margin: 8px 0;
            font-size: 11px;
        }
        
        .footer {
            text-align: center;
            margin-top: 10px;
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
            margin: 0 5px;
        }
        
        .print-button button:hover {
            background: #0056b3;
        }
        
        .print-button button.secondary {
            background: #6c757d;
        }
        
        .print-button button.secondary:hover {
            background: #545b62;
        }
        
        .test-indicator {
            background: #ff6b6b;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 10px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        // Use test data if provided, otherwise fallback to current store settings
        $storeSettings = \App\Models\StoreSetting::current();
        $storeName = $testData['store_name'] ?? $storeSettings->store_name;
        $storeAddress = $testData['store_address'] ?? $storeSettings->store_address;
        $storePhone = $testData['store_phone'] ?? $storeSettings->store_phone;
        $receiptHeader = $testData['receipt_header'] ?? $storeSettings->receipt_header;
        $receiptFooter = $testData['receipt_footer'] ?? $storeSettings->receipt_footer;
        $showReceiptLogo = isset($testData['show_receipt_logo']) ? filter_var($testData['show_receipt_logo'], FILTER_VALIDATE_BOOLEAN) : $storeSettings->show_receipt_logo;
    @endphp

    <div class="receipt">
        <!-- Test Indicator -->
        <div class="test-indicator no-print">
            🧪 TEST RECEIPT - PREVIEW ONLY
        </div>

        <!-- Logo (Center) -->
        @if($showReceiptLogo && $storeSettings->receipt_logo_path)
            <div class="logo">
                <img src="{{ asset($storeSettings->receipt_logo_path) }}" alt="Logo">
            </div>
        @endif

        <!-- Nama Toko (Center) -->
        <div class="store-name">{{ $storeName }}</div>

        <!-- Alamat (Center) -->
        @if($storeAddress)
            <div class="store-info">{{ $storeAddress }}</div>
        @endif

        <!-- Nomor Telp (Center) -->
        @if($storePhone)
            <div class="store-info">{{ $storePhone }}</div>
        @endif

        <!-- Header Text -->
        @if($receiptHeader)
            <div class="header-text">{{ $receiptHeader }}</div>
        @endif
 

        <!-- PESANAN Section (Center) -->
        <div class="order-section">
            <div class="order-title">PESANAN</div>
            
            <!-- Nama Pesanan (if any) -->
            @if($transaction->notes)
                <div class="order-name">{{ $transaction->notes }}</div>
            @endif

            <!-- Hari, Tanggal, Jam -->
            <div class="order-date">
                {{ $transaction->created_at->locale('id')->isoFormat('dddd, D MMMM Y HH:mm') }}
            </div>
            <div class="order-name" style="margin-top: 5px;">Transaksi: {{ $transaction->transaction_code }}</div>
            <div class="order-name">Kasir: {{ $transaction->user->name }}</div>
        </div>
 

        <!-- Items Header -->
        <div class="items-header">
            <span class="item-name">Item</span>
            <span class="item-qty">Qty</span>
            <span class="item-total">Total</span>
        </div>

        <!-- Items List -->
        @foreach($transaction->items as $item)
            <div class="item-row">
                <span class="item-name">{{ $item->product_name }}</span>
                <span class="item-qty">{{ $item->quantity }}</span>
                <span class="item-total">Rp. {{ number_format($item->total, 0, ',', '.') }}</span>
            </div>
        @endforeach
 

        <!-- Totals Section -->
        <div class="totals-section">
            <div class="total-row">
                <span style="text-align: right; width: 100%; display: block;">Sub Total: Rp. {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
            </div>
            
            @if($transaction->total_discount > 0)
                <div class="total-row">
                    <span style="text-align: right; width: 100%; display: block;">Discount: -Rp. {{ number_format($transaction->total_discount, 0, ',', '.') }}</span>
                </div>
            @endif
            
            @if($transaction->partner_commission > 0)
                <div class="total-row">
                    <span style="text-align: right; width: 100%; display: block;">Komisi Partner: -Rp. {{ number_format($transaction->partner_commission, 0, ',', '.') }}</span>
                </div>
            @endif
            
            <div class="total-row final">
                <span style="text-align: right; width: 100%; display: block;">Total: Rp. {{ number_format($transaction->final_total, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="payment-section">
            <div class="total-row">
                <span style="text-align: right; width: 100%; display: block;">Payment Type: {{ strtoupper($transaction->payment_method) }}</span>
            </div>
            
            @if($transaction->payment_method === 'cash')
                <div class="total-row">
                    <span style="text-align: right; width: 100%; display: block;">Payment Amount: Rp. {{ number_format($paymentAmount, 0, ',', '.') }}</span>
                </div>
                <div class="total-row">
                    <span style="text-align: right; width: 100%; display: block;">Kembalian: Rp. {{ number_format($kembalian, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Separator -->
        <div class="separator-line">---oOo---</div>

        <!-- Footer -->
        <div class="footer">
            @if($receiptFooter)
                <div>{{ $receiptFooter }}</div>
            @endif
           
            <div style="margin-top: 8px; font-style: italic; color: #666;">
                ⚠️ Ini adalah struk test untuk preview printer
            </div>
        </div>
    </div>

    <!-- Print Button (hidden when printing) -->
    <div class="print-button no-print">
        <button onclick="window.print()">🖨️ Test Print</button>
        <button onclick="window.close()" class="secondary">✖️ Tutup</button>
    </div>

    <script>
        // Auto print when page loads (optional for testing)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
        
        // Close window after printing (optional)
        window.onafterprint = function() {
            // window.close();
        };
    </script>
</body>
</html> 