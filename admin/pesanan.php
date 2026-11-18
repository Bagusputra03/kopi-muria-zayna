<?php
session_start();
require_once '../includes/config.php';
require_once 'auth_check.php'; // Pastikan login

// --- LOGIKA UPDATE STATUS (Sama seperti sebelumnya) ---
if (isset($_POST['update_status'])) {
    $id_pesanan = $_POST['pesanan_id'];
    $status_baru = $_POST['status_baru'];
    $stmt = $pdo->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id = ?");
    $stmt->execute([$status_baru, $id_pesanan]);
    header("Location: pesanan.php");
    exit;
}

// --- LOGIKA FILTER ---
$where_clauses = [];
$params = [];
if (!empty($_GET['status'])) {
    $where_clauses[] = "status_pesanan = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['tanggal_mulai'])) {
    $where_clauses[] = "DATE(created_at) >= ?";
    $params[] = $_GET['tanggal_mulai'];
}
if (!empty($_GET['tanggal_selesai'])) {
    $where_clauses[] = "DATE(created_at) <= ?";
    $params[] = $_GET['tanggal_selesai'];
}
$sql = "SELECT * FROM pesanan";
if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pesanan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusColor($status) {
    switch ($status) {
        case 'pending': return 'warning text-dark';
        case 'diproses': return 'info text-white';
        case 'selesai': return 'success';
        case 'dibatalkan': return 'danger';
        default: return 'secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS INI MENYAMAKAN TAMPILAN DENGAN DASHBOARD */
        :root {
            --primary-color: #8B4513;
            --secondary-color: #D2691E;
        }
        
        body { background-color: #f8f9fa; }

        /* Order Card Style */
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-left: 6px solid;
            transition: transform 0.2s;
        }
        .order-card:hover { transform: translateY(-3px); }
        .order-card.pending { border-left-color: #ffc107; }
        .order-card.diproses { border-left-color: #0dcaf0; }
        .order-card.selesai { border-left-color: #198754; }
        .order-card.dibatalkan { border-left-color: #dc3545; }
        
        .info-label { font-size: 0.85rem; color: #6c757d; margin-bottom: 2px; }
        .info-value { font-weight: 600; color: #212529; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-dark">Kelola Pesanan</h2>
                        <p class="text-muted">Kelola semua pesanan pelanggan</p>
                    </div>
                    <?php if(!empty($_GET['status']) || !empty($_GET['tanggal_mulai'])): ?>
                        <a href="pesanan.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-sync me-1"></i>Reset Filter</a>
                    <?php endif; ?>
                </div>

                <div class="card border-0 shadow-sm mb-4 p-3 rounded-3">
                    <form method="GET" action="" class="row g-2">
                        <div class="col-md-3">
                            <select name="status" class="form-select border-0 bg-light">
                                <option value="">Semua Status</option>
                                <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="diproses" <?= (isset($_GET['status']) && $_GET['status'] == 'diproses') ? 'selected' : '' ?>>Diproses</option>
                                <option value="selesai" <?= (isset($_GET['status']) && $_GET['status'] == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="tanggal_mulai" class="form-control border-0 bg-light" value="<?= $_GET['tanggal_mulai'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="tanggal_selesai" class="form-control border-0 bg-light" value="<?= $_GET['tanggal_selesai'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>

                <?php if (count($pesanan_list) > 0): ?>
                    <?php foreach ($pesanan_list as $order): ?>
                        <?php
                            $stmt_item = $pdo->prepare("SELECT dp.*, p.nama_produk FROM detail_pesanan dp JOIN produk p ON dp.produk_id = p.id WHERE dp.pesanan_id = ?");
                            $stmt_item->execute([$order['id']]);
                            $items = $stmt_item->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="order-card <?= $order['status_pesanan'] ?>">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1">#<?= $order['kode_pesanan'] ?></h5>
                                    <small class="text-muted"><i class="far fa-clock me-1"></i> <?= date('d M Y - H:i', strtotime($order['created_at'])) ?></small>
                                </div>
                                
                                <div class="text-end">
                                    <span class="badge bg-<?= getStatusColor($order['status_pesanan']) ?> px-3 py-2 rounded-pill">
                                        <?= strtoupper($order['status_pesanan']) ?>
                                    </span>
                                    <h4 class="text-primary fw-bold mt-2 mb-0">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></h4>
                                </div>
                            </div>

                            <div class="row bg-light rounded p-3 mb-3 mx-0">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <div class="info-label">Pelanggan</div>
                                    <div class="info-value"><?= htmlspecialchars($order['nama_pelanggan']) ?></div>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?= htmlspecialchars($order['email_pelanggan']) ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-label">Pembayaran</div>
                                    <div class="info-value">
                                        <?= strtoupper($order['metode_pembayaran']) ?>
                                        <?php if($order['status_pembayaran'] == 'berhasil'): ?>
                                            <i class="fas fa-check-circle text-success ms-1"></i>
                                        <?php else: ?>
                                            <i class="fas fa-clock text-warning ms-1"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-center">
                                <div class="col-md-8 mb-3 mb-md-0">
                                    <?php foreach ($items as $item): ?>
                                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1" style="font-size: 0.9rem;">
                                            <span><?= $item['nama_produk'] ?> <span class="text-muted">x<?= $item['quantity'] ?></span></span>
                                            <span class="fw-bold text-muted">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="d-grid gap-2">
                                        <?php if ($order['status_pesanan'] == 'pending'): ?>
                                            <a href="kasir_pembayaran.php?id=<?= $order['id'] ?>" class="btn btn-success fw-bold">
                                                <i class="fas fa-check me-2"></i>Proses
                                            </a>
                                            <form method="POST" style="display:contents">
                                                <input type="hidden" name="pesanan_id" value="<?= $order['id'] ?>">
                                                <input type="hidden" name="status_baru" value="dibatalkan">
                                                <button type="submit" name="update_status" class="btn btn-danger" onclick="return confirm('Batalkan pesanan?')">
                                                    <i class="fas fa-times me-2"></i>Batalkan
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="kasir_pembayaran.php?id=<?= $order['id'] ?>" class="btn btn-info text-white fw-bold">
                                                <i class="fas fa-eye me-2"></i>Detail
                                            </a>
                                            <?php if ($order['status_pesanan'] == 'diproses'): ?>
                                                <form method="POST">
                                                    <input type="hidden" name="pesanan_id" value="<?= $order['id'] ?>">
                                                    <input type="hidden" name="status_baru" value="selesai">
                                                    <button type="submit" name="update_status" class="btn btn-success w-100 mt-2">
                                                        <i class="fas fa-check-double me-2"></i>Selesai
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="100" class="mb-3 opacity-50">
                        <p class="text-muted">Tidak ada pesanan ditemukan.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>