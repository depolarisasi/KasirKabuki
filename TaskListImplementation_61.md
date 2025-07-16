# Task List Implementation #61

## Request Overview
Menambahkan fitur konfigurasi dan perhitungan pajak restoran serta service charge pada sistem KasirKabuki:
1. Mengatur besaran % pajak restoran untuk setiap transaksi
2. Mengatur besaran % service charge untuk setiap transaksi

## Analysis Summary
Implementasi akan menambahkan sistem konfigurasi untuk pajak dan service charge yang dapat diatur oleh admin, kemudian mengintegrasikannya ke dalam flow transaksi dengan perhitungan otomatis. Fitur ini akan menambah breakdown cost yang lebih detail dalam sistem POS.

## Implementation Tasks

### Task 1: Database Schema - Tambah Tax & Service Charge Configuration
- [X] Subtask 1.1: Buat migration untuk menambah kolom tax_rate dan service_charge_rate ke store_settings table
- [X] Subtask 1.2: Update default values untuk tax rate (10%) dan service charge (5%)
- [X] Subtask 1.3: Jalankan migration untuk implement schema changes

### Task 2: Update Models - Store Configuration Enhancement
- [X] Subtask 2.1: Update StoreSetting model untuk include tax_rate dan service_charge_rate fields
- [X] Subtask 2.2: Add fillable fields dan validation rules
- [X] Subtask 2.3: Add accessor methods untuk format percentage display
- [X] Subtask 2.4: Add business logic methods untuk tax/service calculation

### Task 3: Update Transaction Service - Calculation Logic
- [X] Subtask 3.1: Update TransactionService::getCartTotals() untuk include tax dan service charge calculation
- [X] Subtask 3.2: Update TransactionService::completeTransaction() untuk save tax dan service amounts
- [X] Subtask 3.3: Update TransactionService::getCheckoutSummary() untuk breakdown display
- [X] Subtask 3.4: Update validation logic untuk ensure proper calculation order

### Task 4: Database Schema - Transaction Tables Enhancement  
- [X] Subtask 4.1: Buat migration untuk add tax_amount, tax_rate, service_charge_amount, service_charge_rate ke transactions table
- [X] Subtask 4.2: Update existing transactions dengan default values (0) untuk backward compatibility
- [X] Subtask 4.3: Add proper indexes untuk performance pada tax/service fields

### Task 5: Update Transaction Models - Enhanced Fields
- [X] Subtask 5.1: Update Transaction model untuk include tax dan service charge fields
- [X] Subtask 5.2: Add fillable fields dan casting untuk decimal precision
- [X] Subtask 5.3: Add accessor methods untuk formatted display
- [X] Subtask 5.4: Update relationship methods jika diperlukan

### Task 6: Admin Configuration Interface - Tax & Service Settings
- [X] Subtask 6.1: Update StoreConfigController untuk handle tax dan service charge settings
- [X] Subtask 6.2: Add validation rules untuk tax/service rate input (0-100%)
- [X] Subtask 6.3: Update store config form dengan tax dan service charge fields
- [X] Subtask 6.4: Add success/error handling untuk config updates

### Task 7: Configuration UI - Admin Interface Enhancement
- [X] Subtask 7.1: Update store config view dengan tax rate input field
- [X] Subtask 7.2: Update store config view dengan service charge input field  
- [X] Subtask 7.3: Add percentage format display dan validation feedback
- [X] Subtask 7.4: Add help text dan explanation untuk tax/service settings

### Task 8: Cashier Interface - Transaction Breakdown Display
- [X] Subtask 8.1: Update CashierComponent untuk display tax dan service breakdown
- [X] Subtask 8.2: Update cart totals display dengan tax dan service amounts
- [X] Subtask 8.3: Update checkout summary dengan detailed breakdown
- [X] Subtask 8.4: Ensure proper formatting dan user-friendly display

### Task 9: Receipt & Reporting Integration - Tax/Service Display
- [X] Subtask 9.1: Update receipt generation untuk include tax dan service amounts
- [X] Subtask 9.2: Update transaction listing dengan tax/service columns
- [X] Subtask 9.3: Update sales report untuk include tax dan service analysis
- [X] Subtask 9.4: Update export functions untuk include tax/service data

### Task 10: Business Logic Validation - Calculation Flow
- [X] Subtask 10.1: Implement proper calculation order: subtotal → discount → tax → service charge → final total
- [X] Subtask 10.2: Add validation untuk prevent negative amounts
- [X] Subtask 10.3: Add rounding logic untuk currency precision
- [X] Subtask 10.4: Test calculation accuracy dengan berbagai scenarios

### Task 11: Testing & Validation - End-to-End Flow
- [X] Subtask 11.1: Test admin configuration functionality
- [X] Subtask 11.2: Test transaction flow dengan tax dan service charge enabled
- [X] Subtask 11.3: Test receipt generation dengan breakdown
- [X] Subtask 11.4: Test reporting accuracy dengan tax/service amounts
- [X] Subtask 11.5: Test edge cases (discount > subtotal, zero amounts, etc.)

### Task 12: Documentation & Final Cleanup
- [X] Subtask 12.1: Update memory-bank documentation dengan tax/service features
- [X] Subtask 12.2: Add comments untuk business logic calculation
- [X] Subtask 12.3: Update API documentation jika ada endpoints affected
- [X] Subtask 12.4: Final testing dan cleanup temporary files

## Notes
- Tax calculation: (Subtotal - Discount) × Tax Rate
- Service charge calculation: (Subtotal - Discount + Tax) × Service Charge Rate  
- Final total: Subtotal - Discount + Tax + Service Charge
- Default tax rate: 10% (PPN Indonesia standard)
- Default service charge: 5% (restaurant standard)
- Both rates should be configurable from 0% to 100%
- Maintain backward compatibility dengan existing transactions
- Ensure proper decimal precision untuk currency calculations 