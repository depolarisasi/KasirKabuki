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
                font-family: 'Courier New', monospace !important;
                font-size: 12px !important;
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
                font-family: 'Courier New', monospace;
                font-size: 14px;
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
            text-align: center;
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
            font-size: 12px;
        }
        
        .bluetooth-guide h4 {
            margin: 0 0 10px 0;
            color: #1976d2;
            font-size: 14px;
        }
        
        .bluetooth-guide ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .bluetooth-guide li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    @php
        $storeSettings = \App\Models\StoreSetting::current();
        $paymentAmount = request()->input('payment_amount', $transaction->final_total);
        $kembalian = $transaction->payment_method === 'qris' ? 0 : max(0, $paymentAmount - $transaction->final_total);
    @endphp

    <div class="receipt">
        <!-- Mobile Bluetooth Printing Guide -->
        <div class="bluetooth-guide no-print">
            <h4>📱 Panduan Print Bluetooth (Android)</h4>
            <ol>
                <li>Pastikan printer Bluetooth sudah dipasangkan (paired)</li>
                <li>Tekan tombol "Print Struk" di bawah</li>
                <li>Pilih printer Bluetooth dari dialog print</li>
                <li>Sesuaikan ukuran kertas ke "80mm" jika diperlukan</li>
                <li>Tekan "Print" untuk mencetak</li>
            </ol>
            <p><strong>💡 Tips:</strong> Jika printer tidak muncul, pastikan Bluetooth aktif dan printer dalam mode pairing.</p>
        </div>

        <!-- Logo (Center) -->
        @if($storeSettings->show_receipt_logo && $storeSettings->receipt_logo_path)
            <div class="logo">
                <img src="{{ asset($storeSettings->receipt_logo_path) }}" alt="Logo">
            </div>
        @endif

        <!-- Nama Toko (Center) -->
        <div class="store-name">{{ $storeSettings->store_name }}</div>

        <!-- Alamat (Center) -->
        @if($storeSettings->store_address)
            <div class="store-info">{{ $storeSettings->store_address }}</div>
        @endif

        <!-- Nomor Telp (Center) -->
        @if($storeSettings->store_phone)
            <div class="store-info">{{ $storeSettings->store_phone }}</div>
        @endif

        <!-- Header Text -->
        @if($storeSettings->receipt_header)
            <div class="header-text">{{ $storeSettings->receipt_header }}</div>
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
                <div class="order-name" style="margin-top: 5px;">Transaksi: {{ $transaction->transaction_code }}</div>
                <div class="order-name">Kasir: {{ $transaction->user->name }}</div>
            </div>
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
            @if($storeSettings->receipt_footer)
                <div>{{ $storeSettings->receipt_footer }}</div>
            @endif
            
        </div>
    </div>

    <!-- Enhanced Mobile Print Button -->
    <div class="print-button no-print">
        <button onclick="handlePrint()" id="printBtn">
            🖨️ Print Struk
        </button>
        <button onclick="window.close()" class="secondary">
            ✖️ Tutup
        </button>
    </div>

    <script>
        // Enhanced mobile print handling
        function handlePrint() {
            const printBtn = document.getElementById('printBtn');
            printBtn.innerHTML = '🔄 Printing...';
            printBtn.disabled = true;
            
            // Small delay to ensure UI updates
            setTimeout(() => {
                window.print();
            }, 300);
        }
        
        // Handle print dialog events
        window.addEventListener('beforeprint', function() {
            console.log('Print dialog opening...');
        });
        
        window.addEventListener('afterprint', function() {
            const printBtn = document.getElementById('printBtn');
            printBtn.innerHTML = '🖨️ Print Struk';
            printBtn.disabled = false;
            console.log('Print dialog closed');
        });
        
        // Mobile-specific optimizations
        if (/Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            // Mobile device detected
            console.log('Mobile device detected - optimizing for mobile print');
            
            // Add touch event handling
            document.addEventListener('touchstart', function() {}, false);
            
            // Prevent zoom on double tap for better UX
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function (event) {
                const now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
        }
        
        // Auto-focus on mobile for better accessibility
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 768) {
                const printBtn = document.getElementById('printBtn');
                if (printBtn) {
                    printBtn.focus();
                }
            }
        });
    </script>
</body>
</html> 