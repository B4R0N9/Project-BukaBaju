<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

if ($_POST['action'] ?? '' === 'add_to_cart') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    $_SESSION['success_message'] = "Produk berhasil ditambahkan ke keranjang!";
    header("Location: pembeli.php");
    exit;
}

if ($_POST['action'] ?? '' === 'checkout') {
    if (!empty($_SESSION['cart'])) {
        $order_query = "INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, 'pending', NOW())";
        $order_stmt = $db->prepare($order_query);
        $total = 0;
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $price_query = "SELECT harga FROM products WHERE id = ?";
            $price_stmt = $db->prepare($price_query);
            $price_stmt->execute([$product_id]);
            $product = $price_stmt->fetch(PDO::FETCH_ASSOC);
            $total += $product['harga'] * $quantity;
        }
        
        $order_stmt->execute([$_SESSION['user_id'], $total]);
        $order_id = $db->lastInsertId();
        
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $price_query = "SELECT harga, nama_barang FROM products WHERE id = ?";
            $price_stmt = $db->prepare($price_query);
            $price_stmt->execute([$product_id]);
            $product = $price_stmt->fetch(PDO::FETCH_ASSOC);
            
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price, product_name) VALUES (?, ?, ?, ?, ?)";
            $item_stmt = $db->prepare($item_query);
            $item_stmt->execute([$order_id, $product_id, $quantity, $product['harga'], $product['nama_barang']]);
        }
        
        unset($_SESSION['cart']);
        $_SESSION['order_success'] = "Pesanan berhasil dibuat! ID Pesanan: #" . $order_id;
        header("Location: pembeli.php");
        exit;
    }
}

$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

$query = "SELECT * FROM products WHERE stok > 0 ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk - Toko BukaBaju</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .cart-icon {
            position: relative;
            display: inline-block;
            margin-right: 10px;
            cursor: pointer;
            font-size: 20px;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .cart-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .cart-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            max-height: 80%;
            overflow-y: auto;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .cart-total {
            font-weight: bold;
            font-size: 18px;
            text-align: right;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        .btn-add-cart {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn-add-cart:hover {
            background: #27ae60;
        }
        
        .btn-checkout {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
        }
        
        .btn-checkout:hover {
            background: #2980b9;
        }
        
        .quantity-input {
            width: 60px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-right: 10px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .order-success {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">👕 Toko BukaBaju</div>
            <div class="nav-links">
                <a href="order_history.php" style="color: white; text-decoration: none; margin-right: 15px;">📋 Pesanan</a>
                <div class="cart-icon" onclick="openCart()">
                    🛒
                    <span class="cart-badge" id="cartBadge"><?php echo $cart_count; ?></span>
                </div>
                <span class="welcome-user">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" id="logoutBtn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Katalog Produk</h2>
            <p>Selamat datang di katalog lengkap produk kami!</p>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['order_success'])): ?>
                <div class="order-success">
                    🎉 <?php 
                    echo $_SESSION['order_success']; 
                    unset($_SESSION['order_success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="product-grid">
                <?php if (count($products) > 0): ?>
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
                            
                            <form method="POST" style="margin-top: 10px;">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stok']; ?>" class="quantity-input">
                                <button type="submit" class="btn-add-cart">Tambah ke Keranjang</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Belum ada produk tersedia.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div id="cartModal" class="cart-modal">
        <div class="cart-content">
            <span class="close" onclick="closeCart()">&times;</span>
            <h2>Keranjang Belanja</h2>
            <div id="cartItems">
                <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                    <?php 
                    $total = 0;
                    foreach ($_SESSION['cart'] as $product_id => $quantity): 
                        $query = "SELECT * FROM products WHERE id = ?";
                        $stmt = $db->prepare($query);
                        $stmt->execute([$product_id]);
                        $product = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($product):
                            $subtotal = $product['harga'] * $quantity;
                            $total += $subtotal;
                    ?>
                    <div class="cart-item">
                        <div>
                            <strong><?php echo htmlspecialchars($product['nama_barang']); ?></strong><br>
                            <small><?php echo formatRupiah($product['harga']); ?> x <?php echo $quantity; ?></small>
                        </div>
                        <div><?php echo formatRupiah($subtotal); ?></div>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                    <div class="cart-total">
                        Total: <?php echo formatRupiah($total); ?>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="btn-checkout">Checkout Sekarang</button>
                    </form>
                <?php else: ?>
                    <p>Keranjang belanja kosong.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/script.js"></script>
    <script>
        function openCart() {
            document.getElementById('cartModal').style.display = 'block';
        }
        
        function closeCart() {
            document.getElementById('cartModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            var modal = document.getElementById('cartModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>