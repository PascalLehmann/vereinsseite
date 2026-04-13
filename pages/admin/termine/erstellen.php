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

// 2. DATENBANK EINBINDEN
require_once __DIR__ . '/../../../db.php';

$error = '';
$success = '';

// Hilfsfunktion: Wandelt leere Strings in echte NULL-Werte für die DB um
function setNullIfEmpty($val)
{
    return (empty($val) && $val !== '0') ? null : $val;
}

// 3. POST-REQUEST VERARBEITEN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Grunddaten (Immer vorhanden)
    $typ = $_POST['typ'] ?? 'veranstaltung';
    $titel = trim($_POST['titel'] ?? '');
    $termin_datum = setNullIfEmpty($_POST['termin_datum']);
    $uhrzeit = setNullIfEmpty($_POST['uhrzeit']);

    // Veranstaltungs-Daten
    $veranstaltungsart = setNullIfEmpty($_POST['veranstaltungsart'] ?? '');
    $ort = setNullIfEmpty($_POST['ort'] ?? '');
    $beschreibung = setNullIfEmpty($_POST['beschreibung'] ?? '');

    // Spiel-Daten
    $heimspiel = isset($_POST['heimspiel']) ? 1 : 0;
    $gegner_id = setNullIfEmpty($_POST['gegner_id'] ?? null);
    $treffpunkt_zeit = setNullIfEmpty($_POST['treffpunkt_zeit'] ?? null);
    $treffpunkt_ort = setNullIfEmpty($_POST['treffpunkt_ort'] ?? null);

    // Kader (S1-S6, A1-A3, Spielführer)
    $s1 = setNullIfEmpty($_POST['s1'] ?? null);
    $s2 = setNullIfEmpty($_POST['s2'] ?? null);
    $s3 = setNullIfEmpty($_POST['s3'] ?? null);
    $s4 = setNullIfEmpty($_POST['s4'] ?? null);
    $s5 = setNullIfEmpty($_POST['s5'] ?? null);
    $s6 = setNullIfEmpty($_POST['s6'] ?? null);
    $a1 = setNullIfEmpty($_POST['a1'] ?? null);
    $a2 = setNullIfEmpty($_POST['a2'] ?? null);
    $a3 = setNullIfEmpty($_POST['a3'] ?? null);
    $spielfuehrer_id = setNullIfEmpty($_POST['spielfuehrer_id'] ?? null);

    if (empty($termin_datum)) {
        $error = "Das Datum ist ein Pflichtfeld.";
    } else {
        try {
            $sql = "INSERT INTO termine 
                    (typ, titel, veranstaltungsart, termin_datum, uhrzeit, treffpunkt_zeit, treffpunkt_ort, heimspiel, gegner_id, ort, beschreibung, s1, s2, s3, s4, s5, s6, a1, a2, a3, spielfuehrer_id) 
                    VALUES 
                    (:typ, :titel, :veranstaltungsart, :termin_datum, :uhrzeit, :treffpunkt_zeit, :treffpunkt_ort, :heimspiel, :gegner_id, :ort, :beschreibung, :s1, :s2, :s3, :s4, :s5, :s6, :a1, :a2, :a3, :spielfuehrer_id)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':typ' => $typ,
                ':titel' => $titel,
                ':veranstaltungsart' => $veranstaltungsart,
                ':termin_datum' => $termin_datum,
                ':uhrzeit' => $uhrzeit,
                ':treffpunkt_zeit' => $treffpunkt_zeit,
                ':treffpunkt_ort' => $treffpunkt_ort,
                ':heimspiel' => $heimspiel,
                ':gegner_id' => $gegner_id,
                ':ort' => $ort,
                ':beschreibung' => $beschreibung,
                ':s1' => $s1,
                ':s2' => $s2,
                ':s3' => $s3,
                ':s4' => $s4,
                ':s5' => $s5,
                ':s6' => $s6,
                ':a1' => $a1,
                ':a2' => $a2,
                ':a3' => $a3,
                ':spielfuehrer_id' => $spielfuehrer_id
            ]);

            header("Location: übersicht.php?success=1");
            exit;

        } catch (PDOException $e) {
            $error = "Fehler beim Speichern: " . $e->getMessage();
        }
    }
}

