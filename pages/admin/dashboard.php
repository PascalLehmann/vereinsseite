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

$canNews = !empty($perms['news_create']) || !empty($perms['news_edit']) || !empty($perms['news_delete']) || !empty($perms['news_delete_hard']);
$canTermine = !empty($perms['termine_create']) || !empty($perms['termine_edit']) || !empty($perms['termine_delete']) || !empty($perms['termine_delete_hard']);
$canMitglieder = !empty($perms['mitglieder_create']) || !empty($perms['mitglieder_edit']) || !empty($perms['mitglieder_delete']) || !empty($perms['mitglieder_bestleistungen']);
$canGalerie = !empty($perms['galerie_upload']) || !empty($perms['galerie_delete']) || !empty($perms['galerie_delete_hard']);
$canGalerieKat = !empty($perms['galerie_kat_create']) || !empty($perms['galerie_kat_delete']) || !empty($perms['galerie_kat_delete_hard']);
$isAdmin = !empty($perms['admin']);

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
    if ($canNews || $canTermine || $canGalerie):
        ?>
        <div class="dashboard-section">
            <h3>Inhalte & Verein</h3>
            <div class="dashboard-grid">
                <?php if ($canNews): ?>
                    <a href="news/uebersicht.php" class="dashboard-tile">
                        <i class="fas fa-newspaper"></i>
                        <span>News verwalten</span>
                    </a>
                <?php endif; ?>
                <?php if ($canTermine): ?>
                    <a href="termine/uebersicht.php" class="dashboard-tile">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Termine verwalten</span>
                    </a>
                <?php endif; ?>
                <?php if ($canGalerie): ?>
                    <a href="galerie/uebersicht.php" class="dashboard-tile">
                        <i class="fas fa-images"></i>
                        <span>Galerie verwalten</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // --- SYSTEM BEREICH ---
    // Nur Admins und User mit entsprechenden Rechten
    if ($isAdmin || $canMitglieder || $canGalerieKat): // This outer check is fine, it just groups sections
        ?>
        <div class="dashboard-section">
            <h3>System & Verwaltung</h3>
            <div class="dashboard-grid">
                <?php if ($canMitglieder): ?>
                    <a href="mitglieder/uebersicht.php" class="dashboard-tile">
                        <i class="fas fa-users"></i>
                        <span>Mitgliederverwaltung</span>
                    </a>
                <?php endif; ?>
                <?php if ($isAdmin): // Gegner vorerst nur für Admins ?>
                    <a href="gegner/uebersicht.php" class="dashboard-tile">
                        <i class="fas fa-shield-alt"></i>
                        <span>Gegner verwalten</span>
                    </a>
                <?php endif; ?>
                <?php if ($canGalerieKat): ?>
                    <a href="galerie_kategorien/uebersicht.php" class="dashboard-tile">
                        <i class="fas fa-folder-open"></i>
                        <span>Galerie Kategorien</span>
                    </a>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                    <a href="positionen/uebersicht.php" class="dashboard-tile">
                        <i class="fas fa-sitemap"></i>
                        <span>Positionen verwalten</span>
                    </a>
                    <a href="rollen/uebersicht.php" class="dashboard-tile">
                        <i class="fas fa-user-shield"></i>
                        <span>Rollenverwaltung</span>
                    </a>
                    <a href="benutzer/uebersicht.php" class="dashboard-tile">
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