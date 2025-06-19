<header>
    <div class="header-container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;">
        <div class="logo-section" style="display:flex;align-items:center;gap:12px;">
            <a href="index.php" style="display:flex;align-items:center;text-decoration:none;">
                <!-- Logo SVG -->
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                  <circle cx="20" cy="20" r="20" fill="url(#grad)" />
                  <text x="50%" y="55%" text-anchor="middle" fill="#fff" font-size="18" font-family="Montserrat,sans-serif" font-weight="bold" dy=".3em">MP3</text>
                  <defs>
                    <linearGradient id="grad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                      <stop stop-color="#6a5af9"/>
                      <stop offset="1" stop-color="#c86dd7"/>
                    </linearGradient>
                  </defs>
                </svg>
                <span style="font-size:1.45rem;font-weight:700;color:#fff;margin-left:7px;letter-spacing:1.5px;">Zing MP3 Web</span>
            </a>
        </div>
        <nav class="main-nav" style="display:flex;gap:14px;align-items:center;">
            <a href="index.php"><i class="fa fa-home"></i> Trang chủ</a>
            <a href="top.php"><i class="fa fa-star"></i> BXH Nhạc</a>
            <a href="playlist.php"><i class="fa fa-music"></i> Playlist</a>
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="admin/upload.php"><i class="fa fa-upload"></i> Upload Nhạc</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="logout.php"><i class="fa fa-sign-out"></i> Đăng xuất</a>
            <?php else: ?>
                <a href="login.php"><i class="fa fa-sign-in"></i> Đăng nhập</a>
                <a href="register.php"><i class="fa fa-user-plus"></i> Đăng ký</a>
            <?php endif; ?>
        </nav>
        <form method="get" action="index.php" class="header-search" style="margin-top:10px;display:flex;align-items:center;">
            <input type="text" name="search" placeholder="Tìm kiếm bài hát, ca sĩ, album..." value="<?=isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''?>" />
            <button type="submit"><i class="fa fa-search"></i></button>
        </form>
    </div>
</header>
<!-- Font Awesome CDN for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
<style>
.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 18px;
}
.main-nav a {
    color: #fff;
    font-weight: 500;
    font-size: 1.08rem;
    padding: 8px 16px;
    border-radius: 7px;
    transition: background 0.18s, color 0.18s;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
}
.main-nav a:hover {
    background: rgba(255,255,255,0.13);
    color: #ffcaff;
}
.header-search input[type="text"] {
    border: none;
    outline: none;
    border-radius: 8px 0 0 8px;
    padding: 8px 14px;
    font-size: 1rem;
    background: #fff;
    color: #222;
    width: 180px;
}
.header-search button {
    border: none;
    background: var(--primary,#6a5af9);
    color: #fff;
    border-radius: 0 8px 8px 0;
    padding: 8px 16px;
    font-weight: 600;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.2s;
}
.header-search button:hover {
    background: var(--primary-dark,#4431b7);
}
@media (max-width: 850px) {
    .header-container { flex-direction: column; align-items: flex-start; padding: 10px;}
    .main-nav { margin-top: 6px; margin-bottom: 6px; gap:8px;}
    .header-search { margin-top: 10px; width: 100%; }
}
</style>
