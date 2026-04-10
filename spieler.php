<?php 
ini_set('display_errors', 1); 
error_reporting(E_ALL); 
$pageTitle = "Unsere Spieler"; 
$activePage = 'spieler.php'; // Für die Fokus-Logik im Menü
include 'includes/header.php'; 
?>

<div id="page-wrapper">
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
            <h1>Unsere Mannschaft</h1>
            <p>Die aktiven Mitglieder unseres Kaders.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 30px;">
                
                <?php 
                // Simulation von 8 Spielern für Phase 1
                $spielerNamen = ["Thomas Torjäger", "Lukas Läufer", "Markus Mauer", "Sven Stopper", "Finn Flanke", "Paul Pfosten", "Denni Dribbler", "Kevin Keeper"];
                $positionen = ["Sturm", "Mittelfeld", "Abwehr", "Abwehr", "Mittelfeld", "Torwart", "Sturm", "Torwart"];
                
                for ($i = 0; $i < 8; $i++): 
                ?>
                <article class="news-card" style="text-align: center;">
                    <div class="profile-preview-circle">
                        <img src="https://via.placeholder.com/150" alt="Spieler Bild">
                    </div>
                    <h3 style="color: var(--secondary-blue);"><?php echo $spielerNamen[$i]; ?></h3>
                    <p style="font-weight: bold; margin-bottom: 10px;"><?php echo $positionen[$i]; ?></p>
                    <p style="font-size: 0.85rem; color: #777;">Spieler-ID: #<?php echo ($i + 10); ?></p>
                    <a href="mitglied-details.php?id=<?php echo ($i + 10); ?>&typ=spieler" class="read-more">Statistik</a>
                </article>
                <?php endfor; ?>

            </div>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>