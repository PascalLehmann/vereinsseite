<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL); 
$pageTitle = "Galerie Übersicht"; 
include 'includes/header.php'; 
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
            <h1>Galerie Übersicht</h1>
            <p style="margin-bottom: 30px;">Wähle eine Kategorie, um alle Bilder zu sehen.</p>
            
            <div class="gallery-grid">
                
                <a href="galerie-details.php?id=1" class="gallery-category-card">
                    
                    <div class="category-preview-circle">
                        <img src="https://via.placeholder.com/200x200" alt="Vorschau Sommerfest">
                    </div>

                    <div class="gallery-info">
                        Sommerfest 2025
                        <span class="img-count">(12 Bilder)</span>
                    </div>
                </a>

                <a href="galerie-details.php?id=2" class="gallery-category-card">
                    <div class="category-preview-circle">
                        <img src="https://via.placeholder.com/200x200" alt="Vorschau Training">
                    </div>
                    <div class="gallery-info">
                        Jugendtraining
                        <span class="img-count">(8 Bilder)</span>
                    </div>
                </a>

            </div>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>