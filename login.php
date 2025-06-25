<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, username, password, role FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: pembeli.php");
                }
                exit();
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BukaBaju</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">👕 Toko BukaBaju</div>
            <div class="nav-links">
                <a href="index.php">Beranda</a>
                <a href="register.php">Daftar</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 400px; margin: 2rem auto;">
            <h2>Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <p style="text-align: center; margin-top: 1rem;">
                Belum punya akun? <a href="register.php">Daftar di sini</a>
            </p>
        </div>
    </div>
</body>
</html>