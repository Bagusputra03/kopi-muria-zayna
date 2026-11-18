<?php
require_once '../includes/config.php';
require_once '../includes/email_helper.php'; 
require_once 'auth_check.php';

$id_pesanan = $_GET['id'];

// Ambil Data Pesanan
$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id = ?");
$stmt->execute([$id_pesanan]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) die("Pesanan tidak ditemukan");

// Ambil Detail Item
$stmt_detail = $pdo->prepare("SELECT dp.*, p.nama_produk FROM detail_pesanan dp JOIN produk p ON dp.produk_id = p.id WHERE dp.pesanan_id = ?");
$stmt_detail->execute([$id_pesanan]);
$items = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);

// --- LOGIKA PEMBAYARAN SUKSES & KURANGI STOK ---
if (isset($_POST['konfirmasi_bayar']) || isset($_POST['konfirmasi_demo'])) {
    try {
        $pdo->beginTransaction(); // Mulai transaksi agar aman

        // Stok dicatat saat checkout. Konfirmasi hanya memperbarui status.
        $update = $pdo->prepare(
            "UPDATE pesanan SET status_pembayaran = 'berhasil', status_pesanan = 'selesai'
             WHERE id = ? AND status_pembayaran = 'pending' AND status_pesanan <> 'dibatalkan'"
        );
        $update->execute([$id_pesanan]);
        $pdo->commit();
        header('Location: pesanan.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack(); // Batalkan jika ada error
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kasir - <?= $order['kode_pesanan'] ?></title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    
    <style>
        body { background-color: #f8f9fa; }
        .card-invoice { max-width: 600px; margin: 30px auto; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: none; }
        .invoice-header { background: #8B4513; color: white; padding: 20px; border-radius: 15px 15px 0 0; text-align: center; }
        .total-amount { font-size: 2.5rem; font-weight: bold; color: #8B4513; }
        .item-list { background: #fdfdfd; border-radius: 10px; padding: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card card-invoice">
            <div class="invoice-header">
                <h4 class="mb-0">Kasir Pembayaran</h4>
                <small>Order #<?= $order['kode_pesanan'] ?></small>
            </div>
            <div class="card-body p-4 text-center">
                
                <h5 class="mb-1"><?= htmlspecialchars($order['nama_pelanggan']) ?></h5>
                <p class="text-muted mb-4"><?= htmlspecialchars($order['email_pelanggan']) ?></p>
                
                <div class="total-amount mb-4">
                    Rp <?= number_format($order['total_harga'], 0, ',', '.') ?>
                </div>
                
                <div class="alert alert-warning d-inline-block px-4">
                    Metode: <strong><?= strtoupper($order['metode_pembayaran']) ?></strong>
                </div>

                <div class="item-list text-start mb-4 border">
                    <?php foreach($items as $item): ?>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span><?= htmlspecialchars($item['nama_produk']) ?> (x<?= $item['quantity'] ?>)</span>
                        <span class="fw-bold">Rp <?= number_format($item['subtotal'],0,',','.') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($order['status_pembayaran'] == 'pending'): ?>
                    
                    <?php if ($order['metode_pembayaran'] == 'qris'): ?>
                        <div class="alert alert-success border-success">
                                <strong><i class="fas fa-flask"></i> MODE DEMO AKTIF</strong><br>
                                Sistem menggunakan token simulasi. Tidak perlu scan sungguhan.
                            </div>
                            
                            <form method="POST">
                                <button type="submit" name="konfirmasi_demo" class="btn btn-primary btn-lg w-100 shadow-sm">
                                    <i class="fas fa-magic me-2"></i> Simulasi Scan Berhasil
                                </button>
                            </form>

                    <?php else: ?>
                        <div class="alert alert-info">
                            Terima uang tunai sejumlah total di atas.
                        </div>
                        <form method="POST">
                            <button type="submit" name="konfirmasi_bayar" class="btn btn-success btn-lg w-100" onclick="return confirm('Uang tunai sudah diterima?')">
                                <i class="fas fa-money-bill-wave me-2"></i> Konfirmasi Uang Diterima
                            </button>
                        </form>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle fa-3x mb-3"></i><br>
                        <h5>TRANSAKSI SELESAI</h5>
                        <p class="mb-0">Status: <strong>LUNAS</strong></p>
                    </div>
                    <a href="pesanan.php" class="btn btn-secondary w-100">Kembali ke Daftar Pesanan</a>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>
</html>