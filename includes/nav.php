<?php
// Session nur starten, wenn sie nicht bereits läuft
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="sidebar">
    <input type="checkbox" id="nav-toggle" class="nav-toggle">
    <label for="nav-toggle" class="nav-toggle-label">
        <i class="fa-solid fa-bars"></i> MENÜ
    </label>

    <div class="nav-links">
        <div class="nav-section">
            <h3 class="desktop-hide-title">Hauptmenü</h3>
            <ul>
                <li><a href="/index.php"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="/news.php"><i class="fa-solid fa-newspaper"></i> News</a></li>
                <li><a href="/termine.php"><i class="fa-solid fa-calendar-days"></i> Termine</a></li>

                <li>
                    <input type="checkbox" id="drop-mitglieder" class="drop-check">
                    <label for="drop-mitglieder" class="drop-label">
                        <i class="fa-solid fa-users"></i> Mitglieder
                        <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; margin-left: auto;"></i>
                    </label>
                    <ul class="sub-menu">
                        <li><a href="/vorstand.php"><i class="fa-solid fa-user-tie"></i> Vorstand</a></li>
                        <li><a href="/spieler.php"><i class="fa-solid fa-user-group"></i> Spieler</a></li>
                    </ul>
                </li>

                <li><a href="/galerie.php"><i class="fa-solid fa-images"></i> Galerie</a></li>
            </ul>
        </div>

        <div class="nav-section admin-nav">
            <?php if (isset($_SESSION['admin_id'])): ?>
                <h3>Verwaltung</h3>
                <ul>
                    <li><a href="/admin/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                    <li><a href="/admin/news/uebersicht.php"><i class="fa-solid fa-pen-to-square"></i> News verwalten</a>
                    </li>
                    <li><a href="/admin/mitglieder/uebersicht.php"><i class="fa-solid fa-user-gear"></i> Mitglieder
                            verwalten</a></li>
                    <li><a href="/admin/gegner/uebersicht.php"><i class="fa-solid fa-shield-halved"></i> Gegner
                            verwalten</a></li>
                    <li><a href="/admin/logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i>
                            Logout</a></li>
                </ul>
            <?php else: ?>
                <ul>
                    <li><a href="/admin/login.php"><i class="fa-solid fa-lock"></i> Admin Login</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>