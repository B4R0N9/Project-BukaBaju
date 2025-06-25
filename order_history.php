<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT o.*, COUNT(oi.id) as total_items 
          FROM orders o 
          LEFT JOIN order_items oi ON o.id = oi.order_id 
          WHERE o.user_id = ? 
          GROUP BY o.id 
          ORDER BY o.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$order_details = null;
if (isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    
    $verify_query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $verify_stmt = $db->prepare($verify_query);
    $verify_stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        $items_query = "SELECT * FROM order_items WHERE order_id = ?";
        $items_stmt = $db->prepare($items_query);
        $items_stmt->execute([$order_id]);
        $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $order_details = [
            'order' => $order,
            'items' => $order_items
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Toko BukaBaju</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .order-card {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .order-id {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background: #f39c12; }
        .status-processing { background: #3498db; }
        .status-shipped { background: #9b59b6; }
        .status-delivered { background: #27ae60; }
        .status-cancelled { background: #e74c3c; }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .order-detail {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .order-detail strong {
            color: #2c3e50;
        }
        
        .btn-view-details {
            background: #3498db;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        
        .btn-view-details:hover {
            background: #2980b9;
        }
        
        .btn-back {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .btn-back:hover {
            background: #7f8c8d;
        }
        
        .order-items {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .item-row:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-details {
            color: #6c757d;
            font-size: 14px;
        }
        
        .item-total {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .order-total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #3498db;
        }
        
        .empty-orders {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .nav-buttons {
            margin-bottom: 20px;
        }
        
        .nav-buttons a {
            background: #2ecc71;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .nav-buttons a:hover {
            background: #27ae60;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">👕 Toko BukaBaju</div>
            <div class="nav-links">
                <span class="welcome-user">Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" id="logoutBtn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <?php if ($order_details): ?>
               
                <a href="order_history.php" class="btn-back">← Kembali ke Riwayat</a>
                
                <h2>Detail Pesanan #<?php echo $order_details['order']['id']; ?></h2>
                
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">Pesanan #<?php echo $order_details['order']['id']; ?></div>
                        <div class="order-status status-<?php echo $order_details['order']['status']; ?>">
                            <?php echo ucfirst($order_details['order']['status']); ?>
                        </div>
                    </div>
                    
                    <div class="order-info">
                        <div class="order-detail">
                            <strong>Tanggal Pesanan:</strong><br>
                            <?php echo date('d M Y H:i', strtotime($order_details['order']['created_at'])); ?>
                        </div>
                        <div class="order-detail">
                            <strong>Total Item:</strong><br>
                            <?php echo count($order_details['items']); ?> item
                        </div>
                        <div class="order-detail">
                            <strong>Total Pembayaran:</strong><br>
                            <?php echo formatRupiah($order_details['order']['total_amount']); ?>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <h4>Item Pesanan:</h4>
                        <?php foreach ($order_details['items'] as $item): ?>
                        <div class="item-row">
                            <div>
                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="item-details">
                                    <?php echo formatRupiah($item['price']); ?> × <?php echo $item['quantity']; ?>
                                </div>
                            </div>
                            <div class="item-total">
                                <?php echo formatRupiah($item['price'] * $item['quantity']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="order-total">
                            Total: <?php echo formatRupiah($order_details['order']['total_amount']); ?>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
              
                <div class="nav-buttons">
                    <a href="pembeli.php">← Kembali ke Katalog</a>
                </div>
                
                <h2>Riwayat Pesanan</h2>
                <p>Berikut adalah riwayat semua pesanan Anda.</p>
                
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-id">Pesanan #<?php echo $order['id']; ?></div>
                            <div class="order-status status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </div>
                        </div>
                        
                        <div class="order-info">
                            <div class="order-detail">
                                <strong>Tanggal:</strong><br>
                                <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>
                            </div>
                            <div class="order-detail">
                                <strong>Total Item:</strong><br>
                                <?php echo $order['total_items']; ?> item
                            </div>
                            <div class="order-detail">
                                <strong>Total:</strong><br>
                                <?php echo formatRupiah($order['total_amount']); ?>
                            </div>
                            <div>
                                <a href="order_history.php?order_id=<?php echo $order['id']; ?>" 
                                   class="btn-view-details">Lihat Detail</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-orders">
                        <h3>Belum Ada Pesanan</h3>
                        <p>Anda belum pernah melakukan pesanan.</p>
                        <a href="pembeli.php" class="btn-view-details">Mulai Belanja</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>