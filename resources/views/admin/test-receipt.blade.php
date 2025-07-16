<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Receipt - Preview Konfigurasi Struk</title>
    
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
                border-radius: 5px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .store-info {
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .divider {
            text-align: center;
            margin: 10px 0;
            border-top: 1px dashed #333;
            padding-top: 5px;
        }
        
        .transaction-info {
            margin: 10px 0;
            font-size: 11px;
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
        
        .footer {
            text-align: center;
            margin-top: 15px;
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
        
        .test-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-size: 12px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="test-note no-print">
        <strong>🧪 TEST PREVIEW STRUK</strong><br>
        Ini adalah preview konfigurasi struk Anda dengan data sample
    </div>

    <div class="receipt">
        <!-- Header Section -->
        <div class="header">
            @if(request('show_receipt_logo') === 'true')
                <div style="margin-bottom: 10px;">
                    <strong>[LOGO TOKO]</strong>
                </div>
            @endif
            
            <div class="store-name">
                {{ request('store_name', 'SATE BRAGA') }}
            </div>
            
            @if(request('store_address'))
                <div class="store-info">{{ request('store_address') }}</div>
            @endif
            
            @if(request('store_phone'))
                <div class="store-info">Telp: {{ request('store_phone') }}</div>
            @endif
        </div>

        <!-- Custom Header -->
        @if(request('receipt_header'))
            <div class="divider">
                {{ request('receipt_header') }}
            </div>
        @endif

        <!-- Transaction Info -->
        <div class="transaction-info">
            <div>No: TRX-TEST-{{ date('ymd') }}-001</div>
            <div>Tanggal: {{ date('d/m/Y H:i') }}</div>
            <div>Kasir: Admin Test</div>
            <div>Pelanggan: Customer Test</div>
            <div>Jenis: Dine In</div>
        </div>

        <div class="divider"></div>

        <!-- Items -->
        <div class="items-header">
            <span>Item</span>
            <span>Qty</span>
            <span>Total</span>
        </div>

        <div class="item-row">
            <span class="item-name">Sate Ayam</span>
            <span class="item-qty">2</span>
            <span class="item-total">30.000</span>
        </div>

        <div class="item-row">
            <span class="item-name">Sate Kambing</span>
            <span class="item-qty">1</span>
            <span class="item-total">20.000</span>
        </div>

        <div class="item-row">
            <span class="item-name">Nasi Putih</span>
            <span class="item-qty">2</span>
            <span class="item-total">6.000</span>
        </div>

        <div class="item-row">
            <span class="item-name">Es Teh Manis</span>
            <span class="item-qty">2</span>
            <span class="item-total">8.000</span>
        </div>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp. 64.000</span>
            </div>
            
            <div class="total-row">
                <span>Diskon (10%):</span>
                <span>- Rp. 6.400</span>
            </div>
            
            <div class="total-row">
                <span>Pajak (10%):</span>
                <span>Rp. 5.760</span>
            </div>
            
            <div class="total-row">
                <span>Biaya Layanan (5%):</span>
                <span>Rp. 2.880</span>
            </div>
            
            <div class="total-row final">
                <span>TOTAL:</span>
                <span>Rp. 66.240</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Payment -->
        <div style="font-size: 11px; margin: 10px 0;">
            <div>Bayar (QRIS): Rp. 70.000</div>
            <div>Kembali: Rp. 3.760</div>
        </div>

        <!-- Custom Footer -->
        @if(request('receipt_footer'))
            <div class="divider">
                {{ request('receipt_footer') }}
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>Terima kasih atas kunjungan Anda!</div>
            <div>Semoga berkenan dan sampai jumpa lagi</div>
            <div style="margin-top: 5px;">{{ date('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    <!-- Print Controls -->
    <div class="print-button no-print">
        <button onclick="window.print()">🖨️ Print Test</button>
        <button onclick="window.close()">❌ Tutup</button>
    </div>

    <script>
        // Auto-focus for keyboard shortcuts
        window.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });

        // Optional: Auto-print when opened (uncomment if needed)
        // window.addEventListener('load', function() {
        //     setTimeout(() => window.print(), 500);
        // });
    </script>
</body>
</html> 