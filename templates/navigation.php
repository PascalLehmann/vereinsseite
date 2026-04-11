<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="sidebar">
    <input type="checkbox" id="nav-toggle" class="nav-toggle">
    <label for="nav-toggle" class="nav-toggle-label desktop-hide">
        <i class="fa-solid fa-bars"></i> MENÜ
    </label>

    <div class="nav-links">
        <div class="nav-section">
            <ul>
                <li><a href="/index.php"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="/pages/news/news.php"><i class="fa-solid fa-newspaper"></i> News</a></li>
                <li><a href="/pages/termine/termine.php"><i class="fa-solid fa-calendar-days"></i> Termine</a></li>
                <li class="dropdown">
                    <input type="checkbox" id="drop-1" class="drop-check">
                    <label for="drop-1" class="drop-label <?= ($isMitgliederArea) ? 'active' : ''; ?>">
                        <i class="fa-solid fa-users"></i> Mitglieder ▼
                    </label>
                    <ul class="sub-menu">
                        <li><a href="/pages/verein/vorstand.php"
                                class="<?= ($activePage == '/pages/verein/vorstand.php') ? 'active' : ''; ?>">Vorstand</a>
                        </li>
                        <li><a href="/pages/verein/spieler.php"
                                class="<?= ($activePage == '/pages/verein/spieler.php') ? 'active' : ''; ?>">Spieler</a>
                        </li>
                    </ul>
                </li>
                <li><a href="/pages/galerie/galerie.php"><i class="fa-solid fa-images"></i> Galerie</a></li>
            </ul>
        </div>

        <div class="nav-section admin-nav">
            <?php if (isset($_SESSION['admin_id'])): ?>
                <h3 class="desktop-hide">Verwaltung</h3>
                <ul>
                    <li><a href="/pages/admin/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                    <li><a href="/pages/admin/logout.php" style="color: #ffcccc !important;"><i
                                class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                </ul>
            <?php else: ?>
                <ul>
                    <li><a href="/pages/admin/login.php"><i class="fa-solid fa-lock"></i> Admin Login</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>