<?php
session_start();
require_once '../includes/config.php';
require_once 'auth_check.php';

// --- 1. LOGIKA FILTER TANGGAL ---
$periode = $_GET['periode'] ?? 'month';
$start_date = $_GET['start'] ?? date('Y-m-01'); // Default: Awal bulan ini
$end_date = $_GET['end'] ?? date('Y-m-d');      // Default: Hari ini

// Jika user memilih filter preset (bukan custom), kita hitung tanggalnya di sini
if (isset($_GET['periode'])) {
    switch ($_GET['periode']) {
        case 'today':
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
            break;
        case 'week':
            $start_date = date('Y-m-d', strtotime('-7 days'));
            $end_date = date('Y-m-d');
            break;
        case 'month':
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
            break;
        case 'year':
            $start_date = date('Y-01-01');
            $end_date = date('Y-12-31');
            break;
        // Case 'custom': Kita pakai data dari $_GET['start'] dan $_GET['end'] yang dikirim form
    }
}

// Tambahkan jam untuk query SQL
$sql_start = $start_date . " 00:00:00";
$sql_end = $end_date . " 23:59:59";

// --- 2. QUERY STATISTIK ---
// Total Pesanan
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE status_pesanan != 'dibatalkan' AND created_at BETWEEN ? AND ?");
$stmt->execute([$sql_start, $sql_end]);
$total_pesanan = $stmt->fetchColumn();

// Total Pendapatan
$stmt = $pdo->prepare("SELECT SUM(total_harga) FROM pesanan WHERE status_pembayaran = 'berhasil' AND created_at BETWEEN ? AND ?");
$stmt->execute([$sql_start, $sql_end]);
$total_pendapatan = $stmt->fetchColumn() ?? 0;

// Produk Terjual
$stmt = $pdo->prepare("
    SELECT SUM(dp.quantity) 
    FROM detail_pesanan dp 
    JOIN pesanan p ON dp.pesanan_id = p.id 
    WHERE p.status_pembayaran = 'berhasil' AND p.created_at BETWEEN ? AND ?
");
$stmt->execute([$sql_start, $sql_end]);
$produk_terjual = $stmt->fetchColumn() ?? 0;

// Total Pelanggan
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT email_pelanggan) FROM pesanan WHERE created_at BETWEEN ? AND ?");
$stmt->execute([$sql_start, $sql_end]);
$total_pelanggan = $stmt->fetchColumn();

// --- 3. TABEL PRODUK TERLARIS ---
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
    LIMIT 5
";
$stmt = $pdo->prepare($query_top);
$stmt->execute([$sql_start, $sql_end]);
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin Kopi Muria</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #8B4513; --secondary-color: #D2691E; }
        body { background-color: #f8f9fa; }
        .report-card { background: white; border-radius: 15px; padding: 25px; margin-bottom: 20px; box-shadow: 0 3px 10px rgba(0,0,0,0.05); }
        .stat-number { font-size: 2rem; font-weight: bold; color: var(--primary-color); }
        .date-filter { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 3px 10px rgba(0,0,0,0.05); }
        /* Style untuk input yang disabled agar terlihat jelas */
        input:disabled { background-color: #e9ecef; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-dark">Laporan Penjualan</h2>
                        <p class="text-muted mb-0">Analisis performa bisnis pada periode terpilih</p>
                    </div>
                    <a href="cetak_laporan.php?start=<?= $start_date ?>&end=<?= $end_date ?>" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-print me-2"></i>Cetak Laporan PDF
                    </a>
                </div>

                <div class="date-filter">
                    <form method="GET" action="" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Periode Cepat</label>
                                <select name="periode" id="periodeSelect" class="form-select">
                                    <option value="today" <?= $periode == 'today' ? 'selected' : '' ?>>Hari Ini</option>
                                    <option value="week" <?= $periode == 'week' ? 'selected' : '' ?>>7 Hari Terakhir</option>
                                    <option value="month" <?= $periode == 'month' ? 'selected' : '' ?>>Bulan Ini</option>
                                    <option value="year" <?= $periode == 'year' ? 'selected' : '' ?>>Tahun Ini</option>
                                    <option value="custom" <?= $periode == 'custom' ? 'selected' : '' ?>>Kustom (Pilih Tanggal)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Dari Tanggal</label>
                                <input type="date" name="start" id="startDate" class="form-control" value="<?= $start_date ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Sampai Tanggal</label>
                                <input type="date" name="end" id="endDate" class="form-control" value="<?= $end_date ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i>Terapkan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="report-card text-center h-100">
                            <div class="text-primary mb-2"><i class="fas fa-shopping-bag fa-2x"></i></div>
                            <h6 class="text-muted text-uppercase small fw-bold">Total Pesanan</h6>
                            <div class="stat-number"><?= number_format($total_pesanan) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="report-card text-center h-100">
                            <div class="text-success mb-2"><i class="fas fa-wallet fa-2x"></i></div>
                            <h6 class="text-muted text-uppercase small fw-bold">Total Omzet</h6>
                            <div class="stat-number" style="font-size: 1.5rem;">
                                Rp <?= number_format($total_pendapatan, 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="report-card text-center h-100">
                            <div class="text-warning mb-2"><i class="fas fa-coffee fa-2x"></i></div>
                            <h6 class="text-muted text-uppercase small fw-bold">Produk Terjual</h6>
                            <div class="stat-number"><?= number_format($produk_terjual) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="report-card text-center h-100">
                            <div class="text-info mb-2"><i class="fas fa-users fa-2x"></i></div>
                            <h6 class="text-muted text-uppercase small fw-bold">Pelanggan</h6>
                            <div class="stat-number"><?= number_format($total_pelanggan) ?></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="report-card">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold">Produk Terlaris (Top 5)</h5>
                                <small class="text-muted">Periode: <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?></small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Nama Produk</th>
                                            <th>Kategori</th>
                                            <th class="text-center">Terjual (Qty)</th>
                                            <th class="text-end">Total Pendapatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($top_products) > 0): ?>
                                            <?php foreach ($top_products as $prod): ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($prod['nama_produk']) ?></td>
                                                <td><span class="badge bg-light text-secondary border"><?= $prod['nama_kategori'] ?></span></td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary rounded-pill px-3"><?= $prod['total_qty'] ?></span>
                                                </td>
                                                <td class="text-end fw-bold text-success">
                                                    Rp <?= number_format($prod['total_omzet'], 0, ',', '.') ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">Belum ada data penjualan pada periode ini.</td>
                                            </tr>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const periodeSelect = document.getElementById('periodeSelect');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');

            function checkPeriode() {
                if (periodeSelect.value === 'custom') {
                    // Jika Kustom, aktifkan input tanggal
                    startDate.disabled = false;
                    endDate.disabled = false;
                } else {
                    // Jika Preset (Hari ini, Bulan ini, dll), matikan input tanggal
                    startDate.disabled = true;
                    endDate.disabled = true;
                }
            }

            // Jalankan saat halaman pertama dimuat
            checkPeriode();

            // Jalankan setiap kali user mengubah pilihan dropdown
            periodeSelect.addEventListener('change', checkPeriode);
        });
    </script>
</body>
</html>