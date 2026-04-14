<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = "Bildergalerie";

// 1. DATENBANK & LAYOUT EINBINDEN
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main>
    <h2>Bildergalerie</h2>
    <p style="margin-bottom: 25px; color: #666;">Einige Eindrücke aus unserem Vereinsleben und den Spieltagen.</p>

    <div class="content-tile">
        <div class="news-gallery">
            <?php
            try {
                $stmt = $pdo->query("SELECT bild_pfad FROM galerie_bilder ORDER BY hochgeladen_am DESC");
                $bilder = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($bilder) > 0) {
                    foreach ($bilder as $row) {
                        echo "<img src='" . htmlspecialchars($row['bild_pfad']) . "' class='news-thumbnail' alt='Galerie Bild'>";
                    }
                } else {
                    echo "<p>Aktuell sind noch keine Bilder in der Galerie vorhanden.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='alert-error'>Fehler beim Laden der Galerie: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </div>

    <!-- Das unsichtbare Lightbox-Modal für die Vollbild-Ansicht (Wird von script.js gesteuert) -->
    <div id="imageLightbox" class="news-lightbox-modal">
        <span class="news-lightbox-close">&times;</span>
        <div class="news-lightbox-content">
            <img class="news-lightbox-image" id="lightboxImage" alt="Vergrößerte Ansicht">
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>