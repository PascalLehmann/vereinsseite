<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL); 

// ID-Validierung (analog zu argc in C)
$newsId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$pageTitle = "News Details"; 
include 'includes/header.php'; 
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
            <a href="news.php" class="read-more" style="margin-bottom: 25px;">&laquo; Zurück zur Übersicht</a>
            
            <article class="news-card" style="margin-top: 20px;">
                <small>Veröffentlicht am: 15. März 2026</small>
                <h1 style="color: var(--primary-orange); margin: 10px 0;">Saisonstart der ersten Mannschaft</h1>
                
                <div style="line-height: 1.8; font-size: 1.05rem; margin-top: 20px;">
                    <p><strong>Dies ist der vollständige Text des Artikels.</strong></p>
                    
                    <p>In Phase 2 werden wir hier den vollständigen "Longtext" aus der MySQL-Tabelle `news` abfragen. Die übergebene ID (<code><?php echo $newsId; ?></code>) wird dabei als Filter in der WHERE-Klausel benutzt, um exakt diesen Datensatz zu laden.</p>
                    
                    <p>Hier können dann auch Bilder, Zitate und ausführliche Berichte stehen, die in der Übersicht zu viel Platz wegnehmen würden. Das Design-Konzept mit den abgerundeten Ecken und dem leichten Schattenwurf bleibt auch hier erhalten, was für ein professionelles und ruhiges Gesamtbild sorgt.</p>
                    
                    <div style="margin: 30px 0; padding: 15px; background: #f9f9f9; border-left: 5px solid var(--primary-orange); border-radius: 5px;">
                        <em>Zitat: "Wir sind hochmotiviert und gehen mit viel Optimismus in die neue Spielzeit!", so der Trainer.</em>
                    </div>
                    
                    <p>Wir laden alle Fans herzlich ein, unser Team beim ersten Heimspiel lautstark zu unterstützen. Der Eintritt ist wie immer für Vereinsmitglieder frei.</p>
                </div>
            </article>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>