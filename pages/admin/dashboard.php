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

    <?php
    // --- CONTENT BEREICH ---
    // Admin und Autor dürfen News und Termine verwalten
    if ($isAdmin || $isAutor):
        ?>
        <div class="dashboard-section">
            <h3>Inhalte & Verein</h3>
            <div class="dashboard-grid">
                <a href="news/übersicht.php" class="dashboard-tile">
                    <i class="fas fa-newspaper"></i>
                    <span>News verwalten</span>
                </a>
                <a href="termine/übersicht.php" class="dashboard-tile">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Termine verwalten</span>
                </a>
                <a href="gegner/übersicht.php" class="dashboard-tile">
                    <i class="fas fa-shield-alt"></i>
                    <span>Gegner verwalten</span>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // --- SYSTEM BEREICH ---
    // Nur der Admin darf Mitglieder und Rollen verwalten
    if ($isAdmin):
        ?>
        <div class="dashboard-section">
            <h3>System & Verwaltung</h3>
            <div class="dashboard-grid">
                <a href="mitglieder/übersicht.php" class="dashboard-tile">
                    <i class="fas fa-users"></i>
                    <span>Mitgliederverwaltung</span>
                </a>
                <a href="rollen.php" class="dashboard-tile">
                    <i class="fas fa-user-shield"></i>
                    <span>Rollenverwaltung</span>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="dashboard-section">
        <h3>Konto</h3>
        <div class="dashboard-grid">
            <a href="logout.php" class="dashboard-tile logout-tile">
                <i class="fas fa-sign-out-alt"></i>
                <span>Ausloggen</span>
            </a>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>