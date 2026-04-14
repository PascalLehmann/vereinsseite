<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$roles = $_SESSION['roles'] ?? [];
if (!in_array('admin', $roles) && !in_array('autor', $roles)) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    die("News nicht gefunden.");
}

$pageTitle = "News bearbeiten";
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>News bearbeiten</h2>

    <div class="action-bar">
        <a href="übersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <form action="aktualisieren.php" method="POST" enctype="multipart/form-data" class="content-tile"
        style="max-width: 800px;">
        <input type="hidden" name="id" value="<?= $news['id']; ?>">

        <div class="form-group">
            <label for="titel">Titel:</label>
            <input type="text" id="titel" name="titel" class="form-control"
                value="<?= htmlspecialchars($news['titel']); ?>" required>
        </div>

        <div class="form-group">
            <label for="inhalt">Text:</label>
            <textarea id="editor" name="inhalt"
                class="form-control"><?= htmlspecialchars($news['inhalt']); ?></textarea>
        </div>

        <div class="file-upload-box">
            <label for="bilder">Weitere Bilder hinzufügen (Optional):</label>
            <input type="file" id="bilder" name="bilder[]" multiple accept=".jpg, .jpeg, .png, .webp"
                class="form-control" style="border: none; padding: 0;">
            <small style="color: #666; display: block; margin-top: 5px;">Erlaubt: JPG, PNG, WEBP. Max. 5MB pro
                Bild.</small>
        </div>

        <button type="submit" class="btn btn-primary"
            style="margin-top: 15px; font-size: 1.1rem; padding: 12px 20px;">Änderungen speichern</button>
    </form>

    <script>
        CKEDITOR.replace('editor', {
            height: 400,
            language: 'de',
            versionCheck: false
        });
    </script>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>