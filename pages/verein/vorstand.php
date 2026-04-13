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
// Wir sortieren die Mitglieder nach Wichtigkeit des Amtes (1. Vorsitzender zuerst etc.)
$sql = "SELECT id, vorname, nachname, vorstands_rolle, profilbild 
        FROM mitglieder 
        WHERE im_vorstand = 1 
        ORDER BY 
            CASE vorstands_rolle
                WHEN '1. Vorsitzender' THEN 1
                WHEN '1. Vorsitzende' THEN 1
                WHEN '2. Vorsitzender' THEN 2
                WHEN '2. Vorsitzende' THEN 2
                WHEN 'Schatzmeister' THEN 3
                WHEN 'Schatzmeisterin' THEN 3
                WHEN 'Kassenwart' THEN 3
                WHEN 'Kassenwartin' THEN 3
                WHEN 'Schriftführer' THEN 4
                WHEN 'Schriftführerin' THEN 4
                WHEN 'Sportwart' THEN 5
                ELSE 99
            END ASC,
            nachname ASC";
$vorstand_mitglieder = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>

<main class="content">
    <h1>Unser Vorstand</h1>
    <p>Die gewählten Vertreter unseres Vereins.</p>

    <div class="vorstand-grid">
        <?php if (count($vorstand_mitglieder) > 0): ?>
            <?php foreach ($vorstand_mitglieder as $m): ?>
                <div class="vorstand-card">
                    <div class="vorstand-avatar">
                        <img src="<?= !empty($m['profilbild']) ? '/assets/img/mitglieder/' . htmlspecialchars($m['profilbild']) : '/assets/img/mitglieder/default-user.png' ?>"
                            alt="<?= htmlspecialchars($m['vorname'] . ' ' . $m['nachname']) ?>">
                    </div>
                    <div class="vorstand-info">
                        <h3>
                            <?= htmlspecialchars($m['vorname'] . ' ' . $m['nachname']) ?>
                        </h3>
                        <p class="rolle">
                            <?= htmlspecialchars($m['vorstands_rolle']) ?>
                        </p>
                        <a href="mitglied-details.php?id=<?= $m['id'] ?>&typ=vorstand" class="btn btn-secondary btn-sm"
                            style="margin-top: 10px;">Kontakt & Info</a>
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