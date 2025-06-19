<?php
require_once 'inc/db.php';
$database = new Database();
$conn = $database->connect();

session_start();
$stmt = $conn->query("SELECT s.*, AVG(r.rating) as avg_rating, COUNT(r.rating) as total FROM songs s LEFT JOIN ratings r ON s.id=r.song_id GROUP BY s.id ORDER BY avg_rating DESC, total DESC LIMIT 20");
$songs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>BXH Bài hát</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include 'templates/header.php'; ?>
    <a href="index.php">← Trang chủ</a>
    <h2>Bảng xếp hạng bài hát</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>STT</th>
            <th>Bìa</th>
            <th>Bài hát</th>
            <th>Ca sĩ</th>
            <th>Điểm TB</th>
            <th>Lượt đánh giá</th>
        </tr>
        <?php $i=1; foreach($songs as $song): ?>
        <tr>
            <td><?=$i++?></td>
            <td><img src="<?=htmlspecialchars($song['cover_path'] ?: 'assets/images/no_cover.png')?>" width="50"></td>
            <td><a href="song.php?id=<?=$song['id']?>"><?=htmlspecialchars($song['title'])?></a></td>
            <td><?=htmlspecialchars($song['artist'])?></td>
            <td><?=round($song['avg_rating'],1)?></td>
            <td><?=$song['total']?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>