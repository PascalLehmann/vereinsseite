<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ID der gewählten Galerie-Kategorie abgreifen (analog zu argv in C)
$galerieId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

require_once __DIR__ . '/../../db.php';

$stmt = $pdo->prepare("SELECT name FROM galerie_kategorien WHERE id = ? AND is_deleted = 0");
$stmt->execute([$galerieId]);
$kategorieName = $stmt->fetchColumn();

if (!$kategorieName) {
    header("Location: galerie.php");
    exit;
}

$pageTitle = "Galerie: " . $kategorieName;

// 3. LAYOUT EINBINDEN
require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/navigation.php';
?>

<main class="content">
    <a href="galerie.php" class="read-more" style="margin-bottom: 25px;">
        <i class="fa-solid fa-arrow-left"></i> Zurück zur Galerie
    </a>

    <h1><?= htmlspecialchars($kategorieName) ?></h1>

    <div class="news-gallery">
        <?php
        $stmtBilder = $pdo->prepare("SELECT bild_pfad FROM galerie_bilder WHERE kategorie_id = ? AND is_deleted = 0 ORDER BY hochgeladen_am DESC");
        $stmtBilder->execute([$galerieId]);
        $bilder = $stmtBilder->fetchAll(PDO::FETCH_ASSOC);

        if (count($bilder) > 0) {
            foreach ($bilder as $row) {
                echo "<img src='" . htmlspecialchars($row['bild_pfad']) . "' class='news-thumbnail' alt='Galerie Bild'>";
            }
        } else {
            echo "<p>Aktuell sind noch keine Bilder in dieser Kategorie vorhanden.</p>";
        }
        ?>
    </div>

    <div id="imageLightbox" class="news-lightbox-modal">
        <span class="news-lightbox-close">&times;</span>
        <div class="news-lightbox-content">
            <img class="news-lightbox-image" id="lightboxImage" alt="Vergrößerte Ansicht">
        </div>
    </div>

</main>

<?php
// 3. FOOTER EINBINDEN
require_once __DIR__ . '/../../templates/footer.php';
?>