// 4. DATEN FÜR DROPDOWNS LADEN (Gegner & Spieler)
$gegner_liste = $pdo->query("SELECT id, name FROM gegner ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$spieler_liste = $pdo->query("SELECT id, vorname, nachname FROM mitglieder ORDER BY vorname, nachname")->fetchAll(PDO::FETCH_ASSOC);

// 5. LAYOUT EINBINDEN
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>Neuen Termin anlegen</h2>

    <div class="action-bar">
        <a href="übersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <?php if ($error): ?>
        <p class="alert-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="erstellen.php" method="POST" class="content-tile" style="max-width: 800px;">

        <div class="form-group">
            <label for="typ">Art des Termins:</label>
            <select id="typ" name="typ" class="form-control" style="font-weight: bold; font-size: 1.1rem;">
                <option value="spiel">Ligaspiel / Pokalspiel</option>
                <option value="veranstaltung">Training / Event / Sonstiges</option>
            </select>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label for="termin_datum">Datum *</label>
                <input type="date" id="termin_datum" name="termin_datum" class="form-control" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="uhrzeit">Uhrzeit</label>
                <input type="time" id="uhrzeit" name="uhrzeit" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="titel">Titel (z.B. "1. Spieltag" oder "Weihnachtsfeier")</label>
            <input type="text" id="titel" name="titel" class="form-control">
        </div>

        <div id="bereich-spiel"
            style="background: #fdf8f5; border-left: 4px solid #e67e22; padding: 15px; margin-top: 20px; border-radius: 5px;">
            <h3 style="margin-top: 0; color: #e67e22;"><i class="fa-solid fa-trophy"></i> Spiel-Details</h3>

            <div class="form-group">
                <label for="gegner_id">Gegner Mannschaft</label>
                <select id="gegner_id" name="gegner_id" class="form-control select2-box" style="width: 100%;">
                    <option value="">-- Bitte wählen --</option>
                    <?php foreach ($gegner_liste as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>
                        <input type="checkbox" id="heimspiel_checkbox" name="heimspiel" value="1" checked
                            style="transform: scale(1.5); margin-right: 10px;">
                        <strong>Heimspiel</strong>
                    </label>
                </div>
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label for="treffpunkt_zeit">Treffpunkt (Uhrzeit)</label>
                    <input type="time" id="treffpunkt_zeit" name="treffpunkt_zeit" class="form-control">
                </div>
                <div class="form-group" id="treffpunkt_ort_container" style="flex: 1; margin-bottom: 0; display: none;">
                    <label for="treffpunkt_ort">Treffpunkt (Ort)</label>
                    <input type="text" id="treffpunkt_ort" name="treffpunkt_ort" class="form-control"
                        placeholder="z.B. Vereinsheim">
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
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['vorname'] . ' ' . $s['nachname']) ?>
                                </option>
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
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['vorname'] . ' ' . $s['nachname']) ?>
                                </option>
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
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['vorname'] . ' ' . $s['nachname']) ?>
                            </option>
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
                <input type="text" id="veranstaltungsart" name="veranstaltungsart" class="form-control">
            </div>

            <div class="form-group">
                <label for="ort">Ort / Anschrift</label>
                <input type="text" id="ort" name="ort" class="form-control">
            </div>

            <div class="form-group">
                <label for="beschreibung">Zusätzliche Infos (optional)</label>
                <textarea id="beschreibung" name="beschreibung" rows="4" class="form-control"></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"
            style="margin-top: 25px; width: 100%; font-size: 1.1rem; padding: 12px;">
            Termin Speichern
        </button>
    </form>
</main>

<script>
    $(document).ready(function () {
        // 1. SELECT2 INIT (Macht die Dropdowns suchbar!)
        $('.select2-box').select2({
            placeholder: "Bitte wählen...",
            allowClear: true
        });

        // 2. LOGIK FÜR DAS UMSCHALTEN (Spiel vs Veranstaltung)
        const $typSelect = $('#typ');
        const $bereichSpiel = $('#bereich-spiel');
        const $bereichVeranstaltung = $('#bereich-veranstaltung');
        const $heimspielCheckbox = $('#heimspiel_checkbox');
        const $treffpunktOrtContainer = $('#treffpunkt_ort_container');

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

        // Beim Start einmal ausführen
        toggleBereiche();
        toggleTreffpunkt();

        // Bei jeder Änderung im Dropdown ausführen
        $typSelect.on('change', toggleBereiche);
        $heimspielCheckbox.on('change', toggleTreffpunkt);
    });
</script>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>