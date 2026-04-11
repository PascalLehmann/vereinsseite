<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

$pageTitle = "News bearbeiten";
include $_SERVER['DOCUMENT_ROOT'] . '/templates/header.php';
?>

<div id="page-wrapper">
    <div class="container">

        <main class="content">
            <h1>News bearbeiten</h1>

            <form action="/pages/admin/news/aktualisieren.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="id" value="<?= $news['id']; ?>">

                <label>Titel</label>
                <input type="text" name="titel" value="<?= htmlspecialchars($news['titel']); ?>" required>

                <label>Inhalt</label>
                <textarea name="inhalt" id="editor"><?= $news['inhalt']; ?></textarea>

                <label>Neue Bilder (optional)</label>
                <input type="file" name="bilder[]" multiple>

                <button type="submit" class="btn-primary">Aktualisieren</button>
            </form>

            <script>
                CKEDITOR.replace('editor');
            </script>

        </main>

    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/templates/footer.php'; ?>
</div>