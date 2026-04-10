<?php
include_once '../auth.php';
checkLogin();
$pageTitle = "News erstellen";
include_once '../../includes/header.php';
?>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>
        <main class="content">
            <h1>Neuen News-Beitrag schreiben</h1>

            <form action="speichern.php" method="POST" enctype="multipart/form-data" class="news-card">
                <div style="margin-bottom:15px;">
                    <label>Titel</label>
                    <input type="text" name="titel" required>
                </div>

                <div style="margin-bottom:15px;">
                    <label>Inhalt</label>
                    <textarea name="inhalt" id="editor1"></textarea>
                </div>

                <div style="margin-bottom:20px;">
                    <label>Bilder auswählen (Mehrfachauswahl möglich)</label>
                    <input type="file" name="bilder[]" accept="image/*" multiple>
                    <small>Das erste Bild wird als Vorschaubild verwendet.</small>
                </div>

                <button type="submit" class="read-more">Beitrag speichern</button>
                <a href="übersicht.php" style="margin-left:15px; color:gray;">Abbrechen</a>
            </form>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</div>

<script>
    CKEDITOR.replace('editor1');
</script>