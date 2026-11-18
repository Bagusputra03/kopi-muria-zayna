<?php
session_start();
require_once '../includes/config.php';

// Cek apakah keranjang ada isinya
$has_items = !empty($_SESSION['cart']);
$total_belanja = 0;
$total_qty = 0; // Variabel baru untuk menampung total jumlah barang

// Hitung total di awal agar bisa dipakai di ringkasan
if ($has_items) {
    foreach ($_SESSION['cart'] as $item) {
        $total_belanja += $item['harga'] * $item['qty'];
        $total_qty += $item['qty']; // Menjumlahkan qty setiap item
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Kopi Muria Zayna</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #D2691E;
        }
        
        body { background-color: #f8f9fa; }

        /* Navbar Sederhana */
        .navbar { background-color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: bold; color: var(--primary-color) !important; }

        /* Style Keranjang */
        .cart-card {
            background: white; border-radius: 15px; border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden;
        }
        
        .table thead th {
            background-color: #f1f1f1; border: none;
            color: #555; font-weight: 600; padding: 15px;
        }
        
        .img-product {
            width: 80px; height: 80px; object-fit: cover;
            border-radius: 10px; border: 1px solid #eee;
        }
        
        /* Kontrol Quantity */
        .qty-control {
            display: flex; align-items: center; justify-content: center;
            border: 1px solid #ddd; border-radius: 50px; width: fit-content;
            overflow: hidden; margin: 0 auto;
        }
        .qty-btn {
            background: white; border: none; padding: 5px 15px;
            color: var(--primary-color); font-weight: bold; cursor: pointer;
            transition: 0.3s;
        }
        .qty-btn:hover { background: #f8f9fa; }
        .qty-input {
            width: 40px; text-align: center; border: none; font-weight: bold;
            background: transparent;
        }
        
        /* Tombol Checkout */
        .btn-checkout {
            background-color: var(--primary-color); color: white;
            border-radius: 50px; padding: 12px 30px; font-weight: bold;
            width: 100%; transition: 0.3s; border: none;
        }
        .btn-checkout:hover { background-color: #6B3A0F; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>

    <nav class="navbar navbar-light sticky-top mb-4">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-coffee me-2"></i>Kopi Muria Zayna
            </a>
            <a href="../index.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="fas fa-arrow-left me-2"></i>Kembali Belanja
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row">
            <div class="col-12 mb-4">
                <h3 class="fw-bold text-dark">Keranjang Belanja</h3>
            </div>
        </div>

        <?php if ($has_items): ?>
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="cart-card p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;">Produk</th>
                                        <th style="width: 20%;" class="text-center">Harga</th>
                                        <th style="width: 20%;" class="text-center">Jumlah</th>
                                        <th style="width: 20%;" class="text-end">Total</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['cart'] as $id => $item): 
                                        $subtotal = $item['harga'] * $item['qty'];
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center p-3">
                                                <img src="../assets/images/<?= $item['gambar'] ?>" class="img-product me-3" alt="Produk">
                                                <div>
                                                    <h6 class="mb-1 fw-bold text-dark"><?= htmlspecialchars($item['nama']) ?></h6>
                                                    <small class="text-muted">Kopi Asli Muria</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold text-secondary">
                                            Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="qty-control">
                                                <form action="update_cart.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $id ?>">
                                                    <input type="hidden" name="action" value="minus">
                                                    <button type="submit" class="qty-btn">-</button>
                                                </form>
                                                
                                                <input type="text" class="qty-input" value="<?= $item['qty'] ?>" readonly>
                                                
                                                <form action="update_cart.php" method="POST" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $id ?>">
                                                    <input type="hidden" name="action" value="plus">
                                                    <button type="submit" class="qty-btn">+</button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-dark">
                                            Rp <?= number_format($subtotal, 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center">
                                            <form action="update_cart.php" method="POST">
                                                <input type="hidden" name="id" value="<?= $id ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Hapus item ini?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cart-card p-4">
                        <h5 class="fw-bold mb-4">Ringkasan Pesanan</h5>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Total Barang</span>
                            <span class="fw-bold"><?= $total_qty ?> Pcs</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold">Rp <?= number_format($total_belanja, 0, ',', '.') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="text-muted">Pajak / Layanan</span>
                            <span class="text-success">Gratis</span>
                        </div>
                        
                        <hr class="mb-4">
                        
                        <div class="d-flex justify-content-between mb-4">
                            <h5 class="fw-bold">Total Tagihan</h5>
                            <h4 class="fw-bold text-primary">Rp <?= number_format($total_belanja, 0, ',', '.') ?></h4>
                        </div>

                        <a href="checkout.php" class="btn btn-checkout mb-3">
                            <i class="fas fa-lock me-2"></i>Lanjut Pembayaran
                        </a>
                        
                        <div class="text-center">
                            <small class="text-muted"><i class="fas fa-shield-alt me-1"></i> Pembayaran Aman & Terpercaya</small>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="row justify-content-center mt-5">
                <div class="col-md-6 text-center">
                    <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" alt="Empty Cart" style="width: 150px; opacity: 0.6;" class="mb-4">
                    <h3 class="fw-bold text-secondary">Wah, keranjangmu kosong!</h3>
                    <p class="text-muted mb-4">Sepertinya kamu belum memilih kopi favoritmu. Yuk, lihat katalog kami sekarang.</p>
                    <a href="../index.php" class="btn btn-checkout" style="max-width: 250px;">
                        Mulai Belanja <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>