<?php
session_start();
require_once '../includes/config.php';
require_once 'auth_check.php';

// --- 1. LOGIKA RESTORE (KEMBALIKAN PRODUK) ---
if (isset($_GET['restore'])) {
    $id = $_GET['restore'];
    $stmt = $pdo->prepare("UPDATE produk SET is_deleted = 0 WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>alert('Produk berhasil dikembalikan ke Menu Aktif!'); window.location='produk.php?status=archived';</script>";
}

// --- 2. LOGIKA HAPUS (SOFT DELETE & HARD DELETE) ---
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $type = $_GET['type'] ?? 'soft'; // soft = arsip, hard = hapus selamanya

    try {
        if ($type == 'soft') {
            // A. SOFT DELETE (ARSIPKAN)
            // Cek pesanan aktif dulu
            $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM detail_pesanan dp JOIN pesanan p ON dp.pesanan_id = p.id WHERE dp.produk_id = ? AND p.status_pesanan IN ('pending', 'diproses')");
            $stmt_cek->execute([$id]);
            
            if ($stmt_cek->fetchColumn() > 0) {
                header("Location: produk.php?error=active_order");
                exit;
            }

            $stmt = $pdo->prepare("UPDATE produk SET is_deleted = 1 WHERE id = ?");
            $stmt->execute([$id]);
            echo "<script>alert('Produk diarsipkan!'); window.location='produk.php';</script>";

        } elseif ($type == 'hard') {
            // B. HARD DELETE (HAPUS PERMANEN DARI DB)
            $stmt = $pdo->prepare("DELETE FROM produk WHERE id = ?");
            $stmt->execute([$id]);
            echo "<script>alert('Produk dihapus permanen!'); window.location='produk.php?status=archived';</script>";
        }

    } catch (PDOException $e) {
        // Jika gagal hard delete karena foreign key
        if ($e->getCode() == '23000') {
            header("Location: produk.php?status=archived&error=cannot_delete_history");
            exit;
        } else {
            die("Error: " . $e->getMessage());
        }
    }
}

// --- 3. LOGIKA SIMPAN / UPDATE (DENGAN CEK DUPLIKAT) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama_produk']);
    $kategori = $_POST['kategori_id'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $deskripsi = $_POST['deskripsi'];
    $id_produk = $_POST['id'] ?? null;

    // --- VALIDASI NAMA KEMBAR ---
    if (!empty($id_produk)) {
        // CASE EDIT: Cek nama sama TAPI kecualikan diri sendiri (id != id_produk)
        $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE nama_produk = ? AND id != ?");
        $stmt_cek->execute([$nama, $id_produk]);
    } else {
        // CASE TAMBAH BARU: Cek nama sama di seluruh tabel
        $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE nama_produk = ?");
        $stmt_cek->execute([$nama]);
    }

    // Jika ditemukan nama yang sama (> 0)
    if ($stmt_cek->fetchColumn() > 0) {
        header("Location: produk.php?error=duplicate_name");
        exit;
    }
    // ----------------------------

    // Upload Gambar
    $gambar = 'default.jpg';


    if (!empty($id_produk)) {
        // Update
        $sql = "UPDATE produk SET nama_produk=?, kategori_id=?, harga=?, stok=?, deskripsi=?, gambar=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama, $kategori, $harga, $stok, $deskripsi, $gambar, $id_produk]);
    } else {
        // Insert
        $sql = "INSERT INTO produk (nama_produk, kategori_id, harga, stok, deskripsi, gambar, berat, is_deleted) VALUES (?, ?, ?, ?, ?, ?, '200 g', 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama, $kategori, $harga, $stok, $deskripsi, $gambar]);
    }
    header("Location: produk.php");
    exit;
}

// --- 4. DATA VIEW (AKTIF VS ARSIP) ---
$status_view = $_GET['status'] ?? 'active'; // Default tab Active
$is_deleted_val = ($status_view == 'archived') ? 1 : 0;

