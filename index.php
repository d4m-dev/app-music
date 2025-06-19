<?php
require_once 'inc/db.php';
$database = new Database();
$conn = $database->connect();

session_start();

$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM songs WHERE title LIKE ? OR artist LIKE ? OR album LIKE ? ORDER BY upload_time DESC");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $conn->query("SELECT * FROM songs ORDER BY upload_time DESC");
}
$songs = $stmt->fetchAll();

function isLoggedIn() { return isset($_SESSION['user']); }
function isAdmin() { return isLoggedIn() && $_SESSION['user']['role'] === 'admin'; }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trang chủ | Web nghe nhạc</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <?php include 'templates/header.php'; ?>
        <h1><a href="index.php">Web nghe nhạc</a></h1>
        <nav>
            <?php if (isLoggedIn()): ?>
                <span>Xin chào, <?=htmlspecialchars($_SESSION['user']['username'])?></span>
                <?php if (isAdmin()): ?>
                    <a href="admin/upload.php">Upload nhạc</a>
                <?php endif; ?>
                <a href="playlist.php">Playlist của tôi</a>
                <a href="logout.php">Đăng xuất</a>
            <?php else: ?>
                <a href="login.php">Đăng nhập</a>
                <a href="register.php">Đăng ký</a>
            <?php endif; ?>
        </nav>
        <form method="GET" style="margin-top:10px;">
            <input name="search" placeholder="Tìm kiếm bài hát, ca sĩ, album..." value="<?=htmlspecialchars($search)?>">
            <button type="submit">Tìm</button>
        </form>
    </header>
    <main>
        <h2>Bài hát mới nhất</h2>
        <div class="song-list">
            <?php foreach ($songs as $song): ?>
                <div class="song-item">
                    <img src="<?=htmlspecialchars($song['cover_path'] ?: 'assets/images/no_cover.png')?>" alt="cover" width="100">
                    <h3><?=htmlspecialchars($song['title'])?></h3>
                    <p><?=htmlspecialchars($song['artist'])?></p>
                    <a href="song.php?id=<?=$song['id']?>">Nghe nhạc</a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>