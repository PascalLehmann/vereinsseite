<?php 
// Debug-Modus: Zeigt alle Fehler direkt im Browser an
ini_set('display_errors', 1); 
error_reporting(E_ALL); 

$pageTitle = "Startseite - Mein Verein"; 

// Header enthält: <!DOCTYPE html>, <head> und den Start von <body>
include 'includes/header.php'; 
?>

<div id="page-wrapper">

    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="content">
            <h1>Willkommen beim SKV9Killer</h1>
            <p>Das Grundgerüst steht. Dies ist der Inhaltsbereich auf weißem Hintergrund mit schwarzer Schrift.</p>
            
            <?php 
                // echo $nichtExistierendeVariable; // Würde jetzt eine Warnung ausgeben
            ?>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>

</div> </body>
</html>