<?php
// Cek dulu: Jika session BELUM aktif, baru kita start.
// Jika sudah aktif (dari file lain), lewati baris ini.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Jika belum login, tendang kembali ke halaman login
    header("Location: login.php");
    exit;
}
?>