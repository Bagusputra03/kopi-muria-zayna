<?php
session_start();
require_once '../includes/config.php';

// --- 2. VALIDASI AKSES ---
// Jika bukan metode POST atau keranjang kosong, tendang ke depan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) {
    header("Location: ../index.php");
    exit;
}

// --- 3. AMBIL DATA DARI FORM ---
$nama = strip_tags($_POST['nama_pelanggan']);
$email = filter_var($_POST['email_pelanggan'], FILTER_SANITIZE_EMAIL);
$metode = $_POST['metode_pembayaran'] ?? '';
if (!in_array($metode, ['qris', 'tunai'], true)) {
    http_response_code(400);
    exit('Metode pembayaran demo tidak valid.');
} // 'qris' atau 'tunai'
$kode_pesanan = generateKodePesanan(); // Fungsi ini ada di config.php

// --- 4. HITUNG TOTAL & SIAPKAN ITEM MIDTRANS ---
$total_belanja = 0;
foreach ($_SESSION['cart'] as $pid => $item) {
    $subtotal = $item['harga'] * $item['qty'];
    $total_belanja += $subtotal;

}

try {
    // Mulai Transaksi Database (Biar aman, kalau gagal satu batal semua)
    $pdo->beginTransaction();

    // A. SIMPAN DATA KE TABEL PESANAN
    $sql = "INSERT INTO pesanan (kode_pesanan, nama_pelanggan, email_pelanggan, total_harga, metode_pembayaran, status_pesanan, status_pembayaran) 
            VALUES (?, ?, ?, ?, ?, 'pending', 'pending')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kode_pesanan, $nama, $email, $total_belanja, $metode]);
    
    // Ambil ID Pesanan yang baru saja dibuat
    $pesanan_id = $pdo->lastInsertId();

    // B. Simpan Detail Item & Kurangi Stok
    $sql_detail = "INSERT INTO detail_pesanan (pesanan_id, produk_id, quantity, harga, subtotal) VALUES (?, ?, ?, ?, ?)";
    $stmt_detail = $pdo->prepare($sql_detail);
    
    // Siapkan query untuk mengurangi stok
    $sql_stok = "UPDATE produk SET stok = stok - ? WHERE id = ? AND stok >= ?";
    $stmt_stok = $pdo->prepare($sql_stok);

    foreach ($_SESSION['cart'] as $pid => $item) {
        // Simpan ke detail pesanan
        $stmt_detail->execute([$pesanan_id, $pid, $item['qty'], $item['harga'], ($item['harga'] * $item['qty'])]);
        
        // Eksekusi pengurangan stok produk
        $stmt_stok->execute([$item['qty'], $pid, $item['qty']]);
        if ($stmt_stok->rowCount() !== 1) {
            throw new RuntimeException('Stok tidak mencukupi.');
        }
    }

    if ($metode === 'qris') {
        $update = $pdo->prepare('UPDATE pesanan SET snap_token = ? WHERE id = ?');
        $update->execute(['DEMO-' . $kode_pesanan, $pesanan_id]);
    }

    // Jika semua proses di atas berhasil, simpan permanen (Commit)
    $pdo->commit();
    
    // Kosongkan Keranjang Belanja
    unset($_SESSION['cart']);

    // Redirect ke Halaman Sukses
    header("Location: success_order.php?kode=" . $kode_pesanan);
    exit;

} catch (Exception $e) {
    // Jika ada error, batalkan semua perubahan database (Rollback)
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    
    // Tampilkan pesan error (Bisa dipercantik nanti)
    http_response_code(400);
    exit('Pesanan belum tersimpan. Periksa stok lalu ulangi checkout.');
}
?>