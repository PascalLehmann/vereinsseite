<?php
include 'db.php';
$pageTitle = "News schreiben - Admin";
include 'includes/header.php';
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
            <a href="news.php" class="read-more" style="margin-bottom: 25px;">
                <i class="fa-solid fa-arrow-left"></i> Zurück zur Übersicht
            </a>
            
            <h1>Neue News verfassen</h1>
            
            <form action="news-speichern.php" method="POST" class="news-card">
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px; color: var(--secondary-blue);">Titel der News</label>
                    <input type="text" name="titel" required placeholder="Geben Sie eine Überschrift ein..." 
                           style="width:100%; padding:12px; border-radius:12px; border:1px solid #ddd; font-size:1rem; outline:none; focus:border-orange;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:8px; color: var(--secondary-blue);">Inhalt</label>
                    <textarea name="inhalt" required rows="12" placeholder="Was gibt es Neues?" 
                              style="width:100%; padding:12px; border-radius:12px; border:1px solid #ddd; font-family:inherit; font-size:1rem; resize:vertical; outline:none;"></textarea>
                </div>
                <div style="margin-bottom: 20px;">
        <label style="display:block; font-weight:bold; margin-bottom:8px; color: var(--secondary-blue);">Beitragsbild auswählen</label>
        <input type="file" name="news_bild" accept="image/*" 
               style="width:100%; padding:10px; border:1px dashed var(--primary-orange); border-radius:12px; background:#fff9f2;">
        <small style="color: #666;">Empfohlen: Querformat (z.B. 800x600px)</small>
    </div>
                <button type="submit" class="read-more" 
                        style="background: var(--primary-orange); color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-paper-plane"></i> News veröffentlichen
                </button>
            </form>

        </main>
    </div> <?php include 'includes/footer.php'; ?>
</div>