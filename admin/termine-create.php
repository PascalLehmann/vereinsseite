<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';
$pageTitle = "Termin anlegen";
include_once '../includes/header.php';

// Daten für die Dropdowns laden
$mitglieder = $pdo->query("SELECT id, vorname, nachname FROM mitglieder ORDER BY nachname ASC")->fetchAll();
$gegner = $pdo->query("SELECT id, name FROM gegner ORDER BY name ASC")->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <h1>Neuen Termin / Spieltag anlegen</h1>
            
            <form action="termine-store.php" method="POST" class="news-card">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                    <div>
                        <label>Art des Termins</label>
                        <select name="typ" id="termin_typ" onchange="toggleSpielFields(this.value)" style="width:100%; padding:10px;">
                            <option value="allgemein">Allgemeiner Termin (Feier, Sitzung...)</option>
                            <option value="spiel">Spieltag (Wettkampf)</option>
                        </select>
                    </div>
                    <div>
                        <label>Datum</label>
                        <input type="date" name="termin_datum" required style="width:100%; padding:10px;">
                    </div>
                    <div>
                        <label>Uhrzeit (Beginn)</label>
                        <input type="time" name="uhrzeit" required style="width:100%; padding:10px;">
                    </div>
                </div>

                <div id="bereich_allgemein">
                    <div style="margin-bottom: 15px;">
                        <label>Titel / Anlass</label>
                        <input type="text" name="titel" placeholder="z.B. Weihnachtsfeier" style="width:100%; padding:10px;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Ort / Treffpunkt</label>
                        <input type="text" name="ort" placeholder="z.B. Vereinsheim" style="width:100%; padding:10px;">
                    </div>
                </div>

                <div id="bereich_spiel" style="display:none; background: #fdf2e9; padding: 20px; border-radius: 15px; border-left: 5px solid #e67e22;">
                    <h3 style="color: #e67e22; margin-bottom: 15px;">Spieldetails & Aufstellung</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label>Gegner</label>
                            <select name="gegner_id" style="width:100%; padding:10px;">
                                <option value="">-- Gegner wählen --</option>
                                <?php foreach($gegner as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Spielort</label>
                            <select name="heimspiel" style="width:100%; padding:10px;">
                                <option value="1">Heimspiel</option>
                                <option value="0">Auswärtsspiel</option>
                            </select>
                        </div>
                        <div>
                            <label>Treffpunkt Zeit (bei Auswärts)</label>
                            <input type="time" name="treffpunkt_zeit" style="width:100%; padding:10px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label>Spielführer</label>
                        <select name="spielfuehrer_id" style="width:100%; padding:10px; border: 2px solid #f1c40f;">
                            <option value="">-- Wählen --</option>
                            <?php foreach($mitglieder as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <label><strong>Aufstellung (Stamm 1-6)</strong></label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                        <?php for($i=1; $i<=6; $i++): ?>
                            <select name="s<?= $i ?>" style="width:100%; padding:8px;">
                                <option value="">Spieler <?= $i ?></option>
                                <?php foreach($mitglieder as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endfor; ?>
                    </div>

                    <label><strong>Ersatzspieler (1-3)</strong></label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <?php for($i=1; $i<=3; $i++): ?>
                            <select name="a<?= $i ?>" style="width:100%; padding:8px;">
                                <option value="">Ersatz <?= $i ?></option>
                                <?php foreach($mitglieder as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?></option>
                                <?php endforeach; ?>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="read-more" style="background: var(--primary-orange); border:none; color:white;">Termin speichern</button>
                    <a href="termine-admin.php" style="margin-left:15px; color:gray;">Abbrechen</a>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
function toggleSpielFields(val) {
    document.getElementById('bereich_spiel').style.display = (val === 'spiel') ? 'block' : 'none';
    document.getElementById('bereich_allgemein').style.display = (val === 'allgemein') ? 'block' : 'none';
}
</script>
<?php include_once '../includes/footer.php'; ?>