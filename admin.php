<?php

require_once 'config/database.php';
require_once 'includes/functions.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_product') {
    $nama_barang = sanitizeInput($_POST['nama_barang']);
    $harga = floatval($_POST['harga']);
    $ukuran = sanitizeInput($_POST['ukuran']);
    $warna = sanitizeInput($_POST['warna']);
    $stok = intval($_POST['stok']);
    $deskripsi = sanitizeInput($_POST['deskripsi']);
    
    if (empty($nama_barang) || $harga <= 0 || empty($ukuran) || empty($warna) || $stok < 0) {
        $error = 'Semua field harus diisi dengan benar!';
    } else {
        $query = "INSERT INTO products (nama_barang, harga, ukuran, warna, stok, deskripsi) VALUES (:nama_barang, :harga, :ukuran, :warna, :stok, :deskripsi)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':ukuran', $ukuran);
        $stmt->bindParam(':warna', $warna);
        $stmt->bindParam(':stok', $stok);
        $stmt->bindParam(':deskripsi', $deskripsi);
        
        if ($stmt->execute()) {
            $success = 'Produk berhasil ditambahkan!';
        } else {
            $error = 'Terjadi kesalahan saat menambah produk!';
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    $query = "DELETE FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $product_id);
    
    if ($stmt->execute()) {
        $success = 'Produk berhasil dihapus!';
    } else {
        $error = 'Terjadi kesalahan saat menghapus produk!';
    }
}

$query = "SELECT * FROM products ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Toko Baju Online</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">👕 Toko BukaBaju - Admin</div>
            <div class="nav-links">
                <span class="welcome-user">Halo Admin, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php"id="logoutBtn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Tambah Produk Baru</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="add_product">
                
                <div class="form-group">
                    <label for="nama_barang">Nama Barang:</label>
                    <input type="text" id="nama_barang" name="nama_barang" required>
                </div>
                
                <div class="form-group">
                    <label for="harga">Harga:</label>
                    <input type="number" id="harga" name="harga" min="0" step="1000" required>
                </div>
                
                <div class="form-group">
                    <label for="ukuran">Ukuran:</label>
                    <select id="ukuran" name="ukuran" required>
                        <option value="">Pilih Ukuran</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL</option>
                        <option value="28">28</option>
                        <option value="30">30</option>
                        <option value="32">32</option>
                        <option value="34">34</option>
                        <option value="36">36</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="warna">Warna:</label>
                    <input type="text" id="warna" name="warna" required>
                </div>
                
                <div class="form-group">
                    <label for="stok">Stok:</label>
                    <input type="number" id="stok" name="stok" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn">Tambah Produk</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Daftar Produk</h2>
            
            <?php if (count($products) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Ukuran</th>
                            <th>Warna</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['nama_barang']); ?></td>
                            <td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($product['ukuran']); ?></td>
                            <td><?php echo htmlspecialchars($product['warna']); ?></td>
                            <td><?php echo $product['stok']; ?></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Edit</a>
                                <a href="admin.php?delete=<?php echo $product['id']; ?>" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.8rem;" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada produk tersedia.</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Statistik Toko</h2>
            <div class="stats-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div class="stat-card" style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3 style="color: #007bff; margin: 0;">Total Produk</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0;"><?php echo count($products); ?></p>
                </div>
                
                <div class="stat-card" style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3 style="color: #28a745; margin: 0;">Total Stok</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0;">
                        <?php 
                        $total_stok = 0;
                        foreach ($products as $product) {
                            $total_stok += $product['stok'];
                        }
                        echo $total_stok;
                        ?>
                    </p>
                </div>
                
                <div class="stat-card" style="background: #f8f9fa; padding: 1rem; border-radius: 8px; text-align: center;">
                    <h3 style="color: #ffc107; margin: 0;">Produk Stok Rendah</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin: 0.5rem 0;">
                        <?php 
                        $low_stock = 0;
                        foreach ($products as $product) {
                            if ($product['stok'] <= 5) {
                                $low_stock++;
                            }
                        }
                        echo $low_stock;
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
<script src="assets/script.js"></script>
</body>
</html>