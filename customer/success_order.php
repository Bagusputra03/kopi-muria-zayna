<?php
session_start();
require_once '../includes/config.php';

// 1. Ambil Kode Pesanan dari URL
$kode_pesanan = $_GET['kode'] ?? '';

if (empty($kode_pesanan)) {
    header("Location: ../index.php");
    exit;
}

// 2. Ambil Data Pesanan dari Database
$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE kode_pesanan = ?");
$stmt->execute([$kode_pesanan]);
$order = $stmt->fetch();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

// 3. Tentukan Link Midtrans (Sandbox vs Production)
// Sesuai dengan settingan di config.php
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Diterima - Kopi Muria Zayna</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    
    <style>
        :root { --primary-color: #8B4513; }
        body { background-color: #f8f9fa; }
        .success-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 50px auto;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="success-card">
        
        <?php if ($order['status_pembayaran'] == 'settlement' || $order['status_pembayaran'] == 'paid'): ?>
            <div class="icon-box"><i class="fas fa-check"></i></div>
            <h2 class="fw-bold mb-3">Pembayaran Berhasil!</h2>
            <p class="text-muted">Terima kasih, pesanan Anda sedang kami proses.</p>
        <?php else: ?>
            <div class="icon-box" style="background: #fff3e0; color: #ef6c00;">
                <i class="fas fa-receipt"></i>
            </div>
            <h2 class="fw-bold mb-3">Pesanan Diterima!</h2>
            <p class="text-muted">Selesaikan pembayaran Anda sekarang.</p>
        <?php endif; ?>

        <hr class="my-4">

        <div class="row text-start mb-4">
            <div class="col-6">
                <small class="text-muted d-block">Kode Pesanan</small>
                <span class="fw-bold text-primary"><?= $order['kode_pesanan'] ?></span>
            </div>
            <div class="col-6 text-end">
                <small class="text-muted d-block">Total Tagihan</small>
                <span class="fw-bold fs-5">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
            </div>
        </div>

        <?php if ($order['metode_pembayaran'] == 'qris' && $order['status_pembayaran'] == 'pending'): ?>
            
            <div class="alert alert-info">QRIS simulasi. Status dapat dikonfirmasi di halaman kasir demo.</div>

        <?php elseif ($order['metode_pembayaran'] == 'tunai'): ?>
            
            <div class="alert alert-info">
                Silakan lakukan pembayaran tunai sebesar <strong>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></strong> saat pesanan diantar/diambil.
            </div>

        <?php endif; ?>

        <a href="../index.php" class="btn btn-outline-secondary w-100 rounded-pill">
            Kembali ke Beranda
        </a>

    </div>
</div>


</body>
</html>