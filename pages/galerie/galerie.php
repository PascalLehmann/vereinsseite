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
            // =========================================================
            // HIER KOMMT DEINE LOGIK REIN, UM DIE BILDER ZU LADEN.
            // =========================================================
            
            // BEISPIEL: Alle Bilder aus einem bestimmten Ordner automatisch auslesen
            $galerieOrdner = __DIR__ . '/../../assets/img/galerie/';

            if (is_dir($galerieOrdner)) {
                // Sucht alle Bilder (jpg, png, webp) in diesem Ordner
                $bilder = glob($galerieOrdner . "*.{jpg,jpeg,png,webp,gif}", GLOB_BRACE);

                if (count($bilder) > 0) {
                    foreach ($bilder as $bildPfad) {
                        $bildName = basename($bildPfad);
                        // Die Klassen 'news-thumbnail' sorgt für den 3D-Hover und die Lightbox-Klickbarkeit!
                        echo "<img src='/assets/img/galerie/" . htmlspecialchars($bildName) . "' alt='Galerie Bild' class='news-thumbnail'>";
                    }
                } else {
                    echo "<p>Aktuell sind noch keine Bilder in der Galerie vorhanden.</p>";
                }
            } else {
                echo "<p>Der Galerie-Ordner (/assets/img/galerie/) wurde noch nicht erstellt.</p>";
            }

            /*
             * FALLS DU DIE BILDER AUS DER DATENBANK LÄDST, NUTZE DIESEN CODE-BLOCK STATTDESSEN:
             * 
             * $stmt = $pdo->query("SELECT bild_pfad FROM galerie_bilder ORDER BY id DESC");
             * while ($row = $stmt->fetch()) {
             *     echo "<img src='" . htmlspecialchars($row['bild_pfad']) . "' class='news-thumbnail' alt='Galerie'>";
             * }
             */
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