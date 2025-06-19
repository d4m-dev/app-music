<?php
require_once 'inc/db.php';
$database = new Database();
$conn = $database->connect();

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];
$playlists = $conn->prepare("SELECT * FROM playlists WHERE user_id=?");
$playlists->execute([$user_id]);
$pls = $playlists->fetchAll();

function getSongs($conn, $pid) {
    $stmt = $conn->prepare("SELECT s.* FROM playlist_songs ps JOIN songs s ON ps.song_id=s.id WHERE ps.playlist_id=?");
    $stmt->execute([$pid]);
    return $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Playlist của tôi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'templates/header.php'; ?>
    <a href="index.php">← Trang chủ</a>
    <h2>Playlist của tôi</h2>
    <?php foreach ($pls as $pl): ?>
        <div class="playlist-box">
            <h3><?=htmlspecialchars($pl['name'])?></h3>
            <?php
                $songs = getSongs($conn, $pl['id']);
                if ($songs):
            ?>
            <ul>
                <?php foreach ($songs as $song): ?>
                <li>
                    <a href="song.php?id=<?=$song['id']?>"><?=htmlspecialchars($song['title'])?> - <?=htmlspecialchars($song['artist'])?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
                <em>Chưa có bài hát nào.</em>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>