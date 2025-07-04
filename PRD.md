Product Requirements Document (PRD): KasirBraga
 
1. Pendahuluan

Dokumen ini menguraikan persyaratan produk untuk KasirBraga, sebuah aplikasi Point of Sales (POS) berbasis Progressive Web App (PWA) yang dirancang khusus untuk usaha Sate Braga. Tujuan utama dari aplikasi ini adalah untuk menggantikan sistem kasir sebelumnya (GoKasir) yang akan dihentikan, dengan menyediakan solusi yang sederhana, cepat, andal, dan mudah disesuaikan dengan proses bisnis Sate Braga.
2. Latar Belakang & Masalah

Sate Braga saat ini menggunakan aplikasi GoKasir untuk operasional kasir harian. Dengan dihentikannya layanan GoKasir per 30 Juni, Sate Braga membutuhkan sistem POS baru. Kebutuhan ini menjadi peluang untuk membangun aplikasi internal yang sepenuhnya disesuaikan dengan alur kerja (SOP) yang sudah ada, menghilangkan fitur-fitur yang tidak perlu, dan memberikan kontrol penuh atas data dan fungsionalitas. Aplikasi yang ada di pasaran seringkali terlalu kompleks dan mahal untuk skala usaha kaki lima.

Masalah yang Ingin Diselesaikan:

    Ketiadaan sistem POS setelah GoKasir berhenti beroperasi.

    Kebutuhan akan sistem yang mencatat transaksi, stok, dan pengeluaran secara akurat dalam satu platform.

    Perlunya perhitungan laporan keuangan yang memperhitungkan komisi dari partner penjualan online dan diskon.

    Mempermudah dan mempercepat proses kerja kasir dan staf dapur.

3. Visi & Tujuan Produk

Visi: Menjadi sistem operasional digital yang paling efisien dan andal untuk Sate Braga, memungkinkan pengambilan keputusan berbasis data yang akurat.

Tujuan (Goals):

    Operasional: Menyediakan alat yang cepat dan mudah digunakan untuk pencatatan transaksi penjualan, pengeluaran, dan stok harian, termasuk penerapan diskon.

    Finansial: Menghasilkan laporan pendapatan (bersih dan kotor) dan pengeluaran yang akurat, visual, dan dapat diekspor.

    Stok: Memberikan kontrol dan visibilitas yang jelas terhadap jumlah stok sate yang masuk, terjual, dan tersisa setiap hari.

    Kustomisasi: Memastikan aplikasi dapat dikonfigurasi sesuai kebutuhan unik Sate Braga.

4. Target Pengguna & Hak Akses

Aplikasi akan memiliki dua peran pengguna utama:

    Admin (Pemilik)

        Tugas: Mengelola seluruh aspek aplikasi, menganalisis bisnis melalui laporan, dan melakukan semua tugas Staf.

        Hak Akses: Akses penuh ke semua fitur.

    Staf (Kasir & Staf Dapur)

        Tugas: Menjalankan operasional harian.

        Hak Akses: Terbatas pada fungsi operasional (Transaksi, Stok, Pengeluaran).

5. Deskripsi Fitur & Persyaratan
F1: Pencatatan Transaksi

    Deskripsi: Fitur inti yang memungkinkan staf untuk mencatat semua pesanan pelanggan.

    Persyaratan Fungsional:

        Antarmuka Keranjang Belanja:

            Kasir dapat menambahkan produk dari daftar yang dikelompokkan berdasarkan kategori.

            Kasir dapat mengubah jumlah (quantity) per produk.

            Aplikasi Diskon: Kasir dapat menambahkan diskon ke dalam transaksi (sesuai aturan bisnis yang berlaku).

            Total harga akan ter-update secara otomatis setelah diskon diterapkan.

        Kategori Pesanan:

            Kasir memilih kategori: Dine In, Take Away, Online.

            Jika Online, kasir memilih partner yang relevan. Transaksi online tidak dapat diberikan diskon melalui aplikasi kasir ini.

        Simpan Pesanan:

            Kasir dapat menyimpan keranjang belanjaan aktif dengan nama identifikasi. Pesanan yang tersimpan tidak memiliki batas waktu kedaluwarsa dan harus dihapus secara manual.

        Jenis Pembayaran:

            Kasir memilih metode pembayaran: CASH atau QRIS.

        Penyelesaian & Cetak Struk:

            Setelah transaksi selesai, stok produk akan otomatis berkurang.

            Memicu dialog cetak untuk struk.

            Detail struk mencakup: Header (custom), Nama Item, Qty x Harga Satuan, Subtotal per item, Total Belanja, Detail Diskon, Total Akhir, Metode Pembayaran, Nama Kasir, Tanggal & Jam, Footer (custom).

