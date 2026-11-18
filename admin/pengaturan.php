<?php
session_start();
require_once '../includes/config.php';
require_once 'auth_check.php';

// --- 1. AMBIL DATA PENGATURAN SAAT INI ---
// Kita ambil ID 1 karena pengaturan toko hanya ada 1 baris
$stmt = $pdo->query("SELECT * FROM pengaturan WHERE id = 1");
$toko = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika data kosong (user lupa insert SQL), beri default array agar tidak error
if (!$toko) {
    $toko = [
        'nama_toko' => 'Kopi Muria Zayna',
        'no_telepon' => '',
        'alamat_toko' => '',
        'deskripsi' => ''
    ];
}

// --- 2. LOGIKA SIMPAN INFORMASI TOKO ---
if (isset($_POST['simpan_info'])) {
    $nama      = $_POST['nama_toko'];
    $telepon   = $_POST['no_telepon'];
    $alamat    = $_POST['alamat_toko'];
    $deskripsi = $_POST['deskripsi'];

    try {
        // Cek apakah data sudah ada
        $cek = $pdo->query("SELECT COUNT(*) FROM pengaturan WHERE id = 1")->fetchColumn();

        if ($cek > 0) {
            // Update Data
            $sql = "UPDATE pengaturan SET nama_toko=?, no_telepon=?, alamat_toko=?, deskripsi=? WHERE id=1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $telepon, $alamat, $deskripsi]);
        } else {
            // Insert Data Baru (Jaga-jaga)
            $sql = "INSERT INTO pengaturan (id, nama_toko, no_telepon, alamat_toko, deskripsi) VALUES (1, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $telepon, $alamat, $deskripsi]);
        }
        
        echo "<script>alert('Informasi Toko Berhasil Disimpan!'); window.location='pengaturan.php';</script>";
        
    } catch (Exception $e) {
        echo "<script>alert('Gagal menyimpan: " . $e->getMessage() . "');</script>";
    }
}

