<?php
include_once '../auth.php';
checkLogin();
$pageTitle = "News erstellen";
include_once '../../includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>

        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h1>Neuen News-Beitrag schreiben</h1>
                <a href="uebersicht.php" style="color: gray; text-decoration: none;"><i
                        class="fa-solid fa-arrow-left"></i> Zurück</a>
            </div>

            <form action="speichern.php" method="POST" enctype="multipart/form-data" class="news-card">
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Titel des Beitrags</label>
                    <input type="text" name="titel" required placeholder="Geben Sie einen aussagekräftigen Titel ein..."
                        style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Inhalt / Text</label>
                    <textarea name="inhalt" id="editor1" rows="10" cols="80"></textarea>
                </div>

                <div
                    style="background: #f4f7f6; padding: 20px; border-radius: 12px; margin-bottom:25px; border: 1px dashed #ccc;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Bilder-Galerie</label>
                    <input type="file" name="bilder[]" accept="image/*" multiple
                        style="background: white; padding: 10px; border-radius: 5px; width: 100%;">
                    <p style="font-size: 0.8rem; color: #666; margin-top: 8px;">
                        <i class="fa-solid fa-circle-info"></i> Tipp: Halte <strong>Strg</strong> (oder Cmd) gedrückt,
                        um mehrere Bilder gleichzeitig auszuwählen.
                    </p>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="read-more"
                        style="background: var(--primary-orange); color: white; border: none; cursor: pointer; padding: 12px 30px;">
                        <i class="fa-solid fa-floppy-disk"></i> Beitrag speichern & veröffentlichen
                    </button>
                </div>
            </form>
        </main>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</div>

<script>
    // Ersetzt die Textarea mit der ID 'editor1' durch den CKEditor
    CKEDITOR.replace('editor1', {
        language: 'de',
        height: 400,
        versionCheck: false, // Dies schaltet die Sicherheitswarnung ab
        removeButtons: 'About'
    });
</script>