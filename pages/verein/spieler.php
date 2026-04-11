<?php
include_once 'db.php';
$pageTitle = "Unsere Mannschaft";
include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php';

$mitglieder = $pdo->query("SELECT * FROM mitglieder ORDER BY nachname ASC")->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once 'includes/nav.php'; ?>

        <main class="content">
            <h1>Unsere Spieler & Mitglieder</h1>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
                <?php foreach ($mitglieder as $m): ?>
                    <div class="news-card" style="text-align: center; position: relative;">
                        <?php if ($m['ist_gruendungsmitglied']): ?>
                            <div
                                style="position: absolute; top: 10px; right: 10px; background: var(--primary-orange); color: white; padding: 5px 10px; border-radius: 10px; font-size: 0.7rem; font-weight: bold;">
                                GRÜNDER
                            </div>
                        <?php endif; ?>

                        <div class="profile-preview-circle">
                            <img src="<?= getProfilbild($m['profilbild']) ?>" alt="Profilbild">
                        </div>

                        <h2 style="color: var(--secondary-blue); margin-bottom: 5px;">
                            <?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?>
                        </h2>

                        <p style="color: #666; font-style: italic; margin-bottom: 20px;">
                            <?= $m['im_vorstand'] ? htmlspecialchars($m['vorstands_rolle']) : 'Aktives Mitglied' ?>
                        </p>

                        <div
                            style="background: #f4f7f6; padding: 15px; border-radius: 15px; text-align: left; font-size: 0.9rem;">
                            <p><strong>Persönliche Bestwerte:</strong></p>
                            <hr style="border: 0; border-top: 1px solid #ddd; margin: 10px 0;">
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
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>

</div>