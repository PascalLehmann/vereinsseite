<?php
session_start();
// Fehlerberichterstattung für die Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. DATENBANK EINBINDEN (2 Ebenen nach oben ins Hauptverzeichnis)
require_once __DIR__ . '/../../db.php';

// 2. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';

$mitglieder = $pdo->query("SELECT * FROM mitglieder ORDER BY nachname ASC")->fetchAll();
?>



<main class="content">
    <h1>Unsere Spieler & Mitglieder</h1>

    <div class="vorstand-grid">
        <?php foreach ($mitglieder as $m): ?>
            <div class="vorstand-card <?= $m['im_vorstand'] ? 'vorstand-highlight' : '' ?>" style="position: relative;">
                <?php if ($m['ist_gruendungsmitglied']): ?>
                    <div
                        style="position: absolute; top: 10px; right: 10px; background: var(--primary-orange); color: white; padding: 5px 10px; border-radius: 10px; font-size: 0.7rem; font-weight: bold;">
                        GRÜNDER
                    </div>
                <?php endif; ?>

                <div class="vorstand-avatar">
                    <img src="<?= !empty($m['profilbild']) ? '/assets/img/mitglieder/' . htmlspecialchars($m['profilbild']) : '/assets/img/mitglieder/default-user.png' ?>"
                        alt="Profilbild">
                </div>

                <h3 style="margin-bottom: 5px; color: #333;">
                    <?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?>
                </h3>

                <p class="rolle" style="margin-bottom: 20px;">
                    <?= $m['im_vorstand'] ? htmlspecialchars($m['vorstands_rolle']) : 'Aktives Mitglied' ?>
                </p>

                <div style="background: #f9f9f9; padding: 15px; border-radius: 10px; text-align: left; font-size: 0.9rem;">
                    <p><strong>Persönliche Bestwerte:</strong></p>
                    <hr style="border: 0; border-top: 1px dashed #ccc; margin: 10px 0;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>120 Würfe:</span>
                        <strong><?= $m['best_120_wert'] ?: '-' ?> Holz</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>100 Würfe:</span>
                        <strong><?= $m['best_100_wert'] ?: '-' ?> Holz</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>200 Würfe:</span>
                        <strong><?= $m['best_200_wert'] ?: '-' ?> Holz</strong>
                    </div>
                    <p style="font-size: 0.7rem; color: #999; margin-top: 10px;">
                        Dabei seit:
                        <?= $m['eintrittsdatum'] ? date("d.m.Y", strtotime($m['eintrittsdatum'])) : 'Unbekannt' ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php
// 3. FOOTER EINBINDEN
require_once __DIR__ . '/../../templates/footer.php';
?>