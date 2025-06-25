<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$success = '';
$error = '';
$product = null;
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$product_id = intval($_GET['id']);

$query = "SELECT * FROM products WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $product_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header("Location: admin.php");
    exit();
}

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_barang = sanitizeInput($_POST['nama_barang']);
    $harga = floatval($_POST['harga']);
    $ukuran = sanitizeInput($_POST['ukuran']);
    $warna = sanitizeInput($_POST['warna']);
    $stok = intval($_POST['stok']);
    $deskripsi = sanitizeInput($_POST['deskripsi']);
    
    if (empty($nama_barang) || $harga <= 0 || empty($ukuran) || empty($warna) || $stok < 0) {
        $error = 'Semua field harus diisi dengan benar!';
    } else {
        $query = "UPDATE products SET nama_barang = :nama_barang, harga = :harga, ukuran = :ukuran, warna = :warna, stok = :stok, deskripsi = :deskripsi WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_barang', $nama_barang);
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':ukuran', $ukuran);
        $stmt->bindParam(':warna', $warna);
        $stmt->bindParam(':stok', $stok);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':id', $product_id);
        
        if ($stmt->execute()) {
            $success = 'Produk berhasil diupdate!';
            $query = "SELECT * FROM products WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $product_id);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Terjadi kesalahan saat mengupdate produk!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Admin Panel</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">👕 Toko BukaBaju - Admin</div>
            <div class="nav-links">
                <a href="admin.php">Kembali ke Admin</a>
                <span class="welcome-user">Halo Admin, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <h2>Edit Produk</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="nama_barang">Nama Barang:</label>
                    <input type="text" id="nama_barang" name="nama_barang" value="<?php echo htmlspecialchars($product['nama_barang']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="harga">Harga:</label>
                    <input type="number" id="harga" name="harga" min="0" step="1000" value="<?php echo $product['harga']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="ukuran">Ukuran:</label>
                    <select id="ukuran" name="ukuran" required>
                        <option value="">Pilih Ukuran</option>
                        <?php 
                        $ukuran_options = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '28', '30', '32', '34', '36'];
                        foreach ($ukuran_options as $ukuran_opt): 
                        ?>
                            <option value="<?php echo $ukuran_opt; ?>" <?php echo ($product['ukuran'] == $ukuran_opt) ? 'selected' : ''; ?>><?php echo $ukuran_opt; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="warna">Warna:</label>
                    <input type="text" id="warna" name="warna" value="<?php echo htmlspecialchars($product['warna']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="stok">Stok:</label>
                    <input type="number" id="stok" name="stok" min="0" value="<?php echo $product['stok']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                </div>
                
                <button type="submit" class="btn">Update Produk</button>
                <a href="admin.php" class="btn btn-secondary" style="margin-left: 1rem;">Batal</a>
            </form>
        </div>
    </div>
</body>
</html>
