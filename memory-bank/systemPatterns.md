# System Patterns: KasirBraga

## Arsitektur & Pola Desain
- **Pola Umum:** Menggunakan komponen Livewire untuk semua bagian UI yang interaktif. Logika bisnis yang kompleks akan diekstraksi ke dalam *Service Classes* untuk menjaga komponen tetap bersih.
- **Konvensi Kode:** Mengikuti standar PSR-12 untuk PHP.

## Rancangan Struktur Database
- `users` (id, name, email, password, role: 'admin'/'staf')
- `categories` (id, name, description)
- `products` (id, category_id, name, price)
- `partners` (id, name, commission_rate)
- `transactions` (id, user_id, partner_id, transaction_code, total_price, total_discount, final_price, payment_method, category, status)
- `transaction_items` (id, transaction_id, product_id, quantity, price, discount)
- `expenses` (id, user_id, amount, description, date)
- `stock_logs` (id, product_id, user_id, type, quantity, notes)
- `discounts` (id, name, type, value_type, value, product_id)

## Aturan Bisnis Kritis
1.  **Prioritas Diskon:** Diskon produk dihitung terlebih dahulu, baru kemudian diskon total transaksi.
2.  **Transaksi Online:** Tidak ada diskon yang bisa diterapkan untuk transaksi kategori 'Online'. Komisi partner dihitung dari harga jual asli (gross).

---

## Technical Patterns Established (Bug Resolution #20)

### SweetAlert Integration Pattern ✅ UNIVERSAL
**Problem Solved:** "Swal is not defined" errors across all delete components
**Pattern Established:**
```javascript
// Universal timing fix for SweetAlert in all components
document.addEventListener('DOMContentLoaded', function() {
    function waitForSwal(callback) {
        if (typeof window.Swal !== 'undefined') {
            callback();
        } else {
            setTimeout(() => waitForSwal(callback), 100);
        }
    }

    waitForSwal(() => {
        document.addEventListener('livewire:init', () => {
            Livewire.on('confirm-delete', (event) => {
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Apakah Anda yakin ingin menghapus ${event.itemName}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('delete', event.itemId);
                    }
                });
            });
        });
    });
});
```

**Usage:** Apply this pattern in all Livewire components that require delete confirmation

### Payment Validation Pattern ✅ COMPREHENSIVE
**Problem Solved:** Transaction completion button not responding with invalid payments
**Pattern Established:**
```php
// Frontend validation (Blade template)
<button wire:click="completeTransaction" 
        class="btn btn-primary"
        @if($paymentMethod === 'cash' && ($paymentAmount <= 0 || $paymentAmount < $finalTotal))
            disabled
        @endif>
    Selesaikan Transaksi
</button>

// Backend validation (Livewire component)
public function completeTransaction()
{
    if ($this->paymentMethod === 'cash') {
        if ($this->paymentAmount <= 0) {
            Alert::error('Error!', 'Jumlah uang yang diterima harus diisi.');
            return;
        }
        
        if ($this->paymentAmount < $this->finalTotal) {
            Alert::error('Error!', 'Jumlah uang yang diterima tidak mencukupi.');
            return;
        }
    }
    
    // Proceed with transaction...
}
```

**Usage:** Apply comprehensive validation for all payment-related operations

### Silent Operations Pattern ✅ USER EXPERIENCE
**Problem Solved:** Unwanted alerts appearing on page load
**Pattern Established:**
```php
// Separate methods for different contexts
public function mount()
{
    $this->generateReportSilently(); // No alerts for initial load
}

public function generateReport()
{
    // ... generate report logic ...
    Alert::success('Berhasil!', 'Laporan berhasil dibuat.'); // Alert for user action
}

public function generateReportSilently()
{
    // ... same logic without alert ...
    // No Alert::success() call
}

public function setDatePeriod($period)
{
    // ... set date logic ...
    $this->generateReportSilently(); // No alerts for automated actions
}
```

**Usage:** Use silent methods for automatic operations, regular methods for user-initiated actions

### Property Binding Consistency Pattern ✅ LIVEWIRE
**Problem Solved:** Property mismatch between component and view causing console warnings
**Pattern Established:**
```php
// Component property definition
class ExpenseManagement extends Component
{
    public $date = ''; // Use consistent property name
}

// View binding (must match exactly)
<input wire:model="date" type="date" /> // Use same property name
```

**Usage:** Ensure wire:model bindings exactly match component property names

### Enhanced User Feedback Pattern ✅ UX IMPROVEMENT
**Problem Solved:** Users couldn't tell if operations were successful
**Pattern Established:**
```php
// Detailed success messages with context
Alert::success('✅ Berhasil Disimpan!', 
    'Stok akhir berhasil disimpan untuk ' . $successCount . ' produk' .
    '<br><br><strong>Produk yang berhasil:</strong><br>' .
    '• ' . implode('<br>• ', $successProducts)
);

// Error messages with specific information
Alert::error('❌ Error!', 'Terjadi kesalahan sistem: ' . $e->getMessage());

// Warning messages for partial failures
Alert::warning('⚠️ Perhatian!', 'Beberapa produk gagal diinput:<br>• ' . implode('<br>• ', $errors));
```

**Usage:** Provide detailed, contextual feedback for all user operations

## Pattern Application Guidelines

### 🔄 **When to Apply Each Pattern**
1. **SweetAlert Pattern**: All delete operations requiring user confirmation
2. **Payment Validation**: All transaction processing components
3. **Silent Operations**: Report generation, data loading, automated updates
4. **Property Binding**: All Livewire components with form inputs
5. **Enhanced Feedback**: All CRUD operations, batch processes, critical actions

### 🎯 **Pattern Benefits**
- **Consistency**: Uniform behavior across all components
- **Reliability**: Eliminates timing and validation issues
- **User Experience**: Clear feedback and proper confirmations
- **Maintainability**: Reusable patterns for future development
- **Quality**: Reduces bugs and improves system stability 