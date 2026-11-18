<?php
session_start();

if (isset($_POST['action']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];

    if (!isset($_SESSION['cart'][$id])) {
        exit;
    }

    if ($action == 'plus') {
        $_SESSION['cart'][$id]['qty']++;
    } elseif ($action == 'minus') {
        if ($_SESSION['cart'][$id]['qty'] > 1) {
            $_SESSION['cart'][$id]['qty']--;
        }
    } elseif ($action == 'remove') {
        unset($_SESSION['cart'][$id]);
    }
}

// Redirect kembali ke keranjang
header("Location: keranjang.php");
exit;
?>