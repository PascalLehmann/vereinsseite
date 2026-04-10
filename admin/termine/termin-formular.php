<?php
include_once 'auth.php';
checkLogin();
include_once '../db.php';
$typ = $_GET['typ'] ?? 'standard';
include_once '../includes/header.php';

$mitglieder = $pdo->query("SELECT id, vorname, nachname FROM mitglieder ORDER BY nachname ASC")->fetchAll();
$gegnerListe = $pdo->query("SELECT id, name FROM gegner ORDER BY name ASC")->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../includes/nav.php'; ?>
        
        <main class="content">
            <h1>Spieltag planen (6 Spieler + 3 Auswechsel)</h1>
            
            <form action="termin-speichern.php" method="POST" class="news-card">
                <input type="hidden" name="typ" value="spiel">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label>Datum</label>
                        <input type="date" name="termin_datum" required style="width:100%; padding:10px; border-radius:10px; border:1px solid #ddd;">
                    </div>
                    <div>
                        <label>Gegner (Suche)</label>
                        <select name="gegner_id" id="gegner-search" style="width:100%;">
                            <option value="">Gegner suchen...</option>
                            <?php foreach($gegnerListe as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="background: #f9f9f9; padding: 20px; border-radius: 20px; margin-bottom: 20px;">
                    <h3 style="margin-bottom: 15px; color: var(--secondary-blue);">Aufstellung</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <h4>Stammspieler</h4>
                            <?php for($i=1; $i<=6; $i++): ?>
                                <div style="margin-bottom: 10px;">
                                    <label>Spieler <?= $i ?></label>
                                    <select name="s<?= $i ?>" class="player-select" style="width:100%;">
                                        <option value="">-- wählen --</option>
                                        <?php foreach($mitglieder as $m): ?>
                                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endfor; ?>
                        </div>

                        <div>
                            <h4>Auswechselspieler</h4>
                            <?php for($i=1; $i<=3; $i++): ?>
                                <div style="margin-bottom: 10px;">
                                    <label>Ersatz <?= $i ?></label>
                                    <select name="a<?= $i ?>" class="player-select" style="width:100%;">
                                        <option value="">-- wählen --</option>
                                        <?php foreach($mitglieder as $m): ?>
                                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <div>
                        <label>Spielführer</label>
                        <select name="spielfuehrer_id" id="captain-select" style="width:100%;">
                             <?php foreach($mitglieder as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['vorname'] . " " . $m['nachname']) ?></option>
                             <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Uhrzeit Spiel</label>
                        <input type="time" name="uhrzeit" style="width:100%; padding:8px; border-radius:8px;">
                    </div>
                    <div>
                        <label>Treffpunkt</label>
                        <input type="time" name="treffpunkt_zeit" style="width:100%; padding:8px; border-radius:8px;">
                    </div>
                </div>

                <button type="submit" class="read-more" style="margin-top:30px; background:var(--primary-orange); color:white; border:none; width:100%;">Spieltag Speichern</button>
            </form>
        </main>
    </div>
</div>

<script>
$(document).ready(function() {
    // 1. Select2 initialisieren
    $('#gegner-search, #captain-select').select2();
    $('.player-select').select2();

    // 2. Logik: Spieler darf nicht doppelt gewählt werden
    $('.player-select').on('change', function() {
        updatePlayerLists();
    });

    function updatePlayerLists() {
        let selectedPlayers = [];
        
        // Alle aktuell gewählten IDs sammeln
        $('.player-select').each(function() {
            if ($(this).val() !== "") {
                selectedPlayers.push($(this).val());
            }
        });

        // In jedem Dropdown die Optionen ein/ausblenden
        $('.player-select').each(function() {
            let currentDropdown = $(this);
            let currentValue = currentDropdown.val();

            currentDropdown.find('option').each(function() {
                let optionValue = $(this).val();
                
                if (optionValue !== "" && selectedPlayers.includes(optionValue) && optionValue !== currentValue) {
                    $(this).attr('disabled', 'disabled');
                } else {
                    $(this).removeAttr('disabled');
                }
            });
        });
        
        // Select2 Ansicht aktualisieren
        $('.player-select').select2();
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>