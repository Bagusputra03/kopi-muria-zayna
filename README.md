# Kopi Muria Zayna

Aplikasi e-commerce kuliah berbasis PHP, PDO, MySQL/MariaDB, dan Bootstrap. Demo mencakup katalog, keranjang, checkout, stok, konfirmasi pembayaran oleh kasir, serta laporan omzet dan produk terlaris menurut periode.

## Menjalankan demo lokal

Gunakan PHP 8.1+ dengan PDO MySQL dan MySQL/MariaDB lokal. Dari PowerShell, buat basis data kosong bernama `kopi_portfolio`, lalu impor `database/schema.sql` dan `database/demo.sql` berurutan. Untuk XAMPP, impor dua berkas itu melalui phpMyAdmin. Jika server database menggunakan akun atau port lain, atur variabel `KOPI_DB_HOST`, `KOPI_DB_PORT`, `KOPI_DB_NAME`, `KOPI_DB_USER`, dan `KOPI_DB_PASSWORD` sesuai lingkungan Anda.

```powershell
$env:KOPI_ADMIN_PASSWORD = Read-Host 'Password kasir demo (minimal 12 karakter)'
php database/create_demo_admin.php
php -S 127.0.0.1:8080 router.php
```

Buka http://127.0.0.1:8080 untuk mencoba katalog. Buka http://127.0.0.1:8080/admin/login.php dan masuk sebagai `demo` untuk mengonfirmasi pesanan atau membuka laporan.

## Data dan pembayaran

Data pelanggan, pesanan, produk, dan gambar pada demo adalah contoh. Alur pembayaran publik menggunakan simulasi QRIS atau tunai dan tidak menghubungi Midtrans. Kode checkout versi awal mengintegrasikan Midtrans Snap. Kredensial, endpoint pembayaran eksternal, database operasional dan pengiriman struk email dipisahkan dari demo.

Di laporan, omzet menjumlahkan total transaksi berstatus berhasil. Kuantitas produk terlaris dihitung dari detail transaksi berbayar. Aplikasi ini ditujukan untuk demo lokal.
