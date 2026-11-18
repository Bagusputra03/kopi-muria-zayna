<?php
if (PHP_SAPI !== 'cli' || isset($_SERVER['REQUEST_METHOD'])) {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../includes/config.php';
$password = getenv('KOPI_ADMIN_PASSWORD');
if (!$password || strlen($password) < 12) {
    exit('Atur KOPI_ADMIN_PASSWORD dengan minimal 12 karakter terlebih dahulu.');
}
$stmt = $pdo->prepare('INSERT INTO admin (username, password, nama_lengkap) VALUES (?, ?, ?)');
$stmt->execute(['demo', password_hash($password, PASSWORD_DEFAULT), 'Admin Demo']);
echo 'Akun demo dibuat.' . PHP_EOL;
