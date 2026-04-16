<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Vorstand";
$activePage = 'vorstand.php'; // Für die Fokus-Logik im Menü

// 2. DATENBANK EINBINDEN
require_once __DIR__ . '/../../db.php';

// 3. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';

// 4. VORSTAND AUS DER DATENBANK LADEN
// Wir laden die Mitglieder und sortieren sie anhand der Reihenfolge aus der neuen `vorstand_positionen` Tabelle.
$sql = "SELECT m.* 
        FROM mitglieder m
        LEFT JOIN vorstand_positionen p ON m.vorstands_rolle = p.name
        WHERE m.im_vorstand = 1
        ORDER BY p.sort_order ASC, m.nachname ASC";
$vorstand_mitglieder = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>

<main class="content">
    <h1>Unser Vorstand</h1>
    <p>Die gewählten Vertreter unseres Vereins.</p>

    <div class="vorstand-grid">
        <?php if (count($vorstand_mitglieder) > 0): ?>
            <?php foreach ($vorstand_mitglieder as $m): ?>
                <div class="flip-card-outer">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <!-- Externe URL für das Hintergrundbild (wie gewünscht) -->
                            <div class="flip-card-bkg-photo"
                                style="background-image: url('https://images.unsplash.com/photo-1511207538754-e8555f2bc187?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=88672068827eaeeab540f584b883cc66&auto=format&fit=crop&w=1164&q=80');">
                            </div>
                            <div class="flip-card-face-photo"
                                style="background-image: url('<?= !empty($m['profilbild']) ? '/assets/img/mitglieder/' . htmlspecialchars($m['profilbild']) : '/assets/img/mitglieder/default-user.png' ?>');">
                            </div>
                            <div class="flip-card-text">
                                <h3><?= htmlspecialchars($m['vorname'] . ' ' . $m['nachname']) ?></h3>
                                <p><?= htmlspecialchars($m['vorstands_rolle']) ?></p>
                                <span class="flip-card-hover-badge">Infos drehen</span>
                            </div>
                        </div>
                        <div class="flip-card-back">
                            <div class="flip-card-back-content" style="width: 100%;">
                                <div class="flip-card-back-stats">
                                    <p style="text-align: center; font-weight: bold; margin-bottom: 15px; color: #fff;">Kontakt
                                        & Info</p>
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                        <span>E-Mail:</span>
                                        <strong><?= !empty($m['email']) ? htmlspecialchars($m['email']) : 'Keine Angabe' ?></strong>
                                    </div>
                                    <?php if (!empty($m['telefon'])): ?>
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>Telefon:</span>
                                            <strong><?= htmlspecialchars($m['telefon']) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <hr>
                                    <p style="font-size: 0.75rem; text-align: center; color: #aaa;">
                                        Dabei seit:
                                        <?= !empty($m['eintrittsdatum']) ? date("d.m.Y", strtotime($m['eintrittsdatum'])) : 'Unbekannt' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aktuell sind keine Vorstandsmitglieder hinterlegt.</p>
        <?php endif; ?>

    </div>
</main>
<?php
// 3. FOOTER EINBINDEN
require_once __DIR__ . '/../../templates/footer.php';
?>