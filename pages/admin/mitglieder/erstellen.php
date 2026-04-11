<?php
include_once '../auth.php';
checkLogin();
$pageTitle = "Mitglied hinzufügen";
include_once '../../includes/header.php';
?>
<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>
        <main class="content">
            <h1>Neues Mitglied anlegen</h1>
            <form action="speichern.php" method="POST" enctype="multipart/form-data" class="news-card">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:15px;">
                    <div><label>Vorname</label><input type="text" name="vorname" required></div>
                    <div><label>Nachname</label><input type="text" name="nachname" required></div>
                </div>

                <div style="background: #fdf2e9; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="im_vorstand" id="vorstand_check" value="1"
                            onchange="toggleVorstand(this.checked)" style="width: auto; margin: 0;">
                        <strong>Ist Mitglied im Vorstand?</strong>
                    </label>

                    <div id="vorstand_pos_box" style="display: none; margin-top: 15px;">
                        <label>Vorstands-Position</label>
                        <select name="vorstands_rolle">
                            <option value="">-- Bitte wählen --</option>
                            <option value="1. Vorstand">1. Vorstand</option>
                            <option value="2. Vorstand">2. Vorstand</option>
                            <option value="Sportwart">Sportwart</option>
                            <option value="Kassierer">Kassierer</option>
                            <option value="Schriftführer">Schriftführer</option>
                            <option value="Jugendwart">Jugendwart</option>
                        </select>
                    </div>
                </div>

                <h3 style="margin: 20px 0 10px; color: var(--secondary-blue);">Persönliche Bestwerte</h3>
                <?php $typen = ['100', '120', '200'];
                foreach ($typen as $t): ?>
                    <div
                        style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:15px; margin-bottom:15px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                        <div><label><?= $t ?> Würfe (Holz)</label><input type="number" name="best_<?= $t ?>_wert"></div>
                        <div><label>Erreicht am</label><input type="date" name="best_<?= $t ?>_datum"></div>
                        <div><label>Ort</label><input type="text" name="best_<?= $t ?>_ort" placeholder="Kegelbahn...">
                        </div>
                    </div>
                <?php endforeach; ?>

                <div
                    style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px; align-items: end;">
                    <div>
                        <label>Eintrittsdatum</label>
                        <input type="date" name="eintrittsdatum">
                    </div>
                    <div style="padding-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="ist_gruendungsmitglied" value="1"
                                style="width: auto; margin: 0;">
                            <strong>Gründungsmitglied?</strong>
                        </label>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label>Profilbild</label>
                    <input type="file" name="profilbild">
                </div>

                <button type="submit" class="read-more">Mitglied speichern</button>
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