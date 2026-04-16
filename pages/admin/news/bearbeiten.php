<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. ZUGRIFFSPRÜFUNG
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit;
}
$perms = $_SESSION['permissions'] ?? [];
$canNewsEdit = !empty($perms['news_edit']);
$canNewsDelete = !empty($perms['news_delete']);
$canNewsDeleteHard = !empty($perms['news_delete_hard']);
if (!$canNewsEdit) {
    die("Zugriff verweigert.");
}

require_once __DIR__ . '/../../../db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: uebersicht.php");
    exit;
}

// News-Details laden
$stmtNews = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmtNews->execute([$id]);
$news = $stmtNews->fetch(PDO::FETCH_ASSOC);
if (!$news) {
    header("Location: uebersicht.php");
    exit;
}

// Bilder für die News laden (auch die gelöschten, wenn man die Berechtigung hat)
$stmtBilder = $pdo->prepare("SELECT id, bild_pfad, is_deleted FROM news_bilder WHERE news_id = ? ORDER BY id ASC");
$stmtBilder->execute([$id]);
$bilder = $stmtBilder->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "News bearbeiten";
require_once __DIR__ . '/../../../templates/header.php';
require_once __DIR__ . '/../../../templates/navigation.php';
?>

<main>
    <h2>News bearbeiten</h2>

    <div class="action-bar">
        <a href="uebersicht.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <form action="aktualisieren.php" method="POST" enctype="multipart/form-data" class="content-tile"
        style="max-width: 800px;">
        <input type="hidden" name="id" value="<?= $news['id'] ?>">

        <div class="form-group">
            <label for="titel">Titel:</label>
            <input type="text" id="titel" name="titel" class="form-control"
                value="<?= htmlspecialchars($news['titel']) ?>" required>
        </div>

        <div class="form-group">
            <label for="inhalt">Text:</label>
            <textarea id="inhalt" name="inhalt" rows="8"
                class="form-control"><?= htmlspecialchars($news['inhalt']) ?></textarea>
        </div>

        <div class="file-upload-box">
            <label for="bilder">Weitere Bilder hinzufügen:</label>
            <input type="file" id="bilder" name="bilder[]" multiple accept=".jpg, .jpeg, .png, .webp"
                class="form-control" style="border: none; padding: 0;">
            <small style="color: #666; display: block; margin-top: 5px;">Erlaubt: JPG, PNG, WEBP. Max. 5MB pro
                Bild.</small>
        </div>

        <!-- NEU: Bilder-Verwaltung -->
        <?php if (count($bilder) > 0): ?>
            <hr style="margin: 25px 0;">
            <h3>Bilder verwalten</h3>
            <div class="news-gallery-admin">
                <?php foreach ($bilder as $bild): ?>
                    <?php if ($bild['is_deleted'] && !$canNewsDeleteHard)
                        continue; ?>
                    <div class="admin-image-wrapper <?= $bild['is_deleted'] ? 'deleted' : '' ?>">
                        <img src="<?= htmlspecialchars($bild['bild_pfad']) ?>" alt="News Bild">
                        <div class="admin-image-actions">
                            <?php if ($bild['is_deleted']): ?>
                                <?php if ($canNewsDeleteHard): // Nur wer endgültig löschen darf, darf auch wiederherstellen ?>
                                    <a href="bild_aktion.php?action=restore&bild_id=<?= $bild['id'] ?>&news_id=<?= $id ?>" class="btn-restore" title="Wiederherstellen" style="margin-right: 10px;">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                    <a href="bild_aktion.php?action=hard_delete&bild_id=<?= $bild['id'] ?>&news_id=<?= $id ?>" class="btn-delete" title="Endgültig löschen" onclick="return confirm('Bild wirklich ENDGÜLTIG vom Server löschen? Dieser Vorgang kann nicht rückgängig gemacht werden.');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($canNewsDelete): ?>
                                        <a href="bild_aktion.php?action=delete&bild_id=<?= $bild['id'] ?>&news_id=<?= $id ?>"
                                            class="btn-delete" title="Löschen"
                                            onclick="return confirm('Bild archivieren? Es kann später wiederhergestellt werden.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <!-- Ende Bilder-Verwaltung -->

        <button type="submit" class="btn btn-primary"
            style="width: 100%; margin-top: 25px; padding: 12px; font-size: 1.1rem;">Änderungen speichern</button>
    </form>

    <script>
        CKEDITOR.replace('inhalt', {
            height: 300,
            language: 'de',
            versionCheck: false
        });
    </script>
</main>

<?php require_once __DIR__ . '/../../../templates/footer.php'; ?>