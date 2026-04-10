<?php
$activePage = basename($_SERVER['PHP_SELF']);
$isMitgliederArea = ($activePage == 'vorstand.php' || $activePage == 'spieler.php' || $activePage == 'mitglied-details.php');
?>

<nav class="sidebar">
    <input type="checkbox" id="nav-toggle" class="nav-toggle">
    <label for="nav-toggle" class="nav-toggle-label">Menü</label>

    <ul class="nav-links">
        <li><a href="index.php" class="<?= ($activePage == 'index.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i> Home</a></li>
            
        <li><a href="news.php" class="<?= ($activePage == 'news.php' || $activePage == 'news-details.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-newspaper"></i> News</a></li>
            
        <li><a href="termine.php" class="<?= ($activePage == 'termine.php' || $activePage == 'termin-details.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-calendar-days"></i> Termine</a></li>

        <li class="dropdown">
            <input type="checkbox" id="drop-1" class="drop-check">
            <label for="drop-1" class="drop-label <?= ($isMitgliederArea) ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i> Mitglieder ▼
            </label>
            <ul class="sub-menu">
                <li><a href="vorstand.php" class="<?= ($activePage == 'vorstand.php') ? 'active' : ''; ?>">Vorstand</a></li>
                <li><a href="spieler.php" class="<?= ($activePage == 'spieler.php') ? 'active' : ''; ?>">Spieler</a></li>
            </ul>
        </li>

        <li><a href="galerie.php" class="<?= ($activePage == 'galerie.php' || $activePage == 'galerie-details.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-image"></i> Galerie</a></li>
    </ul>
</nav>