<?php
require_once '../includes/config.php';

if (!isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID missing']);
    exit;
}

$product_id = (int) $_POST['product_id'];

// Ambil produk dari database
$stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->execute([$product_id]);
$produk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produk) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit;
}

// Jika keranjang belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Jika produk sudah ada di keranjang → tambah qty
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['qty']++;
} else {
    // Produk baru
    $_SESSION['cart'][$product_id] = [
        'nama' => $produk['nama_produk'],
        'harga' => $produk['harga'],
        'qty' => 1,
        'gambar' => $produk['gambar']
    ];
}

echo json_encode([
    'status' => 'success',
    'count' => array_sum(array_column($_SESSION['cart'], 'qty'))
]);
exit;
