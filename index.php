<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: admin.php");
    } else {
        header("Location: pembeli.php");
    }
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM products LIMIT 6";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BukaBaju - Beranda</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">👕 BukaBaju</div>
            <div class="nav-links">
                <a href="index.php">Beranda</a>
                <a href="#catalog">Katalog</a>
                <a href="login.php">Login</a>
                <a href="register.php">Daftar</a>
            </div>
        </div>
    </nav>

    <div class="hero">
        <h1>Selamat Datang di Toko BukaBaju</h1>
        <p>Temukan koleksi pakaian terbaik dengan kualitas premium</p>
        <a href="login.php" class="btn">Mulai Belanja</a>
    </div>

    <div class="container">
        <div class="card">
            <h2 id="catalog">Katalog Produk</h2>
            <p>Berikut adalah beberapa produk unggulan kami:</p>
            
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">👕</div>
                    <div class="product-info">
                        <div class="product-title"><?php echo htmlspecialchars($product['nama_barang']); ?></div>
                        <div class="product-price"><?php echo formatRupiah($product['harga']); ?></div>
                        <div class="product-details">
                            Ukuran: <?php echo htmlspecialchars($product['ukuran']); ?> | 
                            Warna: <?php echo htmlspecialchars($product['warna']); ?> | 
                            Stok: <?php echo $product['stok']; ?>
                        </div>
                        <p><?php echo htmlspecialchars($product['deskripsi']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="login.php" class="btn">Login untuk Melihat Semua Produk</a>
            </div>
        </div>
    </div>
</body>
</html>