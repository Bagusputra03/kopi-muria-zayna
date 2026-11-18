<?php
// Tentukan halaman aktif
$page = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* --- STYLING NAVBAR MOBILE --- */
    .mobile-navbar {
        background: linear-gradient(90deg, var(--primary-color) 0%, #6B3A0F 100%);
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 1050; /* Pastikan di atas elemen lain */
    }

    /* Sidebar Style Persis Dashboard */
    .sidebar {
        background: linear-gradient(180deg, var(--primary-color) 0%, #6B3A0F 100%);
        color: white;
        
        /* --- BAGIAN PENTING YANG DIUBAH --- */
        height: 100vh;        /* Paksa tinggi sidebar setinggi layar browser */
        position: sticky;     /* Membuat sidebar 'menempel' */
        top: 0;               /* Menempel di bagian paling atas layar */
        overflow-y: auto;     /* Jika menu sidebar kepanjangan, sidebar bisa di-scroll sendiri */
        z-index: 1000;        /* Pastikan sidebar selalu di atas layer lain */
        /* ---------------------------------- */

        box-shadow: 3px 0 10px rgba(0,0,0,0.1);
    }
    
    /* Sembunyikan sidebar statis di mobile */
    @media (max-width: 767.98px) {
        .sidebar-desktop {
            display: none !important;
        }
    }

    .sidebar::-webkit-scrollbar {
        width: 6px;
    }
    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar .nav-link, .offcanvas-sidebar .nav-link {
        color: rgba(255,255,255,0.8);
        padding: 8px 15px;
        margin: 5px 0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .sidebar .nav-link:hover, .sidebar .nav-link.active,
    .offcanvas-sidebar .nav-link:hover, .offcanvas-sidebar .nav-link.active {
        background: rgba(255,255,255,0.15);
        color: white;
        font-weight: bold;
    }
    .sidebar .nav-link i, .offcanvas-sidebar .nav-link i { width: 25px; }

    /* Styling khusus Offcanvas Mobile agar warnanya sama */
    .offcanvas-sidebar {
        background: linear-gradient(180deg, var(--primary-color) 0%, #6B3A0F 100%);
        color: white;
    }
    .offcanvas-sidebar .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
</style>

<div class="col-12 d-md-none p-0 mb-3">
    <nav class="navbar navbar-dark mobile-navbar px-3 py-2">
        <div class="d-flex align-items-center">
            <img src="../assets/images/logo.png" alt="Logo" width="30" class="me-2 bg-white rounded-circle p-1">
            <span class="fw-bold text-white mb-0" style="font-size: 1.1rem;">Kopi Muria Zayna Admin</span>
        </div>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            <span class="navbar-toggler-icon"></span>
        </button>
    </nav>
</div>

<?php
function renderMenuItems($page) {
?>
    <li class="nav-item w-100">
        <a href="dashboard.php" class="nav-link <?= $page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
    </li>
    <li class="nav-item w-100">
        <a href="produk.php" class="nav-link <?= $page == 'produk.php' ? 'active' : '' ?>">
            <i class="fas fa-box"></i> Kelola Produk
        </a>
    </li>
    <li class="nav-item w-100">
        <a href="pesanan.php" class="nav-link <?= $page == 'pesanan.php' || $page == 'kasir_pembayaran.php' ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i> Kelola Pesanan
        </a>
    </li>
    <li class="nav-item w-100">
        <a href="laporan.php" class="nav-link <?= $page == 'laporan.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i> Laporan
        </a>
    </li>
    <li class="nav-item w-100">
        <a href="pengaturan.php" class="nav-link <?= $page == 'pengaturan.php' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> Pengaturan
        </a>
    </li>
    
    <hr class="w-100 text-white-50 my-3">
        
    <li class="nav-item w-100">
        <a href="logout.php" class="nav-link text-warning" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </li>
<?php
}
?>

<div class="col-md-3 col-lg-2 sidebar sidebar-desktop">
    <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-4">
        <div class="w-100 text-center mb-4 pt-2">
            <img src="../assets/images/logo.png" 
                alt="Logo Kopi Muria Zayna" 
                class="img-fluid bg-white p-2 rounded-circle shadow-sm" 
                style="max-width: 90px; height: auto">
        </div>
        
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100">
            <?php renderMenuItems($page); ?>
        </ul>
    </div>
</div>

<div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="mobileSidebar">
    <div class="offcanvas-header border-bottom border-secondary">
        <div class="d-flex align-items-center">
            <img src="../assets/images/logo.png" alt="Logo" width="35" class="me-2 bg-white rounded-circle p-1">
            <h5 class="offcanvas-title fw-bold text-white mb-0">Menu Admin</h5>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav nav-pills flex-column w-100">
            <?php renderMenuItems($page); ?>
        </ul>
    </div>
</div>