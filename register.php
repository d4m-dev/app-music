<?php
require_once 'inc/db.php';
$database = new Database();
$conn = $database->connect();

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    if ($username && $password && $email) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $hashed, $email]);
            header('Location: login.php');
            exit();
        } catch (PDOException $e) {
            $error = "Tên đăng nhập hoặc email đã tồn tại!";
        }
    } else {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Đăng ký</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input name="username" placeholder="Tên đăng nhập" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit">Đăng ký</button>
    </form>
    <a href="login.php">Đã có tài khoản? Đăng nhập</a>
</body>
</html>