<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Aktuellen Pfad ermitteln
$current_path = $_SERVER['PHP_SELF'];
$isMitgliederActive = strpos($current_path, '/pages/verein/vorstand.php') !== false || strpos($current_path, '/pages/verein/spieler.php') !== false || strpos($current_path, '/pages/verein/mitglied-details.php') !== false;
?>
<nav>
    <input type="checkbox" id="mobile-menu-toggle" class="mobile-only-checkbox">
    <label for="mobile-menu-toggle" class="mobile-menu-btn mobile-only">
        <span class="hamburger-icon">≡ MENÜ</span>
    </label>

    <ul class="nav-links">
        <li><a href="/index.php"
                class="<?= ($current_path == '/index.php' || $current_path == '/') ? 'active' : '' ?>">🏠 Home</a></li>
        <li><a href="/pages/news/news.php"
                class="<?= (strpos($current_path, '/pages/news/') !== false) ? 'active' : '' ?>">📰 News</a></li>
        <li><a href="/pages/termine/termine.php"
                class="<?= (strpos($current_path, '/pages/termine/') !== false) ? 'active' : '' ?>">📅 Termine</a></li>

        <li>
            <details class="dropdown-menu" <?= $isMitgliederActive ? 'open' : '' ?>>
                <summary class="<?= $isMitgliederActive ? 'active' : '' ?>">👥 Mitglieder ▼</summary>
                <ul>
                    <li><a href="/pages/verein/vorstand.php"
                            class="<?= (strpos($current_path, '/pages/verein/vorstand.php') !== false) ? 'active' : '' ?>">👔
                            Vorstand</a></li>
                    <li><a href="/pages/verein/spieler.php"
                            class="<?= (strpos($current_path, '/pages/verein/spieler.php') !== false) ? 'active' : '' ?>">🎳
                            Spieler</a></li>
                </ul>
            </details>
        </li>

        <li><a href="/pages/verein/bestenliste.php"
                class="<?= (strpos($current_path, '/pages/verein/bestenliste.php') !== false) ? 'active' : '' ?>">🏆
                Bestenliste</a></li>

        <li><a href="/pages/galerie/galerie.php"
                class="<?= (strpos($current_path, '/pages/galerie/') !== false && strpos($current_path, '/pages/admin/') === false) ? 'active' : '' ?>">🖼
                Galerie</a></li>

        <li><a href="/pages/verein/ueber-uns.php"
                class="<?= (strpos($current_path, '/pages/verein/ueber-uns.php') !== false) ? 'active' : '' ?>">ℹ️ Über
                uns</a></li>

        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <!-- Dieser Menüpunkt ist nur für eingeloggte User sichtbar -->
            <li><a href="/pages/admin/dashboard.php"
                    class="<?= (strpos($current_path, '/pages/admin/') !== false) ? 'active' : '' ?>"
                    style="color: #ffffff;">⚙️ Admin-Dashboard</a></li>
        <?php endif; ?>
    </ul>
</nav>