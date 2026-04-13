<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$roles = $_SESSION['roles'] ?? [];
if (!in_array('admin', $roles)) {
    die("Zugriff verweigert.");
}

$pageTitle = "Mitglied hinzufügen";

// 2. LAYOUT EINBINDEN
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Neues Mitglied anlegen</h2>

    <div class="action-bar">
        <a href="übersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <form action="speichern.php" method="POST" enctype="multipart/form-data" class="content-tile"
        style="max-width: 800px;">

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Vorname</label>
                <input type="text" name="vorname" class="form-control" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Nachname</label>
                <input type="text" name="nachname" class="form-control" required>
            </div>
        </div>

        <div
            style="background: #fdf8f5; border-left: 4px solid #e67e22; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <div class="form-group" style="margin-bottom: 10px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="im_vorstand" id="vorstand_check" value="1"
                        onchange="toggleVorstand(this.checked)" style="transform: scale(1.5); margin-right: 5px;">
                    <strong>Ist Mitglied im Vorstand?</strong>
                </label>
            </div>

            <div class="form-group" id="vorstand_pos_box" style="display: none; margin-top: 15px;">
                <label>Vorstands-Position</label>
                <select name="vorstands_rolle" class="form-control">
                    <option value="">-- Bitte wählen --</option>
                    <?php
                    $rollen = ["1. Vorsitzender", "2. Vorsitzender", "Sportwart", "Kassenwart", "Schriftführer", "Jugendwart"];
                    foreach ($rollen as $rolle): ?>
                        <option value="<?= $rolle ?>"><?= $rolle ?></option>
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
                        name="best_<?= $t ?>_wert" class="form-control"></div>
                <div class="form-group" style="flex: 1;"><label>Erreicht am</label><input type="date"
                        name="best_<?= $t ?>_datum" class="form-control"></div>
                <div class="form-group" style="flex: 1;"><label>Ort</label><input type="text" name="best_<?= $t ?>_ort"
                        class="form-control" placeholder="Kegelbahn..."></div>
            </div>
        <?php endforeach; ?>

        <div style="display: flex; gap: 20px; align-items: center; margin-top: 20px; margin-bottom: 20px;">
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Eintrittsdatum</label>
                <input type="date" name="eintrittsdatum" class="form-control">
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label
                    style="display: flex; align-items: center; gap: 10px; cursor: pointer; height: 100%; margin-top: 20px;">
                    <input type="checkbox" name="ist_gruendungsmitglied" value="1"
                        style="transform: scale(1.5); margin-right: 5px;">
                    <strong style="color: #2980b9;">Gründungsmitglied?</strong>
                </label>
            </div>
        </div>

        <div class="file-upload-box">
            <label>Profilbild</label>
            <input type="file" name="profilbild" accept=".jpg, .jpeg, .png, .webp" class="form-control"
                style="border: none; padding: 0;">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 12px;">Mitglied
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