<?php
include_once 'auth.php';
checkLogin();
include '../db.php';
$pageTitle = "News schreiben - Admin";
include '../includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include '../includes/nav.php'; ?>
        
        <main class="content">
            <a href="news.php" class="read-more" style="margin-bottom: 25px;">
                <i class="fa-solid fa-arrow-left"></i> Zurück zur Übersicht
            </a>
            
            <h1>Neue News verfassen</h1>
            
            <form action="news-speichern.php" method="POST" enctype="multipart/form-data" class="news-card">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Titel der News</label>
                    <input type="text" name="titel" required style="width:100%; padding:12px; border-radius:12px; border:1px solid #ddd;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Inhalt (mit Formatierung)</label>
                    <textarea name="inhalt" id="news_inhalt" rows="15" style="width:100%;"></textarea>
                </div>

                <div style="margin-bottom: 25px; padding: 15px; background: #f9f9f9; border-radius: 12px; border: 1px dashed var(--primary-orange);">
                    <label style="display:block; font-weight:bold; margin-bottom:8px;">Bilder hochladen (Mehrere möglich)</label>
                    <input type="file" name="news_bilder[]" accept="image/*" multiple>
                    <p style="font-size: 0.8rem; margin-top: 5px; color: #666;">Du kannst mehrere Bilder gleichzeitig auswählen.</p>
                </div>
                
                <button type="submit" class="read-more" style="background: var(--primary-orange); color: white; border: none; cursor: pointer;">
                    <i class="fa-solid fa-paper-plane"></i> News mit Bildern veröffentlichen
                </button>
            </form>
        </main>
    </div>
<script>
    // Sicherstellen, dass das Skript erst läuft, wenn alles geladen ist
    window.addEventListener('load', function() {
        if (typeof CKEDITOR !== 'undefined' && document.getElementById('news_inhalt')) {
            CKEDITOR.replace('news_inhalt', {
                versionCheck: false, // Versucht den Versions-Check zu deaktivieren
                language: 'de',
                height: 400,
                allowedContent: true
            });
        }
    });
</script>
    <?php include '../includes/footer.php'; ?>
</div>