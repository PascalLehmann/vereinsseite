<?php
include_once '../auth.php';
checkLogin();
include_once '../../db.php';

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();
if (!$news) {
    header("Location: uebersicht.php");
    exit;
}

// Hier ebenfalls 'bild_pfad'
$stmtBilder = $pdo->prepare("SELECT * FROM news_bilder WHERE news_id = ? ORDER BY id ASC");
$stmtBilder->execute([$id]);
$galerie = $stmtBilder->fetchAll();

$pageTitle = "News bearbeiten";
include_once '../../includes/header.php';
?>


<div id="page-wrapper">
    <div class="container">
        <?php include_once '../../includes/nav.php'; ?>
        <main class="content">
            <h1>Beitrag bearbeiten</h1>
            <form action="aktualisieren.php" method="POST" enctype="multipart/form-data" class="news-card">
                <input type="hidden" name="id" value="<?= $news['id'] ?>">

                <div style="margin-bottom:20px;">
                    <label>Titel</label>
                    <input type="text" name="titel" value="<?= htmlspecialchars($news['titel']) ?>" required
                        style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;">
                </div>

                <div style="margin-bottom:20px;">
                    <label>Inhalt</label>
                    <textarea name="inhalt" id="editor1"><?= $news['inhalt'] ?></textarea>
                </div>

                <div style="margin-bottom:30px;">
                    <label style="display:block; font-weight:bold; margin-bottom:10px;">Aktuelle Galerie</label>
                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 15px; background: #f9f9f9; padding: 15px; border-radius: 10px; border: 1px solid #ddd;">
                        <?php foreach ($galerie as $bild): ?>
                                <div style="background: white; padding: 5px; border-radius: 5px; text-align: center;">
                                    <?php
                                    $cleanName = str_replace('img/news/', '', $bild['bild_pfad']);
                                    $pfad = "../../img/news/" . $cleanName;
                                    ?>
                                    <img src="<?= $pfad ?>"
                                        style="width: 100%; height: 80px; object-fit: cover; border-radius: 3px;">
                                    <label
                                        style="display: block; font-size: 0.75rem; color: #e74c3c; margin-top: 5px; cursor: pointer;">
                                        <input type="checkbox" name="delete_bilder[]" value="<?= $bild['id'] ?>"> löschen
                                    </label>
                                </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div
                    style="background: #fdf2e9; padding: 20px; border-radius: 12px; margin-bottom:25px; border: 1px dashed var(--primary-orange);">
                    <label>Neue Bilder hinzufügen</label>
                    <input type="file" name="bilder[]" accept="image/*" multiple style="width: 100%; margin-top: 10px;">
                </div>

                <button type="submit" class="read-more"
                    style="background: var(--primary-orange); color:white; border:none; padding:12px 30px; cursor:pointer;">Speichern</button>
                <a href="uebersicht.php" style="margin-left:15px; color:gray;">Abbrechen</a>
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