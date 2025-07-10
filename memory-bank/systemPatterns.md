# System Patterns

## Architecture

### MVC Pattern
KasirBraga menggunakan pola Model-View-Controller (MVC) yang merupakan standar Laravel:
- **Models**: Representasi data dan business logic
- **Views**: Blade templates untuk UI
- **Controllers**: Menangani request dan response

### Service Layer Pattern
Menggunakan service layer untuk memisahkan business logic dari controllers:
- **Services**: Berisi business logic yang kompleks
- **Controllers**: Lebih ramping, fokus pada routing dan response

### Repository Pattern
Untuk beberapa fitur kompleks, menggunakan repository pattern:
- **Repositories**: Abstraksi akses data
- **Services**: Menggunakan repositories untuk operasi data

## Stock Management Systems

### Dual Stock Management
KasirBraga menggunakan dua sistem stock management yang terintegrasi:

1. **StockLog System**
   - Tracking umum untuk semua produk
   - Mencatat setiap perubahan stok (initial, sale, return, adjustment)
   - Single source of truth untuk produk non-sate
   - Menggunakan `StockLog::getCurrentStock()` untuk validasi

2. **StockSate System**
   - Khusus untuk produk sate
   - Tracking stok harian (stok_awal, stok_terjual)
   - Menggunakan `jenis_sate` dan `quantity_effect` untuk konversi unit
   - Validasi menggunakan `StockService::getCurrentStockForSateProduct()`

### Stock Validation Integration
Untuk mengatasi disconnect antara kedua sistem:

1. **StockService::checkStockAvailability()**
   - Mendeteksi produk sate berdasarkan `jenis_sate` dan `quantity_effect`
   - Menggunakan StockSate untuk produk sate
   - Menggunakan StockLog untuk produk non-sate
   - Comprehensive logging untuk debugging

2. **TransactionService Integration**
   - Validasi stok saat menyimpan pesanan
   - Error handling dengan informasi stok yang akurat
   - Backward compatibility dengan sistem yang ada

## Database Structure

### Core Tables
- `users`: Pengguna sistem
- `products`: Produk yang dijual
- `categories`: Kategori produk
- `transactions`: Transaksi penjualan
- `transaction_items`: Item dalam transaksi
- `stock_logs`: Log perubahan stok
- `expenses`: Pengeluaran
- `expense_categories`: Kategori pengeluaran
- `partners`: Mitra bisnis
- `discounts`: Diskon dan promosi

### Stock Management Tables
- `stock_logs`: Tracking semua perubahan stok
- `stock_sates`: Tracking stok khusus untuk produk sate

## Authentication & Authorization

### Authentication
- Laravel Sanctum untuk API authentication
- Session-based authentication untuk web interface
- Remember me functionality

### Authorization
- Role-based access control
- Admin, Cashier, dan Partner roles
- Gate dan Policy untuk permission checks

## Transaction Processing

### Transaction Flow
1. Add items to cart
2. Apply discounts (optional)
3. Choose order type (dine-in, takeaway, online)
4. Choose payment method
5. Complete transaction
6. Print receipt

### Saved Orders
1. Create cart with items
2. Save order with name
3. Validate and reserve stock
4. Load order when needed
5. Complete transaction

## Stock Management

### Stock Operations
- Initial stock input
- Stock reduction on sales
- Stock return on cancellations
- Stock adjustments
- End-of-day reconciliation

### Stock Validation
- Validasi stok saat menyimpan pesanan
- Produk sate: menggunakan StockSate (stok_awal - stok_terjual)
- Produk non-sate: menggunakan StockLog
- Error reporting dengan informasi stok yang akurat

## Partner System

### Partner Types
- Online marketplace
- Individual resellers
- Corporate clients

### Partner Operations
- Partner registration
- Commission calculation
- Sales tracking
- Payment reconciliation

## Discount System

### Discount Types
- Product-specific discounts
- Transaction-level discounts
- Percentage-based discounts
- Fixed amount discounts
- Ad-hoc discounts

## Reporting System

### Report Types
- Sales reports
- Expense reports
- Profit reports
- Stock reports
- Partner commission reports

### Report Formats
- On-screen display
- PDF export
- Excel export
- Print-ready formats

## UI Components

### Admin Dashboard
- Sales overview
- Recent transactions
- Stock alerts
- Expense summary
- Performance charts

### Cashier Interface
- Product catalog
- Cart management
- Payment processing
- Receipt generation
- Saved orders

## Error Handling

### Error Types
- Validation errors
- Business logic errors
- System errors
- Network errors

### Error Responses
- User-friendly messages
- Detailed error codes
- Logging for debugging
- Recovery options

## Logging System

### Log Categories
- User actions
- Transactions
- Stock movements
- System errors
- Security events

### Log Storage
- Database logs
- File logs
- External service logs 