<?php
// Session starten, falls noch nicht geschehen (für die Logik-Prüfung)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="sidebar">
    <div class="nav-links">
        <a href="/index.php" class="nav-item">
            <i class="fa-solid fa-house"></i> Home
        </a>
        <a href="/news.php" class="nav-item">
            <i class="fa-solid fa-newspaper"></i> News
        </a>
        <a href="/termine.php" class="nav-item">
            <i class="fa-solid fa-calendar-days"></i> Termine
        </a>
        
        <div class="dropdown">
            <a href="#" class="nav-item">
                <i class="fa-solid fa-users"></i> Mitglieder ▼
            </a>
            <div class="dropdown-content">
                <a href="/vorstand.php">Vorstand</a>
                <a href="/spieler.php">Spieler</a>
            </div>
        </div>

        <a href="/galerie.php" class="nav-item">
            <i class="fa-solid fa-image"></i> Galerie
        </a>

        <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 15px 0;">

        <?php if (isset($_SESSION['eingeloggt']) && $_SESSION['eingeloggt'] === true): ?>
            <p style="color: rgba(255,255,255,0.5); font-size: 0.7rem; padding-left: 20px; margin-bottom: 5px; text-transform: uppercase;">Verwaltung</p>
            
            <a href="/admin/dashboard.php" class="nav-item" style="color: var(--primary-orange);">
                <i class="fa-solid fa-gauge-high"></i> Admin-Dashboard
            </a>
            <a href="/admin/news-admin.php" class="nav-item">
                <i class="fa-solid fa-pen-to-square"></i> News verwalten
            </a>
            <a href="/admin/logout.php" class="nav-item" style="margin-top: 20px; color: #ff6666;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        <?php else: ?>
            <a href="/admin/login.php" class="nav-item" style="opacity: 0.3; font-size: 0.8rem; margin-top: 50px;">
                <i class="fa-solid fa-lock"></i> Login
            </a>
        <?php endif; ?>
    </div>
</nav>