// Query Produk Sesuai Tab
$stmt = $pdo->prepare("
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    LEFT JOIN kategori k ON p.kategori_id = k.id 
    WHERE p.is_deleted = ? 
    ORDER BY p.id DESC
");
$stmt->execute([$is_deleted_val]);
$produk = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data Pendukung
$kategori_list = $pdo->query("SELECT * FROM kategori")->fetchAll(PDO::FETCH_ASSOC);
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: #8B4513; }
        body { background-color: #f8f9fa; }
        .img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .nav-tabs .nav-link { color: #6c757d; font-weight: bold; border: none; }
        .nav-tabs .nav-link.active { color: var(--primary-color); border-bottom: 3px solid var(--primary-color); background: none; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-dark m-0">Kelola Produk</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProduk">
                        <i class="fas fa-plus me-2"></i>Tambah Produk
                    </button>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <?php if ($_GET['error'] == 'duplicate_name'): ?>
                        <div class="alert alert-danger alert-dismissible fade show border-danger border-2 shadow-sm">
                            <i class="fas fa-exclamation-circle fa-lg me-2"></i>
                            <strong>Gagal Simpan!</strong> Nama produk tersebut sudah ada di database. Silakan gunakan nama lain.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                    <?php elseif ($_GET['error'] == 'active_order'): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <strong>Gagal Arsip!</strong> Produk sedang dipesan (Pending/Proses).
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php elseif ($_GET['error'] == 'cannot_delete_history'): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <strong>Tidak Bisa Hapus Permanen!</strong> Produk ini punya riwayat transaksi. <br>Produk akan tetap disimpan di arsip agar laporan keuangan tidak rusak.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <ul class="nav nav-tabs mb-3 border-bottom-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $status_view == 'active' ? 'active' : '' ?>" href="produk.php?status=active">
                            <i class="fas fa-box-open me-2"></i>Produk Aktif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $status_view == 'archived' ? 'active' : '' ?>" href="produk.php?status=archived">
                            <i class="fas fa-archive me-2"></i>Arsip (Gudang)
                        </a>
                    </li>
                </ul>

                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($produk) > 0): ?>
                                    <?php foreach($produk as $p): ?>
                                    <tr class="<?= $status_view == 'archived' ? 'bg-light text-muted' : '' ?>">
                                        <td>
                                            <img src="../assets/images/<?= $p['gambar'] ?>" class="img-thumb shadow-sm" style="<?= $status_view == 'archived' ? 'filter: grayscale(100%);' : '' ?>">
                                        </td>
                                        <td class="fw-bold"><?= htmlspecialchars($p['nama_produk']) ?></td>
                                        <td><?= $p['nama_kategori'] ?></td>
                                        <td>Rp <?= number_format($p['harga'],0,',','.') ?></td>
                                        <td><?= $p['stok'] ?></td>
                                        
                                        <td class="text-end">
                                            <?php if($status_view == 'active'): ?>
                                                <a href="produk.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-info text-white me-1" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="produk.php?hapus=<?= $p['id'] ?>&type=soft" class="btn btn-sm btn-warning text-dark" onclick="return confirm('Arsipkan produk ini? Produk akan hilang dari menu tapi bisa dikembalikan nanti.');" title="Arsipkan">
                                                    <i class="fas fa-archive"></i>
                                                </a>

                                            <?php else: ?>
                                                <a href="produk.php?restore=<?= $p['id'] ?>" class="btn btn-sm btn-success me-1" title="Kembalikan ke Menu">
                                                    <i class="fas fa-recycle me-1"></i> Pulihkan
                                                </a>
                                                <a href="produk.php?hapus=<?= $p['id'] ?>&type=hard" class="btn btn-sm btn-danger" onclick="return confirm('Hapus PERMANEN? \nPERINGATAN: Jika produk ini pernah terjual, sistem akan menolak penghapusan demi keamanan data.');" title="Hapus Selamanya">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <?php if($status_view == 'active'): ?>
                                                <i class="fas fa-box-open fa-2x mb-2"></i><br>Tidak ada produk aktif.
                                            <?php else: ?>
                                                <i class="fas fa-archive fa-2x mb-2"></i><br>Arsip kosong.
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalProduk" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="produk.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold"><?= $edit_data ? 'Edit Produk' : 'Tambah Produk Baru' ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="window.location='produk.php'"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
                        <input type="hidden" name="gambar_lama" value="<?= $edit_data['gambar'] ?? '' ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Produk</label>
                            <input type="text" name="nama_produk" class="form-control" required value="<?= $edit_data['nama_produk'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kategori</label>
                            <select name="kategori_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach($kategori_list as $kat): ?>
                                    <option value="<?= $kat['id'] ?>" <?= ($edit_data && $edit_data['kategori_id'] == $kat['id']) ? 'selected' : '' ?>>
                                        <?= $kat['nama_kategori'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Harga</label>
                                <input type="number" name="harga" class="form-control" required value="<?= $edit_data['harga'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Stok</label>
                                <input type="number" name="stok" class="form-control" required value="<?= $edit_data['stok'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= $edit_data['deskripsi'] ?? '' ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Gambar</label>
                            <input type="file" name="gambar" class="form-control">
                            <p>*kosongkan jika tidak ingin mengubah gambar</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="produk.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary fw-bold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if($edit_data): ?>
    <script>
        new bootstrap.Modal(document.getElementById('modalProduk')).show();
    </script>
    <?php endif; ?>
</body>
</html>