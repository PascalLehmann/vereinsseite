<?php
// 1. Session starten (muss zwingend ganz oben stehen!)
session_start();

// 2. Wächter (Guard Clause): Ist der User überhaupt eingeloggt?
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// 3. Dynamische Rechte auslesen
$roles = $_SESSION['roles'] ?? [];
$perms = $_SESSION['permissions'] ?? [];

$isAdmin = !empty($perms['admin']);
$canNews = $isAdmin || !empty($perms['news']);
$canTermine = $isAdmin || !empty($perms['termine']);
$canBestleistungen = $isAdmin || !empty($perms['bestleistungen']);

// Header einbinden (absoluter Pfad auf dem Server)
require_once __DIR__ . '/../../templates/header.php';
?>

<main class="dashboard-container">
    <h2>Willkommen im Admin-Bereich, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

    <p>Deine aktuellen Rechte: <strong><?php echo htmlspecialchars(implode(', ', $roles)); ?></strong></p>

    <hr>

    <?php
    // --- CONTENT BEREICH ---
    // Nur anzeigen, wenn der User mindestens eins davon darf
    if ($canNews || $canTermine || $isAdmin):
        ?>
        <div class="dashboard-section">
            <h3>Inhalte & Verein</h3>
            <div class="dashboard-grid">
                <?php if ($canNews): ?>
                    <a href="news/übersicht.php" class="dashboard-tile">
                        <i class="fas fa-newspaper"></i>
                        <span>News verwalten</span>
                    </a>
                <?php endif; ?>
                <?php if ($canTermine): ?>
                    <a href="termine/übersicht.php" class="dashboard-tile">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Termine verwalten</span>
                    </a>
                <?php endif; ?>
                <?php if ($isAdmin): // Gegner vorerst nur für Admins ?>
                    <a href="gegner/übersicht.php" class="dashboard-tile">
                        <i class="fas fa-shield-alt"></i>
                        <span>Gegner verwalten</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // --- SYSTEM BEREICH ---
    // Nur Admins und User mit Rechten für Bestleistungen
    if ($isAdmin || $canBestleistungen):
        ?>
        <div class="dashboard-section">
            <h3>System & Verwaltung</h3>
            <div class="dashboard-grid">
                <?php if ($isAdmin || $canBestleistungen): ?>
                    <a href="mitglieder/übersicht.php" class="dashboard-tile">
                        <i class="fas fa-users"></i>
                        <span>Mitgliederverwaltung</span>
                    </a>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                    <a href="rollen/übersicht.php" class="dashboard-tile">
                        <i class="fas fa-user-shield"></i>
                        <span>Rollenverwaltung</span>
                    </a>
                    <a href="benutzer/übersicht.php" class="dashboard-tile">
                        <i class="fas fa-user-cog"></i>
                        <span>Benutzerverwaltung</span>
                    </a>
                <?php endif; ?>
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