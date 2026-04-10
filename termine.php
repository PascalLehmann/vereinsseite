<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL); 
$pageTitle = "Termine"; 
include 'includes/header.php'; 
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
            <h1>Vereinstermine</h1>
            <p style="margin-bottom: 30px;">Alle kommenden Ereignisse auf einen Blick.</p>
            
            <article class="news-card">
                <small>20. März 2026 | 18:30 Uhr</small>
                <h2>Training Jugend</h2>
                <p>Unser wöchentliches Training für die U19 in der Sporthalle West. Neue Interessenten sind herzlich willkommen!</p>
                <a href="termin-details.php?id=1" class="read-more">Details anzeigen &raquo;</a>
            </article>

            <article class="news-card">
                <small>22. März 2026 | 15:00 Uhr</small>
                <h2>Heimspiel Herren</h2>
                <p>Das Derby gegen den SC Musterstadt. Kommt vorbei und feuert unsere Mannschaft an! Für Verpflegung ist gesorgt.</p>
                <a href="termin-details.php?id=2" class="read-more">Details anzeigen &raquo;</a>
            </article>

        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>