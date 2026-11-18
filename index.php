<?php
require_once 'includes/config.php';

// Ambil data produk beserta kategori
$stmt = $pdo->query("
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    LEFT JOIN kategori k ON p.kategori_id = k.id 
    WHERE p.is_available = 1 
    AND p.is_deleted = 0 
    ORDER BY k.nama_kategori, p.nama_produk
");
$produk = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group produk by kategori
$produkByKategori = [];
foreach ($produk as $item) {
    $produkByKategori[$item['nama_kategori']][] = $item;
}

$cart_count = 0;

if (isset($_SESSION['cart'])) {
    $cart_count = array_sum(array_column($_SESSION['cart'], 'qty'));
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Kopi Muria Zayna</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #D2691E;
            --accent-color: #F4A460;
            --text-dark: #2C1810;
            --text-light: #6B4F3D;
            --background: #FFF8F0;
        }
        
        body {
            background: linear-gradient(135deg, var(--background) 0%, #f9f5f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        }
        
        .hero-section {
            background: linear-gradient(rgba(139, 69, 19, 0.9), rgba(210, 105, 30, 0.8)), 
                        url('assets/images/default.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
            border-radius: 0 0 30px 30px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
            margin-bottom: 25px;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--secondary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .price-tag {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .add-to-cart-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            color: white;
            transition: all 0.3s ease;
        }
        
        .add-to-cart-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.3);
        }
        
        .cart-floating {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        
        .cart-btn {
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .cart-btn:hover {
            transform: scale(1.1);
            background: var(--secondary-color);
        }
        
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        
        .category-tab {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .category-btn {
            background: none;
            border: 2px solid var(--accent-color);
            color: var(--text-light);
            border-radius: 20px;
            padding: 8px 20px;
            margin: 5px;
            transition: all 0.3s ease;
        }
        
        .category-btn.active, .category-btn:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }
        
        .search-box {
            background: white;
            border-radius: 25px;
            padding: 10px 20px;
            border: 2px solid var(--accent-color);
        }
        
        .search-box input {
            border: none;
            outline: none;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php" style="color: #8B4513;">
                <img src="assets/images/logo.png" alt="Logo" width="40" height="40" class="me-2 rounded">
                
                <span>Kopi Muria Zayna</span>
            </a>
            <div class="d-flex align-items-center">
                <div class="search-box me-3">
                    <i class="fas fa-search text-muted me-2"></i>
                    <input type="text" placeholder="Cari produk..." id="search-input">
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section + Kategori -->
    <div class="hero-section">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Rasakan Keautentikan Kopi Khas Muria</h1>
            <p class="lead mb-4">Dibuat dengan cinta dari biji kopi pilihan terbaik</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="category-tab">
                        <button class="category-btn active" data-category="semua">Semua</button>
                        <?php foreach (array_keys($produkByKategori) as $kategori): ?>
                            <button class="category-btn" data-category="<?= htmlspecialchars($kategori) ?>">
                                <?= htmlspecialchars($kategori) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="container">
        <div class="row" id="product-grid">
            <?php foreach ($produkByKategori as $kategori => $items): ?>
                <?php foreach ($items as $item): ?>
                    <div class="col-lg-4 col-md-6 product-item" data-category="<?= htmlspecialchars($kategori) ?>">
                        <div class="product-card">
                            <div class="position-relative">
                                <img src="assets/images/<?= htmlspecialchars($item['gambar']) ?>"
                                     class="product-image"
                                     alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($item['nama_produk']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($item['deskripsi']) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-tag">
                                        Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                                    </span>
                                    <button class="add-to-cart-btn" data-id="<?= $item['id'] ?>">
                                        <i class="fas fa-plus me-1"></i> Tambah
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-box me-1"></i> Stok: <?= $item['stok'] ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Floating Cart Button -->
    <div class="cart-floating">
        <a href="customer/keranjang.php" class="cart-btn position-relative">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count"><?= $cart_count ?></span>

        </a>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ** Filter Kategori **
        document.querySelectorAll('.category-btn').forEach(button => {
            button.addEventListener('click', function () {
                // Set active class
                document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const selectedCategory = this.dataset.category.toLowerCase();

                document.querySelectorAll('.product-item').forEach(product => {
                    const productCategory = product.dataset.category.toLowerCase();
                    if (selectedCategory === "semua" || selectedCategory === productCategory) {
                        product.style.display = "block";
                    } else {
                        product.style.display = "none";
                    }
                });
            });
        });

        // ** Pencarian Produk (opsional) **
        const searchInput = document.getElementById('search-input');
        searchInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase();

            document.querySelectorAll('.product-item').forEach(product => {
                const nama = product.querySelector('.card-title').textContent.toLowerCase();
                const deskripsi = product.querySelector('.card-text').textContent.toLowerCase();
                if (nama.includes(keyword) || deskripsi.includes(keyword)) {
                    product.style.display = "block";
                } else {
                    product.style.display = "none";
                }
            });

            // Jika ada kategori aktif selain "semua", pastikan filter kategori tetap diberlakukan
            const activeCategoryBtn = document.querySelector('.category-btn.active');
            if (activeCategoryBtn && activeCategoryBtn.dataset.category.toLowerCase() !== "semua") {
                const cat = activeCategoryBtn.dataset.category.toLowerCase();
                document.querySelectorAll('.product-item').forEach(product => {
                    const productCategory = product.dataset.category.toLowerCase();
                    if (productCategory !== cat) {
                        product.style.display = "none";
                    }
                });
            }
        });

       // ** Tombol Add to Cart yang terhubung backend **
document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', function() {

        const productId = this.dataset.id;
        const btn = this;

        fetch('customer/add_to_cart.php', {
            method: 'POST',
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "product_id=" + productId
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {

                // Update cart count
                document.querySelector('.cart-count').textContent = data.count;

                // Animasi sukses
                btn.innerHTML = '<i class="fas fa-check me-1"></i> Ditambah';
                btn.style.background = '#28a745';

                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-plus me-1"></i> Tambah';
                    btn.style.background = '';
                }, 1500);

                showNotification("Produk berhasil ditambahkan!");

            } else {
                alert("Gagal menambah produk: " + data.message);
            }
        });

    });
});


        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'alert alert-success position-fixed';
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 2000);
        }


     

    </script>
</body>
</html>
