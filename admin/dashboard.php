<?php
session_start();
require_once '../includes/config.php';
require_once 'auth_check.php';

// --- 1. AMBIL DATA STATISTIK UTAMA ---
$total_produk   = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$total_pesanan  = $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
$total_pending  = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status_pesanan = 'pending'")->fetchColumn();
$pendapatan     = $pdo->query("SELECT SUM(total_harga) FROM pesanan WHERE status_pembayaran = 'berhasil'")->fetchColumn();

// --- 2. AMBIL 5 PESANAN TERBARU ---
$pesanan_terbaru = $pdo->query("SELECT * FROM pesanan ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// --- 3. AMBIL 5 PRODUK TERPOPULER ---
$query_populer = "
    SELECT p.*, k.nama_kategori, COALESCE(SUM(dp.quantity), 0) as terjual 
    FROM produk p 
    LEFT JOIN detail_pesanan dp ON p.id = dp.produk_id 
    LEFT JOIN kategori k ON p.kategori_id = k.id 
    GROUP BY p.id 
    ORDER BY terjual DESC 
    LIMIT 5
";
$produk_populer = $pdo->query($query_populer)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Kopi Muria Zayna</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #8B4513; --secondary-color: #D2691E; }
        body { background-color: #f8f9fa; }
        
        /* Sidebar Sticky (Agar tidak ikut scroll) */
        .sidebar { 
            background: linear-gradient(180deg, var(--primary-color) 0%, #6B3A0F 100%); 
            color: white; height: 100vh; position: sticky; top: 0; overflow-y: auto; z-index: 1000;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); margin: 5px 0; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.15); color: white; font-weight: bold; }

        /* Card Style */
        .stat-card {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-left: 5px solid; transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card.primary { border-color: var(--primary-color); }
        .stat-card.success { border-color: #28a745; }
        .stat-card.warning { border-color: #ffc107; }
        .stat-card.info { border-color: #0dcaf0; }
        
        .content-card {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: 100%;
        }
        .img-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-dark">Dashboard</h2>
                        <p class="text-muted mb-0">Ringkasan aktivitas toko hari ini</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="dashboard.php" class="btn btn-light border shadow-sm"><i class="fas fa-sync text-muted"></i></a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card primary h-100">
                            <div class="d-flex justify-content-between">
                                <div><h5 class="text-muted small fw-bold">TOTAL PRODUK</h5><h2 class="fw-bold mb-0"><?= number_format($total_produk) ?></h2></div>
                                <div><i class="fas fa-coffee fa-2x text-primary opacity-50"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card success h-100">
                            <div class="d-flex justify-content-between">
                                <div><h5 class="text-muted small fw-bold">TOTAL PESANAN</h5><h2 class="fw-bold mb-0"><?= number_format($total_pesanan) ?></h2></div>
                                <div><i class="fas fa-shopping-cart fa-2x text-success opacity-50"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card warning h-100">
                            <div class="d-flex justify-content-between">
                                <div><h5 class="text-muted small fw-bold">PERLU PROSES</h5><h2 class="fw-bold mb-0"><?= number_format($total_pending) ?></h2></div>
                                <div><i class="fas fa-clock fa-2x text-warning opacity-50"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card info h-100">
                            <div class="d-flex justify-content-between">
                                <div><h5 class="text-muted small fw-bold">PENDAPATAN</h5><h3 class="fw-bold mb-0 text-primary">Rp <?= number_format($pendapatan/1000000, 1, ',', '.') ?>Jt</h3></div>
                                <div><i class="fas fa-wallet fa-2x text-info opacity-50"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    
                    <div class="col-lg-6 mb-4">
                        <div class="content-card">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold m-0"><i class="fas fa-history me-2 text-warning"></i>Pesanan Terbaru</h5>
                            </div>
                            
                            <div class="list-group list-group-flush">
                                <?php if(count($pesanan_terbaru) > 0): ?>
                                    <?php foreach($pesanan_terbaru as $order): ?>
                                    <div class="list-group-item px-0 d-flex justify-content-between align-items-center border-bottom mb-2 pb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-3 text-center" style="width:40px; height:40px;">
                                                <i class="fas fa-receipt text-secondary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">#<?= $order['kode_pesanan'] ?></h6>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($order['nama_pelanggan']) ?> • 
                                                    <span class="text-dark fw-bold">Rp <?= number_format($order['total_harga'],0,',','.') ?></span>
                                                </small>
                                            </div>
                                        </div>
                                        <?php 
                                            $bg = 'secondary';
                                            if($order['status_pesanan'] == 'pending') $bg = 'warning text-dark';
                                            if($order['status_pesanan'] == 'selesai') $bg = 'success';
                                            if($order['status_pesanan'] == 'diproses') $bg = 'info text-white';
                                            if($order['status_pesanan'] == 'dibatalkan') $bg = 'danger';
                                        ?>
                                        <span class="badge bg-<?= $bg ?> rounded-pill" style="font-size: 0.75rem;">
                                            <?= ucfirst($order['status_pesanan']) ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i><br>Belum ada pesanan.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="content-card">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold m-0"><i class="fas fa-star me-2 text-warning"></i>Produk Terpopuler</h5>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light text-secondary small">
                                        <tr>
                                            <th class="border-0 rounded-start ps-3">PRODUK</th>
                                            <th class="border-0">HARGA</th>
                                            <th class="border-0 rounded-end text-end pe-3">TERJUAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($produk_populer) > 0): ?>
                                            <?php foreach($produk_populer as $prod): ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="d-flex align-items-center">
                                                        <img src="../assets/images/<?= $prod['gambar'] ?>" class="img-thumb me-3 shadow-sm border">
                                                        <div>
                                                            <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;"><?= htmlspecialchars($prod['nama_produk']) ?></h6>
                                                            <small class="text-muted" style="font-size: 0.75rem;"><?= $prod['nama_kategori'] ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="fw-bold text-primary">Rp <?= number_format($prod['harga'],0,',','.') ?></td>
                                                <td class="text-end pe-3">
                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                                        <?= $prod['terjual'] ?> Pcs
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center py-5 text-muted">Belum ada data penjualan.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>