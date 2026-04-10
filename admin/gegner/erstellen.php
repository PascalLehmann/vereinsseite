<?php
include_once '../auth.php';
checkLogin();
$pageTitle = "Gegner hinzufügen";
include_once '../../includes/header.php';
?>
<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>
        <main class="content">
            <h1>Neuen Gegner anlegen</h1>
            <form action="speichern.php" method="POST" class="news-card">
                <div style="margin-bottom:15px;">
                    <label>Vereinsname</label>
                    <input type="text" name="name" required placeholder="z.B. Alle Neune e.V.">
                </div>
                <div style="display:grid; grid-template-columns: 2fr 1fr 2fr; gap:15px; margin-bottom:15px;">
                    <div><label>Straße</label><input type="text" name="strasse"></div>
                    <div><label>PLZ</label><input type="text" name="plz"></div>
                    <div><label>Ort</label><input type="text" name="ort"></div>
                </div>
                <button type="submit" class="read-more">Gegner speichern</button>
                <a href="index.php" style="margin-left:15px; color:gray;">Abbrechen</a>
            </form>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</div>