<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$perms = $_SESSION['permissions'] ?? [];
$isAdmin = !empty($perms['admin']);
$canBestleistungen = !empty($perms['bestleistungen']);

if (!$isAdmin && !$canBestleistungen) {
    die("Zugriff verweigert: Du hast keine Berechtigung für diese Seite.");
}

require_once __DIR__ . '/../../../db.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM mitglieder WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$m) {
    header("Location: uebersicht.php");
    exit;
}

$pageTitle = "Mitglied bearbeiten";
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Mitglied bearbeiten: <?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?></h2>

    <div class="action-bar">
        <a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <form action="aktualisieren.php" method="POST" enctype="multipart/form-data" class="content-tile"
        style="max-width: 800px;">
        <input type="hidden" name="id" value="<?= $m['id'] ?>">

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Vorname</label>
                <input type="text" name="vorname" class="form-control" value="<?= htmlspecialchars($m['vorname']) ?>"
                    <?= !$isAdmin ? 'readonly style="background:#eee;"' : '' ?> required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Nachname</label>
                <input type="text" name="nachname" class="form-control" value="<?= htmlspecialchars($m['nachname']) ?>"
                    <?= !$isAdmin ? 'readonly style="background:#eee;"' : '' ?> required>
            </div>
        </div>

        <div
            style="background: #fdf8f5; border-left: 4px solid #e67e22; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <div class="form-group" style="margin-bottom: 10px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="im_vorstand" id="vorstand_check" value="1"
                        onchange="toggleVorstand(this.checked)" <?= $m['im_vorstand'] ? 'checked' : '' ?>
                        style="transform: scale(1.5); margin-right: 5px;" <?= !$isAdmin ? 'disabled' : '' ?>>
                    <strong>Ist Mitglied im Vorstand?</strong>
                </label>
            </div>

            <div class="form-group" id="vorstand_pos_box"
                style="display: <?= $m['im_vorstand'] ? 'block' : 'none' ?>; margin-top: 15px;">
                <label>Vorstands-Position</label>
                <select name="vorstands_rolle" class="form-control" <?= !$isAdmin ? 'disabled' : '' ?>>
                    <option value="">-- Bitte wählen --</option>
                    <?php
                    $rollen = ["1. Vorsitzender", "2. Vorsitzender", "Sportwart", "Kassenwart", "Schriftführer", "Jugendwart"];
                    foreach ($rollen as $rolle): ?>
                        <option value="<?= $rolle ?>" <?= ($m['vorstands_rolle'] == $rolle) ? 'selected' : '' ?>><?= $rolle ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h3
            style="margin: 20px 0 10px; color: var(--sidebar-color); border-bottom: 1px solid #eee; padding-bottom: 5px;">
            Persönliche Bestwerte</h3>
        <?php $typen = ['100', '120', '200'];
        foreach ($typen as $t): ?>
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1;"><label><?= $t ?> Würfe (Holz)</label><input type="number"
                        name="best_<?= $t ?>_wert" class="form-control"
                        value="<?= htmlspecialchars($m['best_' . $t . '_wert'] ?? '') ?>"></div>
                <div class="form-group" style="flex: 1;"><label>Erreicht am</label><input type="date"
                        name="best_<?= $t ?>_datum" class="form-control"
                        value="<?= htmlspecialchars($m['best_' . $t . '_datum'] ?? '') ?>"></div>
                <div class="form-group" style="flex: 1;"><label>Ort</label><input type="text" name="best_<?= $t ?>_ort"
                        class="form-control" value="<?= htmlspecialchars($m['best_' . $t . '_ort'] ?? '') ?>"></div>
            </div>
        <?php endforeach; ?>

        <div style="display: flex; gap: 20px; align-items: center; margin-top: 20px; margin-bottom: 20px;">
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Eintrittsdatum</label>
                <input type="date" name="eintrittsdatum" class="form-control"
                    value="<?= htmlspecialchars($m['eintrittsdatum'] ?? '') ?>" <?= !$isAdmin ? 'readonly style="background:#eee;"' : '' ?>>
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label
                    style="display: flex; align-items: center; gap: 10px; cursor: pointer; height: 100%; margin-top: 20px;">
                    <input type="checkbox" name="ist_gruendungsmitglied" value="1"
                        <?= !empty($m['ist_gruendungsmitglied']) ? 'checked' : '' ?>
                        style="transform: scale(1.5); margin-right: 5px;" <?= !$isAdmin ? 'disabled' : '' ?>>
                    <strong style="color: #2980b9;">Gründungsmitglied?</strong>
                </label>
            </div>
        </div>
        <?php if ($isAdmin): ?>
            <div class="file-upload-box">
                <label>Profilbild ändern</label>
                <input type="file" name="profilbild" accept=".jpg, .jpeg, .png, .webp" class="form-control"
                    style="border: none; padding: 0;">
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 12px;">Änderungen
            speichern</button>
    </form>
</main>

<script>
    function toggleVorstand(isChecked) {
        const box = document.getElementById('vorstand_pos_box');
        if (box) {
            box.style.display = isChecked ? 'block' : 'none';
        }
    }
</script>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>