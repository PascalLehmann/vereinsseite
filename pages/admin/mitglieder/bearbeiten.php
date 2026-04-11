<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM mitglieder WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();
if (!$m) {
    header("Location: übersicht.php");
    exit;
}

$pageTitle = "Mitglied bearbeiten";
include_once '../../includes/header.php';
?>
<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>
        <main class="content">
            <h1>Mitglied bearbeiten: <?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?></h1>
            <form action="aktualisieren.php" method="POST" enctype="multipart/form-data" class="news-card">
                <input type="hidden" name="id" value="<?= $m['id'] ?>">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:15px;">
                    <div><label>Vorname</label><input type="text" name="vorname"
                            value="<?= htmlspecialchars($m['vorname']) ?>" required></div>
                    <div><label>Nachname</label><input type="text" name="nachname"
                            value="<?= htmlspecialchars($m['nachname']) ?>" required></div>
                </div>

                <div style="background: #fdf2e9; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="im_vorstand" id="vorstand_check" value="1"
                            onchange="toggleVorstand(this.checked)" <?= $m['im_vorstand'] ? 'checked' : '' ?>
                            style="width: auto; margin: 0;">
                        <strong>Ist Mitglied im Vorstand?</strong>
                    </label>

                    <div id="vorstand_pos_box"
                        style="display: <?= $m['im_vorstand'] ? 'block' : 'none' ?>; margin-top: 15px;">
                        <label>Vorstands-Position</label>
                        <select name="vorstands_rolle">
                            <option value="">-- Bitte wählen --</option>
                            <?php
                            $rollen = ["1. Vorstand", "2. Vorstand", "Sportwart", "Kassierer", "Schriftführer", "Jugendwart"];
                            foreach ($rollen as $rolle): ?>
                                <option value="<?= $rolle ?>" <?= ($m['vorstands_rolle'] == $rolle) ? 'selected' : '' ?>>
                                    <?= $rolle ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <script>
                    function toggleVorstand(isChecked) {
                        const box = document.getElementById('vorstand_pos_box');
                        if (box) {
                            box.style.display = isChecked ? 'block' : 'none';
                        }
                    }
                </script>

                <h3 style="margin: 20px 0 10px; color: var(--secondary-blue);">Persönliche Bestwerte</h3>
                <?php $typen = ['100', '120', '200'];
                foreach ($typen as $t): ?>
                    <div
                        style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:15px; margin-bottom:15px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                        <div><label><?= $t ?> Würfe</label><input type="number" name="best_<?= $t ?>_wert"
                                value="<?= $m['best_' . $t . '_wert'] ?>"></div>
                        <div><label>Datum</label><input type="date" name="best_<?= $t ?>_datum"
                                value="<?= $m['best_' . $t . '_datum'] ?>"></div>
                        <div><label>Ort</label><input type="text" name="best_<?= $t ?>_ort"
                                value="<?= htmlspecialchars($m['best_' . $t . '_ort'] ?? '') ?>"></div>
                    </div>
                <?php endforeach; ?>

                <div
                    style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px; align-items: end;">
                    <div><label>Eintrittsdatum</label><input type="date" name="eintrittsdatum"
                            value="<?= $m['eintrittsdatum'] ?>"></div>
                    <div style="padding-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="ist_gruendungsmitglied" value="1"
                                <?= $m['ist_gruendungsmitglied'] ? 'checked' : '' ?>>
                            <strong>Gründungsmitglied?</strong>
                        </label>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label>Profilbild ändern</label>
                    <input type="file" name="profilbild">
                </div>

                <button type="submit" class="read-more">Änderungen speichern</button>
                <a href="übersicht.php" style="margin-left:15px; color:gray;">Abbrechen</a>
            </form>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</div>
<script>
    function toggleVorstand(isChecked) {
        document.getElementById('vorstand_pos_box').style.display = isChecked ? 'block' : 'none';
    }
</script>