// --- 3. LOGIKA GANTI PASSWORD (SESUAI TABEL ADMIN) ---
if (isset($_POST['ganti_password'])) {
    $pass_lama  = $_POST['pass_lama'];
    $pass_baru  = $_POST['pass_baru'];
    $konfirmasi = $_POST['konfirmasi_pass'];
    
    // Ambil ID dari session, atau default ke 1 (karena admin cuma satu)
    $id_admin = $_SESSION['user']['id'] ?? $_SESSION['admin']['id'] ?? 1;

    try {
        // PERBAIKAN DI SINI: Menggunakan tabel 'admin' (bukan users)
        $stmt = $pdo->prepare("SELECT password FROM admin WHERE id = ?");
        $stmt->execute([$id_admin]);
        $data_admin = $stmt->fetch();

        if ($data_admin && password_verify($pass_lama, $data_admin['password'])) {
            if ($pass_baru === $konfirmasi) {
                $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
                
                // Update ke tabel 'admin'
                $update = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $update->execute([$hash_baru, $id_admin]);
                
                // Logout user agar login ulang
                echo "<script>alert('Password berhasil diubah! Silakan login ulang.'); window.location='logout.php';</script>";
            } else {
                echo "<script>alert('Password baru dan konfirmasi tidak cocok!');</script>";
            }
        } else {
            echo "<script>alert('Password lama SALAH!');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error Database: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Kopi Muria Zayna</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* --- STYLE GLOBAL & SIDEBAR (Wajib Ada) --- */
        :root { --primary-color: #8B4513; --secondary-color: #D2691E; }
        body { background-color: #f8f9fa; }
    
        /* --- STYLE KHUSUS HALAMAN PENGATURAN --- */
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        /* Scoped CSS agar tidak merusak sidebar utama */
        .settings-card .nav-pills .nav-link {
            color: var(--primary-color);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .settings-card .nav-pills .nav-link:hover {
            background-color: rgba(139, 69, 19, 0.05);
        }

        .settings-card .nav-pills .nav-link.active {
            background: var(--primary-color);
            color: white;
        }
        
        .settings-section {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 25px;
            margin-bottom: 25px;
        }
        
        .settings-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            
            <?php include 'sidebar.php'; ?>

            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-dark">Pengaturan Sistem</h2>
                        <p class="text-muted mb-0">Kelola informasi toko dan konfigurasi aplikasi</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="settings-card">
                            <div class="nav flex-column nav-pills" role="tablist">
                                <a class="nav-link active" href="#umum" data-bs-toggle="pill">
                                    <i class="fas fa-store me-2" style="width:20px"></i> Informasi Toko
                                </a>
                                <a class="nav-link" href="#pembayaran" data-bs-toggle="pill">
                                    <i class="fas fa-credit-card me-2" style="width:20px"></i> Pembayaran
                                </a>
                                <a class="nav-link" href="#notifikasi" data-bs-toggle="pill">
                                    <i class="fas fa-bell me-2" style="width:20px"></i> Notifikasi
                                </a>
                                <a class="nav-link" href="#keamanan" data-bs-toggle="pill">
                                    <i class="fas fa-shield-alt me-2" style="width:20px"></i> Keamanan
                                </a>
                                <a class="nav-link" href="#backup" data-bs-toggle="pill">
                                    <i class="fas fa-database me-2" style="width:20px"></i> Backup Data
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="tab-content">
                            
                            <div class="tab-pane fade show active" id="umum">
                                <div class="settings-card">
                                    <h4 class="mb-4 fw-bold text-primary border-bottom pb-3">
                                        <i class="fas fa-store me-2"></i> Informasi Toko
                                    </h4>
                                    
                                    <form method="POST" action="">
                                        <div class="settings-section">
                                            <h5 class="fw-bold mb-3">Informasi Dasar</h5>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Nama Toko</label>
                                                    <input type="text" name="nama_toko" class="form-control" value="<?= htmlspecialchars($toko['nama_toko']) ?>" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Nomor Telepon / WA</label>
                                                    <input type="text" name="no_telepon" class="form-control" value="<?= htmlspecialchars($toko['no_telepon']) ?>">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Alamat Lengkap</label>
                                                <textarea name="alamat_toko" class="form-control" rows="3"><?= htmlspecialchars($toko['alamat_toko']) ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Deskripsi Singkat</label>
                                                <textarea name="deskripsi" class="form-control" rows="2"><?= htmlspecialchars($toko['deskripsi']) ?></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="text-end">
                                            <button type="submit" name="simpan_info" class="btn btn-primary px-4">
                                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="pembayaran">
                                <div class="settings-card">
                                    <h4 class="mb-4 fw-bold text-primary border-bottom pb-3">
                                        <i class="fas fa-credit-card me-2"></i> Pengaturan Pembayaran
                                    </h4>
                                    
                                    <div class="settings-section">
                                        <h5 class="fw-bold mb-3">Midtrans Payment Gateway</h5>
                                        <div class="alert alert-info border-0 shadow-sm">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Koneksikan dengan akun Midtrans untuk menerima pembayaran QRIS & E-Wallet otomatis.
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Server Key</label>
                                                <input type="password" class="form-control" value="SB-Mid-server-xxxxxxxx">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Client Key</label>
                                                <input type="password" class="form-control" value="SB-Mid-client-xxxxxxxx">
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="sandboxMode" checked>
                                            <label class="form-check-label" for="sandboxMode">
                                                Aktifkan <strong>Sandbox Mode</strong> (Mode Testing)
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="settings-section">
                                        <h5 class="fw-bold mb-3">Metode Pembayaran Aktif</h5>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="qris" checked>
                                                    <label class="form-check-label" for="qris">QRIS (Gopay/OVO/Dana)</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="cash" checked>
                                                    <label class="form-check-label" for="cash">Tunai (Bayar di Kasir)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button class="btn btn-primary px-4">
                                            <i class="fas fa-save me-2"></i>Simpan Konfigurasi
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="notifikasi">
                                <div class="settings-card">
                                    <h4 class="mb-4 fw-bold text-primary border-bottom pb-3">
                                        <i class="fas fa-bell me-2"></i> Pengaturan Notifikasi
                                    </h4>
                                    
                                    <div class="settings-section">
                                        <h5 class="fw-bold mb-3">Notifikasi Email (Pelanggan)</h5>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="emailOrders" checked>
                                            <label class="form-check-label" for="emailOrders">
                                                Kirim email konfirmasi saat pesanan dibuat
                                            </label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="emailPayments" checked>
                                            <label class="form-check-label" for="emailPayments">
                                                Kirim email struk saat pembayaran berhasil (Lunas)
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="settings-section">
                                        <h5 class="fw-bold mb-3">Peringatan Sistem (Admin)</h5>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="notifLowStock" checked>
                                            <label class="form-check-label" for="notifLowStock">
                                                Tampilkan peringatan jika stok produk menipis (< 10)
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button class="btn btn-primary px-4">
                                            <i class="fas fa-save me-2"></i>Simpan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="keamanan">
                                <div class="settings-card">
                                    <h4 class="mb-4 fw-bold text-primary border-bottom pb-3">
                                        <i class="fas fa-shield-alt me-2"></i> Keamanan Akun
                                    </h4>
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">Password Lama</label>
                                            <input type="password" name="pass_lama" class="form-control" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Password Baru</label>
                                                <input type="password" name="pass_baru" class="form-control" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Konfirmasi Password Baru</label>
                                                <input type="password" name="konfirmasi_pass" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" name="ganti_password" class="btn btn-danger px-4">
                                                <i class="fas fa-key me-2"></i>Ganti Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="backup">
                                <div class="settings-card text-center py-5">
                                    <i class="fas fa-database fa-4x text-muted mb-3"></i>
                                    <h5>Backup Database</h5>
                                    <p class="text-muted">Unduh salinan database SQL untuk keamanan data.</p>
                                    <p>Ekspor data dinonaktifkan dalam demo.</p>
                                </div>
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