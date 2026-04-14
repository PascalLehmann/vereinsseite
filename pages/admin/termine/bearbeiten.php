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
if (!in_array('admin', $roles) && !in_array('autor', $roles)) {
    die("Zugriff verweigert.");
}

// 2. DATENBANK EINBINDEN
require_once __DIR__ . '/../../../db.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    header("Location: übersicht.php");
    exit;
}

// Termin laden
$stmt = $pdo->prepare("SELECT * FROM termine WHERE id = ?");
$stmt->execute([$id]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$t) {
    die("Termin nicht gefunden.");
}

// Daten für Dropdowns
$spieler_liste = $pdo->query("SELECT id, vorname, nachname FROM mitglieder ORDER BY vorname, nachname")->fetchAll(PDO::FETCH_ASSOC);
$gegner_liste = $pdo->query("SELECT id, name, spielzeit FROM gegner ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Termin bearbeiten";

// 3. LAYOUT EINBINDEN
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Termin bearbeiten</h2>

    <div class="action-bar">
        <a href="übersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <form action="aktualisieren.php" method="POST" class="content-tile" style="max-width: 800px;">
        <input type="hidden" name="id" value="<?= $t['id'] ?>">

        <div class="form-group">
            <label for="typ">Art des Termins:</label>
            <select id="typ" name="typ" class="form-control" style="font-weight: bold; font-size: 1.1rem;">
                <option value="spiel" <?= $t['typ'] === 'spiel' ? 'selected' : '' ?>>Ligaspiel / Pokalspiel</option>
                <option value="veranstaltung" <?= in_array($t['typ'], ['veranstaltung', 'allgemein']) ? 'selected' : '' ?>>Training / Event / Sonstiges</option>
            </select>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label for="termin_datum">Datum *</label>
                <input type="date" id="termin_datum" name="termin_datum" class="form-control"
                    value="<?= htmlspecialchars($t['termin_datum'] ?? '') ?>" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="uhrzeit">Uhrzeit</label>
                <input type="time" id="uhrzeit" name="uhrzeit" class="form-control"
                    value="<?= htmlspecialchars($t['uhrzeit'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="titel">Titel (z.B. "1. Spieltag" oder "Weihnachtsfeier")</label>
            <input type="text" id="titel" name="titel" class="form-control"
                value="<?= htmlspecialchars($t['titel'] ?? '') ?>">
        </div>

        <div id="bereich-spiel"
            style="background: #fdf8f5; border-left: 4px solid #e67e22; padding: 15px; margin-top: 20px; border-radius: 5px;">
            <h3 style="margin-top: 0; color: #e67e22;"><i class="fa-solid fa-trophy"></i> Spiel-Details</h3>

            <div class="form-group">
                <label for="gegner_id">Gegner Mannschaft</label>
                <select id="gegner_id" name="gegner_id" class="form-control select2-box" style="width: 100%;">
                    <option value="">-- Bitte wählen --</option>
                    <?php foreach ($gegner_liste as $g): ?>
                        <option value="<?= $g['id'] ?>" data-spielzeit="<?= htmlspecialchars($g['spielzeit'] ?? '') ?>" <?= $t['gegner_id'] == $g['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>
                        <input type="checkbox" id="heimspiel_checkbox" name="heimspiel" value="1" <?= $t['heimspiel'] ? 'checked' : '' ?>
                            style="transform: scale(1.5); margin-right: 10px;">
                        <strong>Heimspiel</strong>
                    </label>
                </div>
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label for="treffpunkt_zeit">Treffpunkt (Uhrzeit)</label>
                    <input type="time" id="treffpunkt_zeit" name="treffpunkt_zeit" class="form-control"
                        value="<?= htmlspecialchars($t['treffpunkt_zeit'] ?? '') ?>">
                </div>
                <div class="form-group" id="treffpunkt_ort_container" style="flex: 1; margin-bottom: 0; display: <?= $t['heimspiel'] ? 'none' : 'block' ?>;">
                    <label for="treffpunkt_ort">Treffpunkt (Ort)</label>
                    <input type="text" id="treffpunkt_ort" name="treffpunkt_ort" class="form-control" placeholder="z.B. Vereinsheim"
                        value="<?= htmlspecialchars($t['treffpunkt_ort'] ?? '') ?>">
                </div>
            </div>

            <hr style="border: 0; border-top: 1px dashed #ccc; margin: 20px 0;">
            <h4 style="margin-bottom: 10px;">Kader (Stammspieler)</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <div class="form-group">
                        <label for="s<?= $i ?>">Spieler <?= $i ?></label>
                        <select id="s<?= $i ?>" name="s<?= $i ?>" class="form-control select2-box" style="width: 100%;">
                            <option value="">- Leer -</option>
                            <?php foreach ($spieler_liste as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $t['s' . $i] == $s['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['vorname'] . ' ' . $s['nachname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endfor; ?>
            </div>

            <h4 style="margin-top: 20px; margin-bottom: 10px;">Ersatzspieler & Spielführer</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="form-group">
                        <label for="a<?= $i ?>">Auswechselspieler <?= $i ?></label>
                        <select id="a<?= $i ?>" name="a<?= $i ?>" class="form-control select2-box" style="width: 100%;">
                            <option value="">- Leer -</option>
                            <?php foreach ($spieler_liste as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $t['a' . $i] == $s['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['vorname'] . ' ' . $s['nachname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endfor; ?>
                <div class="form-group">
                    <label for="spielfuehrer_id" style="color: #e67e22;"><i class="fa-solid fa-star"></i>
                        Spielführer</label>
                    <select id="spielfuehrer_id" name="spielfuehrer_id" class="form-control select2-box"
                        style="width: 100%;">
                        <option value="">- Auswählen -</option>
                        <?php foreach ($spieler_liste as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $t['spielfuehrer_id'] == $s['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['vorname'] . ' ' . $s['nachname']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div id="bereich-veranstaltung"
            style="background: #f4f8fb; border-left: 4px solid #3498db; padding: 15px; margin-top: 20px; border-radius: 5px; display: none;">
            <h3 style="margin-top: 0; color: #3498db;"><i class="fa-solid fa-calendar-alt"></i> Event-Details</h3>

            <div class="form-group">
                <label for="veranstaltungsart">Art (Training, Sitzung, Feier...)</label>
                <input type="text" id="veranstaltungsart" name="veranstaltungsart" class="form-control"
                    value="<?= htmlspecialchars($t['veranstaltungsart'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="ort">Ort / Anschrift</label>
                <input type="text" id="ort" name="ort" class="form-control"
                    value="<?= htmlspecialchars($t['ort'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="beschreibung">Zusätzliche Infos (optional)</label>
                <textarea id="beschreibung" name="beschreibung" rows="4"
                    class="form-control"><?= htmlspecialchars($t['beschreibung'] ?? '') ?></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"
            style="margin-top: 25px; width: 100%; font-size: 1.1rem; padding: 12px;">
            Änderungen Speichern
        </button>
    </form>
</main>

<script>
    $(document).ready(function () {
        $('.select2-box').select2({
            placeholder: "Bitte wählen...",
            allowClear: true
        });

        const $typSelect = $('#typ');
        const $bereichSpiel = $('#bereich-spiel');
        const $bereichVeranstaltung = $('#bereich-veranstaltung');
        const $heimspielCheckbox = $('#heimspiel_checkbox');
        const $treffpunktOrtContainer = $('#treffpunkt_ort_container');
        const $gegnerSelect = $('#gegner_id');
        const $uhrzeitInput = $('#uhrzeit');

        function toggleBereiche() {
            if ($typSelect.val() === 'spiel') {
                $bereichSpiel.slideDown();
                $bereichVeranstaltung.slideUp();
            } else {
                $bereichSpiel.slideUp();
                $bereichVeranstaltung.slideDown();
            }
        }

        function toggleTreffpunkt() {
            if ($heimspielCheckbox.is(':checked')) {
                $treffpunktOrtContainer.slideUp();
            } else {
                $treffpunktOrtContainer.slideDown();
            }
        }

        function checkAutoUhrzeit() {
            if (!$heimspielCheckbox.is(':checked')) {
                const selectedOption = $gegnerSelect.find('option:selected');
                const spielzeit = selectedOption.attr('data-spielzeit');
                
                if (spielzeit && spielzeit !== '') {
                    $uhrzeitInput.val(spielzeit.substring(0, 5));
                }
            }
        }

        toggleBereiche();
        toggleTreffpunkt();
        $typSelect.on('change', toggleBereiche);
        $heimspielCheckbox.on('change', toggleTreffpunkt);
        $gegnerSelect.on('change', checkAutoUhrzeit);
        $heimspielCheckbox.on('change', checkAutoUhrzeit);
    });
</script>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>