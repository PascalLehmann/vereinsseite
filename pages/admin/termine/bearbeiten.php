<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';
$pageTitle = "Termin bearbeiten";
include_once '../includes/header.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: termine-admin.php"); exit; }

// Termin laden
$stmt = $pdo->prepare("SELECT * FROM termine WHERE id = ?");
$stmt->execute([$id]);
$t = $stmt->fetch();
if (!$t) die("Termin nicht gefunden.");

// Daten für Dropdowns
$mitglieder = $pdo->query("SELECT id, vorname, nachname FROM mitglieder ORDER BY nachname ASC")->fetchAll();
$gegner = $pdo->query("SELECT id, name FROM gegner ORDER BY name ASC")->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <h1>Termin bearbeiten</h1>
            
            <form action="termine-update.php" method="POST" class="news-card">
                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                    <div>
                        <label>Art des Termins</label>
                        <select name="typ" onchange="toggleFields(this.value)" style="width:100%; padding:10px;">
                            <option value="allgemein" <?= $t['typ'] == 'allgemein' ? 'selected' : '' ?>>Allgemein</option>
                            <option value="spiel" <?= $t['typ'] == 'spiel' ? 'selected' : '' ?>>Spieltag</option>
                        </select>
                    </div>
                    <div>
                        <label>Datum</label>
                        <input type="date" name="termin_datum" value="<?= $t['termin_datum'] ?>" required style="width:100%; padding:10px;">
                    </div>
                    <div>
                        <label>Uhrzeit</label>
                        <input type="time" name="uhrzeit" value="<?= $t['uhrzeit'] ?>" required style="width:100%; padding:10px;">
                    </div>
                </div>

                <div id="section_allgemein" style="display: <?= $t['typ'] == 'allgemein' ? 'block' : 'none' ?>;">
                    <label>Titel / Beschreibung / Ort</label>
                    <input type="text" name="titel" value="<?= htmlspecialchars($t['titel'] ?? '') ?>" placeholder="Titel" style="width:100%; padding:10px; margin-bottom:10px;">
                    <textarea name="beschreibung" style="width:100%; padding:10px; height:80px;"><?= htmlspecialchars($t['beschreibung'] ?? '') ?></textarea>
                    <input type="text" name="ort" value="<?= htmlspecialchars($t['ort'] ?? '') ?>" placeholder="Ort" style="width:100%; padding:10px; margin-top:10px;">
                </div>

                <div id="section_spiel" style="display: <?= $t['typ'] == 'spiel' ? 'block' : 'none' ?>; background: #fdf2e9; padding: 20px; border-radius: 15px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label>Gegner</label>
                            <select name="gegner_id" style="width:100%; padding:10px;">
                                <?php foreach($gegner as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= $t['gegner_id'] == $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Heim/Auswärts</label>
                            <select name="heimspiel" style="width:100%; padding:10px;">
                                <option value="1" <?= $t['heimspiel'] ? 'selected' : '' ?>>Heimspiel</option>
                                <option value="0" <?= !$t['heimspiel'] ? 'selected' : '' ?>>Auswärts</option>
                            </select>
                        </div>
                        <div>
                            <label>Treffpunkt</label>
                            <input type="time" name="treffpunkt_zeit" value="<?= $t['treffpunkt_zeit'] ?>" style="width:100%; padding:10px;">
                        </div>
                    </div>

                    <label>Spielführer</label>
                    <select name="spielfuehrer_id" style="width:100%; padding:10px; margin-bottom:20px;">
                        <option value="">-- Keiner --</option>
                        <?php foreach($mitglieder as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $t['spielfuehrer_id'] == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['vorname']." ".$m['nachname']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Aufstellung (1-6 & Ersatz)</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                        <?php 
                        $pos = ['s1','s2','s3','s4','s5','s6','a1','a2','a3'];
                        foreach($pos as $p): ?>
                            <select name="<?= $p ?>" style="width:100%; padding:8px;">
                                <option value="">Leer (<?= strtoupper($p) ?>)</option>
                                <?php foreach($mitglieder as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= $t[$p] == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['vorname']." ".$m['nachname']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="read-more" style="background:var(--primary-orange); border:none; color:white;">Speichern</button>
                    <a href="termine-admin.php" style="margin-left:15px; color:gray;">Abbrechen</a>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
function toggleFields(val) {
    document.getElementById('section_allgemein').style.display = (val === 'allgemein') ? 'block' : 'none';
    document.getElementById('section_spiel').style.display = (val === 'spiel') ? 'block' : 'none';
}
</script>
<?php include_once '../includes/footer.php'; ?>