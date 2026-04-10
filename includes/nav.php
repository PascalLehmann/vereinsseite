<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="sidebar">
    <input type="checkbox" id="nav-toggle" class="nav-toggle">
    <label for="nav-toggle" class="nav-toggle-label">Menü</label>

    <ul class="nav-links">
        <li>
            <a href="/index.php" class="<?= ($_SERVER['PHP_SELF'] == '/index.php' ? 'active' : '') ?>">
                <i class="fa-solid fa-house"></i> Home
            </a>
        </li>
        <li>
            <a href="/news.php" class="<?= ($_SERVER['PHP_SELF'] == '/news.php' ? 'active' : '') ?>">
                <i class="fa-solid fa-newspaper"></i> News
            </a>
        </li>
        <li>
            <a href="/termine.php" class="<?= ($_SERVER['PHP_SELF'] == '/termine.php' ? 'active' : '') ?>">
                <i class="fa-solid fa-calendar-days"></i> Termine
            </a>
        </li>
        
        <li class="dropdown">
            <input type="checkbox" id="drop-1" class="drop-check">
            <label for="drop-1" class="drop-label">
                <i class="fa-solid fa-users"></i> Mitglieder ▼
            </label>
            <ul class="sub-menu">
                <li><a href="/vorstand.php">Vorstand</a></li>
                <li><a href="/spieler.php">Spieler</a></li>
            </ul>
        </li>

        <li>
            <a href="/galerie.php" class="<?= ($_SERVER['PHP_SELF'] == '/galerie.php' ? 'active' : '') ?>">
                <i class="fa-solid fa-image"></i> Galerie
            </a>
        </li>

        <?php if (isset($_SESSION['eingeloggt']) && $_SESSION['eingeloggt'] === true): ?>
            <li style="margin-top: 20px; border-top: 1px solid rgba(0,0,139,0.2); padding-top: 10px;">
                <a href="/admin/dashboard.php">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="/admin/logout.php" style="color: #8b0000;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </li>
        <?php else: ?>
            <li>
                <a href="/admin/login.php" style="opacity: 0.4; margin-top: 30px;">
                    <i class="fa-solid fa-lock"></i> Login
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>