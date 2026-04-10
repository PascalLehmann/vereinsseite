<?php
include_once 'auth.php';
checkLogin();
include '../db.php';
$pageTitle = "News bearbeiten";
include '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Bestehende Daten laden
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    header("Location: news-admin.php");
    exit;
}

// 2. Bilder für diese News laden
$stmtB = $pdo->prepare("SELECT * FROM news_bilder WHERE news_id = ?");
$stmtB->execute([$id]);
$bilder = $stmtB->fetchAll();
?>

<div id="page-wrapper">
    <div class="container">
        <?php include '../includes/nav.php'; ?>
        
        <main class="content">
            <a href="news-admin.php" class="read-more" style="margin-bottom: 25px;">
                <i class="fa-solid fa-arrow-left"></i> Zurück zur Verwaltung
            </a>
            
            <h1>News bearbeiten</h1>
            
            <form action="news-update.php" method="POST" enctype="multipart/form-data" class="news-card">
                <input type="hidden" name="id" value="<?= $id; ?>">

                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Titel</label>
                    <input type="text" name="titel" value="<?= htmlspecialchars($news['titel']); ?>" required 
                           style="width:100%; padding:12px; border-radius:12px; border:1px solid #ddd;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Inhalt</label>
                    <textarea name="inhalt" id="news_inhalt" rows="15"><?= $news['inhalt']; ?></textarea>
                </div>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

                <h3>Bilder verwalten</h3>
                
                <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
                    <?php foreach ($bilder as $bild): ?>
                        <div style="position: relative; width: 120px;">
                            <img src="../img/news/<?= $bild['bild_pfad']; ?>" 
                                 style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd;">
                            <a href="bild-loeschen.php?bild_id=<?= $bild['id']; ?>&news_id=<?= $id; ?>" 
                               style="position: absolute; top: -10px; right: -10px; background: #e74c3c; color: white; 
                                      border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; 
                                      justify-content: center; text-decoration: none; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2);"
                               onclick="return confirm('Bild löschen?');">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-bottom: 25px; padding: 15px; background: #f9f9f9; border-radius: 12px; border: 1px dashed var(--primary-orange);">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Weitere Bilder hinzufügen</label>
                    <input type="file" name="news_bilder[]" accept="image/*" multiple>
                </div>
                
                <button type="submit" class="read-more" style="background: var(--primary-orange); color: white; border: none; cursor: pointer;">
                    <i class="fa-solid fa-floppy-disk"></i> Änderungen speichern
                </button>
            </form>
        </main>
    </div>

    <script>
        window.addEventListener('load', function() {
            if (typeof CKEDITOR !== 'undefined' && document.getElementById('news_inhalt')) {
                CKEDITOR.replace('news_inhalt', {
                    versionCheck: false,
                    language: 'de',
                    height: 400,
                    allowedContent: true
                });
            }
        });
    </script>

    <?php include '../includes/footer.php'; ?>
</div>