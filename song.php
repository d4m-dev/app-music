<?php
require_once 'inc/db.php';
$database = new Database();
$conn = $database->connect();

session_start();

$song_id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM songs WHERE id = ?");
$stmt->execute([$song_id]);
$song = $stmt->fetch();
if (!$song) die('Bài hát không tồn tại.');

$user_id = $_SESSION['user']['id'] ?? null;

// Xử lý bình luận gửi lên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && $user_id) {
    $content = trim($_POST['comment']);
    if ($content) {
        $cmt = $conn->prepare("INSERT INTO comments (user_id, song_id, content) VALUES (?, ?, ?)");
        $cmt->execute([$user_id, $song_id, $content]);
    }
    header("Location: song.php?id=$song_id");
    exit();
}

// Lấy bình luận
$cmts = $conn->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id=u.id WHERE c.song_id=? ORDER BY c.created_at DESC");
$cmts->execute([$song_id]);
$comments = $cmts->fetchAll();

// Kiểm tra yêu thích
$is_fav = false;
if ($user_id) {
    $favstmt = $conn->prepare("SELECT 1 FROM favorites WHERE user_id=? AND song_id=?");
    $favstmt->execute([$user_id, $song_id]);
    $is_fav = $favstmt->fetchColumn() ? true : false;
}

// Xử lý yêu thích (toggle)
if (isset($_GET['fav']) && $user_id) {
    if ($is_fav) {
        $conn->prepare("DELETE FROM favorites WHERE user_id=? AND song_id=?")->execute([$user_id, $song_id]);
    } else {
        $conn->prepare("INSERT IGNORE INTO favorites (user_id, song_id) VALUES (?, ?)")->execute([$user_id, $song_id]);
    }
    header("Location: song.php?id=$song_id");
    exit();
}

// Đánh giá
if (isset($_POST['rating']) && $user_id) {
    $rate = intval($_POST['rating']);
    if ($rate >= 1 && $rate <= 5) {
        $conn->prepare("REPLACE INTO ratings (user_id, song_id, rating) VALUES (?, ?, ?)")->execute([$user_id, $song_id, $rate]);
    }
}

// Xếp hạng trung bình
$rateavg = $conn->prepare("SELECT AVG(rating) FROM ratings WHERE song_id=?");
$rateavg->execute([$song_id]);
$average = round($rateavg->fetchColumn(), 1);

// Playlist
$playlists = [];
if ($user_id) {
    $pls = $conn->prepare("SELECT * FROM playlists WHERE user_id=?");
    $pls->execute([$user_id]);
    $playlists = $pls->fetchAll();
    // Thêm vào playlist
    if (isset($_POST['add_playlist']) && $_POST['playlist_id']) {
        $p_id = intval($_POST['playlist_id']);
        $conn->prepare("INSERT IGNORE INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)")->execute([$p_id, $song_id]);
        $msg = "Đã thêm vào playlist!";
    }
    // Tạo playlist mới
    if (isset($_POST['new_playlist']) && $_POST['pl_name']) {
        $name = trim($_POST['pl_name']);
        if ($name) {
            $conn->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)")->execute([$user_id, $name]);
            header("Location: song.php?id=$song_id");
            exit();
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=htmlspecialchars($song['title'])?> | Nghe nhạc</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'templates/header.php'; ?>
    <a href="index.php">← Trang chủ</a>
    <div class="song-player">
        <img src="<?=htmlspecialchars($song['cover_path'] ?: 'assets/images/no_cover.png')?>" width="200">
        <h2><?=htmlspecialchars($song['title'])?> - <?=htmlspecialchars($song['artist'])?></h2>
        <audio controls src="<?=htmlspecialchars($song['file_path'])?>" style="width:100%"></audio>
        <p>Album: <?=htmlspecialchars($song['album'])?></p>
        <p>Đánh giá trung bình: <?=$average?> / 5</p>
        <?php if ($user_id): ?>
            <form method="POST" style="display:inline;">
                <label>Đánh giá:
                    <select name="rating" onchange="this.form.submit()">
                        <option value="">--</option>
                        <?php for ($i=1;$i<=5;$i++): ?>
                        <option value="<?=$i?>"><?=$i?> sao</option>
                        <?php endfor; ?>
                    </select>
                </label>
            </form>
            <a href="song.php?id=<?=$song_id?>&fav=1" style="margin-left:15px;"><?= $is_fav ? "♥ Bỏ yêu thích" : "♡ Yêu thích" ?></a>
            <form method="POST" style="margin-top:12px;">
                <label>Thêm vào playlist:</label>
                <select name="playlist_id">
                    <?php foreach ($playlists as $pl): ?>
                    <option value="<?=$pl['id']?>"><?=htmlspecialchars($pl['name'])?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="add_playlist" value="1">Thêm</button>
            </form>
            <form method="POST" style="margin-top:6px;">
                <input name="pl_name" placeholder="Tên playlist mới">
                <button type="submit" name="new_playlist" value="1">Tạo playlist</button>
            </form>
        <?php endif; ?>
    </div>
    <hr>
    <h3>Bình luận</h3>
    <?php if ($user_id): ?>
        <form method="POST">
            <textarea name="comment" required placeholder="Viết bình luận..." style="width:100%;height:60px;"></textarea>
            <button type="submit">Gửi</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Đăng nhập</a> để bình luận.</p>
    <?php endif; ?>
    <div class="comment-list">
        <?php foreach ($comments as $c): ?>
            <div class="comment-item">
                <b><?=htmlspecialchars($c['username'])?></b>
                <span style="color:#888;font-size:12px;"><?=htmlspecialchars($c['created_at'])?></span><br>
                <?=nl2br(htmlspecialchars($c['content']))?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (!empty($msg)) echo "<p style='color:green;'>$msg</p>"; ?>
</body>
</html>