<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. DATENBANK EINBINDEN
require_once __DIR__ . '/../../db.php';

// 2. ID AUS DER URL HOLEN UND PRÜFEN
$news_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($news_id <= 0) {
    die("Ungültige News-ID.");
}

// 3. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main>
    <div class="action-bar">
        <a href="news.php" class="btn btn-secondary">&larr; Zurück zur Übersicht</a>
    </div>

    <?php
    try {
        // A) Die spezifische News abfragen
        $sql = "SELECT titel, inhalt, erstellt_am FROM news WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $news_id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($news) {
            echo "<article class='content-tile'>";

            // Titel und Datum
            echo "<h2 style='margin-bottom: 5px;'>" . htmlspecialchars($news['titel']) . "</h2>";
            echo "<small style='color: #6b7280; display: block; margin-bottom: 20px;'>Veröffentlicht am: " . date('d.m.Y H:i', strtotime($news['erstellt_am'])) . "</small>";

            // B) Der Hauptinhalt vom CKEditor
            echo "<div class='news-content'>";
            echo $news['inhalt'];
            echo "</div>";

            // =================================================================
            // HIER WAR DER FEHLER: Dieser Block hat gefehlt!
            // C) Die hochgeladenen Bildergalerie abfragen und in $bilder speichern
            // =================================================================
            $sqlBilder = "SELECT bild_pfad FROM news_bilder WHERE news_id = :id ORDER BY id ASC";
            $stmtBilder = $pdo->prepare($sqlBilder);
            $stmtBilder->execute([':id' => $news_id]);
            $bilder = $stmtBilder->fetchAll(PDO::FETCH_COLUMN);

            // D) Wenn es Bilder gibt, rendern wir sie als klickbare Thumbnails
            if (count($bilder) > 0) {
                echo "<hr style='border: 0; border-top: 1px solid #eee; margin: 30px 0;'>";
                echo "<h3>Galerie (zum Vergrößern klicken)</h3>";

                echo "<div class='news-gallery'>";
                foreach ($bilder as $pfad) {
                    echo "<img src='" . htmlspecialchars($pfad) . "' alt='Bild zur News' class='news-thumbnail'>";
                }
                echo "</div>";
            }

            echo "</article>";

        } else {
            echo "<div class='content-tile alert-error'>Diese News existiert leider nicht (mehr).</div>";
        }

    } catch (PDOException $e) {
        echo "<div class='content-tile alert-error'>Fehler beim Laden der News: " . $e->getMessage() . "</div>";
    }
    ?>

    <div id="imageLightbox" class="news-lightbox-modal">
        <span class="news-lightbox-close">&times;</span>
        <div class="news-lightbox-content">
            <img class="news-lightbox-image" id="lightboxImage" alt="Vergrößerte Ansicht">
        </div>
    </div>

</main>

<?php
require_once __DIR__ . '/../../templates/footer.php';
?>