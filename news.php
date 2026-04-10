<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL); 
include 'db.php'; // Lädt deine Zugangsdaten und die PDO-Verbindung
$pageTitle = "Aktuelle News"; 
include 'includes/header.php'; 
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
            <h1>Aktuelle News</h1>
            
            <?php
            // 1. Abfrage an die Datenbank senden (Neueste zuerst)
            $stmt = $pdo->query("SELECT * FROM news ORDER BY datum DESC");
            $newsEntries = $stmt->fetchAll();

            // 2. Prüfen, ob überhaupt News vorhanden sind
            if ($newsEntries):
                foreach ($newsEntries as $row): 
                    // Datum für deutsche Anzeige formatieren
                    $date = date("d.m.Y", strtotime($row['datum']));
            ?>
                
<article class="news-card">
    <?php if ($row['bild'] && $row['bild'] != 'default.jpg'): ?>
        <div style="width:100%; height:200px; overflow:hidden; border-radius:15px; margin-bottom:15px;">
            <img src="img/news/<?= $row['bild']; ?>" style="width:100%; height:100%; object-fit:cover;">
        </div>
    <?php endif; ?>
    
    <small><i class="fa-regular fa-clock"></i> <?= $date; ?></small>
    <h2><?= htmlspecialchars($row['titel']); ?></h2>
    </article>
            <?php 
                endforeach; 
            else: 
            ?>
                <p>Aktuell sind keine Neuigkeiten vorhanden.</p>
            <?php endif; ?>
            
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>