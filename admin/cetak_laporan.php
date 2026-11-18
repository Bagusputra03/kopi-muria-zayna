<?php
session_start();
require_once '../includes/config.php';
require_once 'auth_check.php';

// --- 1. AMBIL FILTER DARI URL (SAMA SEPERTI DI LAPORAN.PHP) ---
$start_date = $_GET['start'] ?? date('Y-m-01');
$end_date   = $_GET['end'] ?? date('Y-m-d');

// Tambahkan jam agar filter akurat
$sql_start = $start_date . " 00:00:00";
$sql_end   = $end_date . " 23:59:59";

// --- 2. QUERY DATA (COPY DARI LAPORAN.PHP) ---
// Total Pesanan
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE status_pesanan != 'dibatalkan' AND created_at BETWEEN ? AND ?");
$stmt->execute([$sql_start, $sql_end]);
$total_pesanan = $stmt->fetchColumn();

// Total Pendapatan
$stmt = $pdo->prepare("SELECT SUM(total_harga) FROM pesanan WHERE status_pembayaran = 'berhasil' AND created_at BETWEEN ? AND ?");
$stmt->execute([$sql_start, $sql_end]);
$total_pendapatan = $stmt->fetchColumn() ?? 0;

// Produk Terjual
$stmt = $pdo->prepare("SELECT SUM(dp.quantity) FROM detail_pesanan dp JOIN pesanan p ON dp.pesanan_id = p.id WHERE p.status_pembayaran = 'berhasil' AND p.created_at BETWEEN ? AND ?");
$stmt->execute([$sql_start, $sql_end]);
$produk_terjual = $stmt->fetchColumn() ?? 0;

// Produk Terlaris
$query_top = "
    SELECT p.nama_produk, k.nama_kategori, 
           SUM(dp.quantity) as total_qty, 
           SUM(dp.subtotal) as total_omzet
    FROM detail_pesanan dp 
    JOIN produk p ON dp.produk_id = p.id 
    JOIN kategori k ON p.kategori_id = k.id 
    JOIN pesanan ps ON dp.pesanan_id = ps.id 
    WHERE ps.status_pembayaran = 'berhasil' 
    AND ps.created_at BETWEEN ? AND ?
    GROUP BY p.id 
    ORDER BY total_qty DESC 
    LIMIT 20
"; // Limit diperbesar untuk cetak
$stmt = $pdo->prepare($query_top);
$stmt->execute([$sql_start, $sql_end]);
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan - Kopi Muria Zayna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Tampilan Khusus Cetak A4 */
        body { font-family: 'Times New Roman', Times, serif; color: #000; background: #fff; }
        .header { border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        .logo { width: 70px; height: auto; position: absolute; left: 20px; }
        .table thead th { background-color: #f0f0f0 !important; color: #000 !important; border-bottom: 2px solid #000; }
        .ttd-area { margin-top: 50px; float: right; text-align: center; width: 200px; }
        
        @media print {
            @page { size: A4; margin: 2cm; }
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body> <div class="header position-relative">
        <img src="../assets/images/logo.png" class="logo" alt="Logo">
        <h3 class="fw-bold m-0">KOPI MURIA ZAYNA</h3>
        <p class="m-0 small">Jl. Raya Muria No. 123, Kudus, Jawa Tengah</p>
        <p class="m-0 small">Laporan portofolio dengan transaksi demo</p>
    </div>

    <div class="text-center mb-4">
        <h4 class="fw-bold text-uppercase">Laporan Penjualan</h4>
        <p class="mb-0">Periode: <strong><?= date('d F Y', strtotime($start_date)) ?></strong> s/d <strong><?= date('d F Y', strtotime($end_date)) ?></strong></p>
    </div>

    <div class="row mb-4 border py-3 mx-0">
        <div class="col-4 text-center border-end">
            <small class="text-uppercase text-muted">Total Transaksi</small>
            <h5 class="fw-bold mb-0"><?= number_format($total_pesanan) ?></h5>
        </div>
        <div class="col-4 text-center border-end">
            <small class="text-uppercase text-muted">Produk Terjual</small>
            <h5 class="fw-bold mb-0"><?= number_format($produk_terjual) ?> Pcs</h5>
        </div>
        <div class="col-4 text-center">
            <small class="text-uppercase text-muted">Total Pendapatan</small>
            <h5 class="fw-bold mb-0">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h5>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Rincian Produk Terlaris</h5>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th class="text-center">Terjual</th>
                <th class="text-end">Total Omzet</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($top_products) > 0): ?>
                <?php $no = 1; foreach ($top_products as $prod): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($prod['nama_produk']) ?></td>
                    <td><?= $prod['nama_kategori'] ?></td>
                    <td class="text-center"><?= $prod['total_qty'] ?></td>
                    <td class="text-end">Rp <?= number_format($prod['total_omzet'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center py-3">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="ttd-area">
        <p class="mb-5">Kudus, <?= date('d F Y') ?><br>Admin Pengelola,</p>
        <br>
        <p class="fw-bold text-decoration-underline mb-0">Administrator</p>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>