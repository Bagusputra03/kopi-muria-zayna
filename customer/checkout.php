<?php
session_start();
require_once '../includes/config.php';

// 1. Cek apakah keranjang kosong?
if (empty($_SESSION['cart'])) {
    header("Location: keranjang.php");
    exit;
}

// 2. Hitung Total Belanja dari Session
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['harga'] * $item['qty'];
}
$total_bayar = $subtotal; // Tambah biaya lain jika ada
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Kopi Muria Zayna</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #D2691E;
        }
        
        .checkout-step {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }

        /* Styling Custom Radio Button untuk Metode Pembayaran */
        .payment-option {
            position: relative;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-option:hover {
            border-color: var(--secondary-color);
            background: rgba(139, 69, 19, 0.05);
        }

        /* Sembunyikan radio button asli tapi tetap fungsional */
        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 1;
        }

        /* Style saat radio button dipilih */
        .payment-option input[type="radio"]:checked + .payment-content {
            border-color: var(--primary-color);
            background: rgba(139, 69, 19, 0.05);
            color: var(--primary-color);
        }
        
        /* Memberikan border visual pada konten saat dipilih */
        .payment-option:has(input:checked) {
            border-color: var(--primary-color);
            background: rgba(139, 69, 19, 0.05);
        }

        .payment-content {
            padding: 15px;
            display: flex;
            align-items: center;
            border-radius: 10px;
        }
        
        .payment-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .order-summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex justify-content-center mb-5">
                    <div class="d-flex align-items-center text-primary">
                        <div class="step-number">1</div>
                        <a href="keranjang.php" class="text-decoration-none fw-bold">Keranjang</a>
                    </div>
                    <div class="mx-3" style="width: 50px; border-top: 2px solid var(--primary-color);"></div>
                    <div class="d-flex align-items-center text-primary">
                        <div class="step-number">2</div>
                        <span class="fw-bold">Checkout</span>
                    </div>
                    <div class="mx-3" style="width: 50px; border-top: 2px solid #dee2e6;"></div>
                    <div class="d-flex align-items-center text-muted">
                        <div class="step-number" style="background: #dee2e6;">3</div>
                        <span>Selesai</span>
                    </div>
                </div>

                <form action="process_checkout.php" method="POST">
                    <div class="row">
                        <div class="col-lg-8">
                            
                            <div class="checkout-step">
                                <h4 class="mb-4">
                                    <i class="fas fa-user me-2 text-primary"></i>
                                    Informasi Pelanggan
                                </h4>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Nama Pemesan</label>
                                        <input type="text" name="nama_pelanggan" class="form-control" placeholder="Contoh: Budi" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Email</label>
                                        <input type="email" name="email_pelanggan" class="form-control" placeholder="email@contoh.com" required>
                                        <small class="text-muted">*Struk pembayaran akan dikirim ke sini</small>
                                    </div>
                                </div>
                            </div>

                            <div class="checkout-step">
                                <h4 class="mb-4">
                                    <i class="fas fa-credit-card me-2 text-primary"></i>
                                    Metode Pembayaran
                                </h4>
                                
                                <div class="payment-option">
                                    <input type="radio" name="metode_pembayaran" value="qris" id="qris" checked>
                                    <div class="payment-content">
                                        <div class="payment-icon text-success">
                                            <i class="fas fa-qrcode"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold">QRIS / E-Wallet</h6>
                                            <small class="text-muted">Scan QR Code di Kasir (Gopay, OVO, Dana, BCA)</small>
                                        </div>
                                        <i class="fas fa-check-circle text-success ms-2 check-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" name="metode_pembayaran" value="tunai" id="tunai">
                                    <div class="payment-content">
                                        <div class="payment-icon text-warning">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-bold">Tunai (Cash)</h6>
                                            <small class="text-muted">Bayar uang tunai langsung di kasir</small>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="checkout-step">
                                <h4 class="mb-4">Ringkasan Pesanan</h4>
                                
                                <?php if (!empty($_SESSION['cart'])): ?>
                                    <?php foreach ($_SESSION['cart'] as $item): ?>
                                    <div class="order-summary-item">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($item['nama']) ?></h6>
                                            <small class="text-muted"><?= $item['qty'] ?> x Rp <?= number_format($item['harga'], 0, ',', '.') ?></small>
                                        </div>
                                        <span class="fw-bold">Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal</span>
                                        <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Biaya Layanan</span>
                                        <span class="text-success">Gratis</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <strong>Total</strong>
                                        <strong class="text-primary fs-5">Rp <?= number_format($total_bayar, 0, ',', '.') ?></strong>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm">
                                    <i class="fas fa-lock me-2"></i>
                                    BUAT PESANAN
                                </button>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        Pastikan data sudah benar sebelum memesan.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form> </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>