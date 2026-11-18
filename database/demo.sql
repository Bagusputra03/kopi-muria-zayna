INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('Kopi Demo', 'Data sintetis portofolio');
INSERT INTO pengaturan (id, nama_toko, alamat_toko, no_telepon, deskripsi)
VALUES (1, 'Kopi Muria Zayna (Demo)', '', '', 'Toko fiktif untuk mencoba alur aplikasi.');
INSERT INTO produk (kategori_id, nama_produk, berat, deskripsi, harga, stok, gambar)
VALUES (1, 'Arabika Demo', '200 g', 'Produk contoh.', 50000, 30, 'default.jpg'),
       (1, 'Robusta Demo', '200 g', 'Produk contoh.', 38000, 24, 'default.jpg');
INSERT INTO pesanan (kode_pesanan, nama_pelanggan, email_pelanggan, total_harga, status_pesanan, metode_pembayaran, status_pembayaran)
VALUES ('DEMO-RPT-1', 'Pelanggan Demo 1', 'demo1@example.test', 100000, 'selesai', 'tunai', 'berhasil'),
       ('DEMO-RPT-2', 'Pelanggan Demo 2', 'demo2@example.test', 38000, 'selesai', 'tunai', 'berhasil');
INSERT INTO detail_pesanan (pesanan_id, produk_id, quantity, harga, subtotal)
VALUES (1,1,2,50000,100000),(2,2,1,38000,38000);
