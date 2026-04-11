<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$pageTitle = "News erstellen";
include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php';
?>

<div id="page-wrapper">
    <div class="container">

        <main class="content">
            <h1>Neue News erstellen</h1>

            <form action="/pages/admin/news/speichern.php" method="POST" enctype="multipart/form-data">

                <label>Titel</label>
                <input type="text" name="titel" required>

                <label>Inhalt</label>
                <textarea name="inhalt" id="editor"></textarea>

                <label>Bilder (optional)</label>
                <input type="file" name="bilder[]" multiple>

                <button type="submit" class="btn-primary">Speichern</button>
            </form>

            <script>
                CKEDITOR.replace('editor');
            </script>

        </main>

    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>
</div>