<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav>
    <input type="checkbox" id="mobile-menu-toggle" class="mobile-only-checkbox">
    <label for="mobile-menu-toggle" class="mobile-menu-btn mobile-only">
        <span class="hamburger-icon">≡ MENÜ</span>
    </label>

    <ul class="nav-links">
        <li><a href="/index.php">🏠 Home</a></li>
        <li><a href="/pages/news/news.php">📰 News</a></li>
        <li><a href="/pages/termine/termine.php">📅 Termine</a></li>

        <li>
            <details class="dropdown-menu">
                <summary>👥 Mitglieder ▼</summary>
                <ul>
                    <li><a href="/pages/verein/vorstand.php">👔 Vorstand</a></li>
                    <li><a href="/pages/verein/spieler.php">🎳 Spieler</a></li>
                </ul>
            </details>
        </li>

        <li><a href="/pages/verein/bestenliste.php">🏆 Bestenliste</a></li>

        <li><a href="/pages/galerie/galerie.php">🖼 Galerie</a></li>

        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <!-- Dieser Menüpunkt ist nur für eingeloggte User sichtbar -->
            <li><a href="/pages/admin/dashboard.php" style="color: #ffffff;">⚙️ Admin-Dashboard</a></li>
        <?php endif; ?>
    </ul>
</nav>