<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL); 
$pageTitle = "Aktuelle News"; 
include 'includes/header.php'; 
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
            <h1>Aktuelle Neuigkeiten</h1>

            <article class="news-card">
                <small>15. März 2026</small>
                <h2>Saisonstart der ersten Mannschaft</h2>
                <p>Am kommenden Wochenende beginnt die neue Spielzeit. Wir freuen uns auf zahlreiche Unterstützung. Es wird ein spannendes Spiel gegen den Tabellenführer erwartet...</p>
                <a href="news-details.php?id=101" class="read-more">Ganzen Artikel lesen &raquo;</a>
            </article>

            <article class="news-card">
                <small>10. März 2026</small>
                <h2>Mitgliederversammlung verschoben</h2>
                <p>Bitte beachtet, dass die jährliche Mitgliederversammlung aus organisatorischen Gründen verlegt wurde. Der neue Termin ist der 05. April 2026 um 19:00 Uhr im Vereinsheim...</p>
                <a href="news-details.php?id=102" class="read-more">Ganzen Artikel lesen &raquo;</a>
            </article>

            <article class="news-card">
                <small>9. März 2026</small>
                <h2>Mitgliederversammlung verschoben</h2>
                <p>Bitte beachtet, dass die jährliche Mitgliederversammlung aus organisatorischen Gründen verlegt wurde. Der neue Termin ist der 05. April 2026 um 19:00 Uhr im Vereinsheim...</p>
                <a href="news-details.php?id=102" class="read-more">Ganzen Artikel lesen &raquo;</a>
            </article>
            
            
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>