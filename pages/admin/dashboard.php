<?php
// 1. Session starten (muss zwingend ganz oben stehen!)
session_start();

// 2. Wächter (Guard Clause): Ist der User überhaupt eingeloggt?
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// 3. Rollen-Check vorbereiten (macht den HTML-Teil unten viel lesbarer)
// Wir prüfen, ob die Strings 'admin' oder 'autor' im Rollen-Array des Users existieren.
$roles = $_SESSION['roles'] ?? []; // Fallback auf leeres Array, falls fehlerhaft
$isAdmin = in_array('admin', $roles);
$isAutor = in_array('autor', $roles);

// Header einbinden (absoluter Pfad auf dem Server)
require_once __DIR__ . '/../../templates/header.php';
?>

<main class="dashboard-container">
    <h2>Willkommen im Admin-Bereich, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

    <p>Deine aktuellen Rechte: <strong><?php echo htmlspecialchars(implode(', ', $roles)); ?></strong></p>

    <hr>

    <h3>Aktionen</h3>
    <ul class="admin-menu">

        <?php
        // --- CONTENT BEREICH ---
        // Admin und Autor dürfen News und Termine verwalten
        if ($isAdmin || $isAutor):
            ?>
            <li><a href="news/übersicht.php">News verwalten</a></li>
            <li><a href="termine/übersicht.php">Termine verwalten</a></li>
            <li><a href="gegner/übersicht.php">Gegner verwalten</a></li>
        <?php endif; ?>

        <?php
        // --- SYSTEM BEREICH ---
        // Nur der Admin darf Mitglieder und Rollen verwalten
        if ($isAdmin):
            ?>
            <li><a href="mitglieder/übersicht.php">Mitgliederverwaltung</a></li>
            <li><a href="rollen.php">Rollenverwaltung</a></li>
        <?php endif; ?>

        <li><a href="logout.php" style="color: red;">Ausloggen</a></li>
    </ul>
</main>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>