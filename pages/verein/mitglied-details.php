<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. DATENBANK EINBINDEN
require_once __DIR__ . '/../../db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$typ = isset($_GET['typ']) ? $_GET['typ'] : 'mitglied';
$pageTitle = "Profil Details";

// Mitglied aus der Datenbank laden
$stmt = $pdo->prepare("SELECT * FROM mitglieder WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$m) {
    die("Mitglied nicht gefunden.");
}

// 3. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main class="content">
    <a href="<?= ($typ == 'vorstand') ? 'vorstand.php' : 'spieler.php'; ?>" class="btn btn-secondary"
        style="margin-bottom: 25px;">
        &laquo; Zurück zur Übersicht
    </a>

    <article class="content-tile" style="margin-top: 20px; text-align: center;">
        <div class="vorstand-avatar" style="width: 180px; height: 180px; margin: 0 auto 20px auto;">
            <img src="<?= !empty($m['profilbild']) ? '/assets/img/mitglieder/' . htmlspecialchars($m['profilbild']) : '/assets/img/mitglieder/default-user.png' ?>"
                alt="Profilbild">
        </div>

        <div>
            <h1 style="margin-bottom: 5px;"><?= htmlspecialchars($m['vorname'] . ' ' . $m['nachname']) ?></h1>
            <p style="font-size: 1.2rem; color: var(--sidebar-color); font-weight: bold;">
                <?= $m['im_vorstand'] ? htmlspecialchars($m['vorstands_rolle']) : 'Mitglied'; ?>
                <?= (isset($m['ist_aktiv']) && $m['ist_aktiv'] == 0) ? ' (Passiv)' : ''; ?>
            </p>
            <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
            <p>Hier stehen die Detailinformationen, Kontakte oder Statistiken.</p>
        </div>
    </article>
</main>
<?php
// 3. FOOTER EINBINDEN
require_once __DIR__ . '/../../templates/footer.php';
?>