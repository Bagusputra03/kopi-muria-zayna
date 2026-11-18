<?php
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Jakarta');

$host = getenv('KOPI_DB_HOST') ?: '127.0.0.1';
$port = getenv('KOPI_DB_PORT') ?: '3306';
$database = getenv('KOPI_DB_NAME') ?: 'kopi_portfolio';
$user = getenv('KOPI_DB_USER') ?: 'root';
$password = getenv('KOPI_DB_PASSWORD');
define('BASE_URL', 'http://127.0.0.1:8080/');
define('MIDTRANS_SERVER_KEY', '');
define('MIDTRANS_CLIENT_KEY', '');
define('MIDTRANS_IS_PRODUCTION', false);

$pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
    $user,
    $password === false ? '' : $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

function generateKodePesanan(): string
{
    return 'DEMO-' . strtoupper(bin2hex(random_bytes(7)));
}

function formatRupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}