F2: Manajemen Stok Harian

    Deskripsi: Fitur untuk melacak alur masuk dan keluar produk sate setiap hari.

    Persyaratan Fungsional:

        Input Stok Awal: Staf memasukkan jumlah stok awal.

        Pengurangan Stok Otomatis: Stok berkurang setelah transaksi berhasil.

        Input Stok Akhir: Staf memasukkan sisa stok fisik di akhir hari.

        Laporan Rekonsiliasi Stok: Sistem menampilkan laporan harian: Stok Awal, Terjual, Stok Akhir (manual), dan Selisih. Selisih ini hanya untuk tujuan pencatatan.

F3: Pencatatan Pengeluaran

    Deskripsi: Fitur sederhana untuk mencatat semua biaya operasional harian.

    Persyaratan Fungsional:

        Staf dapat menambahkan catatan pengeluaran (Tanggal, Jumlah, Keterangan).

        Data ini akan menjadi pengurang pada laporan laba rugi.

F4: Konfigurasi (Menu Khusus Admin)

    Deskripsi: Halaman pengaturan untuk mengelola parameter dasar aplikasi.

    Persyaratan Fungsional:

        Konfigurasi Toko: Mengubah informasi header dan footer struk.

        Manajemen Kategori Produk: Admin dapat membuat, mengubah, dan menghapus kategori.

        Manajemen Produk: Admin dapat menambah, mengubah, atau menghapus produk, menetapkan harga, dan mengaitkannya ke sebuah kategori.

        Manajemen Partner Online: Admin dapat mengelola partner online dan persentase komisinya.

        Manajemen Diskon: Admin dapat membuat dan mengelola aturan diskon (Diskon Produk dan Diskon Transaksi).

F5: Pelaporan (Menu Khusus Admin)

    Deskripsi: Fitur untuk melihat rekapitulasi data bisnis secara komprehensif.

    Persyaratan Fungsional:

        Filter Laporan: Semua laporan dapat difilter berdasarkan rentang tanggal yang fleksibel.

        Visualisasi Data: Laporan penjualan menyertakan grafik sederhana (misal: grafik batang dan diagram lingkaran).

        Ekspor ke XLSX: Semua data laporan harus dapat diekspor ke format file .xlsx.

        Laporan Penjualan:

            Menampilkan total pendapatan kotor.

            Menampilkan total diskon yang diberikan.

            Menampilkan rincian penjualan per kategori.

            Menampilkan total komisi yang dibayarkan ke partner online (dihitung dari total harga pesanan asli).

            Menampilkan total pendapatan bersih (Pendapatan Kotor - Diskon - Komisi Partner - Pengeluaran).

        Laporan Pengeluaran & Stok: Daftar pengeluaran dan riwayat rekonsiliasi stok.

6. Alur Pengguna (User Flow)

Alur pengguna tetap sama, dengan penambahan opsi penerapan diskon saat kasir melakukan transaksi (kecuali untuk kategori Online).
7. Persyaratan Non-Fungsional

    Teknologi: Laravel, Blade, Vite, Livewire, DaisyUI, MySQL/MariaDB, PWA.

    Kinerja: Cepat dan responsif.

    Usability: Sederhana, bersih, dan minimalis.

    Keamanan: Login berbasis peran (Admin, Staf).

    Lain-lain: Tidak ada pajak, pembulatan matematis standar.

8. Aturan Bisnis (Business Rules)

    Prioritas Perhitungan Diskon: Jika diskon produk dan diskon transaksi diterapkan bersamaan dalam satu pesanan, sistem akan menghitung diskon produk terlebih dahulu. Total setelah diskon produk kemudian akan menjadi dasar perhitungan untuk diskon transaksi.

    Perlakuan Transaksi Kategori Online: Diskon tidak dapat diterapkan pada transaksi dengan kategori 'Online'. Komisi untuk partner online dihitung dari total harga pesanan asli (gross) yang tercatat di aplikasi partner tersebut.