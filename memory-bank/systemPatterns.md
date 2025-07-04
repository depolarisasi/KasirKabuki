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