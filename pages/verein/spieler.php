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
            <div class="flip-card-outer">
                <div class="flip-card-inner">
                    <div class="flip-card-front <?= $m['im_vorstand'] ? 'vorstand-highlight' : '' ?>">
                        <?php if ($m['ist_gruendungsmitglied']): ?>
                            <div
                                style="position: absolute; top: 10px; right: 10px; background: var(--sidebar-color); color: white; padding: 5px 10px; border-radius: 10px; font-size: 0.7rem; font-weight: bold; z-index: 10;">
                                GRÜNDER
                            </div>
                        <?php endif; ?>

                        <!-- Externe URL für das Hintergrundbild (wie gewünscht) -->
                        <div class="flip-card-bkg-photo"
                            style="background-image: url('https://images.unsplash.com/photo-1511207538754-e8555f2bc187?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=88672068827eaeeab540f584b883cc66&auto=format&fit=crop&w=1164&q=80');">
                        </div>
                        <div class="flip-card-face-photo"
                            style="background-image: url('<?= !empty($m['profilbild']) ? '/assets/img/mitglieder/' . htmlspecialchars($m['profilbild']) : '/assets/img/mitglieder/default-user.png' ?>');">
                        </div>

                        <div class="flip-card-text">
                            <h3><?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?></h3>
                            <p><?= $m['im_vorstand'] ? htmlspecialchars($m['vorstands_rolle']) : 'Aktives Mitglied' ?></p>
                            <span class="flip-card-hover-badge">Stats drehen</span>
                        </div>
                    </div>

                    <div class="flip-card-back">
                        <div class="flip-card-back-content" style="width: 100%;">
                            <div class="flip-card-back-stats">
                                <?php
                                // Prüfen, ob überhaupt mindestens ein Bestwert hinterlegt ist
                                $hasBestwerte = !empty($m['best_100_wert']) || !empty($m['best_120_wert']) || !empty($m['best_200_wert']);
                                if ($hasBestwerte):
                                    ?>
                                    <p style="text-align: center; font-weight: bold; margin-bottom: 15px; color: #fff;">
                                        Bestwerte</p>

                                    <?php if (!empty($m['best_120_wert'])): ?>
                                        <div style="margin-bottom: 12px;">
                                            <div style="display: flex; justify-content: space-between;">
                                                <span>120 Würfe:</span>
                                                <strong><?= htmlspecialchars($m['best_120_wert']) ?> Holz</strong>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #aaa; text-align: right; margin-top: 2px;">
                                                <?= !empty($m['best_120_ort']) ? htmlspecialchars($m['best_120_ort']) : '' ?>
                                                <?= !empty($m['best_120_datum']) ? 'am ' . date("d.m.Y", strtotime($m['best_120_datum'])) : '' ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($m['best_100_wert'])): ?>
                                        <div style="margin-bottom: 12px;">
                                            <div style="display: flex; justify-content: space-between;">
                                                <span>100 Würfe:</span>
                                                <strong><?= htmlspecialchars($m['best_100_wert']) ?> Holz</strong>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #aaa; text-align: right; margin-top: 2px;">
                                                <?= !empty($m['best_100_ort']) ? htmlspecialchars($m['best_100_ort']) : '' ?>
                                                <?= !empty($m['best_100_datum']) ? 'am ' . date("d.m.Y", strtotime($m['best_100_datum'])) : '' ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($m['best_200_wert'])): ?>
                                        <div style="margin-bottom: 12px;">
                                            <div style="display: flex; justify-content: space-between;">
                                                <span>200 Würfe:</span>
                                                <strong><?= htmlspecialchars($m['best_200_wert']) ?> Holz</strong>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #aaa; text-align: right; margin-top: 2px;">
                                                <?= !empty($m['best_200_ort']) ? htmlspecialchars($m['best_200_ort']) : '' ?>
                                                <?= !empty($m['best_200_datum']) ? 'am ' . date("d.m.Y", strtotime($m['best_200_datum'])) : '' ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <hr>
                                <?php endif; ?>
                                <p style="font-size: 0.75rem; text-align: center; color: #aaa;">
                                    Dabei seit:
                                    <?= $m['eintrittsdatum'] ? date("d.m.Y", strtotime($m['eintrittsdatum'])) : 'Unbekannt' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php
// 3. FOOTER EINBINDEN
require_once __DIR__ . '/../../templates/footer.php';
?>