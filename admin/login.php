<?php
session_start();
require_once '../includes/config.php';

// Jika sudah login, lempar ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// Proses Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan Password wajib diisi!";
    } else {
        // Cek user di database
        $stmt = $pdo->prepare("SELECT id, username, password, nama_lengkap FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifikasi Password (menggunakan password_verify untuk hash)
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['nama_lengkap'];

            // Redirect ke Dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Username atau Password salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Kopi Muria Zayna</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .logo-area {
            text-align: center;
            margin-bottom: 30px;
            color: #8B4513;
        }
        .form-control {
            border-radius: 25px;
            padding: 12px 20px;
            margin-bottom: 20px;
        }
        .btn-login {
            border-radius: 25px;
            padding: 12px;
            font-weight: bold;
            background: #8B4513;
            border: none;
        }
        .btn-login:hover {
            background: #6B3A0F;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-area">
            <i class="fas fa-coffee fa-3x mb-3"></i>
            <h3>Admin Login</h3>
            <p class="text-muted">Kopi Muria Zayna</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger text-center" role="alert">
                <small><?= $error ?></small>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-login">
                    MASUK <i class="fas fa-sign-in-alt ms-2"></i>
                </button>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <small class="text-muted">&copy; 2025 Kopi Muria Zayna</small>
        </div>
    </div>

</body>
